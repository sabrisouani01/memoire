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
    <link rel="stylesheet" href="../assests/css/enhancements.css" />
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>

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
            <div id="lang-switcher"></div>
        </div>

        <nav class="nav-links" id="nav-links">
            <a href="#home" class="active" data-i18n="nav_home">الرئيسية</a>
            <a href="#products" data-i18n="nav_products">المنتجات</a>
            <a href="#warranty" data-i18n="nav_warranty">الضمان</a>
            <a href="#contact" data-i18n="nav_contact">اتصل بنا</a>
            <?php if ($isLoggedIn): ?>
                <a href="orders/orders.php" data-i18n="nav_orders">طلباتي</a>
                <a href="repairs/repairs.php" data-i18n="nav_repairs">الصيانة</a>
            <?php endif; ?>
        </nav>

        <a href="#home" class="logo"><span class="logo-text">Wise<span>Tech</span></span></a>
    </div>

    <div class="dropdown-menu" id="user-dropdown">
        <?php if ($isLoggedIn): ?>
            <a href="orders/orders.php"><i class="fa-solid fa-box"></i> <span data-i18n="nav_orders">طلباتي</span></a>
            <a href="repairs/repairs.php"><i class="fa-solid fa-wrench"></i> <span data-i18n="nav_repairs">الصيانة</span></a>
            <a href="../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span data-i18n="nav_logout">تسجيل الخروج</span></a>
        <?php else: ?>
            <a href="../auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> <span data-i18n="nav_login">تسجيل الدخول</span></a>
            <a href="../auth/register.php"><i class="fa-solid fa-user-plus"></i> <span data-i18n="nav_register">إنشاء حساب</span></a>
        <?php endif; ?>
    </div>

    <div class="search-modal" id="search-modal">
        <div class="search-modal-content">
            <span class="close-search" id="close-search">&times;</span>
            <h3 data-i18n="search_title">بحث متقدم</h3>
            <form id="search-form">
                <div class="search-group">
                    <label data-i18n="search_name_label">اسم المنتج</label>
                    <input type="text" id="search-term" data-i18n-placeholder="search_name_placeholder" placeholder="مثل: iPhone..." />
                </div>
                <div class="search-group">
                    <label data-i18n="search_category_label">الفئة</label>
                    <select id="category-filter">
                        <option value="" data-i18n="cat_all">الكل</option>
                        <option value="1">الهواتف</option>
                        <option value="2">لابتوبات</option>
                    </select>
                </div>
                <div class="search-group">
                    <label data-i18n="search_min_label">الحد الأدنى</label>
                    <input type="number" id="min-price" placeholder="0" min="0" />
                </div>
                <div class="search-group">
                    <label data-i18n="search_max_label">الحد الأقصى</label>
                    <input type="number" id="max-price" placeholder="100000" min="0" />
                </div>
                <button type="submit" class="search-btn" data-i18n="search_btn">بحث</button>
            </form>
        </div>
    </div>

    <div class="cart-modal" id="cart-modal">
        <div class="cart-modal-content">
            <span class="close-cart" id="close-cart">&times;</span>
            <h3 data-i18n="cart_title">سلة المشتريات</h3>
            <div id="cart-items-list">
                <p id="empty-cart-message" data-i18n="cart_empty">سلة التسوق فارغة.</p>
            </div>
            <div class="cart-total">
                <strong data-i18n="cart_total">المجموع:</strong> <span id="total-amount">0 دج</span>
            </div>
            <div class="cart-actions">
                <button class="checkout" id="checkout-btn" data-i18n="cart_checkout">إتمام الشراء</button>
                <button class="clear" id="clear-cart-btn" data-i18n="cart_clear">تفريغ السلة</button>
            </div>
        </div>
    </div>
</header>

<section class="hero-section" id="home">
    <div class="hero-content">
        <div class="hero-badge"><i class="fa-solid fa-bolt"></i> <span data-i18n="hero_badge">Next Generation Electronics</span></div>
        <h1 class="hero-title" data-i18n="hero_title">أهلاً بك في <span>Wise Tech</span></h1>
        <p class="hero-subtitle" data-i18n="hero_subtitle">اكتشف مجموعتنا المتميزة من الهواتف الذكية واللابتوبات مع ضمان الجودة وخدمة ما بعد البيع.</p>
        <a href="#products" class="hero-cta"><i class="fa-solid fa-arrow-down"></i> <span data-i18n="hero_cta_shop">تصفح المنتجات</span></a>
    </div>
