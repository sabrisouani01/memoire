<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
$message = '';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_ar = trim($_POST['name_ar']);
    $name_fr = trim($_POST['name_fr']);
    $name_en = trim($_POST['name_en']);
    $warranty = trim($_POST['warranty_duration'] ?? '');
if (empty($name_ar) || empty($name_fr) || empty($name_en) || empty($warranty)) {
        $message = "جميع الحقول مطلوبة.";
    } else {
        try {
            $stmt = $pdo->prepare("
    INSERT INTO categories (name_ar, name_fr, name_en, warranty_duration)
    VALUES (?, ?, ?, ?)
");

$stmt->execute([$name_ar, $name_fr, $name_en, $warranty]);

            $message = 'Category added successfully!';
        } catch (PDOException $e) {
            $message = "خطأ في الإدخال.";
            error_log("Category add error: " . $e->getMessage());
        }
    }
}
if ($isAjax && $message) {
    echo $message;
    
    exit;}
?>
    <div class="category-container">

    <h2 class="category-title">
        <i class="fa-solid fa-folder-plus"></i> إضافة تصنيف
    </h2>

    <div class="category-message" id="formMessage" style="display:none"></div>

    <form id="addForm" action= "categories/add.php" class="category-form" method="post">

        <div class="category-input">
            <input type="text" name="name_ar" required>
            <label>الاسم (عربي)</label>
        </div>

        <div class="category-input">
            <input type="text" name="name_fr" required>
            <label>Nom (Français)</label>
        </div>

        <div class="category-input">
            <input type="text" name="name_en" required>
            <label>Name (English)</label>
        </div>

        <div class="category-input">
    <input type="text" name="warranty_duration">
    <label>مدة الضمان</label>
        </div>

        <button type="submit" class="category-btn primary">
           <i class="fa-regular fa-floppy-disk"></i>
                save
        </button>

        <button type="button"
                class="category-btn secondary ajax-link"
                data-page="categories/index">
            <i class="fa-solid fa-backward"></i>
                back
        </button>

    </form>

</div>