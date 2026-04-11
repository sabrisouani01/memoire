<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../include/db_connect.php';
$user_id = $_SESSION['user_id'];

// ✅ ALL delivered orders (no warranty filter)
$stmt = $pdo->prepare("
    SELECT 
        oi.id AS order_item_id,
        o.id AS order_id,
        p.id AS product_id,
        p.name_ar AS product_name,
        o.warranty_expiry,
        o.status
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ? 
      AND o.status = 'delivered'
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$deliveredItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Past repair requests
$repairsStmt = $pdo->prepare("
    SELECT 
        r.id, 
        p.name_ar AS product_name, 
        r.description, 
        r.status, 
        r.created_at,
        r.is_warranty_claim
    FROM repairs r
    LEFT JOIN products p ON r.product_id = p.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$repairsStmt->execute([$user_id]);
$repairs = $repairsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلبات الصيانة</title>
    <link rel="stylesheet" href="../../assests/css/user.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        h2, h3 {
            color: #333;
            margin: 20px 0 15px;
        }
        h2 {
            text-align: center;
            font-size: 24px;
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }
        label {
            display: block;
            margin: 12px 0 6px;
            font-weight: bold;
            color: #495057;
        }
        select, textarea, input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 15px;
            direction: rtl;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        button[type="submit"] {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: #0b5ed7;
        }
        .repair-item {
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
            margin-bottom: 15px;
        }
        .repair-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }
        .repair-item p {
            margin: 6px 0;
            line-height: 1.5;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            background: #e9ecef;
            color: #495057;
        }
        .alert {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-success {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .alert-error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
            width: auto;
            margin-top: 8px;
        }
        .delete-btn:hover {
            background: #bb2d3b;
        }
        .repair-note {
            color: #6c757d;
            font-size: 13px;
            margin-top: 8px;
            font-style: italic;
        }
        .empty-message {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 20px 0;
        }
        .nav-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        .nav-links a {
            text-decoration: none;
            font-weight: bold;
            padding: 8px 16px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        .nav-home {
            background: #198754;
            color: white !important;
        }
        .nav-home:hover {
            background: #157347;
        }
        .nav-orders {
            color: #0d6efd;
        }
        .nav-orders:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                padding: 15px;
            }
            h2 {
                font-size: 22px;
            }
            select, textarea, button {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- ✅ Alert Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success">✅ تم إرسال طلب الصيانة بنجاح! سيتم مراجعته قريباً.</div>
        <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success">✅ تم حذف طلب الصيانة بنجاح.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <?php
            $errorMessages = [
                'missing_fields' => '❌ يرجى تعبئة جميع الحقول المطلوبة.',
                'not_eligible' => '❌ هذا المنتج غير مؤهل للصيانة (الطلب لم يُسلّم بعد).',
                'database' => '❌ حدث خطأ أثناء إرسال الطلب. يرجى المحاولة لاحقاً.',
                'invalid_request' => '❌ طلب غير صالح.',
                'unauthorized' => '❌ غير مسموح لك بحذف هذا الطلب.',
                'delete_failed' => '❌ فشل حذف الطلب. يرجى المحاولة لاحقاً.'
            ];
            $errorMsg = $errorMessages[$_GET['error']] ?? '❌ حدث خطأ غير معروف.';
            ?>
            <div class="alert alert-error"><?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <h2>🔧 طلب صيانة</h2>

        <?php if (!empty($deliveredItems)): ?>
            <div class="card">
                <form action="submit_repair.php" method="POST">
                    <label for="product_id">اختر المنتج الذي تريد صيانته:</label>
                    <select name="product_id" id="product_id" required>
                        <option value="">-- اختر منتجًا --</option>
                        <?php foreach ($deliveredItems as $item): 
                            $hasWarranty = $item['warranty_expiry'] && strtotime($item['warranty_expiry']) >= time();
                        ?>
                            <option value="<?= $item['product_id'] ?>">
                                <?= htmlspecialchars($item['product_name']) ?>
                                <?php if ($item['warranty_expiry']): ?>
                                    (<?= $hasWarranty ? 'ضمن الضمان حتى: ' . htmlspecialchars($item['warranty_expiry']) : 'انتهى الضمان: ' . htmlspecialchars($item['warranty_expiry']) ?>)
                                <?php else: ?>
                                    (بدون ضمان)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="description">وصف المشكلة:</label>
                    <textarea name="description" id="description" placeholder="اذكر تفاصيل العطل..." required></textarea>

                    <button type="submit">📤 إرسال طلب الصيانة</button>
                </form>
            </div>
        <?php else: ?>
            <div class="empty-message">
                ليس لديك منتجات مُسلّمة حتى الآن.
            </div>
        <?php endif; ?>

        <h3>📜 طلبات الصيانة السابقة</h3>
        <?php if (empty($repairs)): ?>
            <div class="empty-message">لم تقم بأي طلبات صيانة بعد.</div>
        <?php else: ?>
            <div class="card">
                <?php foreach ($repairs as $r): ?>
                    <div class="repair-item">
                        <p><strong>📦 المنتج:</strong> <?= htmlspecialchars($r['product_name'] ?? 'غير معروف') ?></p>
                        <p><strong>📝 الوصف:</strong> <?= htmlspecialchars($r['description']) ?></p>
                        <p><strong>🔄 الحالة:</strong> 
                            <span class="status-badge">
                                <?php
                                $status_labels = [
                                    'pending' => 'قيد الانتظار',
                                    'in_progress' => 'قيد التنفيذ',
                                    'completed' => 'مكتمل',
                                    'unrepairable' => 'لا يمكن إصلاحه',
                                    'cancelled' => 'ملغى'
                                ];
                                echo $status_labels[$r['status']] ?? htmlspecialchars($r['status']);
                                ?>
                            </span>
                        </p>
                        <p><strong>🛡️ نوع الطلب:</strong> 
                            <?= $r['is_warranty_claim'] ? 'ضمن الضمان' : 'خارج الضمان' ?>
                        </p>
                        <p><strong>📅 تاريخ الطلب:</strong> <?= date('Y-m-d', strtotime($r['created_at'])) ?></p>
                        
                        <!-- ✅ Delete Button (only show for pending/cancelled requests) -->
                        <?php if ($r['status'] === 'pending' || $r['status'] === 'cancelled'): ?>
                            <form action="delete_repair.php" method="POST" style="display:inline;" 
                                  onsubmit="return confirm('⚠️ هل أنت متأكد من حذف طلب الصيانة هذا؟\n\nلا يمكن التراجع عن هذا الإجراء.');">
                                <input type="hidden" name="repair_id" value="<?= $r['id'] ?>">
                                <button type="submit" class="delete-btn">🗑️ حذف الطلب</button>
                            </form>
                        <?php else: ?>
                            <p class="repair-note">🔒 لا يمكن حذف الطلبات المكتملة أو قيد التنفيذ</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ✅ Navigation Buttons (Logout Removed) -->
        <div class="nav-links">
            <a href="../index.php" class="nav-home">🏠 الصفحة الرئيسية</a>
            <a href="../orders/orders.php" class="nav-orders">📦 عرض الطلبات</a>
        </div>
    </div>

    <script>
        // Clean URL after showing success/error message
        if (window.location.search.includes('success=1') || window.location.search.includes('deleted=1') || window.location.search.includes('error=')) {
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname);
            }
        }
    </script>
</body>
</html>