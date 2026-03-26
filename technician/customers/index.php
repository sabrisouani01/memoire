<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

/* ================================
   جلب المستخدمين (Customers فقط)
================================ */
$stmt = $pdo->prepare("
    SELECT id, username, email, First_name, Last_name, phone, created_at
    FROM users
    WHERE role = ?
    ORDER BY created_at DESC
");
$stmt->execute(['customer']);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="orders-container">

    <h2 class="orders-title">
        <i class="fa-solid fa-users"></i> Customers
    </h2>

    <table class="orders-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php if ($customers): ?>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= (int)$c['id'] ?></td>

                    <td><?= htmlspecialchars($c['First_name'].' '.$c['Last_name']) ?></td>

                    <td><?= htmlspecialchars($c['username']) ?></td>

                    <td><?= htmlspecialchars($c['email']) ?></td>

                    <td><?= htmlspecialchars($c['phone'] ?? '-') ?></td>

                    <td><?= date('Y-m-d', strtotime($c['created_at'])) ?></td>

                    <td>
                        <a href="#"
   class="orders-btn delete delete-customer"
   data-id="<?=$c['id']?>"
   title="Remove">
    <i class="fa-solid fa-trash"></i>
</a>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="orders-empty">
                    No customers found
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>