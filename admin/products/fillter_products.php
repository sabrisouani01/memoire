<?php

require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$search   = $_GET['search'] ?? '';
$stock    = $_GET['stock'] ?? '';
$category = $_GET['category'] ?? '';

$where = [];
$params = [];

if ($search) {
    $where[] = "(p.name_en LIKE :search OR p.name_ar LIKE :search)";
    $params['search'] = "%$search%";
}

if ($stock === 'in') {
    $where[] = "p.stock_quantity > 0";
} elseif ($stock === 'out') {
    $where[] = "p.stock_quantity = 0";
}

if ($category) {
    $where[] = "p.category_id = :cat";
    $params['cat'] = $category;
}

$sql = "SELECT p.*, c.name_en AS cat_name FROM products p LEFT JOIN categories c ON p.category_id = c.id";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($products)):
?>
<div class="ap-empty">
    <i class="fa-solid fa-box-open" style="font-size:40px;color:#ddd;"></i>
    <p>No products found</p>
</div>
<?php else:
foreach ($products as $p):
    $stock   = (int)$p['stock_quantity'];
    $inStock = $stock > 0;
    $pct     = min(100, $stock);
?>
<div class="ap-card" data-id="<?= $p['id'] ?>">

    <!-- Product image -->
    <div class="ap-card-img-wrap">
        <img src="/memoire/assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
             alt="<?= htmlspecialchars($p['name_en']) ?>"
             class="ap-card-img">
        <span class="ap-stock-badge <?= $inStock ? 'in' : 'out' ?>">
            <?= $inStock ? 'In Stock' : 'Out of Stock' ?>
        </span>
    </div>

    <!-- Info -->
    <div class="ap-card-body">
        <div class="ap-card-meta">
            <span class="ap-card-cat"><?= htmlspecialchars($p['cat_name'] ?? '—') ?></span>
            <span class="ap-card-id">#<?= $p['id'] ?></span>
        </div>
        <h3 class="ap-card-name"><?= htmlspecialchars($p['name_en']) ?></h3>

        <div class="ap-card-price"><?= number_format($p['price'], 2) ?> <span>Da</span></div>

        <!-- Stock bar -->
        <div class="ap-stock-row">
            <div class="ap-stock-bar">
                <span style="width:<?= $pct ?>%; background:<?= $inStock ? 'linear-gradient(90deg,#4caf50,#8bc34a)' : '#ef4444' ?>"></span>
            </div>
            <small class="ap-stock-label"><?= $stock ?> units</small>
        </div>
    </div>

    <!-- Actions -->
    <div class="ap-card-actions">
        <a href="#" class="ajax-link ap-btn ap-btn-edit"
           data-page="products/edit.php" data-id="<?= $p['id'] ?>" title="Edit">
            <i class="fa-solid fa-pen"></i> Edit
        </a>
        <a href="#" class="delete-product ap-btn ap-btn-delete"
           data-page="products/delete.php" data-id="<?= $p['id'] ?>" title="Delete">
            <i class="fa-solid fa-trash"></i> Delete
        </a>
    </div>

</div>
<?php endforeach;
endif; ?>