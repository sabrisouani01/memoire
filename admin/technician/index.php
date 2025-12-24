<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

// Get all technicians and count their repairs
$sql = "SELECT u.username, u.First_name, u.Last_name, u.phone,
               COUNT(r.id) as repair_count
        FROM users u
        LEFT JOIN repairs r ON u.username = r.technician
        WHERE u.role = 'technician'
        GROUP BY u.id
        ORDER BY repair_count DESC";
$technicians = $pdo->query($sql)->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>🔧 الفنيون</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <p>مرحباً، <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        <hr>
        <a href="add.php">➕ إضافة فني</a>
        <a href="index.php" class="active">قائمة الفنيين</a>
        <a href="../dashboard.php">الرئيسية</a>
        <a href="../repairs/index.php">الإصلاحات</a>
        <a href="../../auth/logout.php" class="logout">🔐 تسجيل الخروج</a>
    </div>

    <div class="content">
        <h2>🔧 قائمة الفنيين</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>اسم المستخدم</th>
                    <th>الهاتف</th>
                    <th>عدد الطلبات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($technicians as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['First_name'] . ' ' . $t['Last_name']) ?></td>
                        <td><?= htmlspecialchars($t['username']) ?></td>
                        <td><?= htmlspecialchars($t['phone'] ?? 'غير محدد') ?></td>
                        <td>
                            <span style="padding: 6px 12px; border-radius: 20px; 
                                  background: <?= $t['repair_count'] > 0 ? '#28a745' : '#6c757d' ?>;
                                  color: white;">
                                <?= $t['repair_count'] ?>
                            </span>
                        </td>
                        <td>
                            <a href="../repairs/index.php?technician=<?= $t['username'] ?>" 
                               class="btn btn-sm btn-primary">عرض الطلبات</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>