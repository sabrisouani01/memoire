<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$cats = $pdo->query("SELECT id, name_en FROM categories")->fetchAll();

$products = $pdo->query("
    SELECT *
    FROM products
    ORDER BY created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="products-page">
    <h2 class="admin-dash-title">
        <i class="fa-solid fa-box"></i> Products
    </h2>
    <div class="products-header">

        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search product...">
        </div>

        <div class="actions">
            <select id="filterCategory">
                <option value="">All categories</option>
                <?php foreach ($cats as $c): ?>
                    <option value="<?= $c['id'] ?>">
                        <?= htmlspecialchars($c['name_en']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="filterStock">
                <option value="">All products</option>
                <option value="in">On stock</option>
                <option value="out">Out of stock</option>
            </select>

            <a href="#" data-page="products/add" class="ajax-link btn-add22">
                <i class="fa-solid fa-plus"></i> Add product
            </a>
        </div>

    </div>

    <div class="products-list" id="productsList">

        <?php foreach ($products as $p): ?>
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
                <a href="#" class="ajax-link" data-page="products/edit" data-id="<?=$p['id']?>">
                    <i class="fa-solid fa-pen" ></i>
                </a>
                <a href="#" class="delete-product" data-page="prodects/delete" data-id="<?=$p['id']?>" >
                    <i class="fa-solid fa-trash"></i>
                </a>
            </div>

        </div>
        <?php endforeach; ?>

    </div>

</div>