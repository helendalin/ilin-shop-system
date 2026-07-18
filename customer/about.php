<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$aboutImagePath = __DIR__ . '/../assets/images/mother-baby.jpg';
$aboutImage = BASE_URL . '/assets/images/mother-baby.jpg';

if (!file_exists($aboutImagePath)) {
    $aboutImage = BASE_URL . '/assets/images/no-product.png';
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ກ່ຽວກັບ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/about.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="about-page">

    <section class="about-hero">

        <div class="about-hero-content">
            <span class="hero-badge">
                <i class="fa-solid fa-heart"></i>
                About ILIN SHOP
            </span>

            <h1>ຮ້ານສຳລັບແມ່ ແລະ ເດັກນ້ອຍ</h1>

            <p>
                ILIN SHOP ເປັນຮ້ານຈຳໜ່າຍສິນຄ້າສຳລັບແມ່ ແລະ ເດັກນ້ອຍ
                ທີ່ເນັ້ນຄຸນນະພາບ, ຄວາມປອດໄພ, ລາຄາເໝາະສົມ ແລະ
                ການບໍລິການທີ່ອົບອຸ່ນເປັນກັນເອງ.
            </p>

            <div class="about-hero-actions">
                <a href="<?= BASE_URL ?>/customer/products.php" class="primary-hero-btn">
                    <i class="fa-solid fa-bag-shopping"></i>
                    ເບິ່ງສິນຄ້າ
                </a>

                <a href="<?= BASE_URL ?>/customer/contact.php" class="secondary-hero-btn">
                    <i class="fa-solid fa-headset"></i>
                    ຕິດຕໍ່ພວກເຮົາ
                </a>
            </div>
        </div>

        <div class="about-image-wrap">
            <div class="about-image">
                <img src="<?= htmlspecialchars($aboutImage, ENT_QUOTES, 'UTF-8'); ?>" alt="ILIN SHOP Mother and Baby">
            </div>

            <div class="floating-card floating-card-top">
                <i class="fa-solid fa-shield-heart"></i>
                <div>
                    <strong>ປອດໄພ</strong>
                    <span>ສຳລັບແມ່ ແລະ ເດັກ</span>
                </div>
            </div>

            <div class="floating-card floating-card-bottom">
                <i class="fa-solid fa-truck-fast"></i>
                <div>
                    <strong>ຈັດສົ່ງໄວ</strong>
                    <span>ບໍລິການດ້ວຍໃຈ</span>
                </div>
            </div>
        </div>

    </section>

    <section class="about-stats">

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-baby"></i>
            </div>
            <strong>100+</strong>
            <span>ສິນຄ້າສຳລັບເດັກ</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-users"></i>
            </div>
            <strong>500+</strong>
            <span>ລູກຄ້າໄວ້ວາງໃຈ</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <strong>Fast</strong>
            <span>ການຈັດສົ່ງສະດວກ</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-star"></i>
            </div>
            <strong>Premium</strong>
            <span>ຄຸນນະພາບສິນຄ້າ</span>
        </div>

    </section>

    <section class="about-section">

        <div class="section-heading">
            <span>Our Purpose</span>
            <h2>ພວກເຮົາໃຫ້ຄວາມສຳຄັນກັບຫຍັງ?</h2>
            <p>
                ILIN SHOP ຕ້ອງການເປັນຮ້ານທີ່ລູກຄ້າສາມາດໄວ້ວາງໃຈໄດ້
                ໃນການເລືອກຊື້ສິນຄ້າສຳລັບຄອບຄົວ.
            </p>
        </div>

        <div class="about-grid">

            <div class="about-card">
                <div class="about-card-icon">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <h3>ວິໄສທັດ</h3>
                <p>
                    ເປັນຮ້ານອອນລາຍສຳລັບແມ່ ແລະ ເດັກ
                    ທີ່ລູກຄ້າໄວ້ວາງໃຈໃນຄຸນນະພາບ ແລະ ການບໍລິການ.
                </p>
            </div>

            <div class="about-card">
                <div class="about-card-icon">
                    <i class="fa-solid fa-heart"></i>
                </div>
                <h3>ພາລະກິດ</h3>
                <p>
                    ຈັດຫາສິນຄ້າທີ່ມີຄຸນນະພາບ, ປອດໄພ, ເໝາະສົມກັບເດັກນ້ອຍ
                    ແລະ ມີລາຄາທີ່ລູກຄ້າສາມາດເຂົ້າເຖິງໄດ້.
                </p>
            </div>

            <div class="about-card">
                <div class="about-card-icon">
                    <i class="fa-solid fa-hand-holding-heart"></i>
                </div>
                <h3>ການບໍລິການ</h3>
                <p>
                    ໃຫ້ຄຳແນະນຳດ້ວຍຄວາມໃສ່ໃຈ, ຕອບກັບໄວ
                    ແລະ ດູແລລູກຄ້າໃຫ້ຮູ້ສຶກອົບອຸ່ນ.
                </p>
            </div>

        </div>

    </section>

    <section class="story-section">

        <div class="story-card">
            <div class="story-content">
                <span class="story-label">Our Story</span>
                <h2>ເລື່ອງລາວຂອງ ILIN SHOP</h2>
                <p>
                    ILIN SHOP ເກີດຂຶ້ນຈາກຄວາມຕັ້ງໃຈທີ່ຢາກໃຫ້ຄອບຄົວສາມາດຊື້ສິນຄ້າ
                    ສຳລັບແມ່ ແລະ ເດັກໄດ້ງ່າຍຂຶ້ນ. ພວກເຮົາເນັ້ນການຄັດເລືອກສິນຄ້າ
                    ທີ່ເໝາະກັບການໃຊ້ງານຈິງ, ຮູບແບບສວຍງາມ ແລະ ມີຄວາມປອດໄພ.
                </p>

                <p>
                    ພວກເຮົາເຊື່ອວ່າສິນຄ້າສຳລັບເດັກບໍ່ຄວນມີພຽງແຕ່ຄວາມສວຍງາມ,
                    ແຕ່ຕ້ອງມີຄວາມປອດໄພ, ໃຊ້ງານງ່າຍ ແລະ ເໝາະສົມກັບຊີວິດປະຈຳວັນ.
                </p>
            </div>

            <div class="story-list">
                <div class="story-item">
                    <i class="fa-solid fa-check"></i>
                    <span>ຄັດເລືອກສິນຄ້າຢ່າງໃສ່ໃຈ</span>
                </div>

                <div class="story-item">
                    <i class="fa-solid fa-check"></i>
                    <span>ເນັ້ນຄວາມປອດໄພ ແລະ ຄຸນນະພາບ</span>
                </div>

                <div class="story-item">
                    <i class="fa-solid fa-check"></i>
                    <span>ບໍລິການລູກຄ້າດ້ວຍຄວາມຈິງໃຈ</span>
                </div>

                <div class="story-item">
                    <i class="fa-solid fa-check"></i>
                    <span>ສັ່ງຊື້ງ່າຍ ແລະ ຕິດຕາມອໍເດີໄດ້</span>
                </div>
            </div>
        </div>

    </section>

    <section class="why-section">

        <div class="section-heading">
            <span>Why Choose Us</span>
            <h2>ເຫດຜົນທີ່ລູກຄ້າເລືອກ ILIN SHOP</h2>
        </div>

        <div class="why-grid">

            <div class="why-card">
                <i class="fa-solid fa-shield-heart"></i>
                <h3>ຄວາມປອດໄພ</h3>
                <p>ສິນຄ້າຖືກຄັດເລືອກໂດຍເນັ້ນຄວາມປອດໄພຕໍ່ເດັກນ້ອຍ.</p>
            </div>

            <div class="why-card">
                <i class="fa-solid fa-tags"></i>
                <h3>ລາຄາເໝາະສົມ</h3>
                <p>ສິນຄ້າຄຸນນະພາບດີ ແລະ ມີລາຄາທີ່ຄຸ້ມຄ່າ.</p>
            </div>

            <div class="why-card">
                <i class="fa-solid fa-truck-fast"></i>
                <h3>ຈັດສົ່ງສະດວກ</h3>
                <p>ມີຮູບແບບການຈັດສົ່ງໃຫ້ເລືອກຕາມຄວາມສະດວກ.</p>
            </div>

            <div class="why-card">
                <i class="fa-solid fa-headset"></i>
                <h3>ພ້ອມໃຫ້ຄຳແນະນຳ</h3>
                <p>ທີມງານພ້ອມຊ່ວຍເລືອກສິນຄ້າທີ່ເໝາະສົມ.</p>
            </div>

        </div>

    </section>

    <section class="about-cta">
        <div>
            <span>Start Shopping</span>
            <h2>ພ້ອມເລືອກສິນຄ້າສຳລັບຄອບຄົວແລ້ວບໍ?</h2>
            <p>ເບິ່ງສິນຄ້າແມ່ ແລະ ເດັກທີ່ ILIN SHOP ຄັດເລືອກໄວ້ໃຫ້ທ່ານ.</p>
        </div>

        <a href="<?= BASE_URL ?>/customer/products.php">
            <i class="fa-solid fa-bag-shopping"></i>
            ເລືອກຊື້ສິນຄ້າ
        </a>
    </section>

</main>

<?php include 'footer.php'; ?>

</body>
</html>