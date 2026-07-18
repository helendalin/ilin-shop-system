<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';

/*
    This page is ADMIN / EMPLOYEE login page only.

    If admin/employee already logged in, go to admin dashboard.
    If customer already logged in, do nothing.
    Do NOT clear customer session here.
*/

if (
    isset($_SESSION['user_type']) &&
    in_array($_SESSION['user_type'], ['admin', 'employee'], true) &&
    isset($_SESSION['emp_id']) &&
    intval($_SESSION['emp_id']) > 0
) {
    header("Location: " . BASE_URL . "/admin/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css">
</head>
<body>

<div class="login-wrapper">

    <div class="login-card">

        <div class="login-logo">
            ILIN SHOP
        </div>

        <h2 class="login-title">ເຂົ້າສູ່ລະບົບ</h2>
        <p class="login-subtitle">ILIN SHOP Management System</p>

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

        <form action="<?= BASE_URL ?>/auth/login_process.php" method="POST" class="login-form">

            <div class="form-group">
                <label for="email">ອີເມວ</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    placeholder="ປ້ອນອີເມວ"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">ລະຫັດຜ່ານ</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="********"
                    required
                >
            </div>

            <button type="submit" class="btn-signin">
                ເຂົ້າລະບົບ
            </button>

        </form>

    </div>

</div>

</body>
</html>