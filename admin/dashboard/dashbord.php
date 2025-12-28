<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

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
$stmt = $pdo->query("
    SELECT 
        orders.id,
        orders.created_at,
        orders.status,
        users.username
    FROM orders
    JOIN users ON orders.user_id = users.id
    ORDER BY orders.created_at DESC
    LIMIT 5
");

$recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="admin-dashboard">

    <h2 class="admin-dash-title">
        <i class="fa-solid fa-chart-line"></i> Dashboard
    </h2>

    <div class="admin-dash-cards">

        <div class="admin-dash-card products">
            <div class="icon"><i class="fa-solid fa-box"></i></div>
            <div class="info">
                <span>Products</span>
                <h3><?= $count_products ?></h3>
            </div>
        </div>

        <div class="admin-dash-card orders">
            <div class="icon"><i class="fa-solid fa-cart-shopping"></i></div>
            <div class="info">
                <span>Orders</span>
                <h3><?= $count_orders ?></h3>
            </div>
        </div>

        <div class="admin-dash-card customers">
            <div class="icon"><i class="fa-solid fa-users"></i></div>
            <div class="info">
                <span>Customers</span>
                <h3><?= $count_customers ?></h3>
            </div>
        </div>

    </div>
    <div class="admin-dashboard-orders">

    <h3 class="orders-title">
        <i class="fa-solid fa-clock-rotate-left"></i>
        Latest Orders
    </h3>

    <table class="orders-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                    <td class="status <?= $order['status'] ?>">
                        <?= ucfirst($order['status']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
</div>

