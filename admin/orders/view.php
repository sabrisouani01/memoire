<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include "../../include/db_connect.php";

$id = $_GET['id'] ?? null;
if (!$id) die("رقم الطلب غير موجود.");

// Get order + customer
$stmt = $pdo->prepare("SELECT o.*, u.First_name, u.Last_name, u.email, u.phone as user_phone FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();

if (!$order) die("الطلب غير موجود.");

// Get payment method
$method_stmt = $pdo->prepare("SELECT method_name_ar FROM payment_methods WHERE id = ?");
$method_stmt->execute([$order['payment_method_id']]);
$method = $method_stmt->fetch();

// Get order items
$item_stmt = $pdo->prepare("SELECT oi.*, p.name_ar FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$item_stmt->execute([$id]);
$items = $item_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>عرض الطلب #<?= $id ?></title>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <p>مرحباً، <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
        <hr>
        <a href="../dashboard.php">الرئيسية</a>
        <a href="index.php">الطلبيات</a>
        <a href="../../auth/logout.php" class="logout">🔐 تسجيل الخروج</a>
    </div>

    <div class="content">
        <h2>عرض تفاصيل الطلب #<?= $id ?></h2>
        <div style="background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">

            <p><strong>العميل:</strong> <?= htmlspecialchars($order['First_name'] . ' ' . $order['Last_name']) ?></p>
            <p><strong>البريد:</strong> <?= htmlspecialchars($order['email']) ?></p>
            <p><strong>الهاتف:</strong> <?= htmlspecialchars($order['phone'] ?? $order['user_phone']) ?></p>
            <p><strong>عنوان الشحن:</strong> <?= nl2br(htmlspecialchars($order['shipping_address'])) ?></p>
            <p><strong>طريقة الدفع:</strong> <?= htmlspecialchars($method['method_name_ar'] ?? 'غير محدد') ?></p>
            <p><strong>الحالة:</strong> <span style="padding: 4px 8px; background: #ffc107; border-radius: 6px;"><?= htmlspecialchars($order['status']) ?></span></p>
            <p><strong>التاريخ:</strong> <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?></p>
            <p><strong>المجموع:</strong> <strong><?= number_format($order['total_amount'], 2) ?> دج</strong></p>
            <script>
fetch('../../api/warranty-status.php?order_id=<?= $order['id'] ?>')
.then(res => res.json())
.then(data => {
    const el = document.getElementById('warranty-status');
    el.innerHTML = `
        <strong>الضمان:</strong> 
        <span style="color: ${data.is_active ? 'green' : 'red'}">
            ${data.message}
        </span>
    `;
})
.catch(() => {
    document.getElementById('warranty-status').textContent = 'تعذر تحميل حالة الضمان.';
});
</script>

<div id="warranty-status">جارٍ تحميل حالة الضمان...</div>

            <h4>المنتجات:</h4>
            <table class="table">
                <tr><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr>
                <?php foreach ($items as $i): ?>
                    <tr>
                        <td><?= htmlspecialchars($i['name_ar']) ?></td>
                        <td><?= $i['quantity'] ?></td>
                        <td><?= number_format($i['unit_price'], 2) ?> دج</td>
                        <td><?= number_format($i['unit_price'] * $i['quantity'], 2) ?> دج</td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <a href="index.php" class="btn btn-primary">العودة إلى القائمة</a>
        </div>
    </div>
</body>
</html>