<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../include/db_connect.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT 
        o.id AS order_id,
        o.total_amount,
        o.status,
        o.created_at,
        o.warranty_expiry,
        GROUP_CONCAT(p.name_ar SEPARATOR ', ') AS products
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي</title>
    <link rel="stylesheet" href="../../assets/css/user.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 15px;
            text-align: right;
        }
        .orders-table th,
        .orders-table td {
            padding: 14px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
        }
        .orders-table th {
            background-color: #f1f3f5;
            font-weight: bold;
            color: #495057;
        }
        .orders-table tr:hover {
            background-color: #f8fafc;
        }
        .status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }
        .status.pending { background-color: #fff3cd; color: #856404; }
        .status.processing { background-color: #cce7ff; color: #004085; }
        .status.shipped { background-color: #d1ecf1; color: #0c5460; }
        .status.delivered { background-color: #d4edda; color: #155724; }
        .status.cancelled { background-color: #f8d7da; color: #721c24; }

        .actions {
            text-align: center;
            margin-top: 25px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            padding: 10px 20px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            transition: background 0.2s;
        }
        .btn:hover { background: #0b5ed7; }
        .btn-primary { background: #198754; }
        .btn-primary:hover { background: #157347; }

        /* ✅ Delete Button Styles */
        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-delete:hover { background: #c82333; }
        .action-disabled {
            color: #999;
            font-size: 13px;
            font-style: italic;
        }

        /* ✅ Alert Styles */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-weight: 500;
        }
        .alert-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
        .alert-error { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

        .empty-message {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 30px;
        }

        @media (max-width: 768px) {
            .orders-table th, .orders-table td { padding: 10px 8px; font-size: 14px; }
            .orders-table { display: block; overflow-x: auto; }
            .actions { flex-direction: column; }
            .btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- ✅ Alert Messages -->
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">✅ تم إلغاء وحذف الطلب بنجاح.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <?php
            $errorMessages = [
                'invalid' => '❌ طلب غير صالح.',
                'unauthorized' => '❌ غير مسموح لك بحذف هذا الطلب.',
                'status' => '❌ لا يمكن حذف الطلبات التي تم معالجتها أو تسليمها.',
                'failed' => '❌ فشل حذف الطلب. يرجى المحاولة لاحقاً.'
            ];
            $errorMsg = $errorMessages[$_GET['error']] ?? '❌ حدث خطأ غير معروف.';
            ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <h2>📦 طلباتي</h2>

        <?php if (empty($orders)): ?>
            <div class="empty-message">ليس لديك طلبات حتى الآن.</div>
        <?php else: ?>
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>📋 رقم الطلب</th>
                        <th>📦 المنتجات</th>
                        <th>💰 المبلغ</th>
                        <th>🔄 الحالة</th>
                        <th>📅 تاريخ الطلب</th>
                        <th>🛡️ نهاية الضمان</th>
                        <th>⚙️ الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($order['order_id']) ?></td>
                        <td><?= htmlspecialchars($order['products']) ?></td>
                        <td><?= number_format($order['total_amount'], 2) ?> دج</td>
                        <td>
                            <span class="status <?= htmlspecialchars($order['status']) ?>">
                                <?php
                                $status_labels = [
                                    'pending' => 'قيد الانتظار',
                                    'processing' => 'قيد المعالجة',
                                    'shipped' => 'تم الشحن',
                                    'delivered' => 'تم التسليم',
                                    'cancelled' => 'ملغى'
                                ];
                                echo $status_labels[$order['status']] ?? $order['status'];
                                ?>
                            </span>
                        </td>
                        <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                        <td>
                            <?php if ($order['warranty_expiry']): ?>
                                <?= htmlspecialchars($order['warranty_expiry']) ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td>
                            <!-- ✅ DELETE BUTTON (Only for pending orders) -->
                            <?php if ($order['status'] === 'pending'): ?>
                                <form action="delete_order.php" method="POST" style="display:inline;" 
                                      onsubmit="return confirm('⚠️ هل أنت متأكد من إلغاء وحذف هذا الطلب؟\nلا يمكن التراجع عن هذا الإجراء.');">
                                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                    <button type="submit" class="btn-delete">🗑️ حذف</button>
                                </form>
                            <?php else: ?>
                                <span class="action-disabled">🔒 غير متاح</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="actions">
            <a href="../repairs/repairs.php" class="btn btn-primary">🔧 طلب صيانة</a>
            <a href="../index.php" class="btn">🏠 العودة</a>
        </div>
    </div>

    <script>
        // Clean URL after showing message
        if (window.location.search.includes('deleted=1') || window.location.search.includes('error=')) {
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname);
            }
        }
    </script>
</body>
</html>