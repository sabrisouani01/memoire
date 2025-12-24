<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

$customer_id = $_GET['id'] ?? null;
$orders = [];
$customers = $pdo->query("SELECT id, username, email, First_name, Last_name, phone, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll();

if ($customer_id) {
    $stmt = $pdo->prepare("
        SELECT o.*, pm.method_name_ar, SUM(oi.quantity * oi.unit_price) as total
        FROM orders o
        LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$customer_id]);
    $orders = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>👥 العملاء</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
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
        <a href="index.php" class="active">العملاء</a>
        <a href="../reports/sales.php">التقارير</a>
        <a href="../warranty/claims.php">الضمان</a>
        <a href="../categories/index.php" >التصنيفات</a>
        <a href="../repairs/index.php">🛠️ الإصلاحات</a>
        <!-- Logout Button -->
        <a href="../auth/logout.php" class="logout">
            🔐 تسجيل الخروج
        </a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>👥 إدارة العملاء</h2>

        <!-- All Customers Table -->
        <h3>📋 جميع العملاء</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>الاسم</th>
                    <th>اسم المستخدم</th>
                    <th>البريد</th>
                    <th>الهاتف</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= htmlspecialchars($c['First_name'] . ' ' . $c['Last_name']) ?></td>
                            <td><?= htmlspecialchars($c['username']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?? 'غير محدد') ?></td>
                            <td><?= date('Y-m-d', strtotime($c['created_at'])) ?></td>
                            <td>
                                <a href="?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">عرض الطلبات</a>
                                <a href="delete.php?id=<?= $c['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العميل وجميع بياناته؟')">
                                    🗑 حذف الحساب
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            <strong>⚠️ لا توجد عملاء</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Order History -->
        <?php if ($customer_id): ?>
            <h3>🛒 سجل الطلبات</h3>
            <?php if (!empty($orders)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>طريقة الدفع</th>
                            <th>المجموع</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= $o['id'] ?></td>
                                <td><?= date('Y-m-d', strtotime($o['created_at'])) ?></td>
                                <td><?= htmlspecialchars($o['status']) ?></td>
                                <td><?= htmlspecialchars($o['method_name_ar'] ?? 'غير محدد') ?></td>
                                <td><?= number_format($o['total'], 2) ?> دج</td>
                                <td>
                                    <a href="../orders/view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-primary">عرض التفاصيل</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">لا توجد طلبات لهذا العميل.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>