<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}

require_once '../../include/db_connect.php';
$user_id = $_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

$stmt = $pdo->prepare("
    SELECT 
        o.id AS order_id,
        o.total_amount,
        o.status,
        o.created_at,
        o.warranty_expiry,
        GROUP_CONCAT(p.name_ar SEPARATOR ', ') AS products
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalOrders  = count($orders);
$pendingCount = count(array_filter($orders, fn($o) => $o['status'] === 'pending'));
$deliveredCount = count(array_filter($orders, fn($o) => $o['status'] === 'delivered'));
$totalSpent   = array_sum(array_column($orders, 'total_amount'));
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>طلباتي - Wise Tech</title>
    <link rel="stylesheet" href="../../assests/css/user.css">
    <script src="https://kit.fontawesome.com/4060ace190.js" crossorigin="anonymous"></script>
</head>
<body>

<!-- ===== SHARED HEADER ===== -->
<header class="top-header">
    <div class="nav-container">
        <div class="nav-icons">
            <span class="menu" id="menu"><i class="fa-solid fa-bars"></i></span>
            <div class="user-info" id="user-menu-trigger">
                <i class="fa-solid fa-user"></i>
                <span class="username"><?= $username ?></span>
                <i class="fa-solid fa-chevron-down" style="font-size:11px;"></i>
            </div>
        </div>

        <nav class="nav-links" id="nav-links">
            <a href="../index.php">الرئيسية</a>
            <a href="orders.php" class="active">طلباتي</a>
            <a href="../repairs/repairs.php">الصيانة</a>
        </nav>

        <a href="../index.php" class="logo">
            <span class="logo-text">Wise<span>Tech</span></span>
        </a>
    </div>

    <!-- USER DROPDOWN -->
    <div class="dropdown-menu" id="user-dropdown">
        <a href="../index.php"><i class="fa-solid fa-house"></i> الرئيسية</a>
        <a href="../repairs/repairs.php"><i class="fa-solid fa-wrench"></i> الصيانة</a>
        <a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a>
    </div>
</header>

<!-- ===== LAYOUT ===== -->
<div class="page-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <span class="sidebar-logo-text">Wise<span>Tech</span></span>
        </div>

        <p class="sidebar-section-label">التنقل</p>
        <ul class="sidebar-nav">
            <li><a href="../index.php"><i class="fa-solid fa-house"></i> الرئيسية</a></li>
            <li><a href="orders.php" class="active"><i class="fa-solid fa-box"></i> طلباتي</a></li>
            <li><a href="../repairs/repairs.php"><i class="fa-solid fa-screwdriver-wrench"></i> طلبات الصيانة</a></li>
        </ul>

        <hr class="sidebar-divider">

        <p class="sidebar-section-label">ملخص الطلبات</p>
        <ul class="sidebar-nav">
            <li><a href="#orders-table" style="pointer-events:none; opacity:0.7;">
                <i class="fa-solid fa-circle" style="color:var(--accent);font-size:8px;"></i>
                الانتظار: <?= $pendingCount ?>
            </a></li>
            <li><a href="#orders-table" style="pointer-events:none; opacity:0.7;">
                <i class="fa-solid fa-circle" style="color:var(--success);font-size:8px;"></i>
                تم التسليم: <?= $deliveredCount ?>
            </a></li>
        </ul>

        <hr class="sidebar-divider">

        <p class="sidebar-section-label">الحساب</p>
        <ul class="sidebar-nav">
            <li><a href="../../auth/logout.php"><i class="fa-solid fa-right-from-bracket"></i> تسجيل الخروج</a></li>
        </ul>
    </aside>

    <!-- OVERLAY -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="../index.php">الرئيسية</a>
            <span>/</span>
            <span>طلباتي</span>
        </div>

        <div class="page-header">
            <h1><i class="fa-solid fa-box" style="color:var(--primary);margin-left:8px;font-size:24px;"></i> طلباتي</h1>
            <p>تابع حالة طلباتك وإدارة مشترياتك</p>
        </div>

        <!-- Alert Messages -->
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] == 1): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> تم إلغاء وحذف الطلب بنجاح.</div>
        <?php elseif (isset($_GET['error'])): ?>
            <?php
            $errorMessages = [
                'invalid'      => 'طلب غير صالح.',
                'unauthorized' => 'غير مسموح لك بحذف هذا الطلب.',
                'status'       => 'لا يمكن حذف الطلبات التي تم معالجتها أو تسليمها.',
                'failed'       => 'فشل حذف الطلب. يرجى المحاولة لاحقاً.'
            ];
            $errorMsg = $errorMessages[$_GET['error']] ?? 'حدث خطأ غير معروف.';
            ?>
            <div class="alert alert-error"><i class="fa-solid fa-circle-xmark"></i> <?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <!-- Stats Strip -->
        <div class="stats-strip">
            <div class="stat-card">
                <div class="stat-value"><?= $totalOrders ?></div>
                <div class="stat-label">إجمالي الطلبات</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:var(--accent);"><?= $pendingCount ?></div>
                <div class="stat-label">قيد الانتظار</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="color:var(--success);"><?= $deliveredCount ?></div>
                <div class="stat-label">تم التسليم</div>
            </div>
            <div class="stat-card">
                <div class="stat-value" style="font-size:20px; color:var(--primary);"><?= number_format($totalSpent, 0) ?></div>
                <div class="stat-label">إجمالي الإنفاق (دج)</div>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="card" id="orders-table">
            <div class="card-title"><i class="fa-solid fa-table-list"></i> قائمة الطلبات</div>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-box-open"></i>
                    <h3>لا توجد طلبات</h3>
                    <p>ليس لديك أي طلبات حتى الآن. ابدأ التسوق الآن!</p>
                    <br>
                    <a href="../index.php" class="btn btn-primary"><i class="fa-solid fa-store"></i> تصفح المنتجات</a>
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>المنتجات</th>
                                <th>المبلغ</th>
                                <th>الحالة</th>
                                <th>تاريخ الطلب</th>
                                <th>نهاية الضمان</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong style="color:var(--primary);">#<?= htmlspecialchars($order['order_id']) ?></strong></td>
                                <td style="max-width:220px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?= htmlspecialchars($order['products']) ?>
                                </td>
                                <td>
                                    <strong style="color:var(--primary); font-family:'Outfit',sans-serif;">
                                        <?= number_format($order['total_amount'], 2) ?>
                                    </strong> دج
                                </td>
                                <td>
                                    <span class="status-badge <?= htmlspecialchars($order['status']) ?>">
                                        <?php
                                        $status_labels = [
                                            'pending'    => 'قيد الانتظار',
                                            'processing' => 'قيد المعالجة',
                                            'shipped'    => 'تم الشحن',
                                            'delivered'  => 'تم التسليم',
                                            'cancelled'  => 'ملغى'
                                        ];
                                        echo $status_labels[$order['status']] ?? $order['status'];
                                        ?>
                                    </span>
                                </td>
                                <td><?= date('Y-m-d', strtotime($order['created_at'])) ?></td>
                                <td>
                                    <?php if ($order['warranty_expiry']): ?>
                                        <span style="color:var(--success); font-weight:600;">
                                            <i class="fa-solid fa-shield-check"></i>
                                            <?= htmlspecialchars($order['warranty_expiry']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color:var(--gray-3);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($order['status'] === 'pending'): ?>
                                        <form action="delete_order.php" method="POST" style="display:inline;"
                                              onsubmit="return confirm('هل أنت متأكد من إلغاء وحذف هذا الطلب؟\nلا يمكن التراجع عن هذا الإجراء.');">
                                            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                            <button type="submit" class="delete-btn">
                                                <i class="fa-solid fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="action-disabled">
                                            <i class="fa-solid fa-lock"></i> غير متاح
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Actions -->
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:8px;">
            <a href="../repairs/repairs.php" class="btn btn-primary">
                <i class="fa-solid fa-screwdriver-wrench"></i> طلب صيانة
            </a>
            <a href="../index.php" class="btn btn-ghost">
                <i class="fa-solid fa-house"></i> الرئيسية
            </a>
        </div>

    </main>
</div>

<!-- Sidebar Mobile Toggle -->
<button class="sidebar-toggle" id="sidebar-toggle">
    <i class="fa-solid fa-bars"></i>
</button>

<script>
    // Clean URL after showing message
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

    // Mobile nav
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
</body>
</html>
