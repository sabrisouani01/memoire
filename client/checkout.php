<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../include/db_connect.php';
$user_id = $_SESSION['user_id'];

// Step 1: Handle cart_data from localStorage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $cartData = json_decode($_POST['cart_data'], true);
    if (!$cartData || !is_array($cartData)) {
        die("بيانات السلة غير صالحة.");
    }

    $cartItems = [];
    foreach ($cartData as $item) {
        $stmt = $pdo->prepare("SELECT id, name_ar, price, stock_quantity FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();

        if (!$product) {
            die("المنتج غير موجود: " . htmlspecialchars($item['name'] ?? 'غير معروف'));
        }

        $requestedQty = max(1, (int)($item['qty'] ?? 1));
        if ($product['stock_quantity'] < $requestedQty) {
            die("الكمية غير متوفرة للمنتج: " . htmlspecialchars($product['name_ar']));
        }

        $cartItems[] = [
            'id' => $product['id'],
            'name_ar' => $product['name_ar'],
            'price' => (float)$product['price'],
            'quantity' => $requestedQty
        ];
    }

    $_SESSION['checkout_cart'] = $cartItems;
    $_SESSION['checkout_total'] = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cartItems));
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Step 2: Load cart from session
if (!isset($_SESSION['checkout_cart']) || empty($_SESSION['checkout_cart'])) {
    die("سلة التسوق فارغة!");
}

$cartItems = $_SESSION['checkout_cart'];
$total = $_SESSION['checkout_total'];

// Load payment methods
$methodsStmt = $pdo->prepare("SELECT id, method_name_ar FROM payment_methods ORDER BY id");
$methodsStmt->execute();
$paymentMethods = $methodsStmt->fetchAll(PDO::FETCH_ASSOC);

