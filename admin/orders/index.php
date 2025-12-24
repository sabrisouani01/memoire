<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../includes/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

// Fetch all orders with customer info
$sql = "SELECT o.*, u.First_name, u.Last_name, u.username, u.email, u.phone as user_phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.id DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>📑 الطلبيات</title>
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
        <a href="../orders/index.php" class="active">الطلبيات</a>
        <a href="../customers/index.php">العملاء</a>
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
        <h2>📑 إدارة الطلبيات</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>العميل</th>
                    <th>البريد</th>
                    <th>هاتف العميل</th>
                    <th>المجموع (دج)</th>
                    <th>الحالة</th>
                    <th>طريقة الدفع</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?= htmlspecialchars($o['id']) ?></td>
                            <td><?= htmlspecialchars($o['First_name'] . ' ' . $o['Last_name']) ?></td>
                            <td><?= htmlspecialchars($o['email']) ?></td>
                            <td><?= htmlspecialchars($o['phone'] ?? $o['user_phone']) ?></td>
                            <td><?= number_format($o['total_amount'], 2) ?></td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 6px; background: #ffc107; color: #111;">
                                    <?= htmlspecialchars($o['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $method = $pdo->prepare("SELECT method_name_ar FROM payment_methods WHERE id = ?");
                                $method->execute([$o['payment_method_id']]);
                                $row = $method->fetch();
                                echo htmlspecialchars($row['method_name_ar'] ?? 'غير محدد');
                                ?>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
                            <td>
                                <a href="view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-primary">👁️ عرض</a>
                                <a href="edit.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-warning">✏️ تعديل</a>
                                <a href="delete.php?id=<?= $o['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الطلب؟')">
                                    🗑 حذف
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <strong>لا توجد طلبيات</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>