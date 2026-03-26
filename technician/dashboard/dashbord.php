<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

// Handle accept action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['repair_id'])) {
    $repair_id = (int) $_POST['repair_id'];
    $stmt = $pdo->prepare("UPDATE repairs SET status = 'in_progress', technician = ? WHERE id = ?");
    $stmt->execute([$_SESSION['username'], $repair_id]);

    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    if ($isAjax) {
        echo 'success';
        exit;
    }

    // Redirect to refresh the page (normal navigation)
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Count services
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM services");
$count_services = $stmt->fetch()['total'];

// Count orders
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM repairs");
$count_orders = $stmt->fetch()['total'];

// Count My orders
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM repairs WHERE technician = ? AND status != 'in_progress'");
$stmt->execute([$_SESSION['username']]);
$count_my_orders = $stmt->fetch()['total'];

//count repairs
$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM repairs WHERE technician = ? AND status != 'completed'");
$stmt->execute([$_SESSION['username']]);
$count_repairs = $stmt->fetch()['total'];
// Count customers
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'customer'");
$count_customers = $stmt->fetch()['total'];
$stmt = $pdo->query("
    SELECT 
        repairs.id,
        repairs.created_at,
        repairs.status,
        repairs.customer_name
    FROM repairs
    ORDER BY repairs.created_at DESC
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
            <div class="icon"><i class="fa-solid fa-headset"></i></div>
            <div class="info">
                <span>Services</span>
                <h3><?= $count_services ?></h3>
            </div>
        </div>

         <div class="admin-dash-card orders">
            <div class="icon"><i class="fa-solid fa-receipt"></i></div>
            <div class="info">
                <span>Orders</span>
                <h3><?= $count_orders ?></h3>
            </div>
        </div>

        <div class="admin-dash-card Myorders">
            <div class="icon"><i class="fa-regular fa-clipboard"></i></div>
            <div class="info">
                <span>My Orders</span>
                <h3><?= $count_my_orders ?></h3>
            </div>
        </div>

        <div class="admin-dash-card Repairs">
            <div class="icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
            <div class="info">
                <span>Repairs</span>
                <h3><?= $count_repairs ?></h3>
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent_orders as $order): ?>
                <tr>
                    <td>#<?= $order['id'] ?></td>
                    <td><?= htmlspecialchars($order['customer_name']) ?></td>
                    <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                    <td class="status <?= $order['status'] ?>">
                        <?= ucfirst($order['status']) ?>
                    </td>
                    <td>
                        <?php if ($order['status'] == 'pending'): ?>
                            <form class="ajax-form" method="post" action="dashboard/dashbord" style="display:inline;">
                                <input type="hidden" name="repair_id" value="<?= $order['id'] ?>">
                                <button type="submit" name="accept" class="btn btn-primary">Accept</button>
                            </form>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>
</div>