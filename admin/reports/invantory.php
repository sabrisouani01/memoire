<?php
include("../../include/conn.php");

// نجيب المنتجات من جدول products
$query = "SELECT id, name, stock, price, created_at FROM products ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>تقرير المخزون</title>
  <link rel="stylesheet" href="../../assets/css/bootstrap.min.css">
</head>
<body class="p-4">

  <h2>📦 تقرير المخزون</h2>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>المنتج</th>
        <th>الكمية</th>
        <th>السعر</th>
        <th>تاريخ الإضافة</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['name']; ?></td>
            <td><?= $row['stock']; ?></td>
            <td><?= $row['price']; ?> دج</td>
            <td><?= $row['created_at']; ?></td>
          </tr>
      <?php } } else { ?>
        <tr><td colspan="5">لا يوجد مخزون</td></tr>
      <?php } ?>
    </tbody>
  </table>

</body>
</html>
