<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$isAjax  = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
$message = '';
$error   = null;

/* ════════════════════════════════════════════
   Validate product id
════════════════════════════════════════════ */
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { echo "<div class='product-message error-msg'>id غير صالح</div>"; exit; }

/* ════════════════════════════════════════════
   Fetch product
════════════════════════════════════════════ */
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$product) { echo "<div class='product-message error-msg'>المنتج غير موجود</div>"; exit; }

/* ════════════════════════════════════════════
   Fetch existing colors for this product
════════════════════════════════════════════ */
$stmtColors = $pdo->prepare("SELECT * FROM product_colors WHERE product_id = ? ORDER BY id");
$stmtColors->execute([$id]);
$existingColors = $stmtColors->fetchAll(PDO::FETCH_ASSOC);

/* ════════════════════════════════════════════
   Fetch categories
════════════════════════════════════════════ */
$cats = $pdo->query("SELECT * FROM categories ORDER BY name_ar")->fetchAll();

/* ════════════════════════════════════════════
   POST – Update product
════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name_ar  = trim($_POST['name_ar']  ?? '');
    $name_fr  = trim($_POST['name_fr']  ?? '');
    $name_en  = trim($_POST['name_en']  ?? '');
    $price    = (float)($_POST['price'] ?? 0);
    $stock    = (int)($_POST['stock_quantity'] ?? 0);
    $cat_id   = (int)($_POST['category_id']   ?? 0);
    $desc_ar  = trim($_POST['desc_ar']  ?? '');
    $desc_fr  = trim($_POST['desc_fr']  ?? '');
    $desc_en  = trim($_POST['desc_en']  ?? '');

    $uploadDir = __DIR__ . "/../../assests/uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    /* ── Primary image ──────────────────────────────────────── */
    $primary_image = $product['image_url'];
    if (!empty($_FILES['primary_image']['name'])) {
        $ext     = strtolower(pathinfo($_FILES['primary_image']['name'], PATHINFO_EXTENSION));
        $newName = uniqid('img_') . '.' . $ext;
        if (move_uploaded_file($_FILES['primary_image']['tmp_name'], $uploadDir . $newName)) {
            // optionally delete old file:
            // if ($product['image_url'] && file_exists($uploadDir.$product['image_url'])) unlink($uploadDir.$product['image_url']);
            $primary_image = $newName;
        }
    }

    /* ── Extra images – keep existing, maybe delete some, add new ── */
    $existingExtras = json_decode($product['extra_images'] ?? '[]', true) ?: [];

    // Images the admin wants to DELETE (sent as JSON array of filenames)
    $toDelete = json_decode($_POST['delete_images'] ?? '[]', true) ?: [];
    foreach ($toDelete as $del) {
        $existingExtras = array_filter($existingExtras, fn($e) => $e !== $del);
        // Optionally delete file from disk:
        // if (file_exists($uploadDir.$del)) unlink($uploadDir.$del);
    }
    $existingExtras = array_values($existingExtras);

    // New uploads
    if (!empty($_FILES['new_images']['name'][0])) {
        foreach ($_FILES['new_images']['name'] as $idx => $origName) {
            if ($origName === '') continue;
            $ext     = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            $newName = uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($_FILES['new_images']['tmp_name'][$idx], $uploadDir . $newName)) {
                $existingExtras[] = $newName;
            }
        }
    }
    $extra_images_json = !empty($existingExtras) ? json_encode(array_values($existingExtras)) : null;

    /* ── Colors ──────────────────────────────────────────────── */
    // colors_json: JSON array [{id, hex, label, stock, _delete}]
    //   id > 0  → existing row; update or delete
    //   id = 0  → new row to insert
    $colors_raw  = trim($_POST['colors_json'] ?? '');
    $colors_data = $colors_raw !== '' ? json_decode($colors_raw, true) : [];

    // Build legacy hex array for products.colors column
    $legacy_hexes = [];

    if (is_array($colors_data)) {
        foreach ($colors_data as $c) {
            $cid   = (int)($c['id']    ?? 0);
            $hex   = strtoupper($c['hex']   ?? '#000000');
            $label = $c['label'] ?? null;
            $cstk  = (int)($c['stock'] ?? 0);
            $del   = !empty($c['_delete']);

            if ($cid > 0) {
                if ($del) {
                    $pdo->prepare("DELETE FROM product_colors WHERE id = ? AND product_id = ?")
                        ->execute([$cid, $id]);
                } else {
                    $pdo->prepare("UPDATE product_colors SET color_hex=?, color_label=?, stock_quantity=? WHERE id=? AND product_id=?")
                        ->execute([$hex, $label, $cstk, $cid, $id]);
                    $legacy_hexes[] = $hex;
                }
            } else {
                if (!$del) {
                    $pdo->prepare("INSERT INTO product_colors (product_id, color_hex, color_label, stock_quantity) VALUES (?,?,?,?)")
                        ->execute([$id, $hex, $label, $cstk]);
                    $legacy_hexes[] = $hex;
                }
            }
        }
    }
    $legacy_colors = !empty($legacy_hexes) ? json_encode($legacy_hexes) : null;

    /* ── Update product row ──────────────────────────────────── */
    $pdo->prepare("
        UPDATE products SET
            name_ar=?, name_fr=?, name_en=?,
            price=?, stock_quantity=?, category_id=?,
            description_ar=?, description_fr=?, description_en=?,
            image_url=?, extra_images=?, colors=?
        WHERE id=?
    ")->execute([
        $name_ar, $name_fr, $name_en,
        $price, $stock, $cat_id,
        $desc_ar, $desc_fr, $desc_en,
        $primary_image, $extra_images_json, $legacy_colors,
        $id
    ]);

    $message = 'تم تحديث المنتج بنجاح!';

    // Re-fetch updated data
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmtColors->execute([$id]);
    $existingColors = $stmtColors->fetchAll(PDO::FETCH_ASSOC);
}

