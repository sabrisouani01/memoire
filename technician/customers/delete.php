<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("invalid id");
}

/* تحقق من وجود المستخدم */
$stmt = $pdo->prepare(
    "SELECT id FROM users WHERE id = ? AND role = 'customer'"
);
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("customer not found");
}

try {
    $pdo->beginTransaction();

    // حذف عناصر الطلبات
    $stmt = $pdo->prepare("
        DELETE oi
        FROM order_items oi
        INNER JOIN orders o ON oi.order_id = o.id
        WHERE o.user_id = ?
    ");
    $stmt->execute([$id]);

    // حذف الطلبات
    $stmt = $pdo->prepare("DELETE FROM orders WHERE user_id = ?");
    $stmt->execute([$id]);

    // حذف المستخدم
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $pdo->commit();

    echo "success";
} catch (Exception $e) {
    $pdo->rollBack();
    die("error");
}