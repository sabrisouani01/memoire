<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

/* ================================
   جلب الطلبات
================================ */
$sql = "
    SELECT o.*, 
           u.First_name, u.Last_name, u.email, u.phone AS user_phone
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.id DESC
";
$orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
                <th>Payment</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td><?= $o['id'] ?></td>

                <td><?= htmlspecialchars($o['First_name'].' '.$o['Last_name']) ?></td>

                <td><?= number_format($o['total_amount'],2) ?> Da</td>

                <!-- STATUS SELECT -->
                <td>
                    <select class="order-status-select"
                            data-id="<?= $o['id'] ?>"
                            data-current="<?= $o['status'] ?>">

                        <?php
                        $statuses = [
                            'pending'    => 'Pending',
                            'processing' => 'Processing',
                            'shipped'    => 'Shipped',
                            'delivered'  => 'Delivered',
                            'cancelled'  => 'Cancelled<i class="fa-solid fa-trash"></i>'
                        ];
                        foreach ($statuses as $key => $label):
                        ?>
                            <option value="<?= $key ?>"
                                <?= $o['status']===$key?'selected':'' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </td>

                <!-- PAYMENT -->
                <td>
                    <?php
                    $pm = $pdo->prepare("SELECT method_name_ar FROM payment_methods WHERE id=?");
                    $pm->execute([$o['payment_method_id']]);
                    echo htmlspecialchars($pm->fetchColumn() ?? '-');
                    ?>
                </td>

                <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>

                <!-- ACTIONS -->
                <td class="orders-actions">

                    <button class="orders-btn view"
                            data-id="<?= $o['id'] ?>">
                        <i class="fa-solid fa-eye"></i>
                    </button>

                    <?php if ($o['status']==='cancelled'): ?>
                        <a href="delete.php?id=<?= $o['id'] ?>"
                           onclick="return confirm('Remover the order?')"
                           class="orders-btn delete">
                           <i class="fa-solid fa-trash"></i>
                        </a>
                    <?php endif; ?>

                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ================================
   VIEW MODAL
================================ -->
<div class="order-modal" id="orderModal">
    <div class="order-modal-box">
        <span class="close-modal">&times;</span>
        <div id="orderModalContent">Loading</div>
    </div>
</div>

<script src="orders.js"></script>