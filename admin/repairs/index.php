<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

// Fetch repairs with customer & product info
$stmt = $pdo->prepare("
    SELECT r.*, u.First_name, u.Last_name, u.phone as user_phone, p.name_ar as product_name
    FROM repairs r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    ORDER BY r.created_at DESC
");
$stmt->execute();
$repairs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>🛠️ الإصلاحات</title>
    <link rel="stylesheet" href="../../assests/css/admin.css">
</head>
<body>
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
        <a href="index.php" >التصنيفات</a>
        <a href="../repairs/index.php" class="active">🛠️ الإصلاحات</a>
        <!-- Logout Button -->
        <a href="../auth/logout.php" class="logout">
            🔐 تسجيل الخروج
        </a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>🛠️ إدارة الإصلاحات</h2>
        <a href="add.php" class="btn btn-success mb-3">➕ إضافة إصلاح جديد</a>

        <table class="table">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>اسم العميل</th>
                    <th>الهاتف</th>
                    <th>الجهاز</th>
                    <th>المنتج</th>
                    <th>الوصف</th>
                    <th>الفني</th>
                    <th>الحالة</th>
                    <th>مطالبة ضمان</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($repairs) > 0): ?>
                    <?php foreach ($repairs as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td><?= htmlspecialchars($r['customer_name'] ?: ($r['First_name'] . ' ' . $r['Last_name'])) ?></td>
                            <td><?= htmlspecialchars($r['phone'] ?: $r['user_phone']) ?></td>
                            <td><?= htmlspecialchars($r['item']) ?></td>
                            <td><?= htmlspecialchars($r['product_name']) ?></td>
                            <td><?= htmlspecialchars(substr($r['description'], 0, 50)) ?>...</td>
                            <td><?= htmlspecialchars($r['technician'] ?? 'غير مخصص') ?></td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 6px; 
                                     <?php 
                                        switch($r['status']) {
                                            case 'pending': echo 'background: #ffc107; color: #111;'; break;
                                            case 'in_progress': echo 'background: #17a2b8; color: white;'; break;
                                            case 'verifying': echo 'background: #6f42c1; color: white;'; break;
                                            case 'completed': echo 'background: #28a745; color: white;'; break;
                                            case 'unrepairable': echo 'background: #dc3545; color: white;'; break;
                                            case 'cancelled': echo 'background: #6c757d; color: white;'; break;
                                            default: echo 'background: #6c757d;';
                                        }
                                     ?>">
                                    
                                </span>
                            </td>
                            <td><?= $r['is_warranty_claim'] ? '✅ نعم' : '❌ لا' ?></td>
                            <td>
                                <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">✏️ تعديل</a>
                                <a href="delete.php?id=<?= $r['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الطلب؟')">
                                    🗑 حذف
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="text-center text-muted">
                            <strong>لا توجد طلبات إصلاح</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>