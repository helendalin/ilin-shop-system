<?php
include '../config/db.php';

$newProducts = $conn->query("
    SELECT p.product_id, p.product_name, p.price, p.image, p.qty, c.category_name
    FROM tb_product p
    LEFT JOIN tb_category c ON p.category_id = c.category_id
    ORDER BY p.product_id DESC
    LIMIT 8
");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ໜ້າຫຼັກ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/home.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="hero">
    <div class="hero-content">
        <span class="hero-badge">Mother & Baby Store</span>

        <h1>ທຸກສິ່ງສຳລັບແມ່ ແລະ ເດັກນ້ອຍ</h1>

        <p>
            ເລືອກຊື້ສິນຄ້າຄຸນນະພາບສຳລັບເດັກແຮກເກີດ,
            ເດັກນ້ອຍ ແລະ ຄຸນແມ່ ດ້ວຍດີໄຊນ໌ທີ່ສະອາດ ປອດໄພ ແລະ ທັນສະໄໝ.
        </p>

        <div class="hero-buttons">
            <a href="<?= BASE_URL ?>/customer/products.php" class="primary-btn">
                ເລີ່ມຊື້ສິນຄ້າ
            </a>

            <a href="<?= BASE_URL ?>/customer/about.php" class="secondary-btn">
                ຮູ້ຈັກຮ້ານ
            </a>
        </div>
    </div>

    <div class="hero-card">
        <img src="<?= BASE_URL ?>/assets/images/hero-mother-baby.jpg" class="hero-img" alt="Mother and Baby">
    </div>
</section>

<section class="category-section">
    <div class="section-title">
        <h2>ໝວດໝູ່ສິນຄ້າ</h2>
        <p>ເລືອກສິນຄ້າຕາມຄວາມຕ້ອງການ</p>
    </div>

    <div class="category-grid">
        <a href="<?= BASE_URL ?>/customer/products.php?category_id=5" class="category-card">
            <img src="<?= BASE_URL ?>/assets/images/newborn1.jpg" alt="">
            <h3>ນົມຜົງເດັກ</h3>
            <p>Milk Powder</p>
        </a>

        <a href="<?= BASE_URL ?>/customer/products.php?category_id=3" class="category-card">
            <img src="<?= BASE_URL ?>/assets/images/baby-clothes.jpg" alt="">
            <h3>ເສື້ອຜ້າເດັກ</h3>
            <p>Baby Clothing</p>
        </a>

        <a href="<?= BASE_URL ?>/customer/products.php?category_id=7" class="category-card">
            <img src="<?= BASE_URL ?>/assets/images/feeding.jpg" alt="">
            <h3>ຂອງຫຼິ້ນເດັກ</h3>
            <p>Baby Toys</p>
        </a>

        <a href="<?= BASE_URL ?>/customer/products.php?category_id=14" class="category-card">
            <img src="<?= BASE_URL ?>/assets/images/baby1.jpg" alt="">
            <h3>ອາຫານເສີມ</h3>
            <p>Baby Food</p>
        </a>
    </div>
</section>

<section class="product-section">
    <div class="section-title row-title">
        <div>
            <h2>ສິນຄ້າໃໝ່</h2>
            <p>ສິນຄ້າລ່າສຸດຈາກຮ້ານ</p>
        </div>

        <a href="<?= BASE_URL ?>/customer/products.php">ເບິ່ງທັງໝົດ</a>
    </div>

    <div class="product-grid">
        <?php if ($newProducts && $newProducts->num_rows > 0): ?>
            <?php while ($row = $newProducts->fetch_assoc()): ?>
                <div class="product-card">

                    <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= $row['product_id']; ?>" class="product-image">
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($row['image']); ?>" alt="<?= htmlspecialchars($row['product_name']); ?>">
                        <?php else: ?>
                            <img src="<?= BASE_URL ?>/assets/images/no-product.png" alt="">
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

                        <div class="product-bottom">
                            <strong><?= number_format($row['price']); ?> ₭</strong>

                            <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= $row['product_id']; ?>">
                                ເບິ່ງລາຍລະອຽດ
                            </a>
                        </div>
                    </div>

                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-product">
                ຍັງບໍ່ມີສິນຄ້າ
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="about-preview">
    <div>
        <span class="about-badge">Why ILIN SHOP?</span>
        <h2>ເປັນຫຍັງຕ້ອງ ILIN SHOP?</h2>
        <p>
            ພວກເຮົາເນັ້ນສິນຄ້າທີ່ເໝາະສຳລັບແມ່ ແລະ ເດັກນ້ອຍ,
            ຄຸນນະພາບດີ, ໃຊ້ງານງ່າຍ ແລະ ລາຄາເໝາະສົມ.
        </p>
    </div>

    <div class="benefit-grid">
        <div><i class="fa-solid fa-check"></i> ຄຸນນະພາບດີ</div>
        <div><i class="fa-solid fa-check"></i> ລາຄາເໝາະສົມ</div>
        <div><i class="fa-solid fa-check"></i> ເໝາະສຳລັບເດັກ</div>
        <div><i class="fa-solid fa-check"></i> ບໍລິການໄວ</div>
    </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>