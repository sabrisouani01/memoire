<?php
include "../../include/db_connect.php";

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID غير صالح.");
}

// Check if category is used in products
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    die("لا يمكن حذف هذا التصنيف لأنه مستخدم في منتجات.");
}

// Delete category
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

header("Location: index.php");
exit;
?>