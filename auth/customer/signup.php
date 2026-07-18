<?php
session_start();

if (isset($_SESSION['customer_id'])) {
    header("Location: ../../customer/home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>Customer Signup - ILIN SHOP</title>
    <!-- <link rel="stylesheet" href="/ilin-shop-system/assets/css/login.css"> -->
    <link rel="stylesheet" href="/ilin-shop-system/assets/css/customer/customer-auth.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">ILIN SHOP</div>

        <h2 class="login-title">ສ້າງບັນຊີລູກຄ້າ</h2>
        <p class="login-subtitle">Mother & Baby Store</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?= htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="signup_process.php" method="POST" class="login-form">

            <div class="form-group">
                <label>ຊື່</label>
                <input type="text" name="first_name" required>
            </div>

            <div class="form-group">
                <label>ນາມສະກຸນ</label>
                <input type="text" name="last_name" required>
            </div>

            <div class="form-group">
                <label>ເພດ</label>
                <select name="gender" class="form-select" required>
                    <option value="">-- ເລືອກເພດ --</option>
                    <option value="Male">ຊາຍ</option>
                    <option value="Female">ຍິງ</option>
                </select>
            </div>

            <div class="form-group">
                <label>ວັນເກີດ</label>
                <input type="date" name="birth_date" required>
            </div>

            <div class="form-group">
                <label>ເບີໂທ</label>
                <input type="text" name="phone_number" required>
            </div>

            <div class="form-group">
                <label>ທີ່ຢູ່</label>
                <input type="text" name="address" required>
            </div>

            <div class="form-group">
                <label>ອີເມວ</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>ລະຫັດຜ່ານ</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>ຢືນຢັນລະຫັດຜ່ານ</label>
                <input type="password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn-signin">
                ສ້າງບັນຊີ
            </button>

            <p class="signup-text">
                ມີບັນຊີແລ້ວ?
                <a href="login.php">ເຂົ້າລະບົບ</a>
            </p>

        </form>

    </div>
</div>

</body>
</html>