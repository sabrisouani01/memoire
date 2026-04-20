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
    <link rel="stylesheet" href="../assests/css/user.css" />
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>

<body>

<!-- ===== HEADER ===== -->
<header class="top-header">
    <div class="nav-container">
        <div class="nav-icons">
            <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
            <div class="user-info" id="user-menu-trigger">
                <i class="fa-solid fa-user"></i>
                <span class="username" id="username"><?php echo $username; ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:11px;"></i>
            </div>
            <a href="#" class="icon-box" id="search-icon" title="بحث"><i class="fa-solid fa-magnifying-glass"></i></a>
            <a href="#" class="icon-box" id="cart-icon" title="السلة"><i class="fa-solid fa-cart-shopping"></i></a>
        </div>

        <nav class="nav-links" id="nav-links">
            <a href="#home" class="active">الرئيسية</a>
            <a href="#products">المنتجات</a>
            <a href="#warranty">الضمان</a>
            <a href="#contact">اتصل بنا</a>
            <?php if ($isLoggedIn): ?>
                <a href="orders/orders.php">طلباتي</a>
                <a href="repairs/repairs.php">الصيانة</a>
            <?php endif; ?>
        </nav>

        <a href="#home" class="logo">
            <span class="logo-text">Wise<span>Tech</span></span>
        </a>
    </div>

    <!-- USER DROPDOWN -->
    <div class="dropdown-menu" id="user-dropdown">
        <?php if ($isLoggedIn): ?>
            <a href="orders/orders.php"><i class="fa-solid fa-box"></i> طلباتي</a>
            <a href="repairs/repairs.php"><i class="fa-solid fa-wrench"></i> الصيانة</a>
            <a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
        <?php else: ?>
            <a href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> تسجيل الدخول</a>
            <a href="../auth/register.php"><i class="fa-solid fa-user-plus"></i> إنشاء حساب</a>
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
                    <input type="text" id="search-term" placeholder="مثل: iPhone..." />
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
                    <input type="number" id="min-price" placeholder="0" min="0" />
                </div>
                <div class="search-group">
                    <label>الحد الأقصى (دج)</label>
                    <input type="number" id="max-price" placeholder="100000" min="0" />
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

<!-- ===== HERO ===== -->
<section class="hero-section" id="home">
    <div class="hero-content">
        <div class="hero-badge">
            <i class="fa-solid fa-bolt"></i>
            أفضل الأجهزة بأفضل الأسعار
        </div>
        <h1 class="hero-title">
            تقنية <span>حديثة</span><br/>بين يديك الآن
        </h1>
        <p class="hero-subtitle">
            اكتشف مجموعتنا المتميزة من الهواتف الذكية واللابتوبات مع ضمان الجودة وخدمة ما بعد البيع.
        </p>
        <a href="#products" class="hero-cta">
            <i class="fa-solid fa-arrow-down"></i>
            تصفح المنتجات
        </a>
    </div>
</section>

<!-- ===== PRODUCTS ===== -->
<section class="products-section" id="products">
    <div class="section-header">
        <span class="section-label">منتجاتنا</span>
        <h2 class="section-title">اختر جهازك المثالي</h2>
        <div class="section-line"></div>
    </div>
    <div class="products">
        <?php
        require_once '../include/db_connect.php';
        $stmt = $pdo->query("SELECT p.*, c.id AS category_id FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_active = 1 ORDER BY p.created_at DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($products)):
            foreach ($products as $p):
        ?>
            <div class="product-card" data-category="<?= $p['category_id'] ?>">
                <?php if (!empty($p['image_url'])): ?>
                    <div class="product-top">
                        <img
                            src="../assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
                            alt="<?= htmlspecialchars($p['name_ar']) ?>"
                            onclick="toggleDetails(this)" />
                        <div class="details">
                            <p><i class="fa-solid fa-memory" style="color:var(--primary);"></i> الرام: <?= htmlspecialchars($p['ram'] ?? 'غير محدد') ?></p>
                            <p><i class="fa-solid fa-hard-drive" style="color:var(--primary);"></i> السعة: <?= htmlspecialchars($p['storage'] ?? 'غير محدد') ?></p>
                            <p><i class="fa-solid fa-camera" style="color:var(--primary);"></i> الكاميرا: <?= htmlspecialchars($p['camera'] ?? 'غير محدد') ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="product-bottom">
                    <h3><?= htmlspecialchars($p['name_ar']) ?></h3>
                    <p class="price"><?= number_format($p['price'], 2) ?> دج</p>
                    <button class="buy-btn"
                        data-id="<?= $p['id'] ?>"
                        data-name="<?= htmlspecialchars($p['name_ar']) ?>"
                        data-price="<?= $p['price'] ?>"
                        data-img="../assests/uploads/<?= htmlspecialchars($p['image_url']) ?>">
                        <i class="fa-solid fa-cart-plus"></i> أضف للسلة
                    </button>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="empty-state" style="grid-column:1/-1">
                <i class="fa-solid fa-box-open"></i>
                <h3>لا توجد منتجات</h3>
                <p>لا توجد منتجات متاحة حالياً، يرجى المحاولة لاحقاً.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ===== WARRANTY ===== -->
<section class="warranty-conditions" id="warranty">
    <div class="section-header">
        <span class="section-label">الضمان</span>
        <h2 class="section-title">شروط الضمان الأساسية</h2>
        <div class="section-line"></div>
    </div>
    <div class="conditions-container">
        <div class="condition-card">
            <div class="condition-number">1</div>
            <div class="condition-content">
                <h3>الاحتفاظ بالتغليف الأصلي الكامل</h3>
                <p>العلبة، الحماية الداخلية، الملصقات والملحقات يُعد شرطًا أساسيًا للاستفادة من الضمان.</p>
            </div>
        </div>
        <div class="condition-card">
            <div class="condition-number">2</div>
            <div class="condition-content">
                <h3>الضمان لا يشمل استرجاع المبلغ</h3>
                <p>الضمان لا يمنح الزبون الحق في استرجاع المبلغ المدفوع. في حال وجود عطل، يتم الإصلاح أو الاستبدال فقط.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== CONTACT ===== -->
<section class="contact-info-section" id="contact">
    <div class="contact-wrapper">
        <div class="contact-left">
            <div class="section-label" style="text-align:right;">تواصل معنا</div>
        </div>
        <div class="contact-right">
            <h2>Contact Info</h2>
            <div class="contact-item">
                <div class="icon-circle"><i class="fa-solid fa-location-dot"></i></div>
                <div class="text">
                    <h4>Address</h4>
                    <p>Skikda, Algeria</p>
                </div>
            </div>
            <div class="contact-item">
                <div class="icon-circle"><i class="fa-solid fa-phone"></i></div>
                <div class="text">
                    <h4>Phone</h4>
                    <p>0655880712 - 0673633916</p>
                </div>
            </div>
            <div class="contact-item">
                <div class="icon-circle"><i class="fa-solid fa-envelope"></i></div>
                <div class="text">
                    <h4>Email</h4>
                    <p>contact@wisetech.dz</p>
                </div>
            </div>
            <div class="contact-item">
                <div class="icon-circle"><i class="fa-solid fa-clock"></i></div>
                <div class="text">
                    <h4>Working Hours</h4>
                    <p>24 / 7</p>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="footer">
    <p>© 2025 <strong>Wise Tech</strong> - جميع الحقوق محفوظة</p>
</footer>

<script>
    window.IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
</script>
<script src="../assests/js/user.js"></script>
</body>
</html>
