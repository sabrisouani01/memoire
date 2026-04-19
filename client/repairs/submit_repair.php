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
$repair_type = $_POST['repair_type'] ?? 'internal';

require_once '../../include/db_connect.php';

// ✅ Get user info from database
$userStmt = $pdo->prepare("SELECT First_name, Last_name, phone FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$customer_name = trim(($user['First_name'] ?? '') . ' ' . ($user['Last_name'] ?? ''));
$user_phone = $user['phone'] ?? '';

// ========================
// ✅ EXTERNAL REPAIR (Items not from site)
// ========================
if ($repair_type === 'external') {
    $external_item = trim($_POST['external_item'] ?? '');
    $external_phone = trim($_POST['external_phone'] ?? $user_phone);
    $description = trim($_POST['external_description'] ?? '');
    $damage_from_factory = isset($_POST['damage_from_factory']) ? 1 : 0;

    if (!$external_item || !$external_phone || !$description) {
        header("Location: repairs.php?error=missing_fields");
        exit();
    }

    $insert = $pdo->prepare("
        INSERT INTO repairs (
            user_id,
            customer_name,
            phone,
            item,
            description,
            status,
            is_warranty_claim,
            is_external_item,
            damage_from_factory,
            created_at
        ) VALUES (?, ?, ?, 'other', ?, 'pending', 0, 1, ?, NOW())
    ");
    
    $insert->execute([$user_id, $customer_name, $external_phone, $description, $damage_from_factory]);
    
    header("Location: repairs.php?success=1");
    exit();
}

// ========================
// ✅ INTERNAL REPAIR (Products from site)
// ========================
$product_id = $_POST['product_id'] ?? null;
$description = trim($_POST['description'] ?? '');

if (!$product_id || !$description) {
    header("Location: repairs.php?error=missing_fields");
    exit();
}

// Verify user owns the product and order is delivered
$check = $pdo->prepare("
    SELECT 
        o.warranty_expiry,
        c.warranty_duration
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
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

// Check if under warranty
$isWarrantyClaim = 0;
if ($order['warranty_expiry'] && strtotime($order['warranty_expiry']) >= time()) {
    $isWarrantyClaim = 1;
}

// ✅ Insert repair with customer_name and phone
$insert = $pdo->prepare("
    INSERT INTO repairs (
        user_id, 
        product_id, 
        customer_name,
        phone,
        description, 
        status, 
        is_warranty_claim,
        is_external_item,
        created_at
    ) VALUES (?, ?, ?, ?, ?, 'pending', ?, 0, NOW())
");

$insert->execute([
    $user_id, 
    $product_id, 
    $customer_name,  // ✅ Saves user's full name
    $user_phone,     // ✅ Saves user's phone
    $description, 
    $isWarrantyClaim
]);

header("Location: repairs.php?success=1");
exit();