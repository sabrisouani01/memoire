<?php
session_start();
if (!isset($lang_code)) {
    $lang_code = $_SESSION['lang_code'] ?? 'ar';
}

$lang_file = "../languages/{$lang_code}.php";
if (file_exists($lang_file)) {
    $lang = include $lang_file;
} else {
    // Fallback to Arabic
    $lang = [
        'title_login' => 'تسجيل الدخول',
        'username_placeholder' => 'اسم المستخدم أو البريد الإلكتروني',
        'password' => 'كلمة المرور',
        'forgot_password' => 'نسيت كلمة المرور؟',
        'signin_btn' => 'تسجيل الدخول',
        'no_account' => 'ليس لديك حساب؟',
        'signup_btn' => 'اشترك الآن',
        'please_enter_email_password' => 'يرجى إدخال البريد الإلكتروني وكلمة المرور.',
        'email_not_registered' => 'هذا البريد الإلكتروني غير مسجل. يُرجى التسجيل أولاً.',
        'incorrect_password' => 'كلمة المرور غير صحيحة.',
        'server_error' => 'حدث خطأ أثناء تسجيل الدخول.'
    ];
}
include "../include/db_connect.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($input) || empty($password)) {
        $message = $lang['please_enter_email_password'];
    } else {
        try {
            $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
            $stmt = $pdo->prepare("SELECT * FROM users WHERE $field = ?");
            $stmt->execute([$input]);
            $user = $stmt->fetch();

            if (!$user) {
                $message = $lang['email_not_registered'];
            } elseif ($password !== $user['password'])
 {
                $message = $lang['incorrect_password'];
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['profile_image'] = $user['profile_image'] ?? 'default.jpg';

                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashbord.php");
                }elseif ($user['role'] === 'technician') {
                       header("Location: ../technician/panel.php"); 
                }else {
                    header("Location: ../index.php");
                }
                exit;
            }
        } catch (Exception $e) {
            $message = $lang['server_error'];
            error_log("Login error: " . $e->getMessage());
        }
    }}
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
    <div class="form-box login">
        <h2>login</h2>
        <form action="" method="POST">
            <div class="input-box">
                <input type="text" name ="username" required>
                <label for="">Username</label>
                <i class="fa-solid fa-user" ></i>
            </div>
            <div class="input-box">
                <input type="password" name ="password" required>
                <label for="">Password</label>
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="input-box">
                <button class="btn" type="submit">Login</button>
            </div>
            <div class="regi-link">
                <p>don't have an account? <a href="../index.php" class="SingUplink">Sign up</a></p>
            </div>
        </form>
    </div>
    <div class="info-content login">
        <h2 style="color:#f4f6fb">WELCOME BACK!</h2>
        <p style="color:#f4f6fb">WISETECH <br>at your service</p>
    </div>
    </div>
    <script src="../assests/js/login.js"></script>
</body>
</html>