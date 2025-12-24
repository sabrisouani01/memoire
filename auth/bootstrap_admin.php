<?php
require '../include/db_connect.php';

$username = 'admin';
$email    = 'admin@wisetech.dz'; // MUST NOT be NULL
$password = 'admin';
$role     = 'admin';

$hash = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO users
(`id`, `username`, `email`, `password_hash`, `First_name`, `Last_name`,
 `phone`, `address`, `role`, `token_expire`, `reset_token`,
 `created_at`, `updated_at`)
VALUES
(1, :u, :e, :p, 'Admin', 'User',
 NULL, NULL, :r, NULL, NULL, NULL, NULL)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':u' => $username,
    ':e' => $email,
    ':p' => $hash,
    ':r' => $role
]);

echo "Admin created. Delete this file now.";
?>