if ($isAjax && $message) { echo $message; exit; }

/* ── Helpers ── */
$extraImgArr = json_decode($product['extra_images'] ?? '[]', true) ?: [];
?>

<!-- ════ UI ════════════════════════════════════════════════════ -->
<div class="product-container" style="width:740px;min-height:auto;">

    <h2 class="title">
        <i class="fa-solid fa-pen-to-square"></i> تعديل المنتج
    </h2>

    <?php if ($error): ?>
    <div class="product-message error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($message): ?>
    <div class="product-message success-msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div id="editMessage" style="display:none;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-weight:600;"></div>
    <div class="form-box product">
        <form id="editForm" action="/memoire/admin/products/edit.php?id=<?= $id ?>" method="POST" enctype="multipart/form-data">

            <!-- ── Names ────────────────────────────────── -->
            <div class="fields-row">
                <div class="product-input">
                    <input type="text" name="name_ar"
                           value="<?= htmlspecialchars($product['name_ar']) ?>" required placeholder=" ">
                    <label>الاسم (عربي)</label>
                    <i class="fa-solid fa-box"></i>
                </div>
                <div class="product-input">
                    <input type="text" name="name_en"
                           value="<?= htmlspecialchars($product['name_en']) ?>" required placeholder=" ">
                    <label>Name (English)</label>
                    <i class="fa-solid fa-box"></i>
                </div>
            </div>

            <div class="product-input">
                <input type="text" name="name_fr"
                       value="<?= htmlspecialchars($product['name_fr'] ?? '') ?>" placeholder=" ">
                <label>Nom (Français)</label>
                <i class="fa-solid fa-box"></i>
            </div>

            <!-- ── Price / Stock / Category ─────────────── -->
            <div class="fields-row">
                <div class="product-input">
                    <input type="number" step="0.01" name="price"
                           value="<?= $product['price'] ?>" required placeholder=" ">
                    <label>السعر (دج)</label>
                    <i class="fa-solid fa-dollar-sign"></i>
                </div>
                <div class="product-input">
                    <input type="number" name="stock_quantity"
                           value="<?= $product['stock_quantity'] ?>" min="0" placeholder=" ">
                    <label>الكمية الإجمالية</label>
                    <i class="fa-solid fa-warehouse"></i>
                </div>
            </div>

            <div class="product-input textarea-input" style="margin-top:14px">
                <select name="category_id" required>
                    <option value="" disabled hidden></option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= $c['id'] ?>"
                            <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name_ar']) ?>
                            (<?= htmlspecialchars($c['name_en']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <label>التصنيف</label>
            </div>

            <!-- ── Descriptions ─────────────────────────── -->
            <div class="product-input textarea-input">
                <textarea name="desc_ar" placeholder=" "><?= htmlspecialchars($product['description_ar'] ?? '') ?></textarea>
                <label>الوصف (عربي)</label>
            </div>
            <div class="product-input textarea-input">
                <textarea name="desc_en" placeholder=" "><?= htmlspecialchars($product['description_en'] ?? '') ?></textarea>
                <label>Description (English)</label>
            </div>
            <div class="product-input textarea-input">
                <textarea name="desc_fr" placeholder=" "><?= htmlspecialchars($product['description_fr'] ?? '') ?></textarea>
                <label>Description (Français)</label>
            </div>

            <!-- ══ IMAGES ══════════════════════════════════ -->
            <div class="section-card">
                <div class="section-header">
                    <i class="fa-solid fa-images"></i>
                    <span>الصور الحالية</span>
                    <small>انقر × لحذف صورة</small>
                </div>

                <!-- Primary image -->
                <div class="current-images-wrap">
                    <?php if ($product['image_url']): ?>
                    <div class="cur-img-item" id="primary-wrap">
                        <img src="/memoire/assests/uploads/<?= htmlspecialchars($product['image_url']) ?>" alt="">
                        <span class="primary-badge">رئيسية</span>
                        <div class="cur-img-overlay">استبدال ↓</div>
                    </div>
                    <?php endif; ?>

                    <!-- Extra images -->
                    <?php foreach ($extraImgArr as $eImg): ?>
                    <div class="cur-img-item" data-fname="<?= htmlspecialchars($eImg) ?>">
                        <img src="/memoire/assests/uploads/<?= htmlspecialchars($eImg) ?>" alt="">
                        <button type="button" class="remove-existing-img"
                                data-fname="<?= htmlspecialchars($eImg) ?>">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Replace primary -->
                <div class="replace-primary">
                    <label class="replace-label">
                        <i class="fa-solid fa-arrow-rotate-right"></i> استبدال الصورة الرئيسية
                    </label>
                    <input type="file" name="primary_image" accept="image/*" class="file-input-styled">
                </div>

                <!-- Add more images -->
                <div class="section-header" style="margin-top:18px">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>إضافة صور جديدة</span>
                </div>
                <div id="imgDropzone" class="img-dropzone">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <p>اسحب هنا أو انقر للاختيار</p>
                    <span>PNG · JPG · WEBP</span>
                    <input type="file" id="newImagesInput" name="new_images[]"
                           accept="image/*" multiple style="display:none">
                </div>
                <div id="imgPreviewGrid" class="img-preview-grid"></div>

                <!-- Hidden: images to delete -->
                <input type="hidden" name="delete_images" id="deleteImagesInput" value="[]">
            </div>

            <!-- ══ COLORS ══════════════════════════════════ -->
            <div class="section-card">
                <div class="section-header">
                    <i class="fa-solid fa-palette"></i>
                    <span>ألوان المنتج مع المخزون</span>
                    <small>عدّل الكمية · احذف اللون</small>
                </div>

                <!-- Existing colors from DB -->
                <div id="existingColors" class="selected-colors" style="margin-bottom:16px">
                    <?php foreach ($existingColors as $ec):
                        $isOut = (int)$ec['stock_quantity'] <= 0;
                    ?>
                    <div class="color-chip" data-cid="<?= $ec['id'] ?>">
                        <span class="chip-dot" style="background:<?= htmlspecialchars($ec['color_hex']) ?>"></span>
                        <div class="chip-info">
                            <span class="chip-hex"><?= htmlspecialchars($ec['color_hex']) ?></span>
                            <?php if ($ec['color_label']): ?>
                            <span class="chip-label"><?= htmlspecialchars($ec['color_label']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="chip-stock-wrap">
                            <label>الكمية:</label>
                            <input type="number" class="chip-stock-input existing-stock-input"
                                   min="0" value="<?= (int)$ec['stock_quantity'] ?>"
                                   data-cid="<?= $ec['id'] ?>">
                            <span class="stock-badge <?= $isOut ? 'out' : 'in' ?>">
                                <?= $isOut ? 'نفد' : 'متوفر' ?>
                            </span>
                        </div>
                        <button type="button" class="chip-remove delete-existing-color"
                                data-cid="<?= $ec['id'] ?>">×</button>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add new color -->
                <div class="section-header">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>إضافة لون جديد</span>
                </div>
                <div class="color-add-row">
                    <input type="color" id="colorPicker" value="#245bff">
                    <input type="text"  id="colorLabel"  placeholder="اسم اللون" class="color-label-inp">
                    <input type="number" id="colorStock" placeholder="الكمية" min="0" value="0" class="color-stock-inp">
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
                        <button type="button" class="preset-swatch" data-color="<?= $hex ?>"
                                style="background:<?= $hex ?>;border:2px solid <?= in_array($hex,['#FFFFFF','#FFFF00']) ? '#ccc' : $hex ?>;"
                                title="<?= $hex ?>"></button>
                    <?php endforeach; ?>
                </div>

                <div id="newColors" class="selected-colors"></div>

                <!-- Hidden: carries full colors JSON to PHP -->
                <input type="hidden" name="colors_json" id="colorsJsonInput">
            </div>

            <!-- ── Buttons ──────────────────────────────── -->
            <button type="submit" name="update" class="product-btn">
                <i class="fa-solid fa-file-pen"></i> حفظ التغييرات
            </button>
            <button type="button" class="product-btn secondary ajax-link"
                    data-page="products/index.php">
                <i class="fa-solid fa-backward"></i> رجوع
            </button>

        </form>
    </div>
</div>
<!-- ══ SCRIPTS ════════════════════════════════════════════════ -->
<script>
(function(){

/* ══ EXISTING IMAGES – delete management ════════════════════ */
const deleteInput = document.getElementById('deleteImagesInput');
let toDelete = [];

document.querySelectorAll('.remove-existing-img').forEach(btn => {
    btn.addEventListener('click', () => {
        const fname = btn.dataset.fname;
        const wrap  = btn.closest('.cur-img-item');
        if (toDelete.includes(fname)) {
            toDelete = toDelete.filter(f => f !== fname);
            wrap.classList.remove('deleted-img');
        } else {
            toDelete.push(fname);
            wrap.classList.add('deleted-img');
        }
        deleteInput.value = JSON.stringify(toDelete);
    });
});

/* ══ NEW IMAGES ════════════════════════════════════════════= */
const dropzone    = document.getElementById('imgDropzone');
const fileInput   = document.getElementById('newImagesInput');
const previewGrid = document.getElementById('imgPreviewGrid');
let fileList = [];

dropzone.addEventListener('click', () => fileInput.click());
dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('drag-over'); });
dropzone.addEventListener('dragleave', () => dropzone.classList.remove('drag-over'));
dropzone.addEventListener('drop', e => {
    e.preventDefault(); dropzone.classList.remove('drag-over');
    handleFiles([...e.dataTransfer.files]);
});
fileInput.addEventListener('change', () => { handleFiles([...fileInput.files]); fileInput.value = ''; });

function handleFiles(f) {
    f.forEach(fi => { if (fi.type.startsWith('image/') && fileList.length < 10) fileList.push(fi); });
    renderPreviews(); syncFiles();
}
function renderPreviews() {
    previewGrid.innerHTML = '';
    fileList.forEach((f, idx) => {
        const item = document.createElement('div');
        item.className = 'img-preview-item';
        item.innerHTML = `<img src="${URL.createObjectURL(f)}" alt="">
            <button type="button" class="remove-img" data-idx="${idx}"><i class="fa-solid fa-xmark"></i></button>`;
        previewGrid.appendChild(item);
    });
    previewGrid.querySelectorAll('.remove-img').forEach(btn =>
        btn.addEventListener('click', () => { fileList.splice(+btn.dataset.idx,1); renderPreviews(); syncFiles(); })
    );
}
function syncFiles() {
    const dt = new DataTransfer();
    fileList.forEach(f => dt.items.add(f));
    fileInput.files = dt.files;
}

/* ══ COLORS ════════════════════════════════════════════════= */
const picker      = document.getElementById('colorPicker');
const labelInp    = document.getElementById('colorLabel');
const stockInp    = document.getElementById('colorStock');
const addBtn      = document.getElementById('addColorBtn');
const newColDiv   = document.getElementById('newColors');
const jsonInput   = document.getElementById('colorsJsonInput');

// Existing colors state: read from DOM chips
// {id, hex, label, stock, _delete}
let existingState = [];
document.querySelectorAll('#existingColors .color-chip').forEach(chip => {
    const cid   = +chip.dataset.cid;
    const hex   = chip.querySelector('.chip-hex').textContent.trim();
    const lbl   = chip.querySelector('.chip-label')?.textContent.trim() || '';
    const stock = +chip.querySelector('.existing-stock-input').value || 0;
    existingState.push({ id: cid, hex, label: lbl, stock, _delete: false });
});

// New colors (id=0)
let newColors = [];

// Existing stock inputs
document.querySelectorAll('.existing-stock-input').forEach(inp => {
    inp.addEventListener('input', () => {
        const cid = +inp.dataset.cid;
        const entry = existingState.find(e => e.id === cid);
        if (entry) { entry.stock = Math.max(0, +inp.value||0); }
        updateBadge(inp);
        syncJSON();
    });
});

// Delete existing color button
document.querySelectorAll('.delete-existing-color').forEach(btn => {
    btn.addEventListener('click', () => {
        const cid   = +btn.dataset.cid;
        const entry = existingState.find(e => e.id === cid);
        const chip  = btn.closest('.color-chip');
        if (entry) {
            entry._delete = !entry._delete;
            chip.classList.toggle('marked-delete', entry._delete);
        }
        syncJSON();
    });
});

// Add new color
addBtn.addEventListener('click', () => addNewColor(picker.value, labelInp.value.trim(), +stockInp.value||0));
document.querySelectorAll('.preset-swatch').forEach(sw =>
    sw.addEventListener('click', () => addNewColor(sw.dataset.color, '', +stockInp.value||0))
);

function addNewColor(hex, label, stock) {
    hex = hex.toUpperCase();
    if (existingState.find(e=>e.hex===hex && !e._delete) || newColors.find(e=>e.hex===hex)) return;
    newColors.push({id:0, hex, label, stock, _delete:false});
    labelInp.value=''; stockInp.value=0;
    renderNewColors();
    syncJSON();
}
function renderNewColors() {
    newColDiv.innerHTML='';
    newColors.forEach((c,idx)=>{
        const chip = document.createElement('div');
        chip.className='color-chip';
        const isOut = c.stock<=0;
        chip.innerHTML=`
            <span class="chip-dot" style="background:${c.hex}"></span>
            <div class="chip-info">
                <span class="chip-hex">${c.hex}</span>
                ${c.label?`<span class="chip-label">${c.label}</span>`:''}
            </div>
            <div class="chip-stock-wrap">
                <label>الكمية:</label>
                <input type="number" class="chip-stock-input new-stock-input" min="0"
                       value="${c.stock}" data-idx="${idx}">
                <span class="stock-badge ${isOut?'out':'in'}">${isOut?'نفد':'متوفر'}</span>
            </div>
            <button type="button" class="chip-remove" data-idx="${idx}">×</button>`;
        newColDiv.appendChild(chip);
    });
    newColDiv.querySelectorAll('.new-stock-input').forEach(inp=>
        inp.addEventListener('input',()=>{
            newColors[+inp.dataset.idx].stock = Math.max(0,+inp.value||0);
            updateBadge(inp); syncJSON();
        })
    );
    newColDiv.querySelectorAll('.chip-remove').forEach(btn=>
        btn.addEventListener('click',()=>{ newColors.splice(+btn.dataset.idx,1); renderNewColors(); syncJSON(); })
    );
}
function updateBadge(inp) {
    const badge = inp.nextElementSibling;
    const val   = Math.max(0,+inp.value||0);
    badge.textContent = val<=0?'نفد':'متوفر';
    badge.className   = `stock-badge ${val<=0?'out':'in'}`;
}
function syncJSON() {
    const all = [...existingState, ...newColors];
    jsonInput.value = all.length ? JSON.stringify(all) : '';
}
syncJSON(); // init
})();
</script>
