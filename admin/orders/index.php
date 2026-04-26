<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

/* ── Fetch orders with items (including color) ── */
$stmt = $pdo->query("
    SELECT o.*,
           u.First_name, u.Last_name, u.phone AS user_phone,
           oi.id AS item_id, oi.quantity, oi.unit_price, oi.selected_color,
           p.name_en, p.name_fr, p.name_ar
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    ORDER BY o.id DESC
");
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ── Group by order ── */
$groupedOrders = [];
foreach ($data as $row) {
    $id = $row['id'];
    if (!isset($groupedOrders[$id])) {
        $groupedOrders[$id] = ['info' => $row, 'items' => []];
    }
    if (!empty($row['name_en']) || !empty($row['name_ar'])) {
        $groupedOrders[$id]['items'][] = [
            'name_en'        => $row['name_en'] ?? '',
            'name_fr'        => $row['name_fr'] ?? '',
            'name_ar'        => $row['name_ar'] ?? '',
            'quantity'       => $row['quantity'],
            'unit_price'     => $row['unit_price'],
            'selected_color' => $row['selected_color'] ?? '',
        ];
    }
}

/* ── Status badge helper ── */
function statusBadge($s) {
    $map = [
        'pending'    => ['#fff8e1','#f59e0b','Pending'],
        'processing' => ['#eff6ff','#3b82f6','Processing'],
        'shipped'    => ['#f5f3ff','#7c3aed','Shipped'],
        'delivered'  => ['#ecfdf5','#10b981','Delivered'],
        'cancelled'  => ['#fef2f2','#ef4444','Cancelled'],
    ];
    $d = $map[$s] ?? ['#f3f4f6','#6b7280', ucfirst($s)];
    return "<span class='ao-badge' style='background:{$d[0]};color:{$d[1]}'>{$d[2]}</span>";
}
?>

<div class="ao-page">

  <!-- Title -->
  <div class="ao-header">
    <h2 class="ao-title"><i class="fa-solid fa-receipt"></i> Orders</h2>
    <span class="ao-count"><?= count($groupedOrders) ?> total</span>
  </div>

  <!-- Table wrapper (scroll on mobile) -->
  <div class="ao-table-wrap">
    <table class="ao-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Customer</th>
          <th class="ao-hide-sm">Phone</th>
          <th>Total</th>
          <th>Status</th>
          <th class="ao-hide-sm">Date</th>
          <th>Details</th>
        </tr>
      </thead>
      <tbody>

      <?php foreach ($groupedOrders as $order):
          $o     = $order['info'];
          $items = $order['items'];
          $itemCount = count($items);
          /* Build compact items JSON for the modal */
          $modalData = htmlspecialchars(json_encode($items), ENT_QUOTES);
      ?>

        <!-- Main row -->
        <tr class="ao-row">
          <td><span class="ao-id">#<?= $o['id'] ?></span></td>
          <td>
            <div class="ao-customer">
              <div class="ao-avatar"><?= strtoupper(mb_substr($o['First_name'], 0, 1)) ?></div>
              <div>
                <div class="ao-cname"><?= htmlspecialchars($o['First_name'].' '.$o['Last_name']) ?></div>
                <div class="ao-citems"><?= $itemCount ?> item<?= $itemCount != 1 ? 's' : '' ?></div>
              </div>
            </div>
          </td>
          <td class="ao-hide-sm ao-phone"><?= htmlspecialchars($o['user_phone'] ?? '—') ?></td>
          <td class="ao-total"><?= number_format($o['total_amount'], 2) ?> Da</td>
          <td>
            <select class="order-status-select ao-status-sel" data-id="<?= $o['id'] ?>"
                    style="border-color: <?= ['pending'=>'#f59e0b','processing'=>'#3b82f6','shipped'=>'#7c3aed','delivered'=>'#10b981','cancelled'=>'#ef4444'][$o['status']] ?? '#ddd' ?>">
              <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                <option value="<?= $s ?>" <?= $o['status']===$s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
              <?php endforeach; ?>
            </select>
          </td>
          <td class="ao-hide-sm ao-date"><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
          <td>
            <button class="ao-view-btn"
                    data-id="<?= $o['id'] ?>"
                    data-name="<?= htmlspecialchars($o['First_name'].' '.$o['Last_name'], ENT_QUOTES) ?>"
                    data-phone="<?= htmlspecialchars($o['phone'] ?? $o['user_phone'] ?? '', ENT_QUOTES) ?>"
                    data-address="<?= htmlspecialchars($o['shipping_address'] ?? '', ENT_QUOTES) ?>"
                    data-total="<?= number_format($o['total_amount'], 2) ?>"
                    data-date="<?= date('d/m/Y H:i', strtotime($o['created_at'])) ?>"
                    data-items='<?= $modalData ?>'
                    title="View details">
              <i class="fa-solid fa-eye"></i>
            </button>
          </td>
        </tr>

      <?php endforeach; ?>

      <?php if (empty($groupedOrders)): ?>
        <tr><td colspan="7" class="ao-empty">No orders found</td></tr>
      <?php endif; ?>

      </tbody>
    </table>
  </div>
</div>

<!-- ── Detail Modal ── -->
<div class="ao-modal-overlay" id="aoModal">
  <div class="ao-modal-box">

    <button class="ao-modal-close" id="aoModalClose"><i class="fa-solid fa-xmark"></i></button>

    <div class="ao-modal-head">
      <div class="ao-modal-icon"><i class="fa-solid fa-receipt"></i></div>
      <div>
        <h3 id="aoModalTitle">Order #</h3>
        <span id="aoModalDate" class="ao-modal-sub"></span>
      </div>
    </div>

    <!-- Customer info grid -->
    <div class="ao-modal-info-grid">
      <div class="ao-info-block">
        <i class="fa-solid fa-user"></i>
        <div>
          <small>Customer</small>
          <strong id="aoModalName">—</strong>
        </div>
      </div>
      <div class="ao-info-block">
        <i class="fa-solid fa-phone"></i>
        <div>
          <small>Phone</small>
          <strong id="aoModalPhone">—</strong>
        </div>
      </div>
      <div class="ao-info-block ao-info-full">
        <i class="fa-solid fa-location-dot"></i>
        <div>
          <small>Delivery Address</small>
          <strong id="aoModalAddress">—</strong>
        </div>
      </div>
    </div>

    <!-- Items table -->
    <h4 class="ao-modal-section-title"><i class="fa-solid fa-boxes-stacked"></i> Items</h4>
    <div class="ao-modal-table-wrap">
      <table class="ao-modal-table" id="aoModalItemsTable">
        <thead>
          <tr>
            <th>Product</th>
            <th>Color</th>
            <th>Qty</th>
            <th>Price</th>
            <th>Subtotal</th>
          </tr>
        </thead>
        <tbody id="aoModalItems"></tbody>
      </table>
    </div>

    <div class="ao-modal-total">
      Total: <strong id="aoModalTotal">—</strong>
    </div>

  </div>
</div>

<script>
/* ── Modal logic ── */
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.ao-view-btn');
    if (!btn) return;

    const modal = document.getElementById('aoModal');
    document.getElementById('aoModalTitle').textContent  = 'Order #' + btn.dataset.id;
    document.getElementById('aoModalDate').textContent   = btn.dataset.date || '';
    document.getElementById('aoModalName').textContent   = btn.dataset.name || '—';
    document.getElementById('aoModalPhone').textContent  = btn.dataset.phone || '—';
    document.getElementById('aoModalAddress').textContent = btn.dataset.address || '—';
    document.getElementById('aoModalTotal').textContent  = btn.dataset.total + ' Da';

    const items = JSON.parse(btn.dataset.items || '[]');
    const tbody = document.getElementById('aoModalItems');
    tbody.innerHTML = '';

    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#9ca3af;">No items</td></tr>';
    } else {
        items.forEach(function(item) {
            const subtotal = (parseFloat(item.unit_price) * parseInt(item.quantity)).toFixed(2);
            // Parse stored color: "Name (#hex)" or "#hex" or "Name"
            let colorCell = '<span style="color:#9ca3af">—</span>';
            if (item.selected_color) {
                const sc = item.selected_color;
                const hexMatch = sc.match(/#([0-9a-fA-F]{3,8})/);
                const hexVal   = hexMatch ? hexMatch[0] : null;
                // Extract label: everything before " (#..." or the full string if no hex
                const label    = sc.replace(/\s*\(#[0-9a-fA-F]{3,8}\)/, '').trim();
                const dot      = hexVal
                    ? '<span style="display:inline-block;width:13px;height:13px;border-radius:50%;background:' + hexVal + ';border:1px solid rgba(0,0,0,.15);vertical-align:middle;margin-left:5px;flex-shrink:0;"></span>'
                    : '';
                const nameSpan = label ? '<span>' + label + '</span>' : '';
                colorCell = '<span class="ao-color-chip" style="display:inline-flex;align-items:center;gap:4px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:20px;padding:3px 8px;font-size:12px;">' + dot + nameSpan + '</span>';
            }
            tbody.innerHTML += '<tr>' +
                '<td class="ao-item-name">' + (item.name_en || item.name_ar || '—') + '</td>' +
                '<td>' + colorCell + '</td>' +
                '<td><span class="ao-qty-badge">' + item.quantity + '</span></td>' +
                '<td>' + parseFloat(item.unit_price).toFixed(2) + ' Da</td>' +
                '<td><strong>' + subtotal + ' Da</strong></td>' +
                '</tr>';
        });
    }

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
});

function closeAoModal() {
    document.getElementById('aoModal').classList.remove('open');
    document.body.style.overflow = '';
}
document.getElementById('aoModalClose').addEventListener('click', closeAoModal);
document.getElementById('aoModal').addEventListener('click', function(e){
    if (e.target === this) closeAoModal();
});
document.addEventListener('keydown', function(e){ if (e.key === 'Escape') closeAoModal(); });
</script>
