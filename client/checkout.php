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

            // Calculate warranty expiry
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

            // ✅ ✅ ✅ DEDUCT STOCK FROM PRODUCTS TABLE ✅ ✅ ✅
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

            // Clear session cart
            unset($_SESSION['checkout_cart']);
            unset($_SESSION['checkout_total']);

            $pdo->commit();
            header("Location: ../client/orders/orders.php?order_success=1");
            exit();

        } catch (Exception $e) {
            $pdo->rollback();
            $error = "حدث خطأ أثناء إنشاء الطلب. الرجاء المحاولة لاحقًا.";
            // Optional: log error → error_log($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إتمام الطلب</title>
    <link rel="stylesheet" href="../assets/css/user.css">
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .cart-summary { background: white; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn { background: #0d6efd; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0b5ed7; }
        .error { color: red; background: #f8d7da; padding: 10px; border-radius: 4px; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>إتمام الطلب</h2>

    <?php if (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

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