<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$id = (int)$_POST['id'];
$status = $_POST['status'] ?? '';

$allowed = ['pending','processing','shipped','delivered','cancelled'];

if ($id && in_array($status, $allowed)) {
    $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=?");
    $stmt->execute([$status, $id]);
    echo "ok";
}