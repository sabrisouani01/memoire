<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../includes/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

// Fetch all categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name_ar");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>🗂️ التصنيفات</title>
    <link rel="stylesheet" href="../../assets/CSS/categories.css">
   
</head>
<body class="p-4">
    <!-- Sidebar -->
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <!-- Show Admin Username -->
        <p style="color: #ccc; font-size: 14px; text-align: center;">
            مرحباً، <strong><?= $username ?></strong>
        </p>
        
        <hr style="border-color: #495057; margin: 10px 0;">
        <a href="../users/add_admin.php">➕ إضافة مدير</a>
        <a href="../technician/add.php">➕ إضافة مصلح</a>
        <a href="../dashbord.php">الرئيسية</a>
        <a href="../products/index.php">المنتجات</a>
        <a href="../orders/index.php">الطلبيات</a>
        <a href="../customers/index.php">العملاء</a>
        <a href="../reports/sales.php">التقارير</a>
        <a href="../warranty/claims.php">الضمان</a>
        <a href="index.php" class="active">التصنيفات</a>
        <a href="../repairs/index.php">🛠️ الإصلاحات</a>
        <!-- Logout Button -->
        <a href="../auth/logout.php" class="logout">
            🔐 تسجيل الخروج
        </a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>🗂️ إدارة التصنيفات</h2>
        <a href="add.php" class="btn btn-success mb-3">➕ إضافة تصنيف</a>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>الرقم</th>
                    <th>الاسم (عربي)</th>
                    <th>Nom (Français)</th>
                    <th>Name (English)</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['id']); ?></td>
                            <td><?= htmlspecialchars($cat['name_ar']); ?></td>
                            <td><?= htmlspecialchars($cat['name_fr']); ?></td>
                            <td><?= htmlspecialchars($cat['name_en']); ?></td>
                            <td>
                                <a href="delete.php?id=<?= $cat['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا التصنيف؟')">
                                    🗑 حذف
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <strong>⚠️ لا توجد تصنيفات</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>