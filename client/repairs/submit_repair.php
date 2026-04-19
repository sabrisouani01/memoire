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

// ✅ Get user info
$userStmt = $pdo->prepare("SELECT First_name, Last_name, phone FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$customer_name = trim(($user['First_name'] ?? '') . ' ' . ($user['Last_name'] ?? ''));
$user_phone = $user['phone'] ?? '';

// ========================
// ✅ EXTERNAL REPAIR
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
// ✅ INTERNAL REPAIR (Dynamic Warranty Calculation)
// ========================
$product_id = $_POST['product_id'] ?? null;
$description = trim($_POST['description'] ?? '');

if (!$product_id || !$description) {
    header("Location: repairs.php?error=missing_fields");
    exit();
}

// 🔍 Get product, order, and category info
$check = $pdo->prepare("
    SELECT 
        o.created_at AS order_date,
        c.warranty_duration,
        p.name_ar AS product_name
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

// 🛡️ Calculate warranty expiry dynamically
$isWarrantyClaim = 0;
$warranty_expiry = null;

if ($order['warranty_duration']) {
    // Extract months from warranty_duration (e.g., "9 اشهر" -> 9)
    preg_match('/(\d+)/', $order['warranty_duration'], $matches);
    if (isset($matches[1])) {
        $months = (int)$matches[1];
        $orderDate = new DateTime($order['order_date']);
        $expiryDate = clone $orderDate;
        $expiryDate->modify("+{$months} months");
        $warranty_expiry = $expiryDate;
        
        // Check if still under warranty
        if ($expiryDate >= new DateTime()) {
            $isWarrantyClaim = 1;
        }
    }
}

// Insert repair request
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
    $customer_name,
    $user_phone,
    $description, 
    $isWarrantyClaim
]);

header("Location: repairs.php?success=1");
exit();