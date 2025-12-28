<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$categories = $pdo->query("
    SELECT *
    FROM categories
    ORDER BY name_ar
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="categories-container">

    <div class="categories-header">
        <h2 class="categories-title">
            <i class="fa-solid fa-folder-open"></i> Categories
        </h2>

        <a href="#" data-page="categories/add" class="categories-add ajax-link">
            <i class="fa-solid fa-plus"></i> Add Category
        </a>
    </div>

    <table class="categories-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Arabic</th>
                <th>French</th>
                <th>English</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php if ($categories): ?>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><?= (int)$cat['id'] ?></td>

                    <td><?= htmlspecialchars($cat['name_ar']) ?></td>

                    <td><?= htmlspecialchars($cat['name_fr']) ?></td>

                    <td><?= htmlspecialchars($cat['name_en']) ?></td>

                    <td>
                        <div class="categories-actions">
                            <a href="#"
                               class="categories-btn delete delete-category"
                               data-page="categories/delete"
                               data-id="<?= $cat['id'] ?>"
                               title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="categories-empty">
                    No categories found
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>