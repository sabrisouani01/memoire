<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../include/db_connect.php';
$user_id = $_SESSION['user_id'];

// ✅ Get delivered orders with warranty info
$stmt = $pdo->prepare("
    SELECT 
        oi.id AS order_item_id,
        o.id AS order_id,
        p.id AS product_id,
        p.name_ar AS product_name,
        p.category_id,
        o.warranty_expiry,
        c.warranty_duration,
        o.created_at AS order_date
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
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
        r.is_warranty_claim,
        r.is_external_item
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
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }
        body { background: #f5f5f5; padding: 20px; direction: rtl; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2, h3 { color: #333; margin: 20px 0 15px; text-align: center; }
        .card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #ddd; margin-bottom: 20px; }
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; justify-content: center; }
        .tab-btn { padding: 10px 20px; border: 2px solid #0d6efd; background: #fff; color: #0d6efd; border-radius: 6px; cursor: pointer; font-weight: 600; }
        .tab-btn.active, .tab-btn:hover { background: #0d6efd; color: #fff; }
        .form-section { display: none; }
        .form-section.active { display: block; }
        label { display: block; margin: 12px 0 6px; font-weight: bold; color: #444; }
        select, textarea, input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; direction: rtl; }
        textarea { min-height: 80px; resize: vertical; }
        button[type="submit"] { background: #0d6efd; color: #fff; border: none; padding: 12px 20px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; width: 100%; margin-top: 10px; }
        button[type="submit"]:hover { background: #0b5ed7; }
        .repair-item { padding: 15px 0; border-bottom: 1px dashed #eee; }
        .repair-item:last-child { border-bottom: none; }
        .repair-item p { margin: 5px 0; font-size: 14px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; background: #e9ecef; color: #495057; }
        .warranty-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-right: 8px; }
        .warranty-active { background: #d4edda; color: #155724; }
        .warranty-expired { background: #f8d7da; color: #721c24; }
        .warranty-none { background: #fff3cd; color: #856404; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 15px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .empty-message { text-align: center; color: #666; font-style: italic; padding: 20px; }
        .nav-links { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #eee; }
        .nav-links a { display: inline-block; margin: 0 10px; text-decoration: none; color: #0d6efd; font-weight: bold; }
        .delete-btn { background: #dc3545; color: #fff; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; margin-top: 8px; }
        .delete-btn:hover { background: #c82333; }
        .action-disabled { color: #999; font-size: 13px; font-style: italic; }
        @media (max-width: 600px) {
            .container { padding: 15px; }
            .tabs { flex-direction: column; }
            .tab-btn { width: 100%; }
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
                'not_eligible' => '❌ هذا المنتج غير مؤهل للصيانة تحت الضمان.',
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

        <!-- ✅ Tabs for Internal vs External Repair -->
        <div class="tabs">
            <button class="tab-btn active" onclick="showTab('internal')">📦 منتجات من الموقع</button>
            <button class="tab-btn" onclick="showTab('external')">🛍️ منتجات من المتجر (خارج الموقع)</button>
        </div>

        <!-- ✅ Internal Repair Form (Products bought from site) -->
        <div id="internal" class="form-section active">
            <?php if (!empty($deliveredItems)): ?>
                <div class="card">
                    <form action="submit_repair.php" method="POST">
                        <input type="hidden" name="repair_type" value="internal">
                        
                        <label for="product_id">اختر المنتج:</label>
                        <select name="product_id" id="product_id" required>
                            <option value="">-- اختر منتجًا --</option>
                            <?php foreach ($deliveredItems as $item): 
                                $isUnderWarranty = $item['warranty_expiry'] && strtotime($item['warranty_expiry']) >= time();
                            ?>
                                <option value="<?= $item['product_id'] ?>" 
                                        data-warranty="<?= $item['warranty_expiry'] ?>"
                                        data-duration="<?= htmlspecialchars($item['warranty_duration']) ?>">
                                    <?= htmlspecialchars($item['product_name']) ?>
                                    <?php if ($item['warranty_expiry']): ?>
                                        - <span class="warranty-badge <?= $isUnderWarranty ? 'warranty-active' : 'warranty-expired' ?>">
                                            <?= $isUnderWarranty ? 'ضمن الضمان' : 'انتهى الضمان' ?>
                                        </span>
                                        (<?= htmlspecialchars($item['warranty_duration']) ?>)
                                    <?php else: ?>
                                        - <span class="warranty-badge warranty-none">بدون ضمان</span>
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
                <div class="empty-message">ليس لديك منتجات مُسلّمة من الموقع حتى الآن.</div>
            <?php endif; ?>
        </div>

        <!-- ✅ External Repair Form (Walk-in / Shop purchases) -->
        <div id="external" class="form-section">
            <div class="card">
                <form action="submit_repair.php" method="POST">
                    <input type="hidden" name="repair_type" value="external">
                    
                    <label for="external_item">اسم الجهاز / المنتج:</label>
                    <input type="text" name="external_item" id="external_item" placeholder="مثال: آيفون 13 برو" required>

                    <label for="external_phone">رقم الهاتف:</label>
                    <input type="tel" name="external_phone" id="external_phone" placeholder="05XXXXXXXX" value="<?= htmlspecialchars($_SESSION['phone'] ?? '') ?>" required>

                    <label for="external_description">وصف المشكلة:</label>
                    <textarea name="external_description" id="external_description" placeholder="اذكر تفاصيل العطل..." required></textarea>

                    <label>
                        <input type="checkbox" name="damage_from_factory" value="1">
                        العطل من المصنع (ليس بسبب الاستخدام)
                    </label>

                    <button type="submit">📤 إرسال طلب الصيانة</button>
                </form>
            </div>
            <p style="text-align:center; color:#666; font-size:14px; margin-top:10px;">
                ⚠️ المنتجات التي لم تُشترَ من الموقع لا تشملها سياسة الضمان، وسيتم تقييمها بشكل منفصل.
            </p>
        </div>

        <!-- ✅ Past Repairs List -->
        <h3>📜 طلبات الصيانة السابقة</h3>
        <?php if (empty($repairs)): ?>
            <div class="empty-message">لم تقم بأي طلبات صيانة بعد.</div>
        <?php else: ?>
            <div class="card">
                <?php foreach ($repairs as $r): ?>
                    <div class="repair-item">
                        <p><strong>📦 المنتج:</strong> <?= htmlspecialchars($r['product_name'] ?? ($r['is_external_item'] ? 'منتج خارجي' : 'غير معروف')) ?></p>
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
                        <p><strong>🛡️ النوع:</strong> 
                            <?php if ($r['is_external_item']): ?>
                                <span style="color:#856404;">🛍️ منتج خارجي (بدون ضمان)</span>
                            <?php else: ?>
                                <?= $r['is_warranty_claim'] ? '✅ ضمن الضمان' : '❌ خارج الضمان' ?>
                            <?php endif; ?>
                        </p>
                        <p><strong>📅 التاريخ:</strong> <?= date('Y-m-d', strtotime($r['created_at'])) ?></p>
                        
                        <?php if ($r['status'] === 'pending' || $r['status'] === 'cancelled'): ?>
                            <form action="delete_repair.php" method="POST" style="display:inline;" 
                                  onsubmit="return confirm('⚠️ هل أنت متأكد من حذف هذا الطلب؟');">
                                <input type="hidden" name="repair_id" value="<?= $r['id'] ?>">
                                <button type="submit" class="delete-btn">🗑️ حذف</button>
                            </form>
                        <?php else: ?>
                            <span class="action-disabled">🔒 لا يمكن الحذف</span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="nav-links">
            <a href="../index.php">🏠 الرئيسية</a>
            <a href="../orders/orders.php">📦 طلباتي</a>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            event.target.classList.add('active');
        }
        // Clean URL after message
        if (window.location.search.includes('success=1') || window.location.search.includes('deleted=1') || window.location.search.includes('error=')) {
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname);
            }
        }
    </script>
</body>
</html>