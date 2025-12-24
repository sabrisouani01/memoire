<?php
session_start();
include "../include/db_connect.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    // ---------------- LOGIN ----------------
    if ($action === 'login') {
        $input = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($input) || empty($password)) {
            $message = "Please enter username/email and password.";
        } else {
            try {
                $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
                $stmt = $pdo->prepare("SELECT * FROM users WHERE $field = ?");
                $stmt->execute([$input]);
                $user = $stmt->fetch();

                if (!$user) {
                    $message = "This account does not exist.";
                } elseif (!password_verify($password, $user['password_hash'])) {
                    $message = "Incorrect password.";
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.jpg';

                    if ($user['role'] === 'admin') {
                        header("Location: ../admin/dashbord.php");
                    } elseif ($user['role'] === 'technician') {
                        header("Location: ../technician/panel.php");
                    } else {
                        header("Location: ../index.php");
                    }
                    exit;
                }
            } catch (Exception $e) {
                $message = "Server error occurred during login.";
                error_log("Login error: " . $e->getMessage());
            }
        }
    }

    // ---------------- REGISTER ----------------
    // ---------------- REGISTER ----------------
elseif ($action === 'register') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname  = trim($_POST['lastname'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $password  = $_POST['password'] ?? '';

    if (!$firstname || !$lastname || !$username || !$email || !$phone || !$password) {
        $message = "Please fill all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Enter a valid email address.";
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $message = "Username or email already exists.";
            } else {
                // Hash the password
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert the user
                $stmt = $pdo->prepare("
                    INSERT INTO users 
                        (username, email, password_hash, First_name, Last_name, phone, address, role, token_expire, reset_token, created_at, updated_at) 
                    VALUES 
                        (?, ?, ?, ?, ?, ?, NULL, 'customer', NULL, NULL, NOW(), NOW())
                ");

                $stmt->execute([$username, $email, $password_hash, $firstname, $lastname, $phone]);

                // Automatically log the user in after registration
                $user_id = $pdo->lastInsertId();
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = 'customer';
                $_SESSION['profile_image'] = 'default.jpg';

                // Redirect to main dashboard
                header("Location: ../index.php");
                exit;
            }
        } catch (Exception $e) {
            $message = "Server error occurred during registration.";
            error_log("Register error: " . $e->getMessage());
        }
    }
}


    // ---------------- FORGOT PASSWORD ----------------
    elseif ($action === 'forgot') {
        $email = trim($_POST['email'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Enter a valid email address.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if (!$user) {
                    $message = "No account found with this email.";
                } else {
                    // Generate token and expiry
                    $token = bin2hex(random_bytes(16));
                    $stmt = $pdo->prepare("
    UPDATE users 
    SET reset_token = ?, token_expire = DATE_ADD(NOW(), INTERVAL 1 HOUR) 
    WHERE id = ?
");
                    $stmt->execute([$token, $user['id']]);

                    // TODO: Send email with reset link
                    // Example: https://yourdomain.com/reset_password.php?token=$token
                    $message = "A password reset link has been sent to your email.";
                }
            } catch (Exception $e) {
                $message = "Server error occurred.";
                error_log("Forgot password error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wise Tech - Next Generation</title>
    <link rel="stylesheet" href="../assests/css/login.css">
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>
    <!--main section-->
    <div class="main-container">
        <div class="msg"><?php if (!empty($message)): ?>
    <div class="error-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
</div>
    <!--form section-->
    <div class="curved-shape"></div>
    <div class="curved-shape2"></div>
    <div class="form-box login">
        <h2 class="animation" style="--D:0">login</h2>
        <form action="" method="POST">
    <input type="hidden" name="action" value="login">

    <div class="input-box animation" style="--D:1">
        <input type="text" name="username" required>
        <label>Username or Email</label>
        <i class="fa-solid fa-user"></i>
    </div>

    <div class="input-box animation" style="--D:2">
        <input type="password" name="password" required>
        <label>Password</label>
        <i class="fa-solid fa-lock"></i>
    </div>

    <div class="input-box animation" style="--D:3">
        <button class="btn" type="submit">Login</button>
    </div>

    <div class="regi-link animation" style="--D:5">
        <p>Don't have an account? <a href="#" class="SingUplink">Sign up</a></p>
    </div>
    <!-- ADD THIS (uses existing regi-link class) -->
    <div class="regi-link animation" style="--D:4;">
    <a href="#" class="show-forgot">Forgot password?</a>
</div>

</form>

    </div>
    <div class="info-content login">
        <h2 style="color:#f4f6fb" class="animation" style="--D:0">WELCOME BACK!</h2>
        <p style="color:#f4f6fb" class="animation" style="--D:1">WISETECH <br>at your service</p>
    </div>
    <!--signup-->
    <div class="form-box register">
        <h2 class="animation" style="--li:17;">Register</h2>
       <form action="" method="POST">
    <input type="hidden" name="action" value="register">

    <div class="form-row animation" style="--li:18;">
    <div class="input-box">
        <input type="text" name="firstname" required>
        <label>First Name</label>
        <i class="fa-solid fa-id-card"></i>
    </div>

    <div class="input-box">
        <input type="text" name="lastname" required>
        <label>Last Name</label>
        <i class="fa-solid fa-id-card"></i>
    </div>
</div>


    <div class="input-box animation" style="--li:20;">
        <input type="text" name="username" required>
        <label>Username</label>
        <i class="fa-solid fa-user"></i>
    </div>

    <div class="form-row animation" style="--li:19;">
    <div class="input-box">
        <input type="email" name="email" required>
        <label>Email</label>
        <i class="fa-solid fa-envelope"></i>
    </div>

    <div class="input-box">
        <input type="text" name="phone" required>
        <label>Phone</label>
        <i class="fa-solid fa-phone"></i>
    </div>
</div>


    <div class="input-box animation" style="--li:23;">
        <input type="password" name="password" required>
        <label>Password</label>
        <i class="fa-solid fa-lock"></i>
    </div>

    <div class="input-box animation" style="--li:24;">
        <button class="btn" type="submit">Register</button>
    </div>

    <div class="regi-link animation" style="--li:25;">
        <p>Already have an account? <a href="#" class="SingInlink">Login</a></p>
    </div>
</form>

    </div>
    <div class="info-content register">
        <h2 style="color:#f4f6fb" class="animation" style="--li:17;">WELCOME <br>TO WISETECH </h2>
        <p style="color:#f4f6fb" class="animation" style="--li:18;">we're always here for you</p>
    </div>
    <!-- Forgot Password form -->
<div class="form-box forgot-password">
    <h2 class="animation" style="--D:0;">Forgot Password</h2>
    <form action="" method="POST">
        <input type="hidden" name="action" value="forgot">

        <div class="input-box animation" style="--D:1;">
            <input type="email" name="email" required>
            <label>Email</label>
            <i class="fa-solid fa-envelope"></i>
        </div>

        <div class="input-box animation" style="--D:2;">
            <button class="btn" type="submit">Send Reset Link</button>
        </div>

        <div class="regi-link animation" style="--D:3;">
            <p>Remembered your password? <a href="#" class="SingInlink">Login</a></p>
        </div>
    </form>
</div>

    </div>
    <script src="../assests/js/login.js"></script>
</body>
</html>