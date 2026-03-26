<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

$currentTech = $_SESSION['username'];

// Handle ACCEPT action (claim a pending order)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_id'])) {
    $id = (int)$_POST['accept_id'];
    $stmt = $pdo->prepare("UPDATE repairs SET status = 'in_progress', technician = ? WHERE id = ? AND status = 'pending'");
    $stmt->execute([$currentTech, $id]);
    echo 'success';
    exit;
}

// Handle STATUS UPDATE — only the assigned technician can do this
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $allowed = ['in_progress', 'completed', 'unrepairable', 'cancelled'];
    $status  = in_array($_POST['status'], $allowed) ? $_POST['status'] : null;

    if ($status) {
        if ($status === 'cancelled') {
            $stmt = $pdo->prepare("DELETE FROM repairs WHERE id = ? AND technician = ?");
            $stmt->execute([(int)$_POST['id'], $currentTech]);
        } else {
            $stmt = $pdo->prepare("UPDATE repairs SET status = ? WHERE id = ? AND technician = ?");
            $stmt->execute([$status, (int)$_POST['id'], $currentTech]);
        }
    }

    echo 'success';
    exit;
}

// Pagination
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = ($page - 1) * $limit;

$total = $pdo->query("SELECT COUNT(*) FROM repairs")->fetchColumn();
$pages = (int)ceil($total / $limit);

$stmt = $pdo->prepare("
    SELECT id, customer_name, phone, item, status, technician, created_at, description
    FROM repairs
    ORDER BY created_at DESC
    LIMIT :lim OFFSET :off
");
$stmt->bindValue(':lim',  (int)$limit,  PDO::PARAM_INT);
$stmt->bindValue(':off',  (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusColors = [
    'pending'      => 'badge-pending',
    'in_progress'  => 'badge-inprogress',
    'completed'    => 'badge-completed',
    'unrepairable' => 'badge-unrepairable',
    'cancelled'    => 'badge-cancelled',
];
?>

<div class="tp-page">
    <div class="tp-page-header">
        <h2 class="tp-title"><i class="fa-solid fa-receipt"></i> All Orders</h2>
        <span class="tp-count"><?= $total ?> total</span>
    </div>

    <div class="tp-table-wrap">
        <table class="tp-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Item</th>
                    <th>Technician</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr class="tp-row" id="row-<?= $o['id'] ?>">
                    <td>#<?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= htmlspecialchars($o['phone']) ?></td>
                    <td><span class="tp-item-badge"><?= htmlspecialchars($o['item']) ?></span></td>
                    <td><?= $o['technician'] ? htmlspecialchars($o['technician']) : '<span class="tp-unassigned">—</span>' ?></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td>
                        <span class="tp-badge <?= $statusColors[$o['status']] ?? '' ?>">
                            <?= ucfirst(str_replace('_', ' ', $o['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <div class="tp-actions">
                            <button class="tp-btn view" data-target="detail-<?= $o['id'] ?>" title="View details">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <?php if ($o['status'] === 'pending'): ?>
                                <button class="tp-btn tp-accept-btn"
                                        data-id="<?= $o['id'] ?>"
                                        title="Accept this order"
                                        style="background:#2563eb;color:#fff;border:none;padding:4px 12px;border-radius:6px;cursor:pointer;font-size:13px;">
                                    Accept
                                </button>

                            <?php elseif ($o['technician'] === $currentTech && $o['status'] === 'in_progress'): ?>
                                <select class="tp-status-select" data-id="<?= $o['id'] ?>" data-url="orders/index.php">
                                    <option value="in_progress" selected>In Progress</option>
                                    <option value="completed">Completed</option>
                                    <option value="unrepairable">Unrepairable</option>
                                    <option value="cancelled">Cancel (Delete)</option>
                                </select>

                            <?php else: ?>
                                <span style="color:#aaa;">—</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

                <tr class="tp-detail-row" id="detail-<?= $o['id'] ?>" style="display:none;">
                    <td colspan="8">
                        <div class="tp-detail-box">
                            <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($o['description'] ?? '—')) ?></p>
                            <p><strong>Created:</strong> <?= date('d M Y H:i', strtotime($o['created_at'])) ?></p>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" class="tp-empty">
                        <i class="fa-solid fa-inbox"></i> No orders found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($pages > 1): ?>
    <div class="tp-pagination">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <button class="tp-page-btn <?= $i===$page?'active':'' ?>"
                    onclick="loadPage('orders/index.php?page=<?= $i ?>')">
                <?= $i ?>
            </button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
