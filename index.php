<?php
session_start();
include "include/db_connect.php";

$isLoggedIn = isset($_SESSION['user_id']);
$username   = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : 'زائر';

$stmt = $pdo->query("
    SELECT p.*, c.id AS category_id, c.name_en AS category_name, c.name_ar AS category_name_ar, c.name_fr AS category_name_fr
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
");
$products   = $stmt->fetchAll(PDO::FETCH_ASSOC);
$categories = $pdo->query("SELECT * FROM categories ORDER BY name_en ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Wise Tech - Next Generation</title>
  <link rel="stylesheet" href="assests/css/main.css" />
  <link rel="stylesheet" href="assests/css/enhancements.css" />
  <link rel="stylesheet" href="assests/css/index_page.css" />
  <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>
  
<header class="top-header" id="top-header">
  <div class="nav-container">

    <!-- Logo -->
    <div class="logo"><span class="logo-text">WISE<span>TECH</span></span></div>

    <!-- Desktop nav links (hidden on mobile via CSS) -->
    <nav class="nav-links" id="nav-links">
      <a href="#home"             data-i18n="nav_home">الرئيسية</a>
      <a href="#products"         data-i18n="nav_products">المنتجات</a>
      <a href="#services-section" data-i18n="nav_services">خدماتنا</a>
      <a href="#warranty"         data-i18n="nav_warranty">الضمان</a>
      <a href="#contact"          data-i18n="nav_contact">اتصل بنا</a>
      <?php if ($isLoggedIn): ?>
        <a href="client/orders/orders.php"   data-i18n="nav_orders">طلباتي</a>
        <a href="client/repairs/repairs.php" data-i18n="nav_repairs">الصيانة</a>
      <?php endif; ?>
    </nav>

    <!-- Right icons -->
    <div class="nav-icons">
      <!-- Lang switcher (desktop only — also appears in drawer) -->
      <div id="lang-switcher"></div>

      <!-- Search (desktop) -->
      <a href="#" class="icon-box search-icon-desktop" id="search-icon" title="بحث">
        <i class="fa-solid fa-magnifying-glass"></i>
      </a>

      <!-- Cart -->
      <a href="#" class="icon-box" id="cart-icon" title="السلة">
        <i class="fa-solid fa-cart-shopping"></i>
        <span class="cart-badge" id="cart-badge-el"></span>
      </a>

      <!-- User chip -->
      <div class="user-info" id="user-menu-trigger">
        <i class="fa-solid fa-user"></i>
        <span class="username-text" id="username"><?= $username ?></span>
        <i class="fa-solid fa-chevron-down" style="font-size:10px;"></i>
      </div>

      <!-- Hamburger (mobile) -->
      <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
    </div>
  </div>

  <!-- User dropdown (desktop) -->
  <div class="dropdown-menu" id="user-dropdown">
    <?php if ($isLoggedIn): ?>
      <a href="client/orders/orders.php"><i class="fa-solid fa-box"></i> <span data-i18n="nav_orders">طلباتي</span></a>
      <a href="client/repairs/repairs.php"><i class="fa-solid fa-wrench"></i> <span data-i18n="nav_repairs">الصيانة</span></a>
      <a href="auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span data-i18n="nav_logout">تسجيل الخروج</span></a>
    <?php else: ?>
      <a href="auth/login.php"><i class="fa-solid fa-right-to-bracket"></i> <span data-i18n="nav_login">تسجيل الدخول</span></a>
    <?php endif; ?>
  </div>
</header>

<!-- ═══════════════════════════════════════════
     MOBILE DRAWER
     ═══════════════════════════════════════════ -->
<div class="mobile-overlay" id="mobile-overlay"></div>
<div class="mobile-drawer" id="mobile-drawer">
  <div class="drawer-header">
    <span class="drawer-logo">WISE<span>TECH</span></span>
    <button class="drawer-close" id="drawer-close"><i class="fa-solid fa-xmark"></i></button>
  </div>

  <nav class="drawer-nav">
    <a href="#home"             data-i18n="nav_home"><i class="fa-solid fa-house"></i>الرئيسية</a>
    <a href="#products"         data-i18n="nav_products"><i class="fa-solid fa-mobile-screen-button"></i>المنتجات</a>
    <a href="#services-section" data-i18n="nav_services"><i class="fa-solid fa-screwdriver-wrench"></i>خدماتنا</a>
    <a href="#warranty"         data-i18n="nav_warranty"><i class="fa-solid fa-shield-halved"></i>الضمان</a>
    <a href="#contact"          data-i18n="nav_contact"><i class="fa-solid fa-phone"></i>اتصل بنا</a>
    <?php if ($isLoggedIn): ?>
      <hr class="drawer-divider">
      <a href="client/orders/orders.php"  ><i class="fa-solid fa-box"></i><span data-i18n="nav_orders">طلباتي</span></a>
      <a href="client/repairs/repairs.php"><i class="fa-solid fa-wrench"></i><span data-i18n="nav_repairs">الصيانة</span></a>
      <a href="auth/logout.php"           ><i class="fa-solid fa-right-from-bracket"></i><span data-i18n="nav_logout">تسجيل الخروج</span></a>
    <?php else: ?>
      <hr class="drawer-divider">
      <a href="auth/login.php"><i class="fa-solid fa-right-to-bracket"></i><span data-i18n="nav_login">تسجيل الدخول</span></a>
    <?php endif; ?>
  </nav>

  <hr class="drawer-divider">

  <!-- Language switcher inside drawer -->
  <div class="drawer-lang">
    <div class="drawer-lang-label">اللغة / Language</div>
    <div class="drawer-lang-btns" id="drawer-lang-btns">
      <button class="lang-btn" data-lang="ar">AR</button>
      <button class="lang-btn" data-lang="fr">FR</button>
      <button class="lang-btn" data-lang="en">EN</button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════
     SEARCH MODAL
     ═══════════════════════════════════════════ -->
<div class="search-modal" id="search-modal">
  <div class="search-modal-content">
    <button class="close-search" id="close-search">&times;</button>
    <h3><i class="fa-solid fa-magnifying-glass"></i> <span data-i18n="search_title">بحث متقدم</span></h3>
    <form id="search-form">
      <div class="search-group">
        <label data-i18n="search_name_label">اسم المنتج</label>
        <input type="text" id="search-term" placeholder="مثل: iPhone, Samsung…" />
      </div>
      <div class="search-group">
        <label data-i18n="search_category_label">الفئة</label>
        <select id="category-filter">
          <option value="" data-i18n="cat_all">الكل</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name_en']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="search-row">
        <div class="search-group">
          <label data-i18n="search_min_label">الحد الأدنى</label>
          <input type="number" id="min-price" placeholder="0" min="0" />
        </div>
        <div class="search-group">
          <label data-i18n="search_max_label">الحد الأقصى</label>
          <input type="number" id="max-price" placeholder="500000" min="0" />
        </div>
      </div>
      <button type="submit" class="search-btn">
        <i class="fa-solid fa-search"></i> <span data-i18n="search_btn">بحث</span>
      </button>
    </form>
  </div>
</div>

<!-- ═══════════════════════════════════════════
     CART MODAL
     ═══════════════════════════════════════════ -->
<div class="cart-modal" id="cart-modal">
  <div class="cart-modal-content">
    <button class="close-cart" id="close-cart">&times;</button>
    <h3 data-i18n="cart_title">🛒 سلة المشتريات</h3>
    <div id="cart-items-list"></div>
    <div class="cart-total">
      <span data-i18n="cart_total">المجموع</span>
      <span class="cart-total-amount" id="total-amount">0 دج</span>
    </div>
    <div class="cart-actions">
      <button class="checkout" id="checkout-btn"   data-i18n="cart_checkout">إتمام الشراء</button>
      <button class="clear"    id="clear-cart-btn" data-i18n="cart_clear">تفريغ السلة</button>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════════════════
     HERO — full-screen slider
     ═══════════════════════════════════════════ -->
<section class="hero" id="home">
  <div class="hero-slides">
    <img src="assests/uploads/photo_5922664454585781268_x.jpg" class="hero-img active" alt="hero 1" />
    <img src="assests/uploads/photo_5922664454585781246_x.jpg" class="hero-img" alt="hero 2" />
  </div>
  <div class="hero-overlay">
    <div class="hero-badge" data-i18n="hero_badge">Next Generation Electronics</div>
    <h1 data-i18n="hero_title">أهلاً بك في <span>Wise Tech</span></h1>
    <p data-i18n="hero_subtitle">تقنيات العصر الحديث بين يديك — أجهزة، ضمان، وخدمة متكاملة</p>
    <div class="hero-cta">
      <a href="#products" class="btn-primary"><i class="fa-solid fa-bolt"></i> <span data-i18n="hero_cta_shop">تسوّق الآن</span></a>
      <a href="#contact"  class="btn-outline" data-i18n="hero_cta_contact">تواصل معنا</a>
    </div>
  </div>
  <div class="hero-dots" id="hero-dots">
    <span class="dot active" data-index="0"></span>
    <span class="dot" data-index="1"></span>
  </div>
  <button class="side-btn prev" id="prev-btn"><i class="fa-solid fa-chevron-left"></i></button>
  <button class="side-btn next" id="next-btn"><i class="fa-solid fa-chevron-right"></i></button>
</section>

<!-- STATS BAR -->
<section class="stats-bar">
  <div class="stats-container">
    <div class="stat-item"><i class="fa-solid fa-users"></i><div><strong>+2000</strong><span data-i18n="stat_clients">عميل راضٍ</span></div></div>
    <div class="stat-item"><i class="fa-solid fa-box-open"></i><div><strong>+500</strong><span data-i18n="stat_products">منتج متوفر</span></div></div>
    <div class="stat-item"><i class="fa-solid fa-screwdriver-wrench"></i><div><strong>+5000</strong><span data-i18n="stat_repairs">إصلاح ناجح</span></div></div>
    <div class="stat-item"><i class="fa-solid fa-shield-halved"></i><div><strong data-i18n="stat_warranty">ضمان على جميع المنتجات</strong></div></div>
  </div>
</section>

<!-- PRODUCTS + CATEGORY PILLS -->
<section class="products-section" id="products">
  <div class="section-header">
    <h2 class="section-title" data-i18n="sec_products_title">🔥 منتجاتنا</h2>
    <p class="section-sub"   data-i18n="sec_products_sub">اكتشف أحدث التقنيات بأسعار تنافسية</p>
  </div>
  <div class="cat-pills" id="cat-pills">
    <button class="cat-pill active" data-cat="" data-i18n="cat_all">الكل</button>
    <?php foreach ($categories as $cat): ?>
    <button class="cat-pill" data-cat="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name_en']) ?></button>
    <?php endforeach; ?>
  </div>
  <div class="products" id="products-grid">
    <?php if (!empty($products)): ?>
      <?php foreach ($products as $p): ?>
        <?php
          $extraImages = [];
          if (!empty($p['extra_images'])) {
            $d = json_decode($p['extra_images'], true);
            if (is_array($d)) $extraImages = $d;
          }
          $colors = [];
          if (!empty($p['colors'])) {
            $d = json_decode($p['colors'], true);
            if (is_array($d)) $colors = $d;
          }
          // FIX: prefix extra images so popup.js finds them
          $extraWithPath = array_values(array_map(
            fn($img) => 'assests/uploads/' . ltrim($img, '/'),
            array_filter($extraImages)
          ));
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
             data-img="assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
             data-extra-images='<?= htmlspecialchars(json_encode($extraWithPath)) ?>'
             data-colors='<?= htmlspecialchars(json_encode($colors)) ?>'
             data-ram="<?= htmlspecialchars($p['ram'] ?? '') ?>"
             data-storage="<?= htmlspecialchars($p['storage'] ?? '') ?>"
             data-camera="<?= htmlspecialchars($p['camera'] ?? '') ?>"
             data-category-name-ar="<?= htmlspecialchars($p['category_name_ar'] ?? '') ?>"
             data-category-name-fr="<?= htmlspecialchars($p['category_name_fr'] ?? '') ?>"
             data-category-name-en="<?= htmlspecialchars($p['category_name'] ?? '') ?>"
        >
          <div class="product-img-wrap" onclick="openProductPopup(this.closest('.product-card'))">
            <img src="assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
                 alt="<?= htmlspecialchars($p['name_ar']) ?>" loading="lazy" />
            <div class="product-overlay">
              <i class="fa-solid fa-eye"></i> <span data-i18n="btn_details">عرض التفاصيل</span>
            </div>
          </div>
          <div class="product-bottom">
            <h3 class="product-name"><?= htmlspecialchars($p['name_ar']) ?></h3>
            <p class="price"><?= number_format($p['price'], 2) ?> <span class="currency-label">دج</span></p>
            <button class="buy-btn" onclick="openProductPopup(this.closest('.product-card'))">
              <i class="fa-solid fa-eye"></i> <span data-i18n="btn_details">عرض التفاصيل</span>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="grid-column:1/-1;text-align:center;padding:60px 0;">
        <i class="fa-solid fa-box-open fa-3x" style="color:#cbd5e1;margin-bottom:16px;display:block;"></i>
        <p data-i18n="no_products" style="color:#94a3b8;font-size:16px;">لا توجد منتجات حالياً</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- SERVICES -->
<section class="services-section" id="services-section">
  <div class="section-header">
    <h2 class="section-title" data-i18n="sec_services_title">⚙️ خدماتنا</h2>
    <p class="section-sub"   data-i18n="sec_services_sub">كل ما تحتاجه تحت سقف واحد</p>
  </div>
  <div class="services-grid">
    <div class="service-card"><div class="service-icon" style="--c:#245bff"><i class="fa-solid fa-mobile-screen-button"></i></div><h3 data-i18n="svc_phone_title">إصلاح الهواتف</h3><p data-i18n="svc_phone_desc">تشخيص وإصلاح كافة أعطال الهواتف الذكية بأيدي متخصصين</p></div>
    <div class="service-card"><div class="service-icon" style="--c:#7b2cff"><i class="fa-solid fa-laptop"></i></div><h3 data-i18n="svc_laptop_title">صيانة اللابتوب</h3><p data-i18n="svc_laptop_desc">إصلاح الشاشات، الأم، البطارية وتركيب أنظمة التشغيل</p></div>
    <div class="service-card"><div class="service-icon" style="--c:#00b894"><i class="fa-solid fa-shield-halved"></i></div><h3 data-i18n="svc_warranty_title">خدمة الضمان</h3><p data-i18n="svc_warranty_desc">استفد من ضمانك بسهولة — نحن نتولى كل شيء</p></div>
    <div class="service-card"><div class="service-icon" style="--c:#e17055"><i class="fa-solid fa-truck-fast"></i></div><h3 data-i18n="svc_delivery_title">توصيل سريع</h3><p data-i18n="svc_delivery_desc">توصيل جميع الطلبات خلال أقصر وقت ممكن</p></div>
  </div>
</section>

<!-- WARRANTY -->
<section class="warranty-conditions" id="warranty">
  <div class="section-header">
    <h2 class="section-title warranty-title" data-i18n="sec_warranty_title">شروط الضمان الأساسية</h2>
    <div class="title-line"></div>
  </div>
  <div class="conditions-container">
    <div class="condition-card"><div class="condition-number">1</div><div class="condition-content"><h3 data-i18n="w1_title">الاحتفاظ بالتغليف الأصلي الكامل</h3><p data-i18n="w1_desc">العلبة، الحماية الداخلية، الملصقات والملحقات يُعد شرطًا أساسيًا للاستفادة من الضمان.</p></div></div>
    <div class="condition-card"><div class="condition-number">2</div><div class="condition-content"><h3 data-i18n="w2_title">الضمان لا يشمل استرجاع المبلغ</h3><p data-i18n="w2_desc">الضمان لا يمنح الزبون الحق في استرجاع المبلغ المدفوع.</p></div></div>
    <div class="condition-card"><div class="condition-number">3</div><div class="condition-content"><h3 data-i18n="w3_title">عدم التدخل الخارجي</h3><p data-i18n="w3_desc">أي محاولة إصلاح من طرف ثالث قبل التواصل معنا تُلغي الضمان فوراً.</p></div></div>
  </div>
</section>

<!-- CONTACT -->
<section class="contact-info-section" id="contact">
  <div class="section-header" style="text-align:center;margin-bottom:40px;">
    <h2 class="section-title" style="color:#fff;" data-i18n="sec_contact_title">📞 تواصل معنا</h2>
    <p class="section-sub" style="color:rgba(255,255,255,.7);" data-i18n="sec_contact_sub">نحن هنا لمساعدتك في أي وقت</p>
  </div>
  <div class="contact-grid">
    <div class="contact-card"><div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div><h4 data-i18n="contact_address_title">العنوان</h4><p>Skikda, Algeria</p></div>
    <div class="contact-card"><div class="contact-icon"><i class="fa-solid fa-phone"></i></div><h4 data-i18n="contact_phone_title">الهاتف</h4><p>0655 880 712</p><p>0673 633 916</p></div>
    <div class="contact-card"><div class="contact-icon"><i class="fa-solid fa-envelope"></i></div><h4 data-i18n="contact_email_title">البريد الإلكتروني</h4><p>contact@wisetech.dz</p></div>
    <div class="contact-card"><div class="contact-icon"><i class="fa-solid fa-clock"></i></div><h4 data-i18n="contact_hours_title">ساعات العمل</h4><p>24 / 7</p></div>
  </div>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">WISE<span>TECH</span></div>
    <p class="footer-copy" data-i18n="footer_copy">© 2025 Wise Tech — جميع الحقوق محفوظة</p>
    <div class="footer-links">
      <a href="#home"     data-i18n="nav_home">الرئيسية</a>
      <a href="#products" data-i18n="nav_products">المنتجات</a>
      <a href="#contact"  data-i18n="nav_contact">اتصل بنا</a>
    </div>
  </div>
</footer>

<!-- PRODUCT POPUP -->
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

<!-- ═══════════════════════════════════════════
     SCRIPTS
     ═══════════════════════════════════════════ -->
<script>
  window.IS_LOGGED_IN = <?= $isLoggedIn ? 'true' : 'false' ?>;
</script>
<script src="assests/js/i18n.js"></script>
<script src="assests/js/script.js"></script>
<script src="assests/js/popup.js"></script>

<script>
/* ================================================================
   INLINE CONTROLLER — replaces user.js entirely for this page
   Handles: mobile drawer, cart, user dropdown, search
   ================================================================ */
(function () {

  /* ── helpers ── */
  const CART_KEY = 'wt_cart';
  function getCart()    { try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; } catch(e) { return []; } }
  function saveCart(c)  { localStorage.setItem(CART_KEY, JSON.stringify(c)); localStorage.setItem('cart', JSON.stringify(c)); updateBadge(); }

  function updateBadge() {
    const total = getCart().reduce((s, i) => s + (i.qty || 1), 0);
    document.querySelectorAll('.cart-badge').forEach(b => {
      b.textContent = total;
      b.style.display = total > 0 ? 'flex' : 'none';
    });
  }

  /* ── mobile drawer ── */
  const drawer  = document.getElementById('mobile-drawer');
  const overlay = document.getElementById('mobile-overlay');
  const hamburger = document.getElementById('menu');
  const drawerClose = document.getElementById('drawer-close');

  function openDrawer()  { drawer.classList.add('open'); overlay.classList.add('open'); document.body.style.overflow = 'hidden'; }
  function closeDrawer() { drawer.classList.remove('open'); overlay.classList.remove('open'); document.body.style.overflow = ''; }

  hamburger?.addEventListener('click', openDrawer);
  drawerClose?.addEventListener('click', closeDrawer);
  overlay?.addEventListener('click', closeDrawer);

  // Close drawer when a nav link is clicked
  document.querySelectorAll('.drawer-nav a').forEach(a => {
    a.addEventListener('click', closeDrawer);
  });

  // Drawer lang buttons — sync with i18n system
  document.getElementById('drawer-lang-btns')?.addEventListener('click', e => {
    const btn = e.target.closest('.lang-btn');
    if (btn && typeof applyLang === 'function') applyLang(btn.dataset.lang);
  });

  // Keep drawer lang buttons in sync with current lang
  document.addEventListener('langChanged', () => {
    const lang = typeof currentLang !== 'undefined' ? currentLang : 'ar';
    document.querySelectorAll('#drawer-lang-btns .lang-btn').forEach(b => {
      b.classList.toggle('active', b.dataset.lang === lang);
    });
  });

  /* ── user dropdown ── */
  const trigger  = document.getElementById('user-menu-trigger');
  const dropdown = document.getElementById('user-dropdown');
  trigger?.addEventListener('click', e => {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });
  document.addEventListener('click', e => {
    if (dropdown && !trigger?.contains(e.target) && !dropdown.contains(e.target))
      dropdown.style.display = 'none';
  });

  /* ── search modal ── */
  const searchModal = document.getElementById('search-modal');
  document.getElementById('search-icon')?.addEventListener('click', e => {
    e.preventDefault(); searchModal.style.display = 'flex';
  });
  document.getElementById('close-search')?.addEventListener('click', () => {
    searchModal.style.display = 'none';
  });
  searchModal?.addEventListener('click', e => {
    if (e.target === searchModal) searchModal.style.display = 'none';
  });

  document.getElementById('search-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const term     = document.getElementById('search-term')?.value.toLowerCase() || '';
    const catId    = document.getElementById('category-filter')?.value || '';
    const minPrice = parseFloat(document.getElementById('min-price')?.value) || 0;
    const maxPrice = parseFloat(document.getElementById('max-price')?.value) || Infinity;
    searchModal.style.display = 'none';
    document.querySelectorAll('.cat-pill').forEach(p => p.classList.remove('active'));
    document.querySelector('.cat-pill[data-cat=""]')?.classList.add('active');
    document.querySelectorAll('.product-card').forEach(card => {
      const name  = card.querySelector('h3')?.textContent.toLowerCase() || '';
      const price = parseFloat(card.querySelector('.price')?.textContent.replace(/[^0-9.]/g, '')) || 0;
      const cat   = card.dataset.category || '';
      const ok = name.includes(term) && (!catId || cat === catId) && price >= minPrice && price <= maxPrice;
      card.style.display = ok ? '' : 'none';
    });
  });

  /* ── cart modal ── */
  const cartModal   = document.getElementById('cart-modal');
  const cartIconEl  = document.getElementById('cart-icon');
  const closeCartEl = document.getElementById('close-cart');

  cartIconEl?.addEventListener('click', e => {
    e.preventDefault();
    renderCart();
    cartModal.style.display = 'flex';
  });
  closeCartEl?.addEventListener('click', () => { cartModal.style.display = 'none'; });
  cartModal?.addEventListener('click', e => { if (e.target === cartModal) cartModal.style.display = 'none'; });

  /* ── render cart ── */
  function renderCart() {
    const list    = document.getElementById('cart-items-list');
    const totalEl = document.getElementById('total-amount');
    if (!list || !totalEl) return;
    const cart = getCart();

    if (cart.length === 0) {
      list.innerHTML = '<div class="cart-empty-state"><i class="fa-solid fa-cart-shopping"></i><p>السلة فارغة</p></div>';
      totalEl.textContent = '0 دج';
      return;
    }

    let total = 0;
    list.innerHTML = '';
    cart.forEach(item => {
      const qty       = item.qty || 1;
      const price     = parseFloat(item.price) || 0;
      const itemTotal = price * qty;
      total += itemTotal;

      const colorBadge = item.colorHex
        ? `<span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;color:#64748b;margin-bottom:3px;">
             <span style="width:10px;height:10px;border-radius:50%;background:${item.colorHex};border:1px solid rgba(0,0,0,.15);display:inline-block;"></span>
             ${item.color || ''}
           </span>`
        : '';

      const imgEl = item.img
        ? `<img class="cart-item-img" src="${item.img}" alt="${item.name}" onerror="this.style.display='none'">`
        : `<div class="cart-item-img" style="background:#f1f5f9;display:flex;align-items:center;justify-content:center;"><i class="fa-solid fa-image" style="color:#cbd5e1;"></i></div>`;

      const div = document.createElement('div');
      div.className = 'cart-item';
      div.dataset.id = item.id;
      div.innerHTML = imgEl +
        `<div class="cart-item-body">
           <div class="cart-item-name">${item.name}</div>
           ${colorBadge}
           <div class="cart-item-price">${price.toLocaleString('fr-DZ')} دج</div>
           <div class="cart-item-subtotal">= ${itemTotal.toLocaleString('fr-DZ')} دج</div>
         </div>
         <div class="cart-item-controls">
           <div class="qty-row">
             <button class="qty-minus" data-id="${item.id}">−</button>
             <span>${qty}</span>
             <button class="qty-plus"  data-id="${item.id}">+</button>
           </div>
           <button class="cart-item-remove" data-id="${item.id}" title="حذف">
             <i class="fa-solid fa-trash-can"></i>
           </button>
         </div>`;
      list.appendChild(div);
    });

    totalEl.textContent = total.toLocaleString('fr-DZ') + ' دج';

    list.querySelectorAll('.qty-minus').forEach(b => b.addEventListener('click', () => changeQty(b.dataset.id, -1)));
    list.querySelectorAll('.qty-plus' ).forEach(b => b.addEventListener('click', () => changeQty(b.dataset.id, +1)));
    list.querySelectorAll('.cart-item-remove').forEach(b => b.addEventListener('click', () => removeItem(b.dataset.id)));
  }

  function changeQty(id, delta) {
    const cart = getCart();
    const item = cart.find(i => String(i.id) === String(id));
    if (!item) return;
    item.qty = Math.max(1, (item.qty || 1) + delta);
    saveCart(cart); renderCart();
  }
  function removeItem(id) { saveCart(getCart().filter(i => String(i.id) !== String(id))); renderCart(); }

  document.getElementById('clear-cart-btn')?.addEventListener('click', () => {
    if (confirm('هل أنت متأكد من تفريغ السلة؟')) { saveCart([]); renderCart(); }
  });
  document.getElementById('checkout-btn')?.addEventListener('click', () => {
    const cart = getCart();
    if (cart.length === 0) { alert('السلة فارغة!'); return; }
    const form  = document.createElement('form');
    form.method = 'POST'; form.action = 'client/checkout.php';
    const inp   = document.createElement('input');
    inp.type = 'hidden'; inp.name = 'cart_data';
    inp.value = JSON.stringify(cart.map(i => ({
      id: i.id, name: i.name, price: i.price,
      qty: i.qty || 1, img: i.img || '',
      color: i.color || '', colorHex: i.colorHex || ''
    })));
    form.appendChild(inp); document.body.appendChild(form); form.submit();
  });

  /* ── prevent old script.js buy-btn alert ── */
  document.querySelectorAll('.buy-btn').forEach(btn => {
    btn.addEventListener('click', e => e.stopPropagation(), true);
  });

  /* ── init ── */
  updateBadge();

})();
</script>
</body>
</html>
