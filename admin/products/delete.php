<?php
/* Admin product delete — AJAX-friendly (no redirect) */
include "../../include/db_connect.php";

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { echo json_encode(['ok'=>false,'msg'=>'Invalid ID']); exit; }

$stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) { echo json_encode(['ok'=>false,'msg'=>'Not found']); exit; }

try {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

    $image_url = $product['image_url'];
    if ($image_url) {
        $image_path = "../../assests/uploads/" . basename($image_url);
        if (file_exists($image_path)) unlink($image_path);
    }

    echo json_encode(['ok'=>true]);
} catch (PDOException $e) {
    echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
