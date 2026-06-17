<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: orders.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = $_POST['order_id'] ?? null;

if (!$order_id) {
    header("Location: orders.php?error=invalid");
    exit();
}

require_once '../../include/db_connect.php';

// ✅ Verify ownership & fetch current status
$stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php?error=unauthorized");
    exit();
}

// ✅ Only allow deletion of pending orders
if ($order['status'] !== 'pending') {
    header("Location: orders.php?error=status");
    exit();
}

try {
    // ✅ Delete order (FK CASCADE automatically removes order_items)
    $delete = $pdo->prepare("DELETE FROM orders WHERE id = ?");
    $delete->execute([$order_id]);
    
    header("Location: orders.php?deleted=1");
} catch (PDOException $e) {
    error_log("Order delete error: " . $e->getMessage());
    header("Location: orders.php?error=failed");
}
exit();