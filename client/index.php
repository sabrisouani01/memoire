<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$username = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : 'زائر';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Wise Tech - Client Interface</title>
    <!-- Link to user.css from assets/css folder -->
    <link rel="stylesheet" href="../assests/css/user.css" />
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>

<!-- ===== Navbar ===== -->
<header class="top-header">
    <div class="nav-container">
        <div class="logo">
            <img src="../assests/photo/photo_5823341587810339301_y.jpg" alt="Wise Tech Logo" class="logo-img" />
        </div>
        <nav class="nav-links" id="nav-links">
            <a href="#home">الرئيسية</a>
            <a href="#products">المنتجات</a>
            <a href="#contact">اتصل بنا</a>
            <!-- NEW LINKS - Updated paths -->
            <a href="./client/orders/orders.php" id="orders-link">الطلبات</a>
            <a href="./client/repairs/repairs.php" id="repairs-link">الصيانة</a>
        </nav>
        <div class="nav-icons">
            <!-- UPDATED USER INFO -->
            <div class="user-info" id="user-menu-trigger">
                <i class="fa-solid fa-user"></i>
                <span class="username" id="username"><?php echo $username; ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:12px; margin-left:5px;"></i>
            </div>
            <!-- UPDATED ICONS -->
            <a href="#" class="icon-box" id="search-icon"><i class="fa-solid fa-magnifying-glass"></i></a>
            <a href="#" class="icon-box" id="cart-icon"><i class="fa-solid fa-cart-shopping"></i></a>
            <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
        </div>
    </div>

    <!-- USER DROPDOWN -->
    <div class="dropdown-menu" id="user-dropdown">
        <?php if ($isLoggedIn): ?>
            <!-- Profile link points to orders page for now -->
            <a href="./client/orders/orders.php">الملف الشخصي</a>
            <a href="../auth/logout.php">تسجيل الخروج</a>
        <?php else: ?>
            <a href="./auth/login.php">تسجيل الدخول</a>
            <a href="./auth/register.php">إنشاء حساب</a>
        <?php endif; ?>
    </div>

    <!-- SEARCH MODAL -->
    <div class="search-modal" id="search-modal">
        <div class="search-modal-content">
            <span class="close-search" id="close-search">&times;</span>
            <h3>بحث متقدم</h3>
            <form id="search-form">
                <div class="search-group">
                    <label>اسم المنتج</label>
                    <input type="text" id="search-term" placeholder="مثل: iPhone...">
                </div>
                <div class="search-group">
                    <label>الفئة</label>
                    <select id="category-filter">
                        <option value="">الكل</option>
                        <option value="1">الهواتف</option>
                        <option value="2">لابتوبات</option>
                    </select>
                </div>
                <div class="search-group">
                    <label>الحد الأدنى (دج)</label>
                    <input type="number" id="min-price" placeholder="0" min="0">
                </div>
                <div class="search-group">
                    <label>الحد الأقصى (دج)</label>
                    <input type="number" id="max-price" placeholder="100000" min="0">
                </div>
                <button type="submit" class="search-btn">بحث</button>
            </form>
        </div>
    </div>

    <!-- CART MODAL -->
    <div class="cart-modal" id="cart-modal">
        <div class="cart-modal-content">
            <span class="close-cart" id="close-cart">&times;</span>
            <h3>سلة المشتريات</h3>
            <div id="cart-items-list">
                <p id="empty-cart-message">سلة التسوق فارغة.</p>
            </div>
            <div class="cart-total">
                <strong>المجموع:</strong> <span id="total-amount">0 دج</span>
            </div>
            <div class="cart-actions">
                <button class="checkout" id="checkout-btn">إتمام الشراء</button>
                <button class="clear" id="clear-cart-btn">تفريغ السلة</button>
            </div>
        </div>
    </div>
</header>

<!-- ===== Products ===== -->
<section class="products-section" id="products">
    <h2 class="section-title">منتجاتنا</h2>
    <div class="products">
        <div class="product-card">
            <div class="badge">جديد</div>
            <div class="product-top">
                <img src="../assests/uploads/iphone.jpg" alt="iPhone" onclick="toggleDetails(this)" />
                <div class="details">
                    <p>📱 الرام: 4GB</p>
                    <p>💾 السعة: 128GB</p>
                    <p>📷 الكاميرا: 12MP</p>
                </div>
            </div>
            <div class="product-bottom">
                <h3>iPhone 11</h3>
                <p class="price">80000 دج</p>
                <button class="buy-btn" data-id="1" data-name="iPhone 11" data-price="80000" data-img="assets/uploads/iphone.jpg">شراء</button>
            </div>
        </div>
        <!-- Add more products as needed with data-* attributes -->
    </div>
</section>

<footer class="footer">
    <p>© 2025 Wise Tech - جميع الحقوق محفوظة</p>
</footer>

<!-- Pass login state to JS -->
<script>
    window.IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
</script>
<!-- Link to user.js from assets/js folder -->
<script src="../assests/js/user.js"></script>
</body>
</html>