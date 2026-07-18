<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$categories = $conn->query("SELECT * FROM tb_category ORDER BY category_name ASC");
$units = $conn->query("SELECT * FROM tb_unit ORDER BY unit_name ASC");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ເພີ່ມສິນຄ້າ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/product.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">
            <div class="form-header">
                <h1>ເພີ່ມສິນຄ້າ</h1>
                <a href="product.php" class="btn-back">ກັບຄືນ</a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form action="../../actions/product/add_product_action.php" method="POST" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group">
                        <label>ຊື່ສິນຄ້າ</label>
                        <input type="text" name="product_name" required>
                    </div>

                    <div class="form-group">
                        <label>ລາຄາ</label>
                        <input type="number" name="price" min="0" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ປະເພດສິນຄ້າ</label>
                        <select name="category_id" required>
                            <option value="">-- ເລືອກປະເພດ --</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['category_id']; ?>">
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ຫົວໜ່ວຍ</label>
                        <select name="unit_id" required>
                            <option value="">-- ເລືອກຫົວໜ່ວຍ --</option>
                            <?php while ($unit = $units->fetch_assoc()): ?>
                                <option value="<?php echo $unit['unit_id']; ?>">
                                    <?php echo htmlspecialchars($unit['unit_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>ຈຳນວນເລີ່ມຕົ້ນ</label>
                    <input type="number" name="qty" min="0" value="0" required>
                </div>

                <div class="form-group">
                    <label>ລາຍລະອຽດ</label>
                    <textarea name="description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label>ຮູບສິນຄ້າ</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn-reset">ລ້າງຂໍ້ມູນ</button>
                    <button type="submit" class="btn-primary">ບັນທຶກ</button>
                </div>

            </form>
        </div>

    </main>
</div>

</body>
</html>