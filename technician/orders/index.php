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
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="tp-count"><?= $total ?> total</span>
            <button onclick="document.getElementById('addOrderModal').style.display='flex'"
                style="background:#2563eb;color:#fff;border:none;padding:8px 18px;border-radius:10px;cursor:pointer;font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-plus"></i> Add Order
            </button>
        </div>
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

<!-- Add Order Modal — submit handled by order.js (inline scripts don't run in innerHTML) -->
<div id="addOrderModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:32px;width:100%;max-width:500px;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative;max-height:90vh;overflow-y:auto;">
        <button onclick="document.getElementById('addOrderModal').style.display='none'"
            style="position:absolute;top:16px;right:16px;background:#f1f5f9;border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:16px;color:#64748b;">✕</button>
        <h3 style="margin:0 0 20px;color:#1e293b;font-size:18px;"><i class="fa-solid fa-plus" style="color:#2563eb;margin-right:8px;"></i>Add New Order</h3>
        <div id="addOrderMsg" style="display:none;margin-bottom:12px;padding:10px 14px;border-radius:8px;font-size:13px;"></div>
        <form id="addOrderForm" style="display:flex;flex-direction:column;gap:14px;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Customer Name *</label>
                <input name="customer_name" required placeholder="Full name"
                    style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Phone *</label>
                <input name="phone" required placeholder="e.g. 0550 123 456"
                    style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Item / Device *</label>
                <select name="item" required
    style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;transition:border-color .2s;background:#fff;cursor:pointer;"
    onfocus="this.style.borderColor='#2563eb'"
    onblur="this.style.borderColor='#e2e8f0'">
    
    <option value="">Select device</option>
    <option value="phone">Phone</option>
    <option value="laptop">Laptop</option>
    <option value="tablet">Tablet</option>
    <option value="other">Other</option>
</select>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Description / Issue</label>
                <textarea name="description" rows="3" placeholder="Describe the problem…"
                    style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;resize:vertical;box-sizing:border-box;font-family:inherit;"></textarea>
            </div>
            <button type="submit" id="addOrderSubmitBtn"
                style="background:#2563eb;color:#fff;border:none;padding:12px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;margin-top:4px;">
                <i class="fa-solid fa-plus"></i> Create Order
            </button>
        </form>
    </div>
</div>
