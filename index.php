<?php
require "include/db_connect.php";
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Wise Tech - Next Generation</title>

  <link rel="stylesheet" href="./css/main.css" />
  <link rel="stylesheet" href="./assests/css/main.css">
  <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>

<header class="top-header">
  <div class="nav-container">
    <div class="logo">
      <span class="logo-text">WISE<span>TECH</span></span>
    </div>

    <nav class="nav-links" id="nav-links">
      <a href="#home">Home</a>
      <a href="#products">Products</a>
      <a href="#warranty">Warranty</a>
    </nav>

    <div class="nav-icons">
      <a href="#" class="icon-box"><i class="fa-solid fa-magnifying-glass"></i></a>
      <a href="#" class="icon-box"><i class="fa-solid fa-heart"></i></a>
      <a href="#" class="icon-box"><i class="fa-solid fa-cart-shopping"></i></a>
      <a href="./auth/login.php" class="icon-box"><i class="fa-solid fa-user"></i></a>
      <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
    </div>
  </div>
</header>

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

<section class="products-section" id="products">
  <h2 class="section-title">🔥 Our Products</h2>

  <div class="products">
    <?php
      try {
          $sql = "SELECT * FROM products ORDER BY created_at DESC";
          $stmt = $pdo->query($sql);
          $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          echo '<p style="text-align:center;">خطأ في تحميل المنتجات</p>';
          exit;
      }

      if (!empty($products)):
          foreach ($products as $row):
    ?>
      <div class="product-card">
        <div class="product-top">
          <img
            src="assests/uploads/<?php echo htmlspecialchars($row['image_url'], ENT_QUOTES, 'UTF-8'); ?>"
            onclick="toggleDetails(this)"
            alt="<?php echo htmlspecialchars($row['name_ar'], ENT_QUOTES, 'UTF-8'); ?>"
          />
          <div class="details">
            <p><?php echo htmlspecialchars($row['description_ar'], ENT_QUOTES, 'UTF-8'); ?></p>
          </div>
        </div>

        <div class="product-bottom">
          <h3><?php echo htmlspecialchars($row['name_ar'], ENT_QUOTES, 'UTF-8'); ?></h3>
          <p class="price">
            <?php echo number_format((float)$row['price'], 2); ?> دج
          </p>
          <button class="buy-btn">شراء</button>
        </div>
      </div>
    <?php
          endforeach;
      else:
    ?>
      <p style="text-align:center;">لا توجد منتجات حاليا</p>
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
        <p>
          الضمان لا يمنح الزبون الحق في استرجاع المبلغ المدفوع.
          في حال وجود عطل، يتم الإصلاح أو الاستبدال فقط.
        </p>
      </div>
    </div>
  </div>
</section>

<footer class="footer">
  <p>© 2025 Wise Tech - All Rights Reserved</p>
</footer>

<script src="assests/js/script.js"></script>
</body>
</html>
