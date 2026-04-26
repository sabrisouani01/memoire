<?php
include "include/db_connect.php";

$stmt = $pdo->query("
    SELECT p.*, c.id AS category_id, c.name_en AS category_name, c.name_ar AS category_name_ar, c.name_fr AS category_name_fr
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE p.is_active = 1
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>
<header class="top-header" id="top-header">
  <div class="nav-container">
    <div class="logo"><span class="logo-text">WISE<span>TECH</span></span></div>
    <nav class="nav-links" id="nav-links">
      <a href="#home"             data-i18n="nav_home">الرئيسية</a>
      <a href="#products"         data-i18n="nav_products">المنتجات</a>
      <a href="#services-section" data-i18n="nav_services">خدماتنا</a>
      <a href="#warranty"         data-i18n="nav_warranty">الضمان</a>
      <a href="#contact"          data-i18n="nav_contact">اتصل بنا</a>
    </nav>
    <div class="nav-icons">
      <div id="lang-switcher"></div>
      <a href="#" class="icon-box" id="search-icon" title="بحث"><i class="fa-solid fa-magnifying-glass"></i></a>
      <a href="auth/login.php" class="icon-box" title="تسجيل الدخول"><i class="fa-solid fa-user"></i></a>
      <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
    </div>
  </div>
</header>

<div class="search-modal" id="search-modal">
  <div class="search-modal-content">
    <button class="close-search" id="close-search">&times;</button>
    <h3><i class="fa-solid fa-magnifying-glass"></i> <span data-i18n="search_title">بحث متقدم</span></h3>
    <form id="search-form">
      <div class="search-group">
        <label data-i18n="search_name_label">اسم المنتج</label>
        <input type="text" id="search-term" data-i18n-placeholder="search_name_placeholder" placeholder="مثل: iPhone, Samsung…" />
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
      <button type="submit" class="search-btn"><i class="fa-solid fa-search"></i> <span data-i18n="search_btn">بحث</span></button>
    </form>
  </div>
</div>

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
      <a href="#contact" class="btn-outline" data-i18n="hero_cta_contact">تواصل معنا</a>
    </div>
  </div>
  <div class="hero-dots" id="hero-dots">
    <span class="dot active" data-index="0"></span>
    <span class="dot" data-index="1"></span>
  </div>
  <button class="side-btn prev" id="prev-btn"><i class="fa-solid fa-chevron-left"></i></button>
  <button class="side-btn next" id="next-btn"><i class="fa-solid fa-chevron-right"></i></button>
</section>

<section class="stats-bar">
  <div class="stats-container">
    <div class="stat-item"><i class="fa-solid fa-users"></i><div><strong>+2000</strong><span data-i18n="stat_clients">عميل راضٍ</span></div></div>
    <div class="stat-item"><i class="fa-solid fa-box-open"></i><div><strong>+500</strong><span data-i18n="stat_products">منتج متوفر</span></div></div>
    <div class="stat-item"><i class="fa-solid fa-screwdriver-wrench"></i><div><strong>+5000</strong><span data-i18n="stat_repairs">إصلاح ناجح</span></div></div>
    <div class="stat-item"><i class="fa-solid fa-shield-halved"></i><div><strong data-i18n="stat_warranty">ضمان على جميع المنتجات</strong></div></div>
  </div>
</section>

<section class="products-section" id="products">
  <div class="section-header">
    <h2 class="section-title" data-i18n="sec_products_title">🔥 منتجاتنا</h2>
    <p class="section-sub" data-i18n="sec_products_sub">اكتشف أحدث التقنيات بأسعار تنافسية</p>
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
             data-img="assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
             data-extra-images='<?= htmlspecialchars(json_encode($extraImages)) ?>'
             data-colors='<?= htmlspecialchars(json_encode($colors)) ?>'
             data-ram="<?= htmlspecialchars($p['ram'] ?? '') ?>"
             data-storage="<?= htmlspecialchars($p['storage'] ?? '') ?>"
             data-camera="<?= htmlspecialchars($p['camera'] ?? '') ?>"
             data-category-name-ar="<?= htmlspecialchars($p['category_name_ar'] ?? '') ?>"
             data-category-name-fr="<?= htmlspecialchars($p['category_name_fr'] ?? '') ?>"
             data-category-name-en="<?= htmlspecialchars($p['category_name'] ?? '') ?>"
        >
          <div class="product-img-wrap" onclick="openProductPopup(this.closest('.product-card'))">
            <img src="assests/uploads/<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name_ar']) ?>" />
            <div class="product-overlay"><i class="fa-solid fa-eye"></i> <span data-i18n="btn_details">عرض التفاصيل</span></div>
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
      <div class="no-products"><i class="fa-solid fa-box-open fa-3x"></i><p data-i18n="no_products">لا توجد منتجات حالياً</p></div>
    <?php endif; ?>
  </div>
</section>

<section class="services-section" id="services-section">
  <div class="section-header">
    <h2 class="section-title" data-i18n="sec_services_title">⚙️ خدماتنا</h2>
    <p class="section-sub" data-i18n="sec_services_sub">كل ما تحتاجه تحت سقف واحد</p>
  </div>
  <div class="services-grid">
    <div class="service-card"><div class="service-icon" style="--c:#245bff"><i class="fa-solid fa-mobile-screen-button"></i></div><h3 data-i18n="svc_phone_title">إصلاح الهواتف</h3><p data-i18n="svc_phone_desc">تشخيص وإصلاح كافة أعطال الهواتف الذكية بأيدي متخصصين</p></div>
    <div class="service-card"><div class="service-icon" style="--c:#7b2cff"><i class="fa-solid fa-laptop"></i></div><h3 data-i18n="svc_laptop_title">صيانة اللابتوب</h3><p data-i18n="svc_laptop_desc">إصلاح الشاشات، الأم، البطارية وتركيب أنظمة التشغيل</p></div>
    <div class="service-card"><div class="service-icon" style="--c:#00b894"><i class="fa-solid fa-shield-halved"></i></div><h3 data-i18n="svc_warranty_title">خدمة الضمان</h3><p data-i18n="svc_warranty_desc">استفد من ضمانك بسهولة — نحن نتولى كل شيء</p></div>
    <div class="service-card"><div class="service-icon" style="--c:#e17055"><i class="fa-solid fa-truck-fast"></i></div><h3 data-i18n="svc_delivery_title">توصيل سريع</h3><p data-i18n="svc_delivery_desc">توصيل جميع الطلبات خلال أقصر وقت ممكن</p></div>
  </div>
</section>

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

<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">WISE<span>TECH</span></div>
    <p class="footer-copy" data-i18n="footer_copy">© 2025 Wise Tech — جميع الحقوق محفوظة</p>
    <div class="footer-links">
      <a href="#home" data-i18n="nav_home">الرئيسية</a>
      <a href="#products" data-i18n="nav_products">المنتجات</a>
      <a href="#contact" data-i18n="nav_contact">اتصل بنا</a>
    </div>
  </div>
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

<script src="assests/js/i18n.js"></script>
<script src="assests/js/script.js"></script>
<script src="assests/js/popup.js"></script>
</body>
</html>
