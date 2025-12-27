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

$sql = "SELECT p.* FROM products p";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($products as $p):
?>
<div class="product-row">

    <div class="product-info">
        <img src="../assests/uploads/<?= htmlspecialchars($p['image_url']) ?>">
        <div>
            <strong><?= htmlspecialchars($p['name_en']) ?></strong>
            <small>ID: <?= $p['id'] ?></small>
        </div>
    </div>

    <div class="product-price">
        $<?= number_format($p['price'], 2) ?>
    </div>

    <div class="product-stock">
        <div class="progress">
            <span style="width:<?= min(100, $p['stock_quantity']) ?>%"></span>
        </div>
        <small><?= $p['stock_quantity'] ?> in stock</small>
    </div>

    <div class="product-actions">
        <a href="products/edit.php?id=<?= $p['id'] ?>">
            <i class="fa-solid fa-pen"></i>
        </a>
        <a href="products/delete.php?id=<?= $p['id'] ?>" onclick="return confirm('Delete?')">
            <i class="fa-solid fa-trash"></i>
        </a>
    </div>

</div>
<?php endforeach; ?>