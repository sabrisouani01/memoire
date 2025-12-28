<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

// Get admin username
$username = htmlspecialchars($_SESSION['username']);

// Fetch all categories
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name_ar");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>
    <!-- Main Content -->
    <div class="content">
        <h2>🗂️ إدارة التصنيفات</h2>
        <a href="add.php" class="btn btn-success mb-3">➕ إضافة تصنيف</a>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>الرقم</th>
                    <th>الاسم (عربي)</th>
                    <th>Nom (Français)</th>
                    <th>Name (English)</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($categories) > 0): ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= htmlspecialchars($cat['id']); ?></td>
                            <td><?= htmlspecialchars($cat['name_ar']); ?></td>
                            <td><?= htmlspecialchars($cat['name_fr']); ?></td>
                            <td><?= htmlspecialchars($cat['name_en']); ?></td>
                            <td>
                                <a href="delete.php?id=<?= $cat['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا التصنيف؟')">
                                    🗑 حذف
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <strong>⚠️ لا توجد تصنيفات</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>