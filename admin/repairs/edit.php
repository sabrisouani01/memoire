<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

$id = $_GET['id'] ?? null;
if (!$id) die("رقم الطلب غير موجود.");

// Get repair
$stmt = $pdo->prepare("SELECT * FROM repairs WHERE id = ?");
$stmt->execute([$id]);
$repair = $stmt->fetch();

if (!$repair) die("الطلب غير موجود.");

// Get all technicians
$technicians = $pdo->query("SELECT username, First_name, Last_name FROM users WHERE role = 'technician'")->fetchAll();

// Get all products for warranty claim
$products = $pdo->query("SELECT id, name_ar FROM products")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_name = trim($_POST['customer_name']);
    $phone = trim($_POST['phone']);
    $item = trim($_POST['item']);
    $description = trim($_POST['description']);
    $technician = $_POST['technician'] ?? null;
    $status = $_POST['status'];
    $product_id = (int)($_POST['product_id'] ?? 0);
    $is_warranty_claim = isset($_POST['is_warranty_claim']) ? 1 : 0;

    $stmt = $pdo->prepare("UPDATE repairs 
        SET customer_name = ?, phone = ?, item = ?, description = ?, technician = ?, status = ?, product_id = ?, is_warranty_claim = ? 
        WHERE id = ?");
    $stmt->execute([
        $customer_name, $phone, $item, $description, $technician, $status, $product_id, $is_warranty_claim, $id
    ]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>✏️ تعديل الإصلاح #<?= $id ?></title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <p>مرحباً، <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        <hr>
        <a href="index.php">العودة إلى القائمة</a>
        <a href="../../auth/logout.php" class="logout">🔐 تسجيل الخروج</a>
    </div>

    <div class="content">
        <h2>✏️ تعديل طلب الإصلاح #<?= $id ?></h2>

        <form method="post" style="max-width: 700px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>اسم العميل</label>
                    <input type="text" name="customer_name" value="<?= htmlspecialchars($repair['customer_name']) ?>" class="form-control" required>
                </div>
                <div>
                    <label>رقم الهاتف</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($repair['phone']) ?>" class="form-control" required>
                </div>
            </div>

            <div style="margin-top: 15px;">
                <label>نوع الجهاز (مثل: هاتف، لابتوب)</label>
                <input type="text" name="item" value="<?= htmlspecialchars($repair['item']) ?>" class="form-control" required>
            </div>

            <div style="margin-top: 15px;">
                <label>وصف المشكلة</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($repair['description']) ?></textarea>
            </div>

            <div style="margin-top: 15px;">
                <label>تعيين فني</label>
                <select name="technician" class="form-control">
                    <option value="">غير مخصص</option>
                    <?php foreach ($technicians as $t): ?>
                        <option value="<?= $t['username'] ?>" <?= ($repair['technician'] == $t['username']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['First_name'] . ' ' . $t['Last_name']) ?> (<?= $t['username'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-top: 15px;">
                <label>المنتج (إذا كانت مطالبة ضمان)</label>
                <select name="product_id" class="form-control">
                    <option value="">اختر منتجاً</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $repair['product_id'] == $p['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['name_ar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-top: 10px;">
                <label>
                    <input type="checkbox" name="is_warranty_claim" <?= $repair['is_warranty_claim'] ? 'checked' : '' ?>>
                    هذه مطالبة ضمان
                </label>
            </div>

            <div style="margin-top: 15px;">
                <label>الحالة</label>
                <select name="status" class="form-control" required>
                    <option value="pending" <?= $repair['status'] === 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
                    <option value="in_progress" <?= $repair['status'] === 'in_progress' ? 'selected' : '' ?>>قيد الإصلاح</option>
                    <option value="verifying" <?= $repair['status'] === 'verifying' ? 'selected' : '' ?>>قيد التحقق</option>
                    <option value="completed" <?= $repair['status'] === 'completed' ? 'selected' : '' ?>>مكتمل</option>
                    <option value="unrepairable" <?= $repair['status'] === 'unrepairable' ? 'selected' : '' ?>>غير قابل للإصلاح</option>
                    <option value="cancelled" <?= $repair['status'] === 'cancelled' ? 'selected' : '' ?>>ملغى</option>
                </select>
            </div>

            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-success">💾 حفظ التغييرات</button>
                <a href="index.php" class="btn btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
</body>
</html>