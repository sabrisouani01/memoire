<?php
include "../../include/db_connect.php";

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo "Invalid ID.";
    exit;
}

// Check if category is used in products
$stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
$stmt->execute([$id]);
if ($stmt->fetchColumn() > 0) {
    echo "Cannot delete this category — it is used by existing products.";
    exit;
}

// Delete category
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

echo "ok";
exit;
?>