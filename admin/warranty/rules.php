<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../includes/db_connect.php";

if ($_POST['add'] ?? null) {
    $cat = (int)$_POST['category_id'];
    $dur = (int)$_POST['duration'];
    $desc = trim($_POST['description']);
    $pdo->prepare("INSERT INTO warranty_rules (category_id, duration_months, description) VALUES (?, ?, ?)")
        ->execute([$cat, $dur, $desc]);
    header("Location: rules.php"); exit;
}

if ($_GET['delete'] ?? null) {
    $pdo->prepare("DELETE FROM warranty_rules WHERE id = ?")->execute([(int)$_GET['delete']]);
    header("Location: rules.php"); exit;
}

$rules = $pdo->query("
    SELECT wr.*, c.name_ar 
    FROM warranty_rules wr 
    JOIN categories c ON wr.category_id = c.id
")->fetchAll();
$categories = $pdo->query("SELECT id, name_ar FROM categories")->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <title>⚖️ قواعد الضمان</title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <p>مرحباً، <strong><?= $_SESSION['username'] ?></strong></p>
        <hr>
        <a href="../dashbord.php">الرئيسية</a>
        <a href="rules.php" class="active">⚖️ القواعد</a>
        <a href="claims.php">🛡️ المطالبات</a><a href="../auth/logout.php" class="logout">
            🔐 تسجيل الخروج
        </a>
    </div>

    <div class="content">
        <h2>➕ إضافة قاعدة</h2>
        <form method="post">
            <select name="category_id" required>
                <option value="">اختر تصنيف</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['name_ar'] ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="duration" placeholder="المدة (أشهر)" required>
            <textarea name="description" placeholder="الوصف"></textarea>
            <button type="submit" name="add">💾 حفظ</button>
        </form>

        <h3>القواعد الحالية</h3>
        <table class="table">
            <tr><th>التصنيف</th><th>المدة</th><th>الوصف</th><th>الإجراءات</th></tr>
            <?php foreach ($rules as $r): ?>
                <tr>
                    <td><?= $r['name_ar'] ?></td>
                    <td><?= $r['duration_months'] ?> شهر</td>
                    <td><?= htmlspecialchars($r['description']) ?></td>
                    <td><a href="?delete=<?= $r['id'] ?>" class="btn btn-danger" onclick="return confirm('حذف؟')">🗑️</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>