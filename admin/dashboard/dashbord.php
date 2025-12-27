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
?>
<h2>📊 control panel</h2>

<div class="row">
    <div class="col-md-3">
        <div class="card bg-primary">
            <div class="card-body">
                <h5>Products</h5>
                <p><?= $count_products ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-warning">
            <div class="card-body">
                <h5>orders</h5>
                <p><?= $count_orders ?></p>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-danger">
            <div class="card-body">
                <h5>customers</h5>
                <p><?= $count_customers ?></p>
            </div>
        </div>
    </div>
</div>