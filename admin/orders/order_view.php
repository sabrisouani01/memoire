<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT o.*, u.First_name, u.Last_name, u.email, u.phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.id=?
");
$stmt->execute([$id]);
$order = $stmt->fetch();

$item_stmt = $pdo->prepare("
    SELECT oi.*, p.name_ar
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id=?
");
$item_stmt->execute([$id]);
$items = $item_stmt->fetchAll();
?>

<h3>الطلب #<?= $id ?></h3>
<p><strong>العميل:</strong> <?= htmlspecialchars($order['First_name'].' '.$order['Last_name']) ?></p>
<p><strong>الهاتف:</strong> <?= htmlspecialchars($order['phone'] ?? '-') ?></p>

<table class="orders-table">
    <tr>
        <th>المنتج</th><th>الكمية</th><th>السعر</th>
    </tr>
    <?php foreach($items as $i): ?>
    <tr>
        <td><?= htmlspecialchars($i['name_ar']) ?></td>
        <td><?= $i['quantity'] ?></td>
        <td><?= number_format($i['unit_price'],2) ?> دج</td>
    </tr>
    <?php endforeach; ?>
</table>