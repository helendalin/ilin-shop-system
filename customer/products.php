<?php
include '../config/db.php';

$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? ($_GET['category_id'] ?? '');

$sql = "
    SELECT p.*, c.category_name
    FROM tb_product p
    LEFT JOIN tb_category c ON p.category_id = c.category_id
    WHERE 1
";

if (!empty($search)) {
    $safeSearch = $conn->real_escape_string($search);
    $sql .= " AND p.product_name LIKE '%$safeSearch%'";
}

if (!empty($category)) {
    $category = intval($category);
    $sql .= " AND p.category_id = $category";
}

$sql .= " ORDER BY p.product_id DESC";

$products = $conn->query($sql);

$categories = $conn->query("
    SELECT *
    FROM tb_category
    ORDER BY category_name ASC
");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ສິນຄ້າ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/products.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<section class="products-page">

    <div class="section-title">
        <h2>ສິນຄ້າທັງໝົດ</h2>
        <p>ເລືອກຊື້ສິນຄ້າສຳລັບແມ່ ແລະ ເດັກ</p>
    </div>

    <form method="GET" class="filter-bar">

        <input
            type="text"
            name="search"
            placeholder="ຄົ້ນຫາສິນຄ້າ..."
            value="<?= htmlspecialchars($search); ?>"
        >

        <select name="category">
            <option value="">ທຸກປະເພດ</option>

            <?php while ($cat = $categories->fetch_assoc()): ?>
                <option
                    value="<?= $cat['category_id']; ?>"
                    <?= ($category == $cat['category_id']) ? 'selected' : ''; ?>
                >
                    <?= htmlspecialchars($cat['category_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>

        <button type="submit">
            <i class="fa-solid fa-magnifying-glass"></i>
            ຄົ້ນຫາ
        </button>

    </form>

    <div class="product-grid">

        <?php if ($products && $products->num_rows > 0): ?>

            <?php while ($row = $products->fetch_assoc()): ?>

                <div class="product-card">

                    <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= $row['product_id']; ?>" class="product-image">

                        <?php if (!empty($row['image'])): ?>
                            <img
                                src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($row['image']); ?>"
                                alt="<?= htmlspecialchars($row['product_name']); ?>"
                            >
                        <?php else: ?>
                            <span class="no-image">🧸</span>
                        <?php endif; ?>

                        <?php if (intval($row['qty']) <= 0): ?>
                            <span class="product-badge out">ສິນຄ້າໝົດ</span>
                        <?php elseif (intval($row['qty']) <= 5): ?>
                            <span class="product-badge low">ເຫຼືອນ້ອຍ</span>
                        <?php endif; ?>

                    </a>

                    <div class="product-info">

                        <span class="product-category">
                            <?= htmlspecialchars($row['category_name'] ?? 'ທົ່ວໄປ'); ?>
                        </span>

                        <h3 class="product-name">
                            <?= htmlspecialchars($row['product_name']); ?>
                        </h3>

                        <?php if (intval($row['qty']) <= 0): ?>
                            <p class="stock out-stock">ສິນຄ້າໝົດ</p>
                        <?php elseif (intval($row['qty']) <= 5): ?>
                            <p class="stock low-stock">ເຫຼືອນ້ອຍ</p>
                        <?php endif; ?>

                        <div class="product-bottom">

                            <div class="price-box">
                                <span class="price">
                                    <?= number_format($row['price']); ?> ₭
                                </span>
                            </div>

                            <a class="view-btn"
                               href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= $row['product_id']; ?>">
                                ເບິ່ງລາຍລະອຽດ
                            </a>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="empty-product">
                <i class="fa-solid fa-box-open"></i>
                <h3>ບໍ່ພົບສິນຄ້າ</h3>
                <p>ລອງປ່ຽນຄຳຄົ້ນຫາ ຫຼື ເລືອກປະເພດອື່ນ</p>
            </div>

        <?php endif; ?>

    </div>

</section>

<?php include 'footer.php'; ?>

</body>
</html>