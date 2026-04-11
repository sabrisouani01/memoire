<?php
// delete_repair.php
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
$repair_id = $_POST['repair_id'] ?? null;

if (!$repair_id) {
    header("Location: repairs.php?error=invalid_request");
    exit();
}

require_once '../../include/db_connect.php';

// ✅ Verify: user owns this repair request
$check = $pdo->prepare("SELECT id FROM repairs WHERE id = ? AND user_id = ?");
$check->execute([$repair_id, $user_id]);

if (!$check->fetch()) {
    // Either doesn't exist or belongs to another user
    header("Location: repairs.php?error=unauthorized");
    exit();
}

// ✅ Delete the repair request
try {
    $delete = $pdo->prepare("DELETE FROM repairs WHERE id = ? AND user_id = ?");
    $delete->execute([$repair_id, $user_id]);
    
    header("Location: repairs.php?deleted=1");
    exit();
    
} catch (PDOException $e) {
    error_log("Repair delete error: " . $e->getMessage());
    header("Location: repairs.php?error=delete_failed");
    exit();
}