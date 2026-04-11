<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

$search = trim($_GET['q'] ?? '');
$params = [];
$where  = '';

if ($search !== '') {
    $where = "WHERE (name_en LIKE ? OR name_fr LIKE ? OR name_ar LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$stmt = $pdo->prepare("SELECT * FROM services $where ORDER BY created_at DESC");
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="tp-page">
    <div class="tp-page-header">
        <h2 class="tp-title"><i class="fa-solid fa-headset"></i> Services</h2>
        <div style="display:flex;align-items:center;gap:10px;">
            <span class="tp-count"><?= count($services) ?> services</span>
            <button class="tp-add-service-btn" onclick="document.getElementById('addServiceModal').style.display='flex'"
                style="background:#2563eb;color:#fff;border:none;padding:8px 18px;border-radius:10px;cursor:pointer;font-size:13px;font-weight:600;display:flex;align-items:center;gap:6px;">
                <i class="fa-solid fa-plus"></i> Add Service
            </button>
        </div>
    </div>

    <!-- Search bar -->
    <div class="tp-search-bar">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="serviceSearch" placeholder="Search services…"
               value="<?= htmlspecialchars($search) ?>"
               oninput="loadPage('services/index.php?q='+encodeURIComponent(this.value))">
    </div>

    <div class="tp-services-grid">
    <?php foreach ($services as $s): ?>
        <div class="tp-service-card <?= $s['status'] === 'inactive' ? 'inactive' : '' ?>">
            <div class="tp-service-icon" style="display:flex;justify-content:space-between;align-items:flex-start;">
                <i class="fa-solid fa-wrench"></i>
                <button class="tp-service-delete-btn" data-id="<?= $s['id'] ?>" title="Remove service"
                    style="background:#fee2e2;color:#dc2626;border:none;width:28px;height:28px;border-radius:7px;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </div>
            <div class="tp-service-body">
                <h3 class="tp-service-name"><?= htmlspecialchars($s['name_en']) ?></h3>
                <?php if ($s['name_fr']): ?>
                    <p class="tp-service-sub"><?= htmlspecialchars($s['name_fr']) ?></p>
                <?php endif; ?>
                <p class="tp-service-desc"><?= htmlspecialchars($s['description_en'] ?? '—') ?></p>
            </div>
            <div class="tp-service-footer">
                <div class="tp-service-meta">
                    <span class="tp-service-price">
                        <i class="fa-solid fa-tag"></i>
                        <?= number_format((float)$s['price'], 2) ?> DA
                    </span>
                    <?php if ($s['estimated_time']): ?>
                    <span class="tp-service-time">
                        <i class="fa-regular fa-clock"></i>
                        <?= htmlspecialchars($s['estimated_time']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <span class="tp-badge <?= $s['status']==='active' ? 'badge-completed' : 'badge-cancelled' ?>">
                    <?= ucfirst($s['status']) ?>
                </span>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($services)): ?>
        <div class="tp-empty" style="grid-column:1/-1;text-align:center;padding:40px">
            <i class="fa-solid fa-inbox fa-2x"></i><br>No services found.
        </div>
    <?php endif; ?>
    </div>
</div>

<!-- Add Service Modal -->
<div id="addServiceModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:32px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2);position:relative;">
        <button onclick="document.getElementById('addServiceModal').style.display='none'"
            style="position:absolute;top:16px;right:16px;background:#f1f5f9;border:none;width:32px;height:32px;border-radius:8px;cursor:pointer;font-size:16px;color:#64748b;">✕</button>
        <h3 style="margin:0 0 20px;color:#1e293b;font-size:18px;"><i class="fa-solid fa-plus" style="color:#2563eb;margin-right:8px;"></i>Add New Service</h3>
        <div id="addServiceMsg" style="display:none;margin-bottom:12px;padding:10px 14px;border-radius:8px;font-size:13px;"></div>
        <form id="addServiceForm" style="display:flex;flex-direction:column;gap:14px;">
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Service Name (EN) *</label>
                <input name="name_en" required placeholder="e.g. Screen Repair"
                    style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Service Name (FR)</label>
                <input name="name_fr" placeholder="e.g. Réparation écran"
                    style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Description</label>
                <textarea name="description_en" rows="3" placeholder="Describe the service…"
                    style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;resize:vertical;box-sizing:border-box;"></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Price (DA) *</label>
                    <input name="price" type="number" step="0.01" min="0" required placeholder="0.00"
                        style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Est. Time</label>
                    <input name="estimated_time" placeholder="e.g. 2h"
                        style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;">
                </div>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">Status</label>
                <select name="status" style="width:100%;margin-top:4px;padding:10px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;box-sizing:border-box;">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit"
                style="background:#2563eb;color:#fff;border:none;padding:12px;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;margin-top:4px;">
                <i class="fa-solid fa-plus"></i> Add Service
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('addServiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = e.target;
    const msg  = document.getElementById('addServiceMsg');
    const btn  = form.querySelector('button[type=submit]');
    btn.disabled = true; btn.textContent = 'Saving…';

    fetch('services/add.php', {
        method: 'POST',
        body: new FormData(form)
    })
    .then(r => r.text())
    .then(res => {
        if (res.trim() === 'success') {
            document.getElementById('addServiceModal').style.display = 'none';
            form.reset();
            loadPage('services/index.php');
        } else {
            msg.style.display = 'block';
            msg.style.background = '#fee2e2';
            msg.style.color = '#dc2626';
            msg.textContent = res || 'Something went wrong.';
        }
        btn.disabled = false; btn.textContent = '＋ Add Service';
    })
    .catch(() => {
        msg.style.display = 'block';
        msg.textContent = 'Server error.';
        btn.disabled = false; btn.textContent = '＋ Add Service';
    });
});
</script>
