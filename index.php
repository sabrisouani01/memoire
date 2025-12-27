<?php
// الاتصال بقاعدة البيانات (PDO)
include "include/db_connect.php";

// Fetch active products with category_id
$stmt = $pdo->query("
    SELECT p.*, c.id AS category_id 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.is_active = 1 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

<header class="top-header">
  <div class="nav-container">
    <div class="logo">
      <span class="logo-text">WISE<span>TECH</span></span>
    </div>

    <nav class="nav-links" id="nav-links">
      <a href="#home">الرئيسية</a>
      <a href="#products">المنتجات</a>
      <a href="#warranty">الضمان</a>
      <a href="#contact">اتصل بنا</a>
    </nav>

    <div class="nav-icons">
      <a href="#" class="icon-box" id="search-icon">
        <i class="fa-solid fa-magnifying-glass"></i>
      </a>
      <!-- Login icon instead of user dropdown -->
      <a href="auth/login.php" class="icon-box">
        <i class="fa-solid fa-user"></i>
      </a>
      <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
    </div>
  </div>
</header>

<!-- ===== SEARCH MODAL ===== -->
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

<!-- ===== Hero ===== -->
<section class="hero" id="home">
  <img src="assests/uploads/photo_5922664454585781268_x.jpg" class="hero-img active" id="img1" />
  <img src="assests/uploads/photo_5922664454585781246_x.jpg" class="hero-img" id="img2" />

  <div class="hero-overlay">
    <h1>أهلاً بك في Wise Tech</h1>
    <p>Next Generation Electronics</p>
    <button class="shop-btn">Shop Now</button>
  </div>

  <button class="side-btn prev" onclick="changeHero(-1)"><</button>
  <button class="side-btn next" onclick="changeHero(1)">></button>
</section>

<!-- ===== Products ===== -->
<section class="products-section" id="products">
  <h2 class="section-title">🔥 منتجاتنا</h2>
  <div class="products">
    <?php if (!empty($products)): ?>
      <?php foreach ($products as $p): ?>
        <div class="product-card" data-category="<?= $p['category_id'] ?>">
          <div class="product-top">
            <img
              src="assests/uploads/<?= htmlspecialchars($p['image_url']) ?>"
              onclick="toggleDetails(this)"
              alt="<?= htmlspecialchars($p['name_ar']) ?>"
            />
            <div class="details">
              <p><?= htmlspecialchars($p['description_ar']) ?></p>
            </div>
          </div>
          <div class="product-bottom">
            <h3><?= htmlspecialchars($p['name_ar']) ?></h3>
            <p class="price"><?= number_format($p['price'], 2) ?> دج</p>
            <button class="buy-btn">شراء</button>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p style="text-align:center; width:100%;">لا توجد منتجات حالياً</p>
    <?php endif; ?>
  </div>
</section>

<section class="warranty-conditions" id="warranty">
  <h2 class="warranty-title">شروط الضمان الأساسية</h2>
  <div class="title-line"></div>
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

<footer class="footer">
  <p>© 2025 Wise Tech - جميع الحقوق محفوظة</p>
</footer>

<script src="assests/js/script.js"></script>
</body>
</html>
