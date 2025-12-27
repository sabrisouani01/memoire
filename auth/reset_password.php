<?php
require_once __DIR__ . '/../include/db_connect.php';
if(isset($_GET['token'])){
    $token = $_GET['token'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND token_expire > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if($user){
    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $pass1 = $_POST['password1'];
        $pass2 = $_POST['password2'];
        if($pass1 === $pass2){
            $hashed = password_hash($pass1, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ?,reset_token = NULL, token_expire = NULL WHERE id = ?");
            $stmt->execute([$hashed,$user['id']]);
            $message = "<p style='color:green;text-decoration: underline green;'>password change successfully.<a herf='login.php'></p>";
            echo "<script>
        alert('Password changed successfully. Please log in again.');
        window.location.href = 'login.php'; 
        window.close(); // will close if it's a popup window
    </script>";
    exit;
        }
        else{
            $message = "<p style='color:red;text-decoration: underline red;'>password doesn't match.</p>";
        }
    }
    }
}else{
        $message = "<p style='color:red; text-decoration: underline red;'>link expired.</p>";
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assests/css/reset_password.css">
    <title>Wisetech - Next Generation</title>
</head>
<body>
<div class="reset-container">
    <div class="reset-form-box">
        <div class="message">
            <?php if(!empty($message)) echo $message; ?>
        </div>
        <form action="" method="POST">
            <h2>Reset Your Password</h2>
            <div class="input-box">
                <input type="password" name="password1" required>
                <label>Password</label>
            </div>
            <div class="input-box">
                <input type="password" name="password2" required>
                <label>Rewrite Password</label>
            </div>
            <button class="reset-btn">Reset Password</button>
        </form>
    </div>
    <div class="curved-shape"></div>
</div>
</body>
</html>