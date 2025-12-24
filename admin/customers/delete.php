<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../includes/db_connect.php";

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID غير صالح.");
}

// Check if user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'customer'");
$user = $stmt->execute([$id]) ? $stmt->fetch() : null;

if (!$user) {
    die("العميل غير موجود.");
}

// Start transaction
$pdo->beginTransaction();

try {
    // Delete order items
    $pdo->prepare("DELETE oi FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ?")->execute([$id]);

    // Delete orders
    $pdo->prepare("DELETE FROM orders WHERE user_id = ?")->execute([$id]);

    // Delete user
    $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);

    $pdo->commit();

    header("Location: index.php");
    exit;
} catch (Exception $e) {
    $pdo->rollback();
    die("خطأ في الحذف: " . $e->getMessage());
}
?>