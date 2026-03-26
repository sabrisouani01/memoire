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
        <span class="tp-count"><?= count($services) ?> services</span>
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
            <div class="tp-service-icon">
                <i class="fa-solid fa-wrench"></i>
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