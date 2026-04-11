<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['id'])) {
    http_response_code(400); echo 'bad request'; exit;
}

$id = (int)$_POST['id'];

$stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
$stmt->execute([$id]);

echo 'success';
