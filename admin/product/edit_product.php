<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: product.php");
    exit();
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT * FROM tb_product WHERE product_id = ? LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: product.php");
    exit();
}

$row = $result->fetch_assoc();

$categories = $conn->query("SELECT * FROM tb_category ORDER BY category_name ASC");
$units = $conn->query("SELECT * FROM tb_unit ORDER BY unit_name ASC");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ແກ້ໄຂສິນຄ້າ</title>
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
                <h1>ແກ້ໄຂສິນຄ້າ</h1>
                <a href="product.php" class="btn-back">ກັບຄືນ</a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form action="../../actions/product/update_product_action.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                <input type="hidden" name="old_image" value="<?php echo htmlspecialchars($row['image']); ?>">

                <div class="form-group">
                    <label>ລະຫັດສິນຄ້າ</label>
                    <input type="text" disabled value="<?php echo 'PRO-' . str_pad($row['product_id'], 4, '0', STR_PAD_LEFT); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ຊື່ສິນຄ້າ</label>
                        <input type="text" name="product_name" value="<?php echo htmlspecialchars($row['product_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>ລາຄາ</label>
                        <input type="number" name="price" min="0" value="<?php echo htmlspecialchars($row['price']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ປະເພດສິນຄ້າ</label>
                        <select name="category_id" required>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['category_id']; ?>"
                                    <?php if ($cat['category_id'] == $row['category_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ຫົວໜ່ວຍ</label>
                        <select name="unit_id" required>
                            <?php while ($unit = $units->fetch_assoc()): ?>
                                <option value="<?php echo $unit['unit_id']; ?>"
                                    <?php if ($unit['unit_id'] == $row['unit_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($unit['unit_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>ຈຳນວນ</label>
                    <input type="number" name="qty" min="0" value="<?php echo htmlspecialchars($row['qty']); ?>" required>
                </div>

                <div class="form-group">
                    <label>ລາຍລະອຽດ</label>
                    <textarea name="description" rows="4"><?php echo htmlspecialchars($row['description']); ?></textarea>
                </div>

                <?php if (!empty($row['image'])): ?>
                    <div class="form-group">
                        <label>ຮູບປັດຈຸບັນ</label>
                        <img class="preview-img" src="../../assets/images/<?php echo htmlspecialchars($row['image']); ?>">
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>ປ່ຽນຮູບສິນຄ້າ</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">ອັບເດດ</button>
                </div>

            </form>
        </div>

    </main>
</div>

</body>
</html>