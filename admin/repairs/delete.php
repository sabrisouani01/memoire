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

// Delete repair
$pdo->prepare("DELETE FROM repairs WHERE id = ?")->execute([$id]);

header("Location: index.php");
exit;
?>