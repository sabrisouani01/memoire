<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";// Use your language system
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['Phone']);
    if ($phone === ''){
        $phone = null;
    }
    $username = trim($_POST['username']);
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($username)) {
        $message = "Fill all the required labels";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a correct email.';
    } elseif (empty($username)) {
        $message = 'Username is required.';
    } elseif (strlen($password1) < 6) {
        $message = 'the password must cuntain 6 characters.';
    } elseif ($password1 !== $password2) {
        $message = "The secend password doesn't match.";
    }
    else{
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $message = 'This email already used.';
            } else {
                // Check if username exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->rowCount() > 0) {
                    $message = 'Username already used.';
                } else {
                    // Hash password
                    $password_hash = password_hash($password1, PASSWORD_BCRYPT);

                    // Insert new admin
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, First_name, Last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'admin')");
                    $stmt->execute([
                        $username,
                        $email,
                        $password_hash,
                        $first_name,
                        $last_name,
                        $phone
                    ]);

                    $message = 'Account created successfully!';
                }
            }
        } catch (Exception $e) {
            $message = 'Error!';
            error_log("Admin registration error: " . $e->getMessage());
        }
    }
}
if ($isAjax && $message) {
    echo $message;
    exit;
}
?>
<div class="main-container admin">
    <h2 class="title"><i class="fa-solid fa-user-shield"></i> Add New Admin</h2>
  <div class="message" id="formMessage" style="display:none"></div>
        
        <!--form section-->
        <div class="form-box admin"> 
            <form id = "addForm" action= "users/add_admin.php" method= "POST">
            <div class="input-box">
                <input type="text" name="first_name" required>
                <label >First name</label>
                <i class="fa-solid fa-id-card"></i>
            </div>

            <div class="input-box">
                <input type="text" name="last_name" required>
                <label >Last name</label>
                <i class="fa-solid fa-id-card"></i>
            </div>

            <div class="input-box">
                <input type="text" name="username" required>
                <label >Username</label>
                <i class="fa-solid fa-user"></i>
            </div>

            <div class="input-box">
                <input type="text" name="Phone" >
                <label >Phone</label>
                <i class="fa-solid fa-phone"></i>
            </div>

            <div class="input-box">
                <input type="email" name="email" required>
                <label >Email</label>
                <i class="fa-solid fa-envelope"></i>
            </div>

            

            <div class="input-box">
                <input type="password" name="password1" required>
                <label >Password</label>
                <i class="fa-solid fa-lock"></i>
            </div>

            <div class="input-box">
                <input type="password" name="password2" required>
                <label >Confirm password</label>
                <i class="fa-solid fa-lock"></i>
            </div>

            <button type= "submit" class="btn2 btn-primary">Add</button>
            <button type= "button" class="btn2 btn-secendry" onclick="loadPage('dashboard/dashbord.php')">Return</button>
            
        </form></div>
       
</div>
       