<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['status'])) {
    $allowed = ['in_progress','completed','unrepairable','cancelled'];
    $status = in_array($_POST['status'], $allowed) ? $_POST['status'] : null;
    if ($status) {
        $stmt = $pdo->prepare("UPDATE repairs SET status = ? WHERE id = ? AND technician = ?");
        $stmt->execute([$status, (int)$_POST['id'], $_SESSION['username']]);
    }
    echo 'success'; exit;
}

$tech = $_SESSION['username'];

$whereMap = [
    'active'    => "technician = ? AND status NOT IN ('completed','unrepairable','cancelled')",
    'completed' => "technician = ? AND status = 'completed'",
    'all'       => "technician = ?",
];

$filter = $_GET['filter'] ?? 'all';

// ✅ validate AFTER defining whereMap
if (!isset($whereMap[$filter])) {
    $filter = 'all';
}

$where = $whereMap[$filter];

$stmt = $pdo->prepare("SELECT * FROM repairs WHERE $where ORDER BY created_at DESC");
$stmt->execute([$tech]);
$repairs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'in_progress') AS active,
        SUM(status = 'completed') AS completed,
        SUM(status = 'unrepairable') AS unrepairable
    FROM repairs WHERE technician = ?
");
$statsStmt->execute([$tech]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

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
        <h2 class="tp-title"><i class="fa-solid fa-screwdriver-wrench"></i> My Repairs</h2>
    </div>

    <!-- Stats row -->
    <div class="tp-stats-row">
        <div class="tp-stat-card" style="--accent:#3498db">
            <span class="tp-stat-num"><?= $stats['total'] ?></span>
            <span class="tp-stat-label">Total</span>
        </div>
        <div class="tp-stat-card" style="--accent:#ff9f43">
            <span class="tp-stat-num"><?= $stats['active'] ?></span>
            <span class="tp-stat-label">Active</span>
        </div>
        <div class="tp-stat-card" style="--accent:#1dd1a1">
            <span class="tp-stat-num"><?= $stats['completed'] ?></span>
            <span class="tp-stat-label">Completed</span>
        </div>
        <div class="tp-stat-card" style="--accent:#ee5253">
            <span class="tp-stat-num"><?= $stats['unrepairable'] ?></span>
            <span class="tp-stat-label">Unrepairable</span>
        </div>
    </div>

    <!-- Filter tabs -->
    <div class="tp-filter-tabs">
        <button class="tp-tab <?= $filter==='all'      ?'active':'' ?>" onclick="loadPage('Repairs/index.php?filter=all')">All</button>
        <button class="tp-tab <?= $filter==='active'   ?'active':'' ?>" onclick="loadPage('Repairs/index.php?filter=active')">Active</button>
        <button class="tp-tab <?= $filter==='completed'?'active':'' ?>" onclick="loadPage('Repairs/index.php?filter=completed')">Completed</button>
        
    </div>

    <div class="tp-table-wrap">
        <table class="tp-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Device</th>
                    <th>Warranty</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($repairs as $r): ?>
                <tr class="tp-row">
                    <td>#<?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['customer_name']) ?></td>
                    <td><?= htmlspecialchars($r['phone']) ?></td>
                    <td><span class="tp-item-badge"><?= htmlspecialchars($r['item']) ?></span></td>
                    <td>
                        <?php if ($r['is_warranty_claim']): ?>
                            <span class="tp-badge badge-warranty"><i class="fa-solid fa-shield-halved"></i> Warranty</span>
                        <?php else: ?>
                            <span class="tp-unassigned">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                    <td>
                        <span class="tp-badge <?= $statusColors[$r['status']] ?? '' ?>">
                            <?= ucfirst(str_replace('_',' ',$r['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <div class="tp-actions">
                            <button class="tp-btn view" data-target="rep-<?= $r['id'] ?>" title="Details">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                            <?php if (in_array($r['status'], ['in_progress','pending'])): ?>
                            <select class="tp-status-select" data-id="<?= $r['id'] ?>"
                                    data-url="Repairs/update_status.php">
                                <option value="in_progress" <?= $r['status']==='in_progress'?'selected':'' ?>>In Progress</option>
                                <option value="completed"> Completed</option>
                                <option value="unrepairable"> Unrepairable</option>
                                <option value="cancelled">Cancel</option>
                            </select>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <tr class="tp-detail-row" id="rep-<?= $r['id'] ?>" style="display:none;">
                    <td colspan="8">
                        <div class="tp-detail-box">
                            <p><strong>Issue description:</strong> <?= nl2br(htmlspecialchars($r['description'] ?? '—')) ?></p>
                            <?php if ($r['damage_from_factory']): ?>
                                <p><span class="tp-badge badge-warning">Factory damage reported</span></p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($repairs)): ?>
                <tr><td colspan="8" class="tp-empty"><i class="fa-solid fa-inbox"></i> No repairs found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>