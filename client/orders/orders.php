<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../../auth/login.php"); exit(); }

require_once '../../include/db_connect.php';
$user_id  = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

$stmt = $pdo->prepare("
    SELECT o.id AS order_id, o.total_amount, o.status, o.created_at, o.warranty_expiry,
           GROUP_CONCAT(p.name_ar SEPARATOR ', ') AS products_ar,
           GROUP_CONCAT(p.name_fr SEPARATOR ', ') AS products_fr,
           GROUP_CONCAT(p.name_en SEPARATOR ', ') AS products_en
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ? GROUP BY o.id ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalOrders    = count($orders);
$pendingCount   = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$deliveredCount = count(array_filter($orders, fn($o) => $o['status'] === 'delivered'));
$totalSpent     = array_sum(array_column($orders, 'total_amount'));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي - Wise Tech</title>
    <link rel="stylesheet" href="../../assests/css/user.css">
    <link rel="stylesheet" href="../../assests/css/enhancements.css">
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>

<header class="top-header">
    <div class="nav-container">
        <div class="nav-icons">
            <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
            <div class="user-info" id="user-menu-trigger">
                <i class="fa-solid fa-user"></i>
                <span class="username"><?= $username ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:11px;"></i>
            </div>
            <div class="page-lang-switcher" id="lang-switcher-page"></div>
        </div>
        <nav class="nav-links" id="nav-links">
            <a href="../../index.php"           data-i18n-page="nav_home">الرئيسية</a>
            <a href="orders.php" class="active" data-i18n-page="nav_orders">طلباتي</a>
            <a href="../repairs/repairs.php" data-i18n-page="nav_repairs">الصيانة</a>
        </nav>
        <a href="../../index.php" class="logo"><span class="logo-text">Wise<span>Tech</span></span></a>
    </div>
    <div class="dropdown-menu" id="user-dropdown">
        <a href="../../index.php"><i class="fa-solid fa-house"></i> <span data-i18n-page="nav_home">الرئيسية</span></a>
        <a href="../repairs/repairs.php"><i class="fa-solid fa-wrench"></i> <span data-i18n-page="nav_repairs">الصيانة</span></a>
        <a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span data-i18n-page="nav_logout">تسجيل الخروج</span></a>
    </div>
</header>

<div class="page-layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo"><span class="sidebar-logo-text">Wise<span>Tech</span></span></div>
        <p class="sidebar-section-label" data-i18n-page="sidebar_nav">التنقل</p>
        <ul class="sidebar-nav">
            <li><a href="../../index.php"><i class="fa-solid fa-house"></i> <span data-i18n-page="nav_home">الرئيسية</span></a></li>
            <li><a href="orders.php" class="active"><i class="fa-solid fa-box"></i> <span data-i18n-page="nav_orders">طلباتي</span></a></li>
            <li><a href="../repairs/repairs.php"><i class="fa-solid fa-screwdriver-wrench"></i> <span data-i18n-page="nav_repairs">الصيانة</span></a></li>
        </ul>
        <hr class="sidebar-divider">
        <p class="sidebar-section-label" data-i18n-page="sidebar_summary">ملخص الطلبات</p>
        <ul class="sidebar-nav">
            <li><a href="#" style="pointer-events:none;opacity:.7;"><i class="fa-solid fa-circle" style="color:var(--accent);font-size:8px;"></i> <span data-i18n-page="orders_pending">قيد الانتظار</span>: <?= $pendingCount ?></a></li>
            <li><a href="#" style="pointer-events:none;opacity:.7;"><i class="fa-solid fa-circle" style="color:var(--success);font-size:8px;"></i> <span data-i18n-page="orders_delivered">تم التسليم</span>: <?= $deliveredCount ?></a></li>
        </ul>
        <hr class="sidebar-divider">
        <p class="sidebar-section-label" data-i18n-page="sidebar_account">الحساب</p>
        <ul class="sidebar-nav">
            <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span data-i18n-page="nav_logout">تسجيل الخروج</span></a></li>
        </ul>
    </aside>
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <main class="main-content">
        <div class="breadcrumb">
            <a href="../../index.php" data-i18n-page="nav_home">الرئيسية</a>
            <span>/</span>
            <span data-i18n-page="nav_orders">طلباتي</span>
        </div>
        <div class="page-header">
            <h1><i class="fa-solid fa-box" style="color:var(--primary);margin-left:8px;font-size:24px;"></i> <span data-i18n-page="orders_title">طلباتي</span></h1>
            <p data-i18n-page="orders_subtitle">تابع حالة طلباتك وإدارة مشترياتك</p>
        </div>

        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> تم إلغاء وحذف الطلب بنجاح.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <?php
            $errorMessages = ['invalid'=>'طلب غير صالح.','unauthorized'=>'غير مسموح.','status'=>'لا يمكن حذف طلبات معالجة.','failed'=>'فشل الحذف.'];
            $errorMsg = $errorMessages[$_GET['error']] ?? 'حدث خطأ.';
            ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <div class="stats-strip">
            <div class="stat-card"><div class="stat-value"><?= $totalOrders ?></div><div class="stat-label" data-i18n-page="orders_total">إجمالي الطلبات</div></div>
            <div class="stat-card"><div class="stat-value" style="color:var(--accent);"><?= $pendingCount ?></div><div class="stat-label" data-i18n-page="orders_pending">قيد الانتظار</div></div>
            <div class="stat-card"><div class="stat-value" style="color:var(--success);"><?= $deliveredCount ?></div><div class="stat-label" data-i18n-page="orders_delivered">تم التسليم</div></div>
            <div class="stat-card"><div class="stat-value" style="font-size:20px;color:var(--primary);"><?= number_format($totalSpent, 0) ?></div><div class="stat-label" data-i18n-page="orders_spent">إجمالي الإنفاق (<span data-i18n-page="currency">دج</span>)</div></div>
        </div>

        <div class="card" id="orders-table">
            <div class="card-title"><i class="fa-solid fa-table-list"></i> <span data-i18n-page="orders_list">قائمة الطلبات</span></div>
            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-box-open"></i>
                    <h3 data-i18n-page="orders_empty">لا توجد طلبات</h3>
                    <p data-i18n-page="orders_empty_sub">ابدأ التسوق الآن!</p><br>
                    <a href="../../index.php" class="btn btn-primary"><i class="fa-solid fa-store"></i> <span data-i18n-page="orders_browse">تصفح المنتجات</span></a>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th data-i18n-page="col_order">رقم الطلب</th>
                                <th data-i18n-page="col_products">المنتجات</th>
                                <th data-i18n-page="col_amount">المبلغ</th>
                                <th data-i18n-page="col_status">الحالة</th>
                                <th data-i18n-page="col_date">تاريخ الطلب</th>
                                <th data-i18n-page="col_warranty">نهاية الضمان</th>
                                <th data-i18n-page="col_actions">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong style="color:var(--primary);">#<?= htmlspecialchars($order['order_id']) ?></strong></td>
                                <td style="max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                                    data-products-ar="<?= htmlspecialchars($order['products_ar'] ?? '') ?>"
                                    data-products-fr="<?= htmlspecialchars($order['products_fr'] ?? '') ?>"
                                    data-products-en="<?= htmlspecialchars($order['products_en'] ?? '') ?>"
                                    class="product-name-cell">
                                    <?= htmlspecialchars($order['products_ar']) ?>
                                </td>
                                <td><strong style="color:var(--primary);"><?= number_format($order['total_amount'], 2) ?></strong> <span data-i18n-page="currency">دج</span></td>
                                <td>
                                    <?php
                                    $statusKeys = ['pending'=>'status_pending','processing'=>'status_processing','shipped'=>'status_shipped','delivered'=>'status_delivered','cancelled'=>'status_cancelled'];
                                    $statusKey  = $statusKeys[$order['status']] ?? null;
                                    ?>
                                    <span class="status-badge <?= htmlspecialchars($order['status']) ?>"
                                          data-status-key="<?= $statusKey ?>">
                                        <?= htmlspecialchars($order['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <?php if ($order['warranty_expiry']): ?>
                                        <span style="color:var(--success);font-weight:600;"><i class="fa-solid fa-shield-check"></i> <?= htmlspecialchars($order['warranty_expiry']) ?></span>
                                    <?php else: ?><span style="color:var(--gray-3);">—</span><?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <form action="delete_order.php" method="POST" style="display:inline;"
                                              onsubmit="return confirm(window.tp ? tp('confirm_delete') : 'هل أنت متأكد؟');">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <button type="submit" class="delete-btn"><i class="fa-solid fa-trash"></i> <span data-i18n-page="btn_delete">حذف</span></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="action-disabled"><i class="fa-solid fa-lock"></i> <span data-i18n-page="btn_locked">غير متاح</span></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:8px;">
            <a href="../repairs/repairs.php" class="btn btn-primary"><i class="fa-solid fa-screwdriver-wrench"></i> <span data-i18n-page="btn_maintenance">طلب صيانة</span></a>
            <a href="../../index.php" class="btn btn-ghost"><i class="fa-solid fa-house"></i> <span data-i18n-page="btn_home">الرئيسية</span></a>
        </div>
    </main>
</div>

<button class="sidebar-toggle" id="sidebar-toggle"><i class="fa-solid fa-bars"></i></button>

<script src="../../assests/js/i18n_pages.js"></script>
<script>
window.tp = tp;  /* expose for inline onclick */

if (window.history.replaceState && window.location.search) {
    window.history.replaceState(null, null, window.location.pathname);
}
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebar-overlay');
function toggleSidebar() { sidebar?.classList.toggle('show'); overlay?.classList.toggle('show'); }
document.getElementById('sidebar-toggle')?.addEventListener('click', toggleSidebar);
overlay?.addEventListener('click', toggleSidebar);
document.getElementById('menu')?.addEventListener('click', () => document.getElementById('nav-links')?.classList.toggle('show'));
const trigger = document.getElementById('user-menu-trigger');
const dd      = document.getElementById('user-dropdown');
trigger?.addEventListener('click', e => { e.stopPropagation(); dd?.classList.toggle('show'); });
document.addEventListener('click', () => dd?.classList.remove('show'));

/* update product name cells on lang change */
function updateProductCells() {
    const lang = localStorage.getItem('wt_lang') || 'ar';
    document.querySelectorAll('.product-name-cell').forEach(cell => {
        const ar = cell.dataset.productsAr || '';
        const fr = cell.dataset.productsFr || '';
        const en = cell.dataset.productsEn || '';
        cell.textContent = lang === 'ar' ? ar : (lang === 'fr' ? (fr || en || ar) : (en || ar));
    });
    document.querySelectorAll('[data-status-key]').forEach(el => {
        const key = el.dataset.statusKey;
        if (key) el.textContent = tp(key);
    });
}
document.addEventListener('DOMContentLoaded', updateProductCells);

/* patch applyPageLang to also update cells */
const _orig = typeof applyPageLang !== 'undefined' ? applyPageLang : null;
document.addEventListener('DOMContentLoaded', () => {
    const langBtns = document.querySelectorAll('.lang-btn-page');
    langBtns.forEach(btn => btn.addEventListener('click', () => setTimeout(updateProductCells, 50)));
});
</script>

<script>
// Clear localStorage cart after successful order
(function() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('order_success') === '1') {
        localStorage.removeItem('wt_cart');
        localStorage.removeItem('cart');
        // Update badge if visible
        document.querySelectorAll('.cart-badge').forEach(b => { b.textContent = '0'; b.style.display = 'none'; });
    }
})();
</script>
</body>
</html>
