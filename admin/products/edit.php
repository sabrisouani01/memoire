<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
          && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

/* ================================
   التحقق من id
================================ */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    echo "<div class='product-message'>id غير صالح</div>";
    exit;
}

/* ================================
   جلب المنتج
================================ */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "<div class='product-message'>المنتج غير موجود</div>";
    exit;
}

/* ================================
   جلب التصنيفات
================================ */
$cats = $pdo->query("SELECT * FROM categories ORDER BY name_ar")->fetchAll();

/* ================================
   تحديث المنتج
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {

    $name_ar  = trim($_POST['name_ar']);
    $name_en  = trim($_POST['name_en']);
    $price    = (float)$_POST['price'];
    $stock    = (int)$_POST['stock_quantity'];
    $cat_id   = (int)$_POST['category_id'];

    $image = $product['image_url'];

    if (!empty($_FILES['image']['name'])) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $newName = uniqid() . "." . $ext;
        $uploadPath = "../../assests/uploads/" . $newName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $image = $newName;
        }
    }

    $update = $pdo->prepare("
        UPDATE products SET
            name_ar = ?,
            name_en = ?,
            price = ?,
            stock_quantity = ?,
            category_id = ?,
            image_url = ?
        WHERE id = ?
    ");

    $update->execute([
        $name_ar,
        $name_en,
        $price,
        $stock,
        $cat_id,
        $image,
        $id
    ]);

    if ($isAjax) {
        echo "<div class='product-message'>success: product updated</div>";
        exit;
    }
}
?>

<!-- ================================
     EDIT PRODUCT UI
================================ -->
<div class="product-container">

    <h2 class="title">
        <i class="fa-solid fa-pen"></i> Edit Products
    </h2>

    <div id="formMessage" class="product-message" style="display:none"></div>

    <div class="form-box product">

        <form id="addForm"
              method="post"
              action="products/edit.php?id=<?= $product['id'] ?>"
              enctype="multipart/form-data">

            <div class="product-input">
                <input type="text"
                       name="name_ar"
                       value="<?= htmlspecialchars($product['name_ar']) ?>"
                       required
                       placeholder=" ">
                <label>Name (arabic)</label>
            </div>

            <div class="product-input">
                <input type="text"
                       name="name_en"
                       value="<?= htmlspecialchars($product['name_en']) ?>"
                       required
                       placeholder=" ">
                <label>Name (English)</label>
            </div>

            <div class="product-input">
                <input type="number"
                       step="0.01"
                       name="price"
                       value="<?= $product['price'] ?>"
                       required
                       placeholder=" ">
                <label>Price</label>
            </div>

            <div class="product-input">
                <input type="number"
                       name="stock_quantity"
                       value="<?= $product['stock_quantity'] ?>"
                       required
                       placeholder=" ">
                <label>Quantity</label>
            </div>

            <!-- التصنيف (عرض فقط) -->
            <div class="product-input">
                <select disabled><?php foreach ($cats as $c): ?>
                        <option <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name_ar']) ?>
                            (<?= htmlspecialchars($c['name_en']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>category
                </label>

                <input type="hidden"
                       name="category_id"
                       value="<?= (int)$product['category_id'] ?>">
            </div>

            <!-- الصورة -->
            <div class="product-input">
                <label>Current Image</label><br>

                <?php if ($product['image_url']): ?>
                    <img src="../assests/uploads/<?= htmlspecialchars($product['image_url']) ?>"
                         width="120">
                <?php else: ?>
                    <span class="text-muted">No picture</span>
                <?php endif; ?>

                <br><br>
                <input type="file"
                       name="image"
                       accept="image/*">
            </div>

            <button type="submit"
                    name="update"
                    class="product-btn">
                <i class="fa-solid fa-file-pen"></i> 
                update
            </button>

            <button type="button"
                    class="product-btn secondary ajax-link"
                    data-page="products/index">
                <i class="fa-solid fa-backward"></i>
                back
            </button>

        </form>
    </div>
</div>