<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$description = trim($_POST['description'] ?? '');

if (!$product_id || !$description) {
    die("يرجى تعبئة جميع الحقول.");
}

require_once '../../includes/db_connect.php';

// Verify that this user actually bought this product and it's under warranty
$check = $pdo->prepare("
    SELECT o.warranty_expiry 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ? 
      AND oi.product_id = ?
      AND o.warranty_expiry >= CURDATE()
");
$check->execute([$user_id, $product_id]);

if (!$check->fetch()) {
    die("هذا المنتج غير مؤهل للصيانة تحت الضمان.");
}

// Insert repair request as warranty claim
$insert = $pdo->prepare("
    INSERT INTO repairs (
        user_id, 
        product_id, 
        description, 
        status, 
        is_warranty_claim,
        created_at
    ) VALUES (?, ?, ?, 'pending', 1, NOW())
");
$insert->execute([$user_id, $product_id, $description]);

header("Location: index.php?success=1");
exit();