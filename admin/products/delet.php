<?php
// Include database connection
include "../../include/db_connect.php";

// Get and validate ID
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("خطأ: المعرف غير صالح.");
}

// Check if product exists
$stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("المنتج غير موجود.");
}

// Delete the product
try {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    // Optional: Delete image file from server
    $image_url = $product['image_url'];
    if ($image_url) {
        $image_path = "../../assests/uploads/" . basename($image_url);
        if (file_exists($image_path)) {
            unlink($image_path); // Remove file
        }
    }

    // Redirect after success
    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    die("خطأ في الحذف: " . $e->getMessage());
}
?>