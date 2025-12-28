<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

// Fetch all orders with customer info
$sql = "SELECT o.*, u.First_name, u.Last_name, u.username, u.email, u.phone as user_phone
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.id DESC";
$stmt = $pdo->query($sql);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
    <!-- Main Content -->
    <div class="content">
        <h2>📑 إدارة الطلبيات</h2>

        <table class="table">
            <thead>
                <tr>
                    <th>الرقم</th>
                    <th>العميل</th>
                    <th>البريد</th>
                    <th>هاتف العميل</th>
                    <th>المجموع (دج)</th>
                    <th>الحالة</th>
                    <th>طريقة الدفع</th>
                    <th>التاريخ</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td><?= htmlspecialchars($o['id']) ?></td>
                            <td><?= htmlspecialchars($o['First_name'] . ' ' . $o['Last_name']) ?></td>
                            <td><?= htmlspecialchars($o['email']) ?></td>
                            <td><?= htmlspecialchars($o['phone'] ?? $o['user_phone']) ?></td>
                            <td><?= number_format($o['total_amount'], 2) ?></td>
                            <td>
                                <span style="padding: 4px 8px; border-radius: 6px; background: #ffc107; color: #111;">
                                    <?= htmlspecialchars($o['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $method = $pdo->prepare("SELECT method_name_ar FROM payment_methods WHERE id = ?");
                                $method->execute([$o['payment_method_id']]);
                                $row = $method->fetch();
                                echo htmlspecialchars($row['method_name_ar'] ?? 'غير محدد');
                                ?>
                            </td>
                            <td><?= date('Y-m-d H:i', strtotime($o['created_at'])) ?></td>
                            <td>
                                <a href="view.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-primary">👁️ عرض</a>
                                <a href="edit.php?id=<?= $o['id'] ?>" class="btn btn-sm btn-warning">✏️ تعديل</a>
                                <a href="delete.php?id=<?= $o['id'] ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا الطلب؟')">
                                    🗑 حذف
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted">
                            <strong>لا توجد طلبيات</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>