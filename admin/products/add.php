<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
$message = '';
$error   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name_ar        = trim($_POST['name_ar']        ?? '');
    $name_fr        = trim($_POST['name_fr']        ?? '');
    $name_en        = trim($_POST['name_en']        ?? '');
    $price          = (float)($_POST['price']       ?? 0);
    $desc_ar        = trim($_POST['desc_ar']        ?? '');
    $desc_fr        = trim($_POST['desc_fr']        ?? '');
    $desc_en        = trim($_POST['desc_en']        ?? '');
    $category_id    = (int)($_POST['category_id']   ?? 0);
    $stock_quantity = (int)($_POST['stock_quantity'] ?? 0);

    $colors_raw  = trim($_POST['colors_json'] ?? '');
    $colors_data = ($colors_raw !== '') ? json_decode($colors_raw, true) : [];

    if ($name_ar === '' || $name_en === '' || $price <= 0 || $category_id <= 0) {
        $error = "جميع الحقول المطلوبة يجب ملؤها.";
    } else {

        $uploadDir = __DIR__ . "/../../assests/uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $savedImages = [];
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $idx => $origName) {
                if ($origName === '') continue;
                $ext     = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $newName = uniqid('img_') . '.' . $ext;
                if (move_uploaded_file($_FILES['images']['tmp_name'][$idx], $uploadDir . $newName)) {
                    $savedImages[] = $newName;
                }
            }
        }

        $primary_image = $savedImages[0] ?? null;
        $extra_images  = count($savedImages) > 1
                            ? json_encode(array_slice($savedImages, 1))
                            : null;

        $legacy_colors = !empty($colors_data)
                            ? json_encode(array_column($colors_data, 'hex'))
                            : null;

        $stmt = $pdo->prepare("
            INSERT INTO products
              (name_ar, name_fr, name_en,
               price,
               description_ar, description_fr, description_en,
               image_url, extra_images, colors,
               category_id, stock_quantity)
            VALUES (?,?,?, ?, ?,?,?, ?,?,?, ?,?)
        ");
        $stmt->execute([
            $name_ar, $name_fr, $name_en,
            $price,
            $desc_ar, $desc_fr, $desc_en,
            $primary_image, $extra_images, $legacy_colors,
            $category_id, $stock_quantity
        ]);
        $product_id = (int)$pdo->lastInsertId();

        if ($product_id && !empty($colors_data)) {
            $stmtC = $pdo->prepare("
                INSERT INTO product_colors (product_id, color_hex, color_label, stock_quantity)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($colors_data as $c) {
                $stmtC->execute([
                    $product_id,
                    strtoupper($c['hex']  ?? '#000000'),
                    $c['label'] ?? null,
                    (int)($c['stock'] ?? 0)
                ]);
            }
        }

        $message = 'تم إضافة المنتج بنجاح!';
    }
}

if ($isAjax && $message) { echo $message; exit; }

$cats = $pdo->query("SELECT * FROM categories ORDER BY name_ar")->fetchAll();
?>
<div class="product-container">

    <h2 class="title">
        <i class="fa-solid fa-plus"></i> إضافة منتج جديد
    </h2>

    <?php if ($error): ?>
    <div class="product-message error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
    <div class="product-message success-msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="form-box product">
        <form id="addForm" action="/memoire/admin/products/add.php" method="POST" enctype="multipart/form-data">

            <div class="fields-row">
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
            </div>

            <div class="product-input">
                <input type="text" name="name_fr" placeholder=" ">
                <label>Nom (Français)</label>
                <i class="fa-solid fa-box"></i>
            </div>

            <div class="fields-row">
                <div class="product-input">
                    <input type="number" step="0.01" name="price" required placeholder=" ">
                    <label>السعر (دج)</label>
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="product-input">
                    <input type="number" name="stock_quantity" value="0" min="0" placeholder=" ">
                    <label>الكمية الإجمالية</label>
                    <i class="fa-solid fa-warehouse"></i>
                </div>
            </div>

            <div class="product-input textarea-input" style="margin-top:14px">
                <select name="category_id" required>
                    <option value="" disabled selected hidden></option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['name_ar']) ?>
                            (<?= htmlspecialchars($c['name_en']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>التصنيف</label>
            </div>

            <div class="product-input textarea-input">
                <textarea name="desc_ar" placeholder=" "></textarea>
                <label>الوصف (عربي)</label>
            </div>
            <div class="product-input textarea-input">
                <textarea name="desc_en" placeholder=" "></textarea>
                <label>Description (English)</label>
            </div>
            <div class="product-input textarea-input">
                <textarea name="desc_fr" placeholder=" "></textarea>
                <label>Description (Français)</label>
            </div>

            <!-- ══ IMAGES ══ -->
            <div class="section-card">
                <div class="section-header">
                    <i class="fa-solid fa-images"></i>
                    <span>صور المنتج</span>
                    <small>حتى 10 صور · الأولى هي الرئيسية</small>
                </div>

                <div id="imgDropzone" class="img-dropzone">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>اسحب الصور هنا أو انقر للاختيار</p>
                    <span>PNG · JPG · WEBP</span>
                    <input type="file" id="imagesInput" name="images[]"
                           accept="image/*" multiple style="display:none">
                </div>

                <div id="imgPreviewGrid" class="img-preview-grid"></div>
            </div>

            <!-- ══ COLORS ══ -->
            <div class="section-card">
                <div class="section-header">
                    <i class="fa-solid fa-palette"></i>
                    <span>ألوان المنتج مع المخزون</span>
                    <small>كل لون له كميته الخاصة</small>
                </div>

                <div class="color-add-row">
                    <input type="color" id="colorPicker" value="#245bff" title="اختر اللون">
                    <input type="text"  id="colorLabel"  placeholder="اسم اللون (اختياري)"
                           class="color-label-inp">
                    <input type="number" id="colorStock" placeholder="الكمية" min="0" value="0"
                           class="color-stock-inp">
                    <button type="button" id="addColorBtn" class="color-add-btn">
                        <i class="fa-solid fa-plus"></i> إضافة
                    </button>
                </div>

                <div class="color-presets">
                    <?php
                    $presets = ['#000000','#FFFFFF','#FF0000','#00AA00','#0000FF',
                                '#FFFF00','#FF6600','#CC00CC','#808080','#C0C0C0',
                                '#964B00','#FFB6C1','#00CED1','#FF69B4','#90EE90'];
                    foreach ($presets as $hex): ?>
                        <button type="button" class="preset-swatch"
                                data-color="<?= $hex ?>"
                                style="background:<?= $hex ?>;border:2px solid <?= in_array($hex,['#FFFFFF','#FFFF00']) ? '#ccc' : $hex ?>;"
                                title="<?= $hex ?>"></button>
                    <?php endforeach; ?>
                </div>

                <div id="selectedColors" class="selected-colors"></div>
                <input type="hidden" name="colors_json" id="colorsJsonInput">
            </div>

            <!-- ── Buttons ── -->
            <button type="submit" class="product-btn">
                <i class="fa-regular fa-floppy-disk"></i> حفظ المنتج
            </button>
            <button type="button" class="product-btn secondary ajax-link"
                    data-page="products/index.php">
                <i class="fa-solid fa-backward"></i> رجوع
            </button>

        </form>
    </div>
</div>