// Step 3: Process order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['cart_data'])) {
    $payment_method_id = $_POST['payment_method_id'] ?? null;
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$payment_method_id || !$shipping_address || !$phone) {
        $error = "يرجى تعبئة جميع الحقول.";
    } else {
        try {
            $pdo->beginTransaction();

            $warrantyExpiry = null;
            foreach ($cartItems as $item) {
                $wStmt = $pdo->prepare("
                    SELECT wr.duration_months 
                    FROM products p
                    JOIN warranty_rules wr ON p.category_id = wr.category_id
                    WHERE p.id = ?
                ");
                $wStmt->execute([$item['id']]);
                $duration = $wStmt->fetchColumn();

                if ($duration) {
                    $expiry = date('Y-m-d', strtotime("+$duration months"));
                    if (!$warrantyExpiry || $expiry > $warrantyExpiry) {
                        $warrantyExpiry = $expiry;
                    }
                }
            }

            $orderStmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, total_amount, payment_method_id, 
                    shipping_address, phone, status, warranty_expiry, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $orderStmt->execute([$user_id, $total, $payment_method_id, $shipping_address, $phone, $warrantyExpiry]);
            $orderId = $pdo->lastInsertId();

            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($cartItems as $item) {
                $itemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
            }

            $updateStock = $pdo->prepare("
                UPDATE products 
                SET stock_quantity = stock_quantity - :quantity 
                WHERE id = :product_id
            ");
            foreach ($cartItems as $item) {
                $updateStock->execute([
                    ':quantity' => $item['quantity'],
                    ':product_id' => $item['id']
                ]);
            }

            unset($_SESSION['checkout_cart']);
            unset($_SESSION['checkout_total']);

            $pdo->commit();
            header("Location: ../client/orders/orders.php?order_success=1");
            exit();

        } catch (Exception $e) {
            $pdo->rollback();
            $error = "حدث خطأ أثناء إنشاء الطلب. الرجاء المحاولة لاحقًا.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إتمام الطلب — Wise Tech</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --accent: #f59e0b;
            --success: #10b981;
            --danger: #ef4444;
            --dark: #0f172a;
            --dark-2: #1e293b;
            --gray-1: #334155;
            --gray-2: #64748b;
            --gray-3: #94a3b8;
            --gray-4: #cbd5e1;
            --gray-5: #e2e8f0;
            --gray-6: #f1f5f9;
            --white: #ffffff;
            --radius: 16px;
            --radius-sm: 10px;
            --shadow: 0 4px 24px rgba(0,0,0,0.07), 0 1px 4px rgba(0,0,0,0.04);
            --shadow-lg: 0 12px 48px rgba(37,99,235,0.13), 0 2px 8px rgba(0,0,0,0.06);
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Cairo', sans-serif;
            background: #f0f4ff;
            color: var(--dark);
            direction: rtl;
            min-height: 100vh;
        }

        /* ── Background decoration ── */
        body::before {
            content: '';
            position: fixed;
            top: -200px; left: -200px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(37,99,235,0.08) 0%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        /* ── TOP BAR ── */
        .topbar {
            background: var(--white);
            border-bottom: 1px solid var(--gray-5);
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 1px 8px rgba(0,0,0,0.05);
        }
        .topbar-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 900;
            color: var(--primary);
            text-decoration: none;
            letter-spacing: -0.5px;
        }
        .topbar-logo span { color: var(--accent); }
        .topbar-steps {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-3);
        }
        .topbar-steps .step {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .topbar-steps .step.active { color: var(--primary); }
        .topbar-steps .sep { color: var(--gray-4); font-size: 10px; }
        .topbar-back {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--gray-2);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            transition: all 0.2s;
        }
        .topbar-back:hover {
            background: var(--gray-6);
            color: var(--primary);
        }

        /* ── PAGE LAYOUT ── */
        .checkout-page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 24px 80px;
            position: relative;
            z-index: 1;
        }

        .page-title {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .page-subtitle {
            color: var(--gray-2);
            font-size: 15px;
            margin-bottom: 36px;
        }

        .checkout-grid {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 28px;
            align-items: start;
        }

        /* ── CARD ── */
        .card {
            background: var(--white);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }
        .card-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--gray-5);
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .card-icon {
            width: 40px; height: 40px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
        }
        .card-icon.blue { background: var(--primary-light); color: var(--primary); }
        .card-icon.amber { background: #fffbeb; color: var(--accent); }
        .card-header-text h3 {
            font-size: 15px;
            font-weight: 700;
            color: var(--dark);
        }
        .card-header-text p {
            font-size: 12px;
            color: var(--gray-3);
        }
        .card-body { padding: 24px; }

        /* ── ERROR ALERT ── */
        .alert-error {
            margin: 0 0 24px;
            padding: 14px 18px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: var(--radius-sm);
            color: var(--danger);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── FORM ELEMENTS ── */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group:last-child { margin-bottom: 0; }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: var(--gray-1);
            margin-bottom: 8px;
            letter-spacing: 0.2px;
        }

        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--gray-5);
            border-radius: var(--radius-sm);
            font-family: 'Cairo', sans-serif;
            font-size: 14px;
            color: var(--dark);
            background: var(--gray-6);
            transition: all 0.2s;
            outline: none;
            direction: rtl;
        }
        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            border-color: var(--primary);
            background: var(--white);
            box-shadow: 0 0 0 4px rgba(37,99,235,0.08);
        }
        textarea { resize: vertical; min-height: 90px; line-height: 1.6; }
        select { appearance: none; cursor: pointer; }
        .select-wrapper { position: relative; }
        .select-wrapper::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-3);
            pointer-events: none;
            font-size: 13px;
        }

        /* Payment Methods as Cards */
        .payment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 10px;
            margin-top: 4px;
        }
        .payment-option {
            position: relative;
        }
        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0; height: 0;
        }
        .payment-option label {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 14px 10px;
            border: 2px solid var(--gray-5);
            border-radius: var(--radius-sm);
            background: var(--gray-6);
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            color: var(--gray-2);
            transition: all 0.2s;
            text-align: center;
            margin-bottom: 0;
        }
        .payment-option label i {
            font-size: 20px;
            color: var(--gray-3);
            transition: all 0.2s;
        }
        .payment-option input[type="radio"]:checked + label {
            border-color: var(--primary);
            background: var(--primary-light);
            color: var(--primary);
        }
        .payment-option input[type="radio"]:checked + label i {
            color: var(--primary);
        }
        .payment-option label:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Hidden select for form submission */
        #payment_method_id { display: none; }

        /* ── ORDER SUMMARY CARD ── */
        .basket-items {
            display: flex;
            flex-direction: column;
            gap: 0;
        }
        .basket-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 0;
            border-bottom: 1px solid var(--gray-5);
        }
        .basket-item:last-child { border-bottom: none; }
        .item-icon {
            width: 46px; height: 46px;
            background: linear-gradient(135deg, var(--primary-light), #dbeafe);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            color: var(--primary);
        }
        .item-details { flex: 1; min-width: 0; }
        .item-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .item-qty {
            font-size: 12px;
            color: var(--gray-3);
            margin-top: 2px;
        }
        .item-price {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--primary);
            white-space: nowrap;
        }
        .item-price span {
            font-size: 11px;
            font-weight: 500;
            color: var(--gray-3);
            font-family: 'Cairo', sans-serif;
        }

        /* Totals */
        .totals-block {
            background: var(--gray-6);
            border-radius: var(--radius-sm);
            padding: 16px;
            margin-top: 4px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: var(--gray-2);
            padding: 4px 0;
        }
        .total-row.grand {
            font-size: 16px;
            font-weight: 800;
            color: var(--dark);
            border-top: 1px solid var(--gray-5);
            margin-top: 8px;
            padding-top: 12px;
        }
        .total-row.grand .price {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            color: var(--primary);
        }

        /* Item count badge */
        .items-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: var(--white);
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 20px;
            margin-right: 6px;
        }

        /* ── SUBMIT BUTTON ── */
        .btn-checkout {
            width: 100%;
            padding: 16px 24px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            border: none;
            border-radius: var(--radius-sm);
            font-family: 'Cairo', sans-serif;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.25s;
            box-shadow: 0 4px 20px rgba(37,99,235,0.35);
            margin-top: 16px;
            letter-spacing: 0.3px;
        }
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(37,99,235,0.45);
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }
        .btn-checkout:active { transform: translateY(0); }
        .btn-checkout .icon-lock { font-size: 13px; opacity: 0.85; }

        /* Security note */
        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 12px;
            font-size: 12px;
            color: var(--gray-3);
        }
        .security-note i { color: var(--success); }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .checkout-grid {
                grid-template-columns: 1fr;
            }
            .topbar-steps { display: none; }
            .checkout-page { padding: 24px 16px 60px; }
            .page-title { font-size: 22px; }
        }

        /* ── STEP INDICATORS ── */
        .steps-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 32px;
        }
        .step-dot {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            position: relative;
        }
        .step-dot-circle {
            width: 36px; height: 36px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            font-weight: 700;
            transition: all 0.2s;
        }
        .step-dot.done .step-dot-circle {
            background: var(--success);
            color: var(--white);
        }
        .step-dot.active .step-dot-circle {
            background: var(--primary);
            color: var(--white);
            box-shadow: 0 0 0 5px rgba(37,99,235,0.15);
        }
        .step-dot.idle .step-dot-circle {
            background: var(--gray-5);
            color: var(--gray-3);
        }
        .step-dot-label {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-3);
        }
        .step-dot.active .step-dot-label { color: var(--primary); }
        .step-dot.done .step-dot-label { color: var(--success); }
        .step-line {
            width: 60px; height: 2px;
            background: var(--gray-5);
            margin-bottom: 18px;
        }
        .step-line.done { background: var(--success); }
    </style>
