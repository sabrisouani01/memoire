<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../includes/db_connect.php";

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) die("ID غير صالح.");

// Delete order items first
$pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$id]);

// Delete order
$pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$id]);

header("Location: index.php");
exit;
?>