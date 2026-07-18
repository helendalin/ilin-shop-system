<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$form_status = '';
$form_message = '';

$name = '';
$email = '';
$phone = '';
$message = '';

if (isset($_SESSION['contact_flash'])) {
    $form_status = $_SESSION['contact_flash']['status'] ?? '';
    $form_message = $_SESSION['contact_flash']['message'] ?? '';
    unset($_SESSION['contact_flash']);
}

/*
    If customer is logged in, we can save customer_id.
    If not logged in, customer_id will be NULL.
*/
$customer_id = null;

if (isset($_SESSION['customer_id'])) {
    $customer_id = intval($_SESSION['customer_id']);

    /*
        Optional: pre-fill customer data when logged in.
        This uses your tb_customer columns from your project.
    */
    $customerStmt = $conn->prepare("
        SELECT first_name, last_name, email, phone_number
        FROM tb_customer
        WHERE customer_id = ?
        LIMIT 1
    ");

    if ($customerStmt) {
        $customerStmt->bind_param("i", $customer_id);
        $customerStmt->execute();
        $customerData = $customerStmt->get_result()->fetch_assoc();

        if ($customerData) {
            $fullName = trim(($customerData['first_name'] ?? '') . ' ' . ($customerData['last_name'] ?? ''));

            $name = $fullName;
            $email = $customerData['email'] ?? '';
            $phone = $customerData['phone_number'] ?? '';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || $email === '' || $message === '') {
        $form_status = 'error';
        $form_message = 'ກະລຸນາປ້ອນຊື່, ອີເມວ ແລະ ຂໍ້ຄວາມໃຫ້ຄົບຖ້ວນ';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_status = 'error';
        $form_message = 'ຮູບແບບອີເມວບໍ່ຖືກຕ້ອງ';
    } elseif (mb_strlen($name, 'UTF-8') > 100) {
        $form_status = 'error';
        $form_message = 'ຊື່ຂອງທ່ານຍາວເກີນໄປ';
    } elseif (mb_strlen($email, 'UTF-8') > 150) {
        $form_status = 'error';
        $form_message = 'ອີເມວຍາວເກີນໄປ';
    } elseif (mb_strlen($phone, 'UTF-8') > 30) {
        $form_status = 'error';
        $form_message = 'ເບີໂທຍາວເກີນໄປ';
    } elseif (mb_strlen($message, 'UTF-8') > 1000) {
        $form_status = 'error';
        $form_message = 'ຂໍ້ຄວາມຍາວເກີນໄປ';
    } else {
        $insertStmt = $conn->prepare("
            INSERT INTO tb_contact_message
                (customer_id, name, email, phone, message, status)
            VALUES
                (?, ?, ?, ?, ?, 'new')
        ");

        if (!$insertStmt) {
            $form_status = 'error';
            $form_message = 'ບໍ່ສາມາດບັນທຶກຂໍ້ຄວາມໄດ້ ກະລຸນາລອງໃໝ່';
        } else {
            $phoneValue = $phone !== '' ? $phone : null;

            $insertStmt->bind_param(
                "issss",
                $customer_id,
                $name,
                $email,
                $phoneValue,
                $message
            );

            if ($insertStmt->execute()) {
                $_SESSION['contact_flash'] = [
                    'status' => 'success',
                    'message' => 'ຂອບໃຈສຳລັບຂໍ້ຄວາມ ທີມງານ ILIN SHOP ຈະຕິດຕໍ່ກັບທ່ານໃນໄວໆນີ້'
                ];

                header("Location: " . BASE_URL . "/customer/contact.php");
                exit();
            } else {
                $form_status = 'error';
                $form_message = 'ບໍ່ສາມາດສົ່ງຂໍ້ຄວາມໄດ້ ກະລຸນາລອງໃໝ່';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຕິດຕໍ່ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/contact.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="contact-page">

    <section class="contact-hero">
        <div class="contact-hero-content">
            <span class="contact-badge">
                <i class="fa-solid fa-headset"></i>
                ILIN SHOP Support
            </span>

            <h1>ຕິດຕໍ່ພວກເຮົາ</h1>

            <p>
                ມີຄຳຖາມກ່ຽວກັບສິນຄ້າ, ການສັ່ງຊື້ ຫຼື ການຈັດສົ່ງ?
                ທີມງານ ILIN SHOP ພ້ອມໃຫ້ຄຳແນະນຳ.
            </p>

            <div class="hero-actions">
                <a href="tel:02099999999" class="hero-primary-btn">
                    <i class="fa-solid fa-phone"></i>
                    ໂທຫາພວກເຮົາ
                </a>

                <a href="<?= BASE_URL ?>/customer/products.php" class="hero-secondary-btn">
                    <i class="fa-solid fa-bag-shopping"></i>
                    ເບິ່ງສິນຄ້າ
                </a>
            </div>
        </div>

        <div class="contact-hero-icon">
            <i class="fa-solid fa-comments"></i>
        </div>
    </section>

    <section class="contact-info-grid">

        <div class="contact-info-card">
            <div class="info-icon">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <div>
                <h3>ທີ່ຢູ່ຮ້ານ</h3>
                <p>ບ້ານປົ່ງ, ເມືອງໄຊຍະບູລີ, ແຂວງໄຊຍະບູລີ</p>
            </div>
        </div>

        <div class="contact-info-card">
            <div class="info-icon">
                <i class="fa-solid fa-phone"></i>
            </div>
            <div>
                <h3>ເບີໂທ</h3>
                <p>020 9999 9999</p>
            </div>
        </div>

        <div class="contact-info-card">
            <div class="info-icon">
                <i class="fa-regular fa-envelope"></i>
            </div>
            <div>
                <h3>ອີເມວ</h3>
                <p>ilinshop@gmail.com</p>
            </div>
        </div>

        <div class="contact-info-card">
            <div class="info-icon">
                <i class="fa-regular fa-clock"></i>
            </div>
            <div>
                <h3>ເວລາເປີດຮ້ານ</h3>
                <p>08:00 - 20:00 ທຸກມື້</p>
            </div>
        </div>

    </section>

    <section class="contact-layout">

        <div class="contact-left">

            <div class="contact-card">
                <div class="card-title">
                    <div class="card-title-icon">
                        <i class="fa-solid fa-store"></i>
                    </div>
                    <div>
                        <h2>ILIN SHOP</h2>
                        <p>ຮ້ານຂາຍສິນຄ້າສຳລັບແມ່ ແລະ ເດັກ</p>
                    </div>
                </div>

                <div class="contact-list">
                    <div class="contact-list-item">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>ສິນຄ້າຄຸນນະພາບ ແລະ ປອດໄພ</span>
                    </div>

                    <div class="contact-list-item">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>ຈັດສົ່ງສິນຄ້າພາຍໃນວຽງຈັນ</span>
                    </div>

                    <div class="contact-list-item">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>ພ້ອມໃຫ້ຄຳແນະນຳກ່ອນສັ່ງຊື້</span>
                    </div>
                </div>

                <div class="social-box">
                    <h3>ຕິດຕາມພວກເຮົາ</h3>

                    <div class="social-links">
                        <a href="#" aria-label="Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>

                        <a href="#" aria-label="Instagram">
                            <i class="fa-brands fa-instagram"></i>
                        </a>

                        <a href="#" aria-label="WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>

                        <a href="#" aria-label="TikTok">
                            <i class="fa-brands fa-tiktok"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="map-card">
                <div class="map-placeholder">
                    <i class="fa-solid fa-map-location-dot"></i>
                    <h3>ທີ່ຢູ່ຮ້ານ</h3>
                    <p>ບ້ານປົ່ງ, ເມືອງໄຊຍະບູລີ, ແຂວງໄຊຍະບູລີ</p>
                    <span>Map Preview</span>
                </div>
            </div>

        </div>

        <form class="contact-form" action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'); ?>" method="POST">

            <div class="form-title">
                <div class="form-title-icon">
                    <i class="fa-regular fa-paper-plane"></i>
                </div>
                <div>
                    <h2>ສົ່ງຂໍ້ຄວາມຫາພວກເຮົາ</h2>
                    <p>ກອກຂໍ້ມູນດ້ານລຸ່ມ ແລ້ວທີມງານຈະຕິດຕໍ່ກັບທ່ານ</p>
                </div>
            </div>

            <?php if ($form_status !== '' && $form_message !== ''): ?>
                <div class="contact-alert <?= $form_status === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <i class="fa-solid <?= $form_status === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <span><?= htmlspecialchars($form_message, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="name">ຊື່ຂອງທ່ານ</label>
                <div class="input-box">
                    <i class="fa-regular fa-user"></i>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="ປ້ອນຊື່ຂອງທ່ານ"
                        value="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>"
                        maxlength="100"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="email">ອີເມວ</label>
                <div class="input-box">
                    <i class="fa-regular fa-envelope"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="example@email.com"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?>"
                        maxlength="150"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="phone">ເບີໂທ</label>
                <div class="input-box">
                    <i class="fa-solid fa-phone"></i>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        placeholder="020xxxxxxxx"
                        value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'); ?>"
                        maxlength="30"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="message">ຂໍ້ຄວາມ</label>
                <div class="textarea-box">
                    <i class="fa-regular fa-message"></i>
                    <textarea
                        id="message"
                        name="message"
                        placeholder="ພິມຂໍ້ຄວາມຂອງທ່ານ..."
                        maxlength="1000"
                        required
                    ><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <button type="submit" class="send-btn">
                <i class="fa-solid fa-paper-plane"></i>
                ສົ່ງຂໍ້ຄວາມ
            </button>

            <p class="form-note">
                <i class="fa-solid fa-lock"></i>
                ຂໍ້ມູນຂອງທ່ານຈະຖືກໃຊ້ເພື່ອການຕິດຕໍ່ກັບເທົ່ານັ້ນ
            </p>

        </form>

    </section>

</main>

<?php include 'footer.php'; ?>

</body>
</html>