</head>
<body>

<!-- Top Bar -->
<header class="topbar">
    <a href="../client/index.php" class="topbar-logo">Wise<span>Tech</span></a>
    <div class="topbar-steps">
        <div class="step active"><i class="fa-solid fa-cart-shopping"></i> السلة</div>
        <div class="sep"><i class="fa-solid fa-chevron-left"></i></div>
        <div class="step active"><i class="fa-solid fa-file-lines"></i> إتمام الطلب</div>
        <div class="sep"><i class="fa-solid fa-chevron-left"></i></div>
        <div class="step"><i class="fa-solid fa-circle-check"></i> التأكيد</div>
    </div>
    <a href="javascript:history.back()" class="topbar-back">
        <i class="fa-solid fa-arrow-right"></i>
        العودة
    </a>
</header>

<div class="checkout-page">

    <!-- Steps Bar -->
    <div class="steps-bar">
        <div class="step-dot done">
            <div class="step-dot-circle"><i class="fa-solid fa-check"></i></div>
            <div class="step-dot-label">السلة</div>
        </div>
        <div class="step-line done"></div>
        <div class="step-dot active">
            <div class="step-dot-circle">2</div>
            <div class="step-dot-label">بيانات الطلب</div>
        </div>
        <div class="step-line"></div>
        <div class="step-dot idle">
            <div class="step-dot-circle">3</div>
            <div class="step-dot-label">التأكيد</div>
        </div>
    </div>

    <h1 class="page-title">إتمام الطلب</h1>
    <p class="page-subtitle">أدخل بيانات التوصيل واختر طريقة الدفع لإتمام عملية الشراء</p>

    <?php if (isset($error)): ?>
    <div class="alert-error">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <form method="POST" id="checkout-form">
        <input type="hidden" name="payment_method_id" id="payment_method_id">

        <div class="checkout-grid">

            <!-- LEFT: Form -->
            <div style="display:flex; flex-direction:column; gap:24px;">

                <!-- Delivery Info -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon blue"><i class="fa-solid fa-location-dot"></i></div>
                        <div class="card-header-text">
                            <h3>معلومات التوصيل</h3>
                            <p>أدخل عنوان التسليم ورقم التواصل</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="shipping_address"><i class="fa-solid fa-map-pin" style="color:var(--primary);margin-left:6px;"></i>عنوان التسليم</label>
                            <textarea id="shipping_address" name="shipping_address" rows="3" placeholder="أدخل عنوانك الكامل: الولاية، الحي، الشارع..." required><?= htmlspecialchars($_POST['shipping_address'] ?? '') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label for="phone"><i class="fa-solid fa-phone" style="color:var(--primary);margin-left:6px;"></i>رقم الهاتف</label>
                            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="05xxxxxxxx" required>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon amber"><i class="fa-solid fa-credit-card"></i></div>
                        <div class="card-header-text">
                            <h3>طريقة الدفع</h3>
                            <p>اختر الطريقة المناسبة لك</p>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="payment-grid">
                            <?php
                            $payIcons = ['fa-money-bill-wave', 'fa-credit-card', 'fa-mobile-screen-button', 'fa-building-columns', 'fa-wallet'];
                            foreach ($paymentMethods as $idx => $method):
                                $icon = $payIcons[$idx % count($payIcons)];
                                $isSelected = (($_POST['payment_method_id'] ?? '') == $method['id']);
                            ?>
                            <div class="payment-option">
                                <input type="radio" name="pay_choice" id="pay_<?= $method['id'] ?>"
                                       value="<?= $method['id'] ?>"
                                       <?= $isSelected ? 'checked' : '' ?>
                                       onchange="document.getElementById('payment_method_id').value=this.value"
                                       required>
                                <label for="pay_<?= $method['id'] ?>">
                                    <i class="fa-solid <?= $icon ?>"></i>
                                    <?= htmlspecialchars($method['method_name_ar']) ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT: Order Summary -->
            <div class="card" style="position:sticky; top:80px;">
                <div class="card-header">
                    <div class="card-icon blue"><i class="fa-solid fa-bag-shopping"></i></div>
                    <div class="card-header-text">
                        <h3>
                            <span class="items-badge"><?= count($cartItems) ?></span>
                            ملخص الطلب
                        </h3>
                        <p>مراجعة المنتجات في السلة</p>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Basket Items -->
                    <div class="basket-items">
                        <?php foreach ($cartItems as $item): ?>
                        <div class="basket-item">
                            <div class="item-icon">
                                <i class="fa-solid fa-mobile-screen-button"></i>
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['name_ar']) ?></div>
                                <div class="item-qty">الكمية: <?= $item['quantity'] ?> × <?= number_format($item['price'], 0) ?> دج</div>
                            </div>
                            <div class="item-price">
                                <?= number_format($item['price'] * $item['quantity'], 0) ?>
                                <span>دج</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Totals -->
                    <div class="totals-block">
                        <div class="total-row">
                            <span>المجموع الجزئي</span>
                            <span><?= number_format($total, 0) ?> دج</span>
                        </div>
                        <div class="total-row">
                            <span>رسوم التوصيل</span>
                            <span style="color:var(--success); font-weight:700;">مجاني</span>
                        </div>
                        <div class="total-row grand">
                            <span>المجموع الكلي</span>
                            <span class="price"><?= number_format($total, 0) ?> <small style="font-size:14px;">دج</small></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-checkout" id="submit-btn">
                        <i class="fa-solid fa-lock icon-lock"></i>
                        <span>تأكيد وإتمام الشراء</span>
                        <i class="fa-solid fa-arrow-left"></i>
                    </button>

                    <div class="security-note">
                        <i class="fa-solid fa-shield-check"></i>
                        طلبك محمي وآمن بالكامل
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    // Validate payment selection on submit
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        const paymentId = document.getElementById('payment_method_id').value;
        if (!paymentId) {
            e.preventDefault();
            alert('يرجى اختيار طريقة الدفع');
            return;
        }
        const btn = document.getElementById('submit-btn');
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> جاري المعالجة...';
        btn.disabled = true;
    });
</script>

</body>
</html>
