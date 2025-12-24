<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $password = 'technician123'; // Default password

    if (empty($first_name) || empty($last_name) || empty($email) || empty($username)) {
        $message = "جميع الحقول المطلوبة يجب ملؤها.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "يرجى إدخال بريد إلكتروني صالح.";
    } else {
        try {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $message = "هذا البريد الإلكتروني مُستخدم مسبقًا.";
            } else {
                // Check username
                $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $stmt->execute([$username]);
                if ($stmt->rowCount() > 0) {
                    $message = "اسم المستخدم مُستخدم مسبقًا.";
                } else {
                    // Hash password
                    $password_hash = password_hash($password, PASSWORD_BCRYPT);

                    // Insert as technician
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, First_name, Last_name, phone, role) VALUES (?, ?, ?, ?, ?, ?, 'technician')");
                    $stmt->execute([$username, $email, $password_hash, $first_name, $last_name, $phone]);

                    $message = "تم إنشاء حساب الفني بنجاح! كلمة المرور الافتراضية: $password";
                }
            }
        } catch (Exception $e) {
            $message = "حدث خطأ أثناء التسجيل.";
            error_log("Technician add error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>➕ إضافة فني</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <p>مرحباً، <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        <hr>
        <a href="add.php" class="active">➕ إضافة فني</a>
        <a href="index.php">قائمة الفنيين</a>
        <a href="../dashboard.php">الرئيسية</a>
        <a href="../repairs/index.php">الإصلاحات</a>
        <a href="../../auth/logout.php" class="logout">🔐 تسجيل الخروج</a>
    </div>

    <div class="content">
        <h2>➕ إضافة فني جديد</h2>

        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post" style="max-width: 500px;">
            <div>
                <label>الاسم الأول</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div>
                <label>الاسم الأخير</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
            <div>
                <label>البريد الإلكتروني</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div>
                <label>اسم المستخدم</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div>
                <label>رقم الهاتف</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <br>
            <button type="submit" class="btn btn-success">💾 إنشاء الحساب</button>
            <a href="index.php" class="btn btn-secondary">العودة</a>
        </form>
    </div>
</body>
</html>