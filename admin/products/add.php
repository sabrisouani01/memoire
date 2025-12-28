<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$error = null;

if (isset($_POST['save'])) {
    $name_ar = trim($_POST['name_ar']);
    $name_en = trim($_POST['name_en']);
    $price = floatval($_POST['price']);
    $desc_ar = trim($_POST['desc_ar']);
    $desc_en = trim($_POST['desc_en']);
    $category_id = intval($_POST['category_id']);
    $stock_quantity = intval($_POST['stock_quantity']);

    if (empty($name_ar) || empty($name_en) || $price <= 0 || $category_id <= 0) {
        $error = "جميع الحقول المطلوبة يجب ملؤها.";
    } else {
        $image = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = time() . "_" . basename($_FILES['image']['name']);
            $uploadDir = "../../assests/uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $image);
        }

        $stmt = $pdo->prepare("INSERT INTO products 
            (name_ar, name_en, price, description_ar, description_en, image_url, category_id, stock_quantity) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name_ar, $name_en, $price, $desc_ar, $desc_en, $image, $category_id, $stock_quantity]);

        header("Location: index.php");
        exit;
    }
}
?>
        <h2>➕ إضافة منتج جديد</h2>

        <?php if ($error): ?>
            <div class="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">الاسم (عربي)</label>
                        <input type="text" name="name_ar" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Name (English)</label>
                        <input type="text" name="name_en" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">السعر (دج)</label>
                        <input type="number" step="0.01" name="price" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">الكمية في المخزن</label>
                        <input type="number" name="stock_quantity" class="form-control" value="0" min="0">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">الوصف (عربي)</label>
                        <textarea name="desc_ar" class="form-control"></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Description (English)</label>
                        <textarea name="desc_en" class="form-control"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">التصنيف</label>
                <select name="category_id" class="form-control" required>
                    <option value="">اختر التصنيف</option>
                    <?php
                    $cats = $pdo->query("SELECT * FROM categories ORDER BY name_ar")->fetchAll();
                    foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['name_ar']) ?> (<?= htmlspecialchars($c['name_en']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">الصورة</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <div class="actions">
                <button type="submit" name="save" class="btn btn-success">💾 حفظ المنتج</button>
                <a href="#" data-page="products/index" class="ajax-links btn btn-secondary">Return</a>
            </div>
        </form>
    </div>