</section>

<section class="products-section" id="products">
    <div class="section-header">
        <span class="section-label" data-i18n="nav_products">منتجاتنا</span>
        <h2 class="section-title" data-i18n="sec_products_sub">اختر جهازك المثالي</h2>
        <div class="section-line"></div>
    </div>
    <div class="products">
        <?php
        require_once '../include/db_connect.php';
        $stmt = $pdo->query("
            SELECT p.*, c.id AS category_id,
                   c.name_ar AS category_name_ar, c.name_fr AS category_name_fr, c.name_en AS category_name_en
            FROM products p
            JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = 1
            ORDER BY p.created_at DESC
        ");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($products)):
            foreach ($products as $p):
                $extraImages = [];
                if (!empty($p['extra_images'])) { $d = json_decode($p['extra_images'], true); if (is_array($d)) $extraImages = $d; }
                $colors = [];
                if (!empty($p['colors'])) { $d = json_decode($p['colors'], true); if (is_array($d)) $colors = $d; }
        ?>
            <div class="product-card"
                 data-category="<?= $p['category_id'] ?>"
                 data-id="<?= $p['id'] ?>"
                 data-name-ar="<?= htmlspecialchars($p['name_ar'] ?? '') ?>"
                 data-name-fr="<?= htmlspecialchars($p['name_fr'] ?? '') ?>"
                 data-name-en="<?= htmlspecialchars($p['name_en'] ?? '') ?>"
                 data-desc-ar="<?= htmlspecialchars($p['description_ar'] ?? '') ?>"
                 data-desc-fr="<?= htmlspecialchars($p['description_fr'] ?? '') ?>"
                 data-desc-en="<?= htmlspecialchars($p['description_en'] ?? '') ?>"
                 data-price="<?= $p['price'] ?>"
                 data-img="../assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
                 data-extra-images='<?= htmlspecialchars(json_encode($extraImages)) ?>'
                 data-colors='<?= htmlspecialchars(json_encode($colors)) ?>'
                 data-ram="<?= htmlspecialchars($p['ram'] ?? '') ?>"
                 data-storage="<?= htmlspecialchars($p['storage'] ?? '') ?>"
                 data-camera="<?= htmlspecialchars($p['camera'] ?? '') ?>"
                 data-category-name-ar="<?= htmlspecialchars($p['category_name_ar'] ?? '') ?>"
                 data-category-name-fr="<?= htmlspecialchars($p['category_name_fr'] ?? '') ?>"
                 data-category-name-en="<?= htmlspecialchars($p['category_name_en'] ?? '') ?>"
            >
                <?php if (!empty($p['image_url'])): ?>
                    <div class="product-top" onclick="openProductPopup(this.closest('.product-card'))">
                        <img src="../assests/uploads/<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name_ar']) ?>" />
                        <div class="product-overlay-hint"><i class="fa-solid fa-eye"></i></div>
                    </div>
                <?php endif; ?>
                <div class="product-bottom">
                    <h3 class="product-name"><?= htmlspecialchars($p['name_ar']) ?></h3>
                    <p class="price"><?= number_format($p['price'], 2) ?> <span class="currency-label">دج</span></p>
                    <button class="buy-btn"
                            data-id="<?= $p['id'] ?>"
                            data-name="<?= htmlspecialchars($p['name_ar']) ?>"
                            data-price="<?= $p['price'] ?>"
                            data-img="../assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
                            onclick="openProductPopup(this.closest('.product-card'))">
                        <i class="fa-solid fa-eye"></i> <span data-i18n="btn_details">عرض التفاصيل</span>
                    </button>
                </div>
            </div>
        <?php endforeach; else: ?>
            <div class="empty-state" style="grid-column:1/-1">
                <i class="fa-solid fa-box-open"></i>
                <h3 data-i18n="no_products">لا توجد منتجات</h3>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="warranty-conditions" id="warranty">
    <div class="section-header">
        <span class="section-label" data-i18n="nav_warranty">الضمان</span>
        <h2 class="section-title" data-i18n="sec_warranty_title">شروط الضمان الأساسية</h2>
        <div class="section-line"></div>
    </div>
    <div class="conditions-container">
        <div class="condition-card"><div class="condition-number">1</div><div class="condition-content"><h3 data-i18n="w1_title">الاحتفاظ بالتغليف الأصلي الكامل</h3><p data-i18n="w1_desc">العلبة، الحماية الداخلية، الملصقات والملحقات يُعد شرطًا أساسيًا.</p></div></div>
        <div class="condition-card"><div class="condition-number">2</div><div class="condition-content"><h3 data-i18n="w2_title">الضمان لا يشمل استرجاع المبلغ</h3><p data-i18n="w2_desc">الضمان لا يمنح الزبون الحق في استرجاع المبلغ المدفوع.</p></div></div>
    </div>
</section>

<section class="contact-info-section" id="contact">
    <div class="contact-wrapper">
        <div class="contact-left"><div class="section-label" style="text-align:right;" data-i18n="sec_contact_title">تواصل معنا</div></div>
        <div class="contact-right">
            <h2 data-i18n="sec_contact_title">تواصل معنا</h2>
            <div class="contact-item"><div class="icon-circle"><i class="fa-solid fa-location-dot"></i></div><div class="text"><h4 data-i18n="contact_address_title">Address</h4><p>Skikda, Algeria</p></div></div>
            <div class="contact-item"><div class="icon-circle"><i class="fa-solid fa-phone"></i></div><div class="text"><h4 data-i18n="contact_phone_title">Phone</h4><p>0655880712 - 0673633916</p></div></div>
            <div class="contact-item"><div class="icon-circle"><i class="fa-solid fa-envelope"></i></div><div class="text"><h4 data-i18n="contact_email_title">Email</h4><p>contact@wisetech.dz</p></div></div>
            <div class="contact-item"><div class="icon-circle"><i class="fa-solid fa-clock"></i></div><div class="text"><h4 data-i18n="contact_hours_title">Working Hours</h4><p>24 / 7</p></div></div>
        </div>
    </div>
</section>

<footer class="footer">
    <p data-i18n="footer_copy">© 2025 <strong>Wise Tech</strong> - جميع الحقوق محفوظة</p>
</footer>

<!-- Product Popup -->
<div class="product-popup-overlay" id="product-popup-overlay">
  <div class="product-popup" id="product-popup">
    <button class="popup-close" id="popup-close">&times;</button>
    <div class="popup-images">
      <img src="" alt="" class="popup-main-img" id="popup-main-img" />
      <div class="popup-thumbs" id="popup-thumbs"></div>
    </div>
    <div class="popup-info">
      <div class="popup-category-tag"><i class="fa-solid fa-tag"></i> <span id="popup-category-name">—</span></div>
      <div class="popup-product-name" id="popup-name">—</div>
      <div class="popup-price" id="popup-price">—</div>
      <div class="popup-specs" id="popup-specs"></div>
      <div class="popup-colors-row" id="popup-colors-row" style="display:none;">
        <div class="popup-colors-label" data-i18n="popup_colors">الألوان المتاحة</div>
        <div class="popup-color-swatches" id="popup-swatches"></div>
      </div>
      <div class="popup-desc" id="popup-desc-wrap">
        <div class="popup-desc-title" data-i18n="popup_description">الوصف</div>
        <p id="popup-desc-text">—</p>
      </div>
      <div class="popup-actions">
        <button class="popup-add-cart-btn" id="popup-add-cart-btn">
          <i class="fa-solid fa-cart-plus"></i>
          <span data-i18n="popup_add_cart">أضف إلى السلة</span>
        </button>
      </div>
    </div>
  </div>
</div>

<div class="wt-toast" id="wt-toast"></div>

<script>
    window.IS_LOGGED_IN = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
</script>
<script src="../assests/js/i18n.js"></script>
<script src="../assests/js/user.js"></script>
<script src="../assests/js/popup.js"></script>
</body>
</html>
