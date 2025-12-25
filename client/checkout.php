<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../includes/db_connect.php';
$user_id = $_SESSION['user_id'];

// Handle POST from localStorage cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_data'])) {
    $cartData = json_decode($_POST['cart_data'], true);
    if (!$cartData || !is_array($cartData)) {
        die("بيانات السلة غير صالحة.");
    }

    // Validate and fetch real product prices from DB (security!)
    $cartItems = [];
    $total = 0;
    foreach ($cartData as $item) {
        $stmt = $pdo->prepare("SELECT id, name_ar, price, stock_quantity FROM products WHERE id = ? AND is_active = 1");
        $stmt->execute([$item['id']]);
        $product = $stmt->fetch();

        if (!$product) {
            die("المنتج غير موجود: " . htmlspecialchars($item['name']));
        }

        if ($product['stock_quantity'] < ($item['qty'] ?? 1)) {
            die("الكمية غير متوفرة للمنتج: " . htmlspecialchars($product['name_ar']));
        }

        $qty = max(1, (int)($item['qty'] ?? 1));
        $price = (float)$product['price'];
        $cartItems[] = [
            'id' => $product['id'],
            'name_ar' => $product['name_ar'],
            'price' => $price,
            'quantity' => $qty
        ];
        $total += $price * $qty;
    }
} else {
    // Fallback: load from DB cart (if you ever implement it)
    $cartStmt = $pdo->prepare("
        SELECT ci.product_id, p.name_ar, p.price, ci.quantity
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.user_id = ?
    ");
    $cartStmt->execute([$user_id]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($cartItems)) {
        die("سلة التسوق فارغة!");
    }
    $total = array_reduce($cartItems, fn($sum, $i) => $sum + $i['price'] * $i['quantity'], 0);
}

// Rest of checkout: payment methods, form, etc.
$methodsStmt = $pdo->prepare("SELECT id, method_name_ar FROM payment_methods ORDER BY id");
$methodsStmt->execute();
$paymentMethods = $methodsStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission (same as before)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['cart_data'])) {
    // This is the actual order submission (after address/payment form)
    $payment_method_id = $_POST['payment_method_id'] ?? null;
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$payment_method_id || !$shipping_address || !$phone) {
        $error = "يرجى تعبئة جميع الحقول.";
    } else {
        try {
            $pdo->beginTransaction();

            // Calculate warranty expiry (using real product categories)
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

            // Insert order
            $orderStmt = $pdo->prepare("
                INSERT INTO orders (
                    user_id, total_amount, payment_method_id, 
                    shipping_address, phone, status, warranty_expiry, created_at
                ) VALUES (?, ?, ?, ?, ?, 'pending', ?, NOW())
            ");
            $orderStmt->execute([$user_id, $total, $payment_method_id, $shipping_address, $phone, $warrantyExpiry]);
            $orderId = $pdo->lastInsertId();

            // Insert order items
            $itemStmt = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, unit_price)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($cartItems as $item) {
                $itemStmt->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
            }

            // Optional: Clear localStorage cart by redirecting
            // (We can't clear it here, but we redirect to success page)

            $pdo->commit();
            header("Location: client/orders/index.php?order_success=1");
            exit();

        } catch (Exception $e) {
            $pdo->rollback();
            $error = "حدث خطأ أثناء إنشاء الطلب. حاول مرة أخرى.";
        }
    }
}
?>

<!-- Rest of your HTML form stays the same -->
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إتمام الطلب</title>
    <link rel="stylesheet" href="assets/css/user.css">
    <style>
        .cart-summary { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { background: #007BFF; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .error { color: red; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>إتمام الطلب</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <!-- Cart Summary -->
    <div class="cart-summary">
        <h3>ملخص الطلب</h3>
        <?php foreach ($cartItems as $item): ?>
            <p>
                <?= htmlspecialchars($item['name_ar']) ?> × <?= $item['quantity'] ?> — 
                <?= number_format($item['price'] * $item['quantity'], 2) ?> دج
            </p>
        <?php endforeach; ?>
        <hr>
        <p><strong>المجموع:</strong> <?= number_format($total, 2) ?> دج</p>
    </div>

    <!-- Checkout Form -->
    <form method="POST">
        <div class="form-group">
            <label for="shipping_address">عنوان التسليم:</label>
            <textarea id="shipping_address" name="shipping_address" rows="3" required><?= $_POST['shipping_address'] ?? '' ?></textarea>
        </div>

        <div class="form-group">
            <label for="phone">رقم الهاتف:</label>
            <input type="text" id="phone" name="phone" value="<?= $_POST['phone'] ?? '' ?>" required>
        </div>

        <div class="form-group">
            <label for="payment_method">طريقة الدفع:</label>
            <select id="payment_method" name="payment_method_id" required>
                <option value="">-- اختر طريقة الدفع --</option>
                <?php foreach ($paymentMethods as $method): ?>
                    <option value="<?= $method['id'] ?>" <?= (($_POST['payment_method_id'] ?? '') == $method['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($method['method_name_ar']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn">تأكيد الطلب</button>
        <a href="javascript:history.back()" class="btn" style="background:#6c757d">العودة</a>
    </form>
</body>
</html>