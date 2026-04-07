<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(400); echo 'bad request'; exit;
}

$name_en        = trim($_POST['name_en']        ?? '');
$name_fr        = trim($_POST['name_fr']        ?? '');
$description_en = trim($_POST['description_en'] ?? '');
$price          = (float)($_POST['price']       ?? 0);
$estimated_time = trim($_POST['estimated_time'] ?? '');
$status         = in_array($_POST['status'] ?? '', ['active','inactive']) ? $_POST['status'] : 'active';

if ($name_en === '') { echo 'Service name (EN) is required.'; exit; }
if ($price < 0)      { echo 'Price must be a positive number.'; exit; }

$stmt = $pdo->prepare("
    INSERT INTO services (name_en, name_fr, description_en, price, estimated_time, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
");
$stmt->execute([
    $name_en,
    $name_fr ?: null,
    $description_en ?: null,
    $price,
    $estimated_time ?: null,
    $status
]);

echo 'success';
