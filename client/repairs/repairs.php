<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../includes/db_connect.php';

$user_id = $_SESSION['user_id'];

// Get all orders with products that are still under warranty
$stmt = $pdo->prepare("
    SELECT 
        oi.id AS order_item_id,
        o.id AS order_id,
        p.id AS product_id,
        p.name_ar AS product_name,
        o.warranty_expiry
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ? 
      AND o.warranty_expiry IS NOT NULL 
      AND o.warranty_expiry >= CURDATE()
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$warrantyItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get past repair requests
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
    <link rel="stylesheet" href="../../assets/css/user.css">
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
        }
        .repair-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
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
        }
        .nav-links a {
            display: inline-block;
            margin: 0 10px;
            text-decoration: none;
            color: #0d6efd;
            font-weight: bold;
        }
        .nav-links a:hover {
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
        <h2>طلب صيانة (ضمن الضمان)</h2>

        <?php if (!empty($warrantyItems)): ?>
            <div class="card">
                <form action="submit_repair.php" method="POST">
                    <label for="product_id">اختر المنتج الذي تريد صيانته:</label>
                    <select name="product_id" id="product_id" required>
                        <option value="">-- اختر منتجًا --</option>
                        <?php foreach ($warrantyItems as $item): ?>
                            <option value="<?= $item['product_id'] ?>">
                                <?= htmlspecialchars($item['product_name']) ?> 
                                (ضمان حتى: <?= $item['warranty_expiry'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <label for="description">وصف المشكلة:</label>
                    <textarea name="description" id="description" placeholder="اذكر تفاصيل العطل..." required></textarea>

                    <button type="submit">إرسال طلب الصيانة</button>
                </form>
            </div>
        <?php else: ?>
            <div class="empty-message">
                ليس لديك منتجات مؤهلة للصيانة تحت الضمان في الوقت الحالي.
            </div>
        <?php endif; ?>

        <h3>طلبات الصيانة السابقة</h3>
        <?php if (empty($repairs)): ?>
            <div class="empty-message">لم تقم بأي طلبات صيانة بعد.</div>
        <?php else: ?>
            <div class="card">
                <?php foreach ($repairs as $r): ?>
                    <div class="repair-item">
                        <p><strong>المنتج:</strong> <?= htmlspecialchars($r['product_name'] ?? 'غير معروف') ?></p>
                        <p><strong>الوصف:</strong> <?= htmlspecialchars($r['description']) ?></p>
                        <p><strong>الحالة:</strong> 
                            <span class="status-badge">
                                <?php
                                $status_labels = [
                                    'pending' => 'قيد الانتظار',
                                    'in_progress' => 'قيد التنفيذ',
                                    'completed' => 'مكتمل',
                                    'unrepairable' => 'لا يمكن إصلاحه',
                                    'cancelled' => 'ملغى'
                                ];
                                echo $status_labels[$r['status']] ?? $r['status'];
                                ?>
                            </span>
                        </p>
                        <p><strong>نوع الطلب:</strong> 
                            <?= $r['is_warranty_claim'] ? 'ضمن الضمان' : 'خارج الضمان' ?>
                        </p>
                        <p><strong>تاريخ الطلب:</strong> <?= date('Y-m-d', strtotime($r['created_at'])) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="nav-links">
            <a href="../orders/orders.php">عرض الطلبات</a>
            <a href="../auth/logout.php">تسجيل الخروج</a>
        </div>
    </div>
</body>
</html>