<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$id     = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';

$allowed = ['pending','processing','shipped','delivered','cancelled'];
if (!$id || !in_array($status, $allowed)) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
$ok = $stmt->execute([$status, $id]);

echo json_encode(['success' => $ok]);
?>