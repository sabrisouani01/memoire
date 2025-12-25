<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name_ar = trim($_POST['name_ar']);
    $name_fr = trim($_POST['name_fr']);
    $name_en = trim($_POST['name_en']);

    if (empty($name_ar) || empty($name_fr) || empty($name_en)) {
        $message = "جميع الحقول مطلوبة.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name_ar, name_fr, name_en) VALUES (?, ?, ?)");
            $stmt->execute([$name_ar, $name_fr, $name_en]);

            header("Location: index.php");
            exit;
        } catch (PDOException $e) {
            $message = "خطأ في الإدخال.";
            error_log("Category add error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>➕ إضافة تصنيف</title>
    <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f8f9fa; }
        .container { max-width: 600px; margin: 40px auto; padding: 30px; background: white; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-control { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 6px; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .alert { padding: 12px; margin: 10px 0; background: #f8d7da; color: #721c24; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>➕ إضافة تصنيف جديد</h2>

        <?php if ($message): ?>
            <div class="alert"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <div>
                <label>الاسم (عربي)</label>
                <input type="text" name="name_ar" class="form-control" required>
            </div>
            <div>
                <label>Nom (Français)</label>
                <input type="text" name="name_fr" class="form-control" required>
            </div>
            <div>
                <label>Name (English)</label>
                <input type="text" name="name_en" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">💾 حفظ التصنيف</button>
            <a href="index.php" class="btn btn-secondary">⬅️ رجوع</a>
        </form>
    </div>
</body>
</html>