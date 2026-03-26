<?php
require "../includes/tech_auth.php";
include "../../include/db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id'], $_POST['status'])) {
    http_response_code(400); echo 'bad request'; exit;
}

$allowed = ['in_progress', 'completed', 'unrepairable', 'cancelled'];
$status  = in_array($_POST['status'], $allowed) ? $_POST['status'] : null;

if (!$status) { http_response_code(400); echo 'invalid status'; exit; }

$currentTech = $_SESSION['username'];
$id = (int)$_POST['id'];

if ($status === 'cancelled') {
    // Cancel permanently deletes the order; only the assigned technician can do this
    $stmt = $pdo->prepare("DELETE FROM repairs WHERE id = ? AND technician = ?");
    $stmt->execute([$id, $currentTech]);
} else {
    // Only the assigned technician can update status
    $stmt = $pdo->prepare("UPDATE repairs SET status = ? WHERE id = ? AND technician = ?");
    $stmt->execute([$status, $id, $currentTech]);
}

echo 'success';
