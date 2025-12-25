<?php
include("../../include/db_connect.php");

// نجيب المبيعات من جدول orders
$sql = "SELECT id, total_amount, status, created_at FROM orders ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$sales = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>تقرير المبيعات</title>
  <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body class="p-4">
  <!-- Sidebar -->
    <div class="sidebar">
        <h4>⚙️ Admin</h4>
        <!-- Show Admin Username -->
        <p style="color: #ccc; font-size: 14px; text-align: center;">
            مرحباً، <strong><?= $username ?></strong>
        </p>
        
        <hr style="border-color: #495057; margin: 10px 0;">
        <a href="../users/add_admin.php">➕ إضافة مدير</a>
        <a href="../technician/add.php">➕ إضافة مصلح</a>
        <a href="../dashbord.php">الرئيسية</a>
        <a href="../products/index.php">المنتجات</a>
        <a href="../orders/index.php">الطلبيات</a>
        <a href="index.php" class="active">العملاء</a>
        <a href="../reports/sales.php">التقارير</a>
        <a href="../warranty/claims.php">الضمان</a>
        <a href="../categories/index.php" >التصنيفات</a>
        <a href="../repairs/index.php">🛠️ الإصلاحات</a>
        <!-- Logout Button -->
        <a href="../auth/logout.php" class="logout">
            🔐 تسجيل الخروج
        </a>
    </div>
  <h2>📊 تقرير المبيعات</h2>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>الزبون</th>
        <th>المبلغ</th>
        <th>الحالة</th>
        <th>التاريخ</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($sales) > 0): ?>
        <?php foreach ($sales as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['id']); ?></td>
            <td><?= htmlspecialchars($row['customer_name']); ?></td>
            <td><?= htmlspecialchars($row['total_amount']); ?> دج</td>
            <td><?= htmlspecialchars($row['status']); ?></td>
            <td><?= htmlspecialchars($row['created_at']); ?></td>
          </tr>
          <?php endforeach; ?>
                <?php else: ?>
        <tr><td colspan="5">لا يوجد عملاء</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>
