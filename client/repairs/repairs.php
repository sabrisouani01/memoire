<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../include/db_connect.php';
$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// ✅ Get delivered orders with category warranty info
$stmt = $pdo->prepare("
    SELECT 
        oi.id AS order_item_id,
        o.id AS order_id,
        p.id AS product_id,
        p.name_ar AS product_name,
        p.category_id,
        o.created_at AS order_date,
        c.warranty_duration,
        c.name_ar AS category_name
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN categories c ON p.category_id = c.id
    WHERE o.user_id = ? 
      AND o.status = 'delivered'
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$deliveredItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate warranty expiry for each item
foreach ($deliveredItems as &$item) {
    $item['warranty_expiry'] = null;
    $item['is_under_warranty'] = false;
    
    if ($item['warranty_duration']) {
        preg_match('/(\d+)/', $item['warranty_duration'], $matches);
        if (isset($matches[1])) {
            $months = (int)$matches[1];
            $orderDate = new DateTime($item['order_date']);
            $expiryDate = clone $orderDate;
            $expiryDate->modify("+{$months} months");
            $item['warranty_expiry'] = $expiryDate->format('Y-m-d');
            $item['is_under_warranty'] = ($expiryDate >= new DateTime());
        }
    }
}
unset($item);

// Past repair requests
$repairsStmt = $pdo->prepare("
    SELECT 
        r.id, 
        p.name_ar AS product_name, 
        r.description, 
        r.status, 
        r.created_at,
        r.is_warranty_claim,
        r.is_external_item
    FROM repairs r
    LEFT JOIN products p ON r.product_id = p.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$repairsStmt->execute([$user_id]);
$repairs = $repairsStmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalRepairs = count($repairs);
$pendingRepairs = count(array_filter($repairs, fn($r) => $r['status'] === 'pending'));
$completedRepairs = count(array_filter($repairs, fn($r) => $r['status'] === 'completed'));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الصيانة - Wise Tech</title>
    <link rel="stylesheet" href="../../assests/css/user.css">
    <link rel="stylesheet" href="../../assests/css/enhancements.css">
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>

<!-- ===== SHARED HEADER ===== -->
<header class="top-header">
    <div class="nav-container">
        <div class="nav-icons">
            <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
            <div class="page-lang-switcher" id="lang-switcher-page"></div>
            <div class="user-info" id="user-menu-trigger">
                <i class="fa-solid fa-user"></i>
                <span class="username"><?= $username ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:11px;"></i>
            </div>
        </div>

        <nav class="nav-links" id="nav-links">
            <a href="../index.php" data-i18n-page="nav_home">الرئيسية</a>
            <a href="../orders/orders.php" data-i18n-page="nav_orders">طلباتي</a>
            <a href="repairs.php" class="active" data-i18n-page="repairs_title">الصيانة</a>
        </nav>

        <a href="../index.php" class="logo">
            <span class="logo-text">Wise<span>Tech</span></span>
        </a>
    </div>

    <!-- USER DROPDOWN -->
    <div class="dropdown-menu" id="user-dropdown">
        <a href="../index.php"><i class="fa-solid fa-house"></i> <span data-i18n-page="nav_home">الرئيسية</span></a>
        <a href="../orders/orders.php"><i class="fa-solid fa-box"></i> <span data-i18n-page="nav_orders">طلباتي</span></a>
        <a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span data-i18n-page="nav_logout">تسجيل الخروج</span></a>
    </div>
</header>

<!-- ===== LAYOUT ===== -->
<div class="page-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <span class="sidebar-logo-text">Wise<span>Tech</span></span>
        </div>

        <p class="sidebar-section-label" data-i18n-page="sidebar_nav">التنقل</p>
        <ul class="sidebar-nav">
            <li><a href="../index.php"><i class="fa-solid fa-house"></i> <span data-i18n-page="nav_home">الرئيسية</span></a></li>
            <li><a href="../orders/orders.php"><i class="fa-solid fa-box"></i> <span data-i18n-page="nav_orders">طلباتي</span></a></li>
            <li><a href="repairs.php" class="active"><i class="fa-solid fa-screwdriver-wrench"></i> <span data-i18n-page="repairs_title">الصيانة</span></a></li>
        </ul>

        <hr class="sidebar-divider">

        <p class="sidebar-section-label" data-i18n-page="repairs_new_req">طلب صيانة</p>
        <ul class="sidebar-nav">
            <li><a href="#new-repair-section"><i class="fa-solid fa-plus-circle"></i> <span data-i18n-page="repairs_new">طلب جديد</span></a></li>
            <li><a href="#history-section"><i class="fa-solid fa-history"></i> <span data-i18n-page="repairs_history">السجل</span></a></li>
        </ul>

        <hr class="sidebar-divider">

        <p class="sidebar-section-label" data-i18n-page="sidebar_account">الحساب</p>
        <ul class="sidebar-nav">
            <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span data-i18n-page="nav_logout">تسجيل الخروج</span></a></li>
        </ul>
    </aside>

    <!-- OVERLAY -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="../index.php" data-i18n-page="nav_home">الرئيسية</a>
            <span>/</span>
            <span>الصيانة</span>
        </div>

        <div class="page-header">
            <h1><i class="fa-solid fa-screwdriver-wrench" style="color:var(--primary);margin-left:8px;font-size:24px;"></i> <span data-i18n-page="repairs_title">طلبات الصيانة</span></h1>
            <p data-i18n-page="repairs_subtitle">قدّم طلب صيانة لمنتجاتك أو تابع طلباتك السابقة</p>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> تم إرسال طلب الصيانة بنجاح! سيتم مراجعته قريباً.</div>
        <?php elseif (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> تم حذف طلب الصيانة بنجاح.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <?php
            $errorMessages = [
                'missing_fields' => 'يرجى تعبئة جميع الحقول المطلوبة.',
                'not_eligible'   => 'هذا المنتج غير مؤهل للصيانة تحت الضمان.',
                'database'       => 'حدث خطأ أثناء إرسال الطلب. يرجى المحاولة لاحقاً.',
                'invalid_request'=> 'طلب غير صالح.',
                'unauthorized'   => 'غير مسموح لك بحذف هذا الطلب.',
                'delete_failed'  => 'فشل حذف الطلب. يرجى المحاولة لاحقاً.'
            ];
            $errorMsg = $errorMessages[$_GET['error']] ?? 'حدث خطأ غير معروف.';
            ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <!-- Stats Strip -->
        <div class="stats-strip">
            <div class="stat-card">
                <div class="stat-value"><?= $totalRepairs ?></div>
                <div class="stat-label" data-i18n-page="repairs_total">إجمالي الطلبات</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:var(--accent);"><?= $pendingRepairs ?></div>
                <div class="stat-label" data-i18n-page="repairs_pending_r">قيد الانتظار</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:var(--success);"><?= $completedRepairs ?></div>
                <div class="stat-label" data-i18n-page="repairs_completed">مكتملة</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:var(--primary);"><?= count($deliveredItems) ?></div>
                <div class="stat-label" data-i18n-page="repairs_delivered_items">منتجات مُسلَّمة</div>
            </div>
        </div>

        <!-- ===== NEW REPAIR FORM ===== -->
        <div id="new-repair-section">
            <div class="card">
                <div class="card-title"><i class="fa-solid fa-plus-circle"></i> <span data-i18n-page="repairs_new_card">تقديم طلب صيانة جديد</span></div>

                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab-btn active" onclick="showTab('internal', this)">
                        <i class="fa-solid fa-box"></i> <span data-i18n-page="repairs_tab_internal">منتجات من الموقع</span>
                    </button>
                    <button class="tab-btn" onclick="showTab('external', this)">
                        <i class="fa-solid fa-store"></i> <span data-i18n-page="repairs_tab_external">منتجات من المتجر</span>
                    </button>
                </div>

                <!-- INTERNAL FORM -->
                <div id="internal" class="form-section active">
                    <?php if (!empty($deliveredItems)): ?>
                        <form action="submit_repair.php" method="POST">
                            <input type="hidden" name="repair_type" value="internal">

                            <div class="form-group">
                                <label for="product_id"><i class="fa-solid fa-mobile-screen" style="color:var(--primary);margin-left:6px;"></i> اختر المنتج:</label>
                                <select name="product_id" id="product_id" required>
                                    <option value="">-- اختر منتجًا --</option>
                                    <?php foreach ($deliveredItems as $item): ?>
                                        <option value="<?= $item['product_id'] ?>"
                                                data-warranty="<?= $item['warranty_expiry'] ?>"
                                                data-duration="<?= htmlspecialchars($item['warranty_duration']) ?>"
                                                data-under-warranty="<?= $item['is_under_warranty'] ? '1' : '0' ?>">
                                            <?= htmlspecialchars($item['product_name']) ?>
                                            <?php if ($item['warranty_expiry']): ?>
                                                — <?= $item['is_under_warranty'] ? 'ضمن الضمان' : 'انتهى الضمان' ?>
                                                (<?= htmlspecialchars($item['warranty_duration']) ?>)
                                                — ينتهي: <?= $item['warranty_expiry'] ?>
                                            <?php else: ?>
                                                — بدون ضمان
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="description"><i class="fa-solid fa-comment-dots" style="color:var(--primary);margin-left:6px;"></i> وصف المشكلة:</label>
                                <textarea name="description" id="description" placeholder="اذكر تفاصيل العطل بوضوح..." required></textarea>
                            </div>

                            <button type="submit" class="btn-submit">
                                <i class="fa-solid fa-paper-plane"></i> <span data-i18n-page="repairs_submit">إرسال طلب الصيانة</span>
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-box-open"></i>
                            <h3>لا توجد منتجات مُسلَّمة</h3>
                            <p>ليس لديك منتجات مُسلّمة من الموقع حتى الآن.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- EXTERNAL FORM -->
                <div id="external" class="form-section">
                    <form action="submit_repair.php" method="POST">
                        <input type="hidden" name="repair_type" value="external">

                        <div class="form-group">
                            <label for="external_item"><i class="fa-solid fa-mobile-screen-button" style="color:var(--primary);margin-left:6px;"></i> اسم الجهاز / المنتج:</label>
                            <input type="text" name="external_item" id="external_item" placeholder="مثال: آيفون 13 برو" required>
                        </div>

                        <div class="form-group">
                            <label for="external_phone"><i class="fa-solid fa-phone" style="color:var(--primary);margin-left:6px;"></i> رقم الهاتف:</label>
                            <input type="tel" name="external_phone" id="external_phone" placeholder="05XXXXXXXX" required>
                        </div>

                        <div class="form-group">
                            <label for="external_description"><i class="fa-solid fa-comment-dots" style="color:var(--primary);margin-left:6px;"></i> وصف المشكلة:</label>
                            <textarea name="external_description" id="external_description" placeholder="اذكر تفاصيل العطل بوضوح..." required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="damage_from_factory" value="1">
                                العطل من المصنع (ليس بسبب الاستخدام)
                            </label>
                        </div>

                        <button type="submit" class="btn-submit">
                            <i class="fa-solid fa-paper-plane"></i> <span data-i18n-page="repairs_submit">إرسال طلب الصيانة</span>
                        </button>
                    </form>

                    <p style="text-align:center; color:var(--gray-3); font-size:13px; margin-top:14px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color:var(--accent);"></i>
                        المنتجات التي لم تُشترَ من الموقع لا تشملها سياسة الضمان، وسيتم تقييمها بشكل منفصل.
                    </p>
                </div>
            </div>
        </div>

        <!-- ===== PAST REPAIRS ===== -->
        <div id="history-section">
            <div class="card">
                <div class="card-title"><i class="fa-solid fa-history"></i> <span data-i18n-page="repairs_history_card">طلبات الصيانة السابقة</span></div>

                <?php if (empty($repairs)): ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <h3>لا توجد طلبات</h3>
                        <p>لم تقم بأي طلبات صيانة بعد.</p>
                    </div>
                <?php else: ?>
                    <div class="repairs-list">
                        <?php foreach ($repairs as $r): ?>
                            <?php
                            $status_labels = [
                                'pending'      => 'قيد الانتظار',
                                'in_progress'  => 'قيد التنفيذ',
                                'completed'    => 'مكتمل',
                                'unrepairable' => 'لا يمكن إصلاحه',
                                'cancelled'    => 'ملغى'
                            ];
                            ?>
                            <div class="repair-item">
                                <div class="repair-item-header">
                                    <div class="repair-item-title">
                                        <?= htmlspecialchars($r['product_name'] ?? ($r['is_external_item'] ? 'منتج خارجي' : 'غير معروف')) ?>
                                    </div>
                                    <span class="status-badge <?= htmlspecialchars($r['status']) ?>">
                                        <?= $status_labels[$r['status']] ?? htmlspecialchars($r['status']) ?>
                                    </span>
                                </div>

                                <div class="repair-item-meta">
                                    <span>
                                        <i class="fa-solid fa-calendar-days"></i>
                                        <?= date('Y-m-d', strtotime($r['created_at'])) ?>
                                    </span>
                                    <span>
                                        <?php if ($r['is_external_item']): ?>
                                            <i class="fa-solid fa-store" style="color:var(--accent);"></i>
                                            منتج خارجي (بدون ضمان)
                                        <?php else: ?>
                                            <?php if ($r['is_warranty_claim']): ?>
                                                <i class="fa-solid fa-shield-check" style="color:var(--success);"></i>
                                                ضمن الضمان
                                            <?php else: ?>
                                                <i class="fa-solid fa-shield-xmark" style="color:var(--danger);"></i>
                                                خارج الضمان
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </span>
                                    <span><i class="fa-solid fa-hashtag"></i> #<?= $r['id'] ?></span>
                                </div>

                                <div class="repair-item-desc">
                                    <?= htmlspecialchars($r['description']) ?>
                                </div>

                                <div class="repair-item-actions">
                                    <?php if ($r['status'] === 'pending' || $r['status'] === 'cancelled'): ?>
                                        <form action="delete_repair.php" method="POST" style="display:inline;"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا الطلب؟');">
                                            <input type="hidden" name="repair_id" value="<?= $r['id'] ?>">
                                            <button type="submit" class="delete-btn">
                                                <i class="fa-solid fa-trash"></i> <span data-i18n-page="btn_delete">حذف</span>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="action-disabled">
                                            <i class="fa-solid fa-lock"></i> لا يمكن الحذف
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>
</div>

<!-- Sidebar Mobile Toggle Button -->
<button class="sidebar-toggle" id="sidebar-toggle">
    <i class="fa-solid fa-bars"></i>
</button>

<script>
    // Tab switching
    function showTab(tabName, btn) {
        document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById(tabName).classList.add('active');
        btn.classList.add('active');
    }

    // Clean URL after message
    if (window.location.search) {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.pathname);
        }
    }

    // Sidebar
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebarMenuBtn = document.getElementById('sidebar-menu-btn');

    function toggleSidebar() {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
    sidebarToggle?.addEventListener('click', toggleSidebar);
    sidebarMenuBtn?.addEventListener('click', toggleSidebar);
    overlay?.addEventListener('click', toggleSidebar);

    // Mobile nav toggle
    const menuBtn = document.getElementById('menu');
    const navLinks = document.getElementById('nav-links');
    menuBtn?.addEventListener('click', () => navLinks.classList.toggle('show'));

    // User dropdown
    const trigger = document.getElementById('user-menu-trigger');
    const dropdown = document.getElementById('user-dropdown');
    trigger?.addEventListener('click', (e) => {
        e.stopPropagation();
        dropdown.classList.toggle('show');
    });
    document.addEventListener('click', () => dropdown.classList.remove('show'));
</script>
<script src="../../assests/js/i18n_pages.js"></script>
</body>
</html>
