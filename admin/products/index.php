<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

try {
    $sql = "SELECT p.*, c.name_ar AS cat_name_ar, c.name_en AS cat_name_en 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC";
    $result = $pdo->query($sql);
    $products = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>📦 المنتجات</title>
    <link rel="stylesheet" href="../../assests/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assests/css/admin.css">
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
        <a href="../products/index.php" class="active">المنتجات</a>
        <a href="../orders/index.php">الطلبيات</a>
        <a href="../customers/index.php">العملاء</a>
        <a href="../reports/sales.php">التقارير</a>
        <a href="../warranty/claims.php">الضمان</a>
        <a href="index.php" >التصنيفات</a>
        <a href="../repairs/index.php">🛠️ الإصلاحات</a>
        <!-- Logout Button -->
        <a href="../auth/logout.php" class="logout">
            🔐 تسجيل الخروج
        </a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>📦 قائمة المنتجات</h2>
        <a href="add.php" class="btn btn-primary mb-3">➕ إضافة منتج</a>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>الرقم</th>
                    <th>الاسم (عربي)</th>
                    <th>Name (English)</th>
                    <th>السعر (دج)</th>
                    <th>الوصف (عربي)</th>
                    <th>Description (English)</th>
                    <th>التصنيف</th>
                    <th>الكمية</th>
                    <th>الصورة</th>
                    <th>تاريخ الإضافة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']); ?></td>
                            <td><?= htmlspecialchars($row['name_ar']); ?></td>
                            <td><?= htmlspecialchars($row['name_en']); ?></td>
                            <td><?= number_format($row['price'], 2); ?></td>
                            <td><?= htmlspecialchars(substr($row['description_ar'], 0, 100)); ?>...</td>
                            <td><?= htmlspecialchars(substr($row['description_en'], 0, 100)); ?>...</td>
                            <td><?= htmlspecialchars($row['cat_name_ar']); ?> (<?= htmlspecialchars($row['cat_name_en']); ?>)</td>
                            <td style="color: <?= $row['stock_quantity'] <= 5 ? 'red' : 'inherit' ?>">
                                <?= htmlspecialchars($row['stock_quantity']); ?>
                                <?= $row['stock_quantity'] <= 5 ? ' ⚠️' : '' ?>
                            </td>
                            <td>
                                <?php if (!empty($row['image_url'])): ?>
                                    <img src="../../assests/uploads/<?= htmlspecialchars(basename($row['image_url'])); ?>" width="80">
                                <?php else: ?>
                                    <span class="text-muted">لا صورة</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))); ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">✏️ تعديل</a>
                                <a href="delete.php?id=<?= $row['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا المنتج؟')">
                                    🗑 حذف
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center text-danger">
                            <strong>⚠️ لا توجد منتجات</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script src="../../assests/js/bootstrap.bundle.min.js"></script>
</body>
</html>