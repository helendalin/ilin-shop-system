<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../../config/db.php';

/*
    Customer login page only.
    If customer already logged in, go customer home.
    If admin/employee is logged in, do nothing.
*/
if (isset($_SESSION['customer_id']) && intval($_SESSION['customer_id']) > 0) {
    header("Location: " . BASE_URL . "/customer/home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>Customer Login - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/customer-auth.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">ILIN SHOP</div>

        <h2 class="login-title">ເຂົ້າສູ່ບັນຊີລູກຄ້າ</h2>
        <p class="login-subtitle">Mother & Baby Store</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?= htmlspecialchars($_GET['success'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/auth/customer/login_process.php" method="POST" class="login-form">

            <div class="form-group">
                <label>ອີເມວ</label>
                <input type="email" name="email" placeholder="ປ້ອນອີເມວ" required>
            </div>

            <div class="form-group">
                <label>ລະຫັດຜ່ານ</label>
                <input type="password" name="password" placeholder="********" required>
            </div>

            <button type="submit" class="btn-signin">
                ເຂົ້າລະບົບ
            </button>

            <p class="signup-text">
                ຍັງບໍ່ມີບັນຊີ?
                <a href="<?= BASE_URL ?>/auth/customer/signup.php">
                    ສ້າງບັນຊີລູກຄ້າ
                </a>
            </p>

            <p class="signup-text">
                <a href="<?= BASE_URL ?>/customer/home.php">
                    ກັບໄປໜ້າຫຼັກ
                </a>
            </p>

        </form>

    </div>
</div>

</body>
</html>