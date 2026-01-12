<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
$message = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name_ar  = trim($_POST['name_ar']);
    $name_en  = trim($_POST['name_en']);
    $price    = (float)$_POST['price'];
    $desc_ar  = trim($_POST['desc_ar']);
    $desc_en  = trim($_POST['desc_en']);
    $category_id = (int)$_POST['category_id'];
    $stock_quantity = (int)$_POST['stock_quantity'];

    if (
        $name_ar === '' ||
        $name_en === '' ||
        $price <= 0 ||
        $category_id <= 0
    ) {
        $error = "جميع الحقول المطلوبة يجب ملؤها.";
    } else {

        $image = null;

        if (!empty($_FILES['image']['name'])) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image = uniqid() . "." . $ext;
            $uploadDir = "../../assests/uploads/";

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
        }

        $stmt = $pdo->prepare("
            INSERT INTO products
            (name_ar, name_en, price, description_ar, description_en, image_url, category_id, stock_quantity)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $name_ar,
            $name_en,
            $price,
            $desc_ar,
            $desc_en,
            $image,
            $category_id,
            $stock_quantity
        ]);
        $message = 'Product created successfully!';
    }
}
if ($isAjax && $message) {
    echo $message;
    exit;
}
$cats = $pdo->query("SELECT * FROM categories ORDER BY name_ar")->fetchAll();
?>

<!-- ================================
     ADD PRODUCT UI
================================ -->
<div class="product-container">

    <h2 class="title">
        <i class="fa-solid fa-plus"></i> إضافة منتج جديد
    </h2>

    <div id="formMessage" class="product-message" style="display:<?= $error ? 'block' : 'none' ?>">
        <?= $error ? htmlspecialchars($error) : '' ?>
    </div>

    <div class="form-box product">

        <form id = "addForm" action= "products/add.php" method= "POST">

            <div class="product-input">
                <input type="text" name="name_ar" required placeholder=" ">
                <label>الاسم (عربي)</label>
                <i class="fa-solid fa-box"></i>
            </div>

            <div class="product-input">
                <input type="text" name="name_en" required placeholder=" ">
                <label>Name (English)</label>
                <i class="fa-solid fa-box"></i>
            </div>

            <div class="product-input">
                <input type="number" step="0.01" name="price" required placeholder=" ">
                <label>السعر</label>
                <i class="fa-solid fa-dollar-sign"></i>
            </div>

            <div class="product-input">
                <input type="number" name="stock_quantity" value="0" min="0" placeholder=" ">
                <label>الكمية</label>
                <i class="fa-solid fa-warehouse"></i>
            </div>

            <div class="product-input ">
                <textarea name="desc_ar" placeholder=" "></textarea>
                <label>الوصف (عربي)</label>
            </div>

            <div class="product-input textarea-input">
                <textarea name="desc_en" placeholder=" "></textarea>
                <label>Description (English)</label>
            </div>

            <div class="product-input textarea-input">
                <select name="category_id" required>
                    <option value="" disabled selected hidden></option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name_ar']) ?> (<?= htmlspecialchars($c['name_en']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>التصنيف</label>
            </div>

            <div class="product-input">
                <input type="file" name="image" accept="image/*">
                <label>الصورة</label>
            </div>

            <button type="submit" class="product-btn">
                <i class="fa-regular fa-floppy-disk"></i>
                save
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