<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../includes/db_connect.php";
$lang = include "../../languages/ar.php"; // Use your language system

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $password1 = $_POST['password1'];
    $password2 = $_POST['password2'];

    // Validation
    if (empty($first_name) || empty($last_name)) {
        $message = 'يرجى إدخال الاسم الأول والاسم الأخير.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'يرجى إدخال بريد إلكتروني صالح.';
    } elseif (empty($username)) {
        $message = 'اسم المستخدم مطلوب.';
    } elseif (strlen($password1) < 6) {
        $message = 'يجب أن تكون كلمة المرور مكونة من 6 أحرف على الأقل.';
    } elseif ($password1 !== $password2) {
        $message = 'كلمتا المرور غير متطابقتين.';
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $message = 'هذا البريد الإلكتروني مُستخدم مسبقًا.';
            } else {
                // Check if username exists
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->rowCount() > 0) {
                    $message = 'اسم المستخدم مُستخدم مسبقًا.';
                } else {
                    // Hash password
                    $password_hash = password_hash($password1, PASSWORD_BCRYPT);

                    // Insert new admin
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, First_name, Last_name, role) VALUES (?, ?, ?, ?, ?, 'admin')");
                    $stmt->execute([$username, $email, $password_hash, $first_name, $last_name]);

                    $message = 'تم إنشاء حساب المدير بنجاح!';
                }
            }
        } catch (Exception $e) {
            $message = 'حدث خطأ أثناء التسجيل.';
            error_log("Admin registration error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>➕ إضافة مدير</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <a href="../dashboard.php">الرئيسية</a>
        <a href="../products/index.php">المنتجات</a>
        <a href="../services/index.php">الخدمات</a>
        <a href="../orders/index.php">الطلبات</a>
        <a href="../customers/index.php">العملاء</a>
        <a href="add_admin.php" class="active">➕ إضافة مدير</a>
        <a href="../logout.php">تسجيل الخروج</a>
    </div>

    <div class="content">
        <h2>➕ إضافة مدير جديد</h2>

        <?php if ($message): ?>
            <div style="padding: 12px; margin: 10px 0; background: #d4edda; color: #155724; border-radius: 6px;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <form method="post" style="max-width: 500px;">
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">الاسم الأول</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">الاسم الأخير</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">اسم المستخدم</label>
                <input type="text" name="username" class="form-control" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">كلمة المرور</label>
                <input type="password" name="password1" class="form-control" required>
            </div>

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px;">تأكيد كلمة المرور</label>
                <input type="password" name="password2" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">➕ إنشاء حساب مدير</button>
            <a href="../dashboard.php" class="btn btn-secondary">العودة</a>
        </form>
    </div>
</body>
</html>