<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

include "../include/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

// Count products
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM products");
$count_products = $stmt->fetch()['total'];

// Count services
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM services");
$count_services = $stmt->fetch()['total'];

// Count orders
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM orders");
$count_orders = $stmt->fetch()['total'];

// Count customers
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
$count_customers = $stmt->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>📊 لوحة التحكم</title>
   <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
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
        <a href="users/add_admin.php">➕ إضافة مدير</a>
        <a href="technician/add.php">➕ إضافة مصلح</a>
        <a href="dashbord.php" class="active">الرئيسية</a>
        <a href="products/index.php">المنتجات</a>
        <a href="orders/index.php">الطلبيات</a>
        <a href="customers/index.php">العملاء</a>
        <a href="reports/sales.php">التقارير</a>
        <a href="warranty/claims.php">الضمان</a>
        <a href="categories/index.php">التصنيفات</a>
        <a href="repairs/index.php">🛠️ الإصلاحات</a>
        <!-- Logout Button -->
        <a href="../auth/logout.php" class="logout">
            🔐 تسجيل الخروج
        </a>
    </div>

    <!-- Main Content -->
    <div class="content">
        <h2>📊 لوحة التحكم</h2>
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary">
                    <div class="card-body">
                        <h5>المنتجات</h5>
                        <p><?= $count_products ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning">
                    <div class="card-body">
                        <h5>الطلبيات</h5>
                        <p><?= $count_orders ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger">
                    <div class="card-body">
                        <h5>العملاء</h5>
                        <p><?= $count_customers ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>