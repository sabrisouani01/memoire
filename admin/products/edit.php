<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) die("ID غير صالح.");

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) die("المنتج غير موجود.");

$error = null;

if (isset($_POST['update'])) {
    $name_ar = trim($_POST['name_ar']);
    $name_en = trim($_POST['name_en']);
    $price = floatval($_POST['price']);
    $desc_ar = trim($_POST['desc_ar']);
    $desc_en = trim($_POST['desc_en']);
    $stock_quantity = intval($_POST['stock_quantity']);

    if (empty($name_ar) || empty($name_en) || $price <= 0) {
        $error = "جميع الحقول المطلوبة يجب ملؤها.";
    } else {
        $image = $product['image_url'];
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image = time() . "_" . basename($_FILES['image']['name']);
            $path = "../../assests/uploads/" . $image;
            move_uploaded_file($_FILES['image']['tmp_name'], $path);
        }

        $stmt = $pdo->prepare("UPDATE products SET 
            name_ar=?, name_en=?, price=?, description_ar=?, description_en=?, 
            image_url=?, stock_quantity=? 
            WHERE id=?");
        $stmt->execute([$name_ar, $name_en, $price, $desc_ar, $desc_en, $image, $stock_quantity, $id]);

        header("Location: index.php");
        exit;
    }
}
?>
    <div class="container">
        <div class="logo">
            <h3>🔧 Wise Technologie</h3>
        </div>

        <h2>✏️ تعديل منتج</h2>

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
                        <input type="text" name="name_ar" value="<?= htmlspecialchars($product['name_ar']) ?>" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Name (English)</label>
                        <input type="text" name="name_en" value="<?= htmlspecialchars($product['name_en']) ?>" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">السعر (دج)</label>
                        <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">الكمية في المخزن</label>
                        <input type="number" name="stock_quantity" value="<?= htmlspecialchars($product['stock_quantity']) ?>" class="form-control" min="0">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">الوصف (عربي)</label>
                        <textarea name="desc_ar" class="form-control"><?= htmlspecialchars($product['description_ar']) ?></textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">Description (English)</label>
                        <textarea name="desc_en" class="form-control"><?= htmlspecialchars($product['description_en']) ?></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">التصنيف</label>
                <select name="category_id" class="form-control" disabled>
                    <?php
                    $cats = $pdo->query("SELECT * FROM categories ORDER BY name_ar")->fetchAll();
                    foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name_ar']) ?> (<?= htmlspecialchars($c['name_en']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">تعديل التصنيف غير مدعوم حاليًا.</small>
            </div>

            <div class="form-group">
                <label class="form-label">الصورة الحالية</label><br>
                <?php if ($product['image_url']): ?>
                    <img src="../../assests/uploads/<?= htmlspecialchars(basename($product['image_url'])) ?>" width="100" alt="Current Image">
                <?php else: ?>
                    <span class="text-muted">لا صورة</span>
                <?php endif; ?>
                <br><br>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <div class="actions">
                <button type="submit" name="update" class="btn btn-warning">✅ تحديث</button>
                <a href="index.php" class="btn btn-secondary">⬅️ رجوع إلى القائمة</a>
            </div>
        </form>
    </div>