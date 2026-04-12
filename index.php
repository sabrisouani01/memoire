<?php
include "include/db_connect.php";

$stmt = $pdo->query("
    SELECT p.*, c.id AS category_id, c.name_en AS category_name
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
  <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>

<!-- ===== NAVBAR ===== -->
<header class="top-header" id="top-header">
  <div class="nav-container">
    <div class="logo">
      <span class="logo-text">WISE<span>TECH</span></span>
    </div>

    <nav class="nav-links" id="nav-links">
      <a href="#home">الرئيسية</a>
      <a href="#products">المنتجات</a>
      <a href="#services-section">خدماتنا</a>
      <a href="#warranty">الضمان</a>
      <a href="#contact">اتصل بنا</a>
    </nav>

    <div class="nav-icons">
      <a href="#" class="icon-box" id="search-icon" title="بحث">
        <i class="fa-solid fa-magnifying-glass"></i>
      </a>
      <a href="auth/login.php" class="icon-box" title="تسجيل الدخول">
        <i class="fa-solid fa-user"></i>
      </a>
      <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
    </div>
  </div>
</header>

<!-- ===== SEARCH MODAL ===== -->
<div class="search-modal" id="search-modal">
  <div class="search-modal-content">
    <button class="close-search" id="close-search">&times;</button>
    <h3><i class="fa-solid fa-magnifying-glass"></i> بحث متقدم</h3>
    <form id="search-form">
      <div class="search-group">
        <label>اسم المنتج</label>
        <input type="text" id="search-term" placeholder="مثل: iPhone, Samsung…" />
      </div>
      <div class="search-group">
        <label>الفئة</label>
        <select id="category-filter">
          <option value="">الكل</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name_en']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="search-row">
        <div class="search-group">
          <label>الحد الأدنى (دج)</label>
          <input type="number" id="min-price" placeholder="0" min="0" />
        </div>
        <div class="search-group">
          <label>الحد الأقصى (دج)</label>
          <input type="number" id="max-price" placeholder="500000" min="0" />
        </div>
      </div>
      <button type="submit" class="search-btn"><i class="fa-solid fa-search"></i> بحث</button>
    </form>
  </div>
</div>

<!-- ===== HERO ===== -->
<section class="hero" id="home">
  <div class="hero-slides">
    <img src="assests/uploads/photo_5922664454585781268_x.jpg" class="hero-img active" id="img1" alt="hero 1" />
    <img src="assests/uploads/photo_5922664454585781246_x.jpg" class="hero-img" id="img2" alt="hero 2" />
  </div>
  <div class="hero-overlay">
    <div class="hero-badge">Next Generation Electronics</div>
    <h1>أهلاً بك في <span>Wise Tech</span></h1>
    <p>تقنيات العصر الحديث بين يديك — أجهزة، ضمان، وخدمة متكاملة</p>
    <div class="hero-cta">
      <a href="#products" class="btn-primary"><i class="fa-solid fa-bolt"></i> تسوّق الآن</a>
      <a href="#contact" class="btn-outline">تواصل معنا</a>
    </div>
  </div>
  <div class="hero-dots" id="hero-dots">
    <span class="dot active" data-index="0"></span>
    <span class="dot" data-index="1"></span>
  </div>
  <button class="side-btn prev" id="prev-btn"><i class="fa-solid fa-chevron-left"></i></button>
  <button class="side-btn next" id="next-btn"><i class="fa-solid fa-chevron-right"></i></button>
</section>

<!-- ===== STATS BAR ===== -->
<section class="stats-bar">
  <div class="stats-container">
    <div class="stat-item">
      <i class="fa-solid fa-users"></i>
      <div><strong>+2000</strong><span>عميل راضٍ</span></div>
    </div>
    <div class="stat-item">
      <i class="fa-solid fa-box-open"></i>
      <div><strong>+500</strong><span>منتج متوفر</span></div>
    </div>
    <div class="stat-item">
      <i class="fa-solid fa-screwdriver-wrench"></i>
      <div><strong>+5000</strong><span>إصلاح ناجح</span></div>
    </div>
    <div class="stat-item">
      <i class="fa-solid fa-shield-halved"></i>
      <div><strong>ضمان</strong><span>على جميع المنتجات</span></div>
    </div>
  </div>
</section>

<!-- ===== PRODUCTS ===== -->
<section class="products-section" id="products">
  <div class="section-header">
    <h2 class="section-title">🔥 منتجاتنا</h2>
    <p class="section-sub">اكتشف أحدث التقنيات بأسعار تنافسية</p>
  </div>

  <!-- Category filter pills -->
  <div class="cat-pills" id="cat-pills">
    <button class="cat-pill active" data-cat="">الكل</button>
    <?php foreach ($categories as $cat): ?>
    <button class="cat-pill" data-cat="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name_en']) ?></button>
    <?php endforeach; ?>
  </div>

  <div class="products" id="products-grid">
    <?php if (!empty($products)): ?>
      <?php foreach ($products as $p): ?>
        <div class="product-card" data-category="<?= $p['category_id'] ?>">
          <div class="product-img-wrap">
            <img src="assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
                 alt="<?= htmlspecialchars($p['name_ar']) ?>"
                 onclick="toggleDetails(this)" />
            <div class="product-overlay">
              <i class="fa-solid fa-eye"></i> عرض التفاصيل
            </div>
          </div>
          <div class="details" id="details-<?= $p['id'] ?>">
            <p><?= htmlspecialchars($p['description_ar'] ?? '—') ?></p>
          </div>
          <div class="product-bottom">
            <h3><?= htmlspecialchars($p['name_ar']) ?></h3>
            <p class="price"><?= number_format($p['price'], 2) ?> <span>دج</span></p>
            <button class="buy-btn"><i class="fa-solid fa-cart-shopping"></i> شراء</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="no-products">
        <i class="fa-solid fa-box-open fa-3x"></i>
        <p>لا توجد منتجات حالياً</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== SERVICES ===== -->
<section class="services-section" id="services-section">
  <div class="section-header">
    <h2 class="section-title">⚙️ خدماتنا</h2>
    <p class="section-sub">كل ما تحتاجه تحت سقف واحد</p>
  </div>
  <div class="services-grid">
    <div class="service-card">
      <div class="service-icon" style="--c:#245bff"><i class="fa-solid fa-mobile-screen-button"></i></div>
      <h3>إصلاح الهواتف</h3>
      <p>تشخيص وإصلاح كافة أعطال الهواتف الذكية بأيدي متخصصين</p>
    </div>
    <div class="service-card">
      <div class="service-icon" style="--c:#7b2cff"><i class="fa-solid fa-laptop"></i></div>
      <h3>صيانة اللابتوب</h3>
      <p>إصلاح الشاشات، الأم، البطارية وتركيب أنظمة التشغيل</p>
    </div>
    <div class="service-card">
      <div class="service-icon" style="--c:#00b894"><i class="fa-solid fa-shield-halved"></i></div>
      <h3>خدمة الضمان</h3>
      <p>استفد من ضمانك بسهولة — نحن نتولى كل شيء</p>
    </div>
    <div class="service-card">
      <div class="service-icon" style="--c:#e17055"><i class="fa-solid fa-truck-fast"></i></div>
      <h3>توصيل سريع</h3>
      <p>توصيل جميع الطلبات خلال أقصر وقت ممكن</p>
    </div>
  </div>
</section>

<!-- ===== WARRANTY ===== -->
<section class="warranty-conditions" id="warranty">
  <div class="section-header">
    <h2 class="section-title warranty-title">شروط الضمان الأساسية</h2>
    <div class="title-line"></div>
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
    <div class="condition-card">
      <div class="condition-number">3</div>
      <div class="condition-content">
        <h3>عدم التدخل الخارجي</h3>
        <p>أي محاولة إصلاح من طرف ثالث قبل التواصل معنا تُلغي الضمان فوراً.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== CONTACT ===== -->
<section class="contact-info-section" id="contact">
  <div class="section-header" style="text-align:center;margin-bottom:40px;">
    <h2 class="section-title" style="color:#fff;">📞 تواصل معنا</h2>
    <p class="section-sub" style="color:rgba(255,255,255,.7);">نحن هنا لمساعدتك في أي وقت</p>
  </div>
  <div class="contact-grid">
    <div class="contact-card">
      <div class="contact-icon"><i class="fa-solid fa-location-dot"></i></div>
      <h4>العنوان</h4>
      <p>Skikda, Algeria</p>
    </div>
    <div class="contact-card">
      <div class="contact-icon"><i class="fa-solid fa-phone"></i></div>
      <h4>الهاتف</h4>
      <p>0655 880 712</p>
      <p>0673 633 916</p>
    </div>
    <div class="contact-card">
      <div class="contact-icon"><i class="fa-solid fa-envelope"></i></div>
      <h4>البريد الإلكتروني</h4>
      <p>contact@wisetech.dz</p>
    </div>
    <div class="contact-card">
      <div class="contact-icon"><i class="fa-solid fa-clock"></i></div>
      <h4>ساعات العمل</h4>
      <p>24 / 7</p>
    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-logo">WISE<span>TECH</span></div>
    <p class="footer-copy">© 2025 Wise Tech — جميع الحقوق محفوظة</p>
    <div class="footer-links">
      <a href="#home">الرئيسية</a>
      <a href="#products">المنتجات</a>
      <a href="#contact">اتصل بنا</a>
    </div>
  </div>
</footer>

<script src="assests/js/script.js"></script>
</body>
</html>
