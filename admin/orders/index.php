<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

/* ===============================
   FETCH ORDERS + ITEMS (OPTIMIZED)
=============================== */
$stmt = $pdo->query("
    SELECT o.*, 
           u.First_name, u.Last_name, u.phone AS user_phone,
           oi.quantity, oi.unit_price,
           p.name_ar
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    ORDER BY o.id DESC
");

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===============================
   GROUP DATA
=============================== */
$groupedOrders = [];

foreach ($data as $row) {
    $id = $row['id'];

    if (!isset($groupedOrders[$id])) {
        $groupedOrders[$id] = [
            'info' => $row,
            'items' => []
        ];
    }

    if (!empty($row['name_ar'])) {
        $groupedOrders[$id]['items'][] = [
            'name_ar'   => $row['name_ar'],
            'quantity'  => $row['quantity'],
            'unit_price'=> $row['unit_price']
        ];
    }
}
?>

<div class="orders-container">

    <h2 class="orders-title">
        <i class="fa-solid fa-receipt"></i> Orders
    </h2>

    <table class="orders-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>

        <?php foreach ($groupedOrders as $order): 
            $o = $order['info'];
            $items = $order['items'];
        ?>

            <!-- MAIN ROW -->
            <tr>
                <td><?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['First_name'].' '.$o['Last_name']) ?></td>
                <td><?= number_format($o['total_amount'],2) ?> Da</td>

                <td>
                    <select class="order-status-select" data-id="<?= $o['id'] ?>">
                        <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                            <option value="<?= $s ?>" <?= $o['status']===$s?'selected':'' ?>>
                                <?= ucfirst($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>

                <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>

                <td class="orders-actions">
                    <button class="orders-btn view"
                            data-target="details-<?= $o['id'] ?>">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </td>
            </tr>

            <!-- DETAILS ROW -->
            <tr id="details-<?= $o['id'] ?>" style="display:none;">
                <td colspan="6" style="background:#f9fbff; padding:20px;">

                    <h4 style="margin-bottom:10px; color:#245bff;">
                        Products in Order #<?= $o['id'] ?>
                    </h4>

                    <strong>Phone:</strong> <?= htmlspecialchars($o['user_phone'] ?? '-') ?>
                    <br><br>

                    <table class="orders-table">
                        <tr>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>

                        <?php foreach ($items as $i): ?>
                            <tr>
                                <td><?= htmlspecialchars($i['name_ar']) ?></td>
                                <td><?= (int)$i['quantity'] ?></td>
                                <td><?= number_format($i['unit_price'],2) ?> Da</td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="3">No items found</td>
                            </tr>
                        <?php endif; ?>

                    </table>

                </td>
            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>
</div>