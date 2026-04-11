<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: repairs.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$description = trim($_POST['description'] ?? '');

if (!$product_id || !$description) {
    // Redirect back with error
    header("Location: repairs.php?error=missing_fields");
    exit();
}

require_once '../../include/db_connect.php';

// ✅ Verify: user owns product + order is DELIVERED
$check = $pdo->prepare("
    SELECT o.warranty_expiry 
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ? 
      AND oi.product_id = ?
      AND o.status = 'delivered'
");
$check->execute([$user_id, $product_id]);
$order = $check->fetch();

if (!$order) {
    header("Location: repairs.php?error=not_eligible");
    exit();
}

// Determine if it's a warranty claim
$isWarrantyClaim = 0;
if ($order['warranty_expiry'] && strtotime($order['warranty_expiry']) >= time()) {
    $isWarrantyClaim = 1;
}

// Insert repair request
try {
    $insert = $pdo->prepare("
        INSERT INTO repairs (
            user_id, 
            product_id, 
            description, 
            status, 
            is_warranty_claim,
            created_at
        ) VALUES (?, ?, ?, 'pending', ?, NOW())
    ");
    $insert->execute([$user_id, $product_id, $description, $isWarrantyClaim]);
    
    // ✅ Success - redirect to repairs page with success flag
    header("Location: repairs.php?success=1");
    exit();
    
} catch (PDOException $e) {
    // ✅ Error - redirect with error message
    error_log("Repair insert error: " . $e->getMessage());
    header("Location: repairs.php?error=database");
    exit();
}