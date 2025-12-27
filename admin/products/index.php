<?php
require "../includes/admin_auth.php";
include "../../include/db_connect.php";

try {
    $sql = "SELECT p.*, c.name_ar AS cat_name_ar, c.name_en AS cat_name_en 
            FROM products p 
            JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC";
    $result = $pdo->query($sql);
    $products = $result->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("خطأ في قاعدة البيانات: " . $e->getMessage());
}
?>
    <!-- Main Content -->
    <div class="content">
        <h2>📦 قائمة المنتجات</h2>
        <a href="products/add.php" class="btn btn-primary mb-3">➕ إضافة منتج</a>

        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>الرقم</th>
                    <th>الاسم (عربي)</th>
                    <th>Name (English)</th>
                    <th>السعر (دج)</th>
                    <th>الوصف (عربي)</th>
                    <th>Description (English)</th>
                    <th>التصنيف</th>
                    <th>الكمية</th>
                    <th>الصورة</th>
                    <th>تاريخ الإضافة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']); ?></td>
                            <td><?= htmlspecialchars($row['name_ar']); ?></td>
                            <td><?= htmlspecialchars($row['name_en']); ?></td>
                            <td><?= number_format($row['price'], 2); ?></td>
                            <td><?= htmlspecialchars(substr($row['description_ar'], 0, 100)); ?>...</td>
                            <td><?= htmlspecialchars(substr($row['description_en'], 0, 100)); ?>...</td>
                            <td><?= htmlspecialchars($row['cat_name_ar']); ?> (<?= htmlspecialchars($row['cat_name_en']); ?>)</td>
                            <td style="color: <?= $row['stock_quantity'] <= 5 ? 'red' : 'inherit' ?>">
                                <?= htmlspecialchars($row['stock_quantity']); ?>
                                <?= $row['stock_quantity'] <= 5 ? ' ⚠️' : '' ?>
                            </td>
                            <td>
                                <?php if (!empty($row['image_url'])): ?>
                                    <img src="../../assests/uploads/<?= htmlspecialchars(basename($row['image_url'])); ?>" width="80">
                                <?php else: ?>
                                    <span class="text-muted">لا صورة</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))); ?></td>
                            <td>
                                <a href="products/edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-warning">✏️ تعديل</a>
                                <a href="products/delete.php?id=<?= $row['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('هل أنت متأكد أنك تريد حذف هذا المنتج؟')">
                                    🗑 حذف
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center text-danger">
                            <strong>⚠️ لا توجد منتجات</strong>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
