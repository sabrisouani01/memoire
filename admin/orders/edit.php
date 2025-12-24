<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../includes/db_connect.php";

$id = $_GET['id'] ?? null;
if (!$id) die("رقم الطلب غير موجود.");

$stmt = $pdo->prepare("SELECT o.*, u.First_name, u.Last_name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) die("الطلب غير موجود.");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل الطلب #<?= $id ?></title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <p>مرحباً، <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        <hr>
        <a href="index.php">الطلبيات</a>
        <a href="../../auth/logout.php" class="logout">🔐 تسجيل الخروج</a>
    </div>

    <div class="content">
        <h2>تعديل حالة الطلب #<?= $id ?></h2>
        <form method="post">
            <label>العميل: <strong><?= htmlspecialchars($order['First_name'] . ' ' . $order['Last_name']) ?></strong></label><br><br>
            <label>الحالة الحالية: <strong><?= htmlspecialchars($order['status']) ?></strong></label><br><br>
            <label for="status">تحديث الحالة:</label>
            <select name="status" id="status" class="form-control" required>
                <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>قيد الانتظار</option>
                <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>قيد المعالجة</option>
                <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>تم الشحن</option>
                <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>تم التسليم</option>
                <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>ملغى</option>
            </select>
            <br><br>
            <button type="submit" class="btn btn-success">💾 حفظ التغييرات</button>
            <a href="index.php" class="btn btn-secondary">إلغاء</a>
        </form>
    </div>
</body>
</html>