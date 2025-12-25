<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

$claims = $pdo->query("
    SELECT r.*, u.username, u.phone, p.name_ar, c.name_ar AS cat_name 
    FROM repairs r
    JOIN users u ON r.user_id = u.id
    JOIN products p ON r.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE r.is_warranty_claim = 1 AND r.status = 'verifying'
")->fetchAll();

$technicians = $pdo->query("SELECT username, First_name, Last_name FROM users WHERE role = 'technician'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>🛡️ مطالبات الضمان</title>
    <link rel="stylesheet" href="../../assests/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <p>مرحباً، <strong><?= $_SESSION['username'] ?></strong></p>
        <hr>
        <a href="../dashbord.php">الرئيسية</a>
        <a href="rules.php">⚖️ القواعد</a>
        <a href="claims.php" class="active">🛡️ المطالبات</a>
        <a href="../auth/logout.php" class="logout">
            🔐 تسجيل الخروج
        </a>
    </div>

    <div class="content">
        <h2>🛡️ مطالبات الضمان</h2>
        <?php foreach ($claims as $c): ?>
            <div style="background:white;padding:20px;margin:10px 0;border-radius:10px;">
                <p><strong>العميل:</strong> <?= $c['customer_name'] ?> (<?= $c['phone'] ?>)</p>
                <p><strong>المنتج:</strong> <?= $c['name_ar'] ?> (<?= $c['cat_name'] ?>)</p>
                <p><strong>الوصف:</strong> <?= htmlspecialchars($c['description']) ?></p>
                <form method="post">
                    <input type="hidden" name="repair_id" value="<?= $c['id'] ?>">
                    <select name="technician" required>
                        <option value="">اختر فنياً</option>
                        <?php foreach ($technicians as $t): ?>
                            <option value="<?= $t['username'] ?>"><?= $t['First_name'] ?> <?= $t['Last_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">إرسال للتحقق</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <?php
    if ($_POST['repair_id'] ?? null) {
        $pdo->prepare("UPDATE repairs SET technician = ?, status = 'verifying' WHERE id = ?")
            ->execute([$_POST['technician'], (int)$_POST['repair_id']]);
        echo "<script>location.reload();</script>";
    }
    ?>
</body>
</html>