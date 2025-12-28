<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

$customer_id = $_GET['id'] ?? null;
$orders = [];
$customers = $pdo->query("SELECT id, username, email, First_name, Last_name, phone, created_at FROM users WHERE role = 'customer' ORDER BY created_at DESC")->fetchAll();

if ($customer_id) {
    $stmt = $pdo->prepare("
        SELECT o.*, pm.method_name_ar, SUM(oi.quantity * oi.unit_price) as total
        FROM orders o
        LEFT JOIN payment_methods pm ON o.payment_method_id = pm.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$customer_id]);
    $orders = $stmt->fetchAll();
}
?>
    <!-- Main Content -->
    <div class="content">
        <h2>👥 إدارة العملاء</h2>

        <!-- All Customers Table -->
        <h3>📋 جميع العملاء</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>الاسم</th>
                    <th>اسم المستخدم</th>
                    <th>البريد</th>
                    <th>الهاتف</th>
                    <th>تاريخ الإنشاء</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><?= htmlspecialchars($c['First_name'] . ' ' . $c['Last_name']) ?></td>
                            <td><?= htmlspecialchars($c['username']) ?></td>
                            <td><?= htmlspecialchars($c['email']) ?></td>
                            <td><?= htmlspecialchars($c['phone'] ?? 'غير محدد') ?></td>
                            <td><?= date('Y-m-d', strtotime($c['created_at'])) ?></td>
                            <td>
                                <a href="?id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">عرض الطلبات</a>
                                <a href="delete.php?id=<?= $c['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا العميل وجميع بياناته؟')">
                                    🗑 حذف الحساب
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            <strong>⚠️ لا توجد عملاء</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Order History -->
        <?php if ($customer_id): ?>
            <h3>🛒 سجل الطلبات</h3>
            <?php if (!empty($orders)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>رقم الطلب</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                            <th>طريقة الدفع</th>
                            <th>المجموع</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><?= $o['id'] ?></td>
                                <td><?= date('Y-m-d', strtotime($o['created_at'])) ?></td>
                                <td><?= htmlspecialchars($o['status']) ?></td>
                                <td><?= htmlspecialchars($o['method_name_ar'] ?? 'غير محدد') ?></td>
                                <td><?= number_format($o['total'], 2) ?> دج</td>
                                <td>
                                    <a href="../orders/view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-primary">عرض التفاصيل</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">لا توجد طلبات لهذا العميل.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>