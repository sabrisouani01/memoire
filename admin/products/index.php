<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$cats = $pdo->query("SELECT id, name_en FROM categories")->fetchAll();

$products = $pdo->query("
    SELECT p.*, c.name_en AS cat_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="../assests/css/products.css">
<div class="ap-page">

  <!-- Page Header -->
  <div class="ap-header">
    <div class="ap-header-left">
      <h2 class="ap-title"><i class="fa-solid fa-box-open"></i> Products</h2>
      <span class="ap-count"><?= count($products) ?> total</span>
    </div>
    <a href="#" data-page="products/add.php" class="ajax-link ap-add-btn">
      <i class="fa-solid fa-plus"></i> Add Product
    </a>
  </div>

  <!-- Filters Bar -->
  <div class="ap-filters">
    <div class="ap-search-wrap">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" id="searchInput" placeholder="Search by name…" autocomplete="off">
    </div>
    <select id="filterCategory" class="ap-select">
      <option value="">All categories</option>
      <?php foreach ($cats as $c): ?>
        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name_en']) ?></option>
      <?php endforeach; ?>
    </select>
    <select id="filterStock" class="ap-select">
      <option value="">All stock</option>
      <option value="in">In stock</option>
      <option value="out">Out of stock</option>
    </select>
  </div>

  <!-- Products Grid -->
  <div class="ap-grid" id="productsList">
    <?php foreach ($products as $p):
      $stock = (int)$p['stock_quantity'];
      $inStock = $stock > 0;
      $pct = min(100, $stock);
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
    <?php endforeach; ?>

    <?php if (empty($products)): ?>
      <div class="ap-empty">
        <i class="fa-solid fa-box-open" style="font-size:40px;color:#ddd;"></i>
        <p>No products found</p>
      </div>
    <?php endif; ?>
  </div>

</div>
