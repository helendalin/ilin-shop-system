<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>Admin Sign Up</title>
    <link rel="stylesheet" href="/ilin-shop-system/assets/css/signup.css">
</head>
<body>

<div class="signup-container">

    <form action="signup_process.php" method="POST" class="signup-box">

        <h2>ສ້າງບັນຊີ Admin</h2>
        <p>ILIN SHOP Management System</p>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>

        <div class="form-row">

            <input type="text"
                   name="first_name"
                   placeholder="ຊື່"
                   required>

            <input type="text"
                   name="last_name"
                   placeholder="ນາມສະກຸນ"
                   required>

        </div>

        <select name="gender" required>
            <option value="">-- ເລືອກເພດ --</option>
            <option value="Male">ຊາຍ</option>
            <option value="Female">ຍິງ</option>
        </select>

        <input type="text"
               name="phone_number"
               placeholder="ເບີໂທ"
               required>

        <input type="email"
               name="email"
               placeholder="ອີເມວ"
               required>

        <input type="password"
               name="password"
               placeholder="ລະຫັດຜ່ານ"
               required>

        <input type="password"
               name="confirm_password"
               placeholder="ຢືນຢັນລະຫັດຜ່ານ"
               required>

        <button type="submit">
            ສ້າງ Admin
        </button>

        <div class="login-link">
            ມີບັນຊີແລ້ວ?
            <a href="login.php">ເຂົ້າລະບົບ</a>
        </div>

    </form>

</div>

</body>
</html>