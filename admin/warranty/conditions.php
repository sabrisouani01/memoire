<?php
include("../../include/conn.php");


// جلب الشروط
$result = mysqli_query($conn, "SELECT * FROM conditions");
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>⚖️ شروط الضمان</title>
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="p-4">

  <h2>⚖️ شروط الضمان</h2>

  <form method="post" action="">
    <div class="mb-3">
      <label class="form-label">إضافة شرط جديد:</label>
      <textarea name="description" class="form-control" required></textarea>
    </div>
    <button type="submit" name="add" class="btn btn-primary">➕ إضافة</button>
  </form>

  <hr>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th>#</th>
        <th>الوصف</th>
        <th>إجراءات</th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = mysqli_fetch_assoc($result)) { ?>
      <tr>
        <td><?= $row['id']; ?></td>
        <td><?= $row['description']; ?></td>
        <td>
          <a href="?delete=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('تحذف الشرط؟')">🗑️ حذف</a>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>

</body>
</html>

<?php
// إضافة شرط
if(isset($_POST['add'])){
  $desc = mysqli_real_escape_string($conn, $_POST['description']);
  mysqli_query($conn, "INSERT INTO conditions(description) VALUES('$desc')");
  header("Location: conditions.php");
}

// حذف شرط
if(isset($_GET['delete'])){
  $id = intval($_GET['delete']);
  mysqli_query($conn, "DELETE FROM conditions WHERE id=$id");
  header("Location: conditions.php");
}
?>
