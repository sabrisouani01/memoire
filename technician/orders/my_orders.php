<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

$currentTech = $_SESSION['username'];

// Handle STATUS UPDATE — only this technician's own orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $allowed = ['in_progress', 'completed', 'unrepairable', 'cancelled'];
    $status  = in_array($_POST['status'], $allowed) ? $_POST['status'] : null;

    if ($status) {
        if ($status === 'cancelled') {
            // Cancel permanently removes the order from DB
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

$statusFilter = $_GET['status'] ?? 'in_progress';
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 15;
$offset = (int)(($page - 1) * $limit);

// Only show in_progress or completed orders for this technician
$allowedFilters = ['in_progress', 'completed'];
if (!in_array($statusFilter, $allowedFilters)) {
    $statusFilter = 'in_progress';
}

$params = [$currentTech, $statusFilter];

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM repairs WHERE technician = ? AND status = ?");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();
$pages = (int)ceil($total / $limit);

$sql = "
    SELECT id, customer_name, phone, item, status, created_at, description, updated_at
    FROM repairs
    WHERE technician = ? AND status = ?
    ORDER BY created_at DESC
    LIMIT $limit OFFSET $offset
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statusColors = [
    'in_progress'  => 'badge-inprogress',
    'completed'    => 'badge-completed',
    'unrepairable' => 'badge-unrepairable',
];
?>

<div class="tp-page">
    <div class="tp-page-header">
        <h2 class="tp-title"><i class="fa-regular fa-clipboard"></i> My Orders</h2>
        <span class="tp-count"><?= $total ?> assigned to you</span>
    </div>

    <!-- Filter tabs: only In Progress and Completed -->
    <div class="tp-filter-tabs">
        <button class="tp-tab <?= $statusFilter==='in_progress'?'active':'' ?>"
                onclick="loadPage('orders/my_orders.php?status=in_progress')">
            In Progress
        </button>
        <button class="tp-tab <?= $statusFilter==='completed'?'active':'' ?>"
                onclick="loadPage('orders/my_orders.php?status=completed')">
            Completed
        </button>
    </div>

    <div class="tp-table-wrap">
        <table class="tp-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Item</th>
                    <th>Received</th>
                    <th>Last Update</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr class="tp-row">
                    <td>#<?= $o['id'] ?></td>
                    <td><?= htmlspecialchars($o['customer_name']) ?></td>
                    <td><?= htmlspecialchars($o['phone']) ?></td>
                    <td><span class="tp-item-badge"><?= htmlspecialchars($o['item']) ?></span></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><?= date('d M Y H:i', strtotime($o['updated_at'])) ?></td>
                    <td>
                        <span class="tp-badge <?= $statusColors[$o['status']] ?? '' ?>">
                            <?= ucfirst(str_replace('_', ' ', $o['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <div class="tp-actions">
                            <button class="tp-btn view" data-target="mydetail-<?= $o['id'] ?>" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </button>

                            <?php if ($o['status'] === 'in_progress'): ?>
                            <select class="tp-status-select" data-id="<?= $o['id'] ?>" data-url="orders/my_orders.php">
                                <option value="in_progress" selected>In Progress</option>
                                <option value="completed">Mark Completed</option>
                                <option value="unrepairable">Unrepairable</option>
                                <option value="cancelled">Cancel (Delete)</option>
                            </select>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>

                <tr class="tp-detail-row" id="mydetail-<?= $o['id'] ?>" style="display:none;">
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
                    onclick="loadPage('orders/my_orders.php?status=<?= urlencode($statusFilter) ?>&page=<?= $i ?>')">
                <?= $i ?>
            </button>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
