<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php");
    exit();
}

$customer_id = intval($_SESSION['customer_id']);

$stmt = $conn->prepare("
    SELECT customer_id, first_name, last_name, gender, birth_date, address, phone_number, email
    FROM tb_customer
    WHERE customer_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $customer_id);
$stmt->execute();

$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    header("Location: " . BASE_URL . "/auth/customer/logout.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ບັນຊີຂອງຂ້ອຍ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/account.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<section class="account-page">

    <div class="account-card">

        <div class="profile-header">
            <div class="profile-avatar">
                <i class="fa-solid fa-user"></i>
            </div>

            <div>
                <h2>
                    <?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                </h2>

                <p>Customer Account</p>
            </div>
        </div>

        <div class="profile-grid">

            <div class="profile-item">
                <label>ຊື່</label>
                <span><?= htmlspecialchars($customer['first_name']); ?></span>
            </div>

            <div class="profile-item">
                <label>ນາມສະກຸນ</label>
                <span><?= htmlspecialchars($customer['last_name']); ?></span>
            </div>

            <div class="profile-item">
                <label>ເພດ</label>
                <span><?= htmlspecialchars($customer['gender'] ?? '-'); ?></span>
            </div>

            <div class="profile-item">
                <label>ວັນເກີດ</label>
                <span>
                    <?= !empty($customer['birth_date']) ? date('d-m-Y', strtotime($customer['birth_date'])) : '-'; ?>
                </span>
            </div>

            <div class="profile-item">
                <label>ເບີໂທ</label>
                <span><?= htmlspecialchars($customer['phone_number'] ?? '-'); ?></span>
            </div>

            <div class="profile-item">
                <label>Email</label>
                <span><?= htmlspecialchars($customer['email'] ?? '-'); ?></span>
            </div>

            <div class="profile-item full">
                <label>ທີ່ຢູ່</label>
                <span><?= nl2br(htmlspecialchars($customer['address'] ?? '-')); ?></span>
            </div>

        </div>

        <!-- <div class="account-actions">

            <a href="<?= BASE_URL ?>/customer/order_history.php" class="account-btn">
                <i class="fa-solid fa-receipt"></i>
                ປະຫວັດການສັ່ງຊື້
            </a>

            <a href="<?= BASE_URL ?>/auth/customer/logout.php" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                ອອກຈາກລະບົບ
            </a>

        </div> -->

    </div>

</section>

<?php include 'footer.php'; ?>

</body>
</html>