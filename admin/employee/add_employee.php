<?php
include '../../includes/session_check.php';
include '../../config/db.php';
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ເພີ່ມພະນັກງານ</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/employee.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">

            <div class="form-header">
                <h1>ເພີ່ມພະນັກງານ</h1>

                <a href="<?= BASE_URL ?>/admin/employee/employee.php" class="btn-back">
                    ກັບຄືນ
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/actions/employee/add_employee_action.php" method="POST">

                <div class="form-row">
                    <div class="form-group">
                        <label>ຊື່</label>
                        <input type="text" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label>ນາມສະກຸນ</label>
                        <input type="text" name="last_name" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ເພດ</label>

                        <select name="gender" required>
                            <option value="">-- ເລືອກເພດ --</option>
                            <option value="Male">ຊາຍ</option>
                            <option value="Female">ຍິງ</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ວັນເດືອນປີເກີດ</label>
                        <input type="date" name="birth_date" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>ທີ່ຢູ່</label>
                    <input type="text" name="address" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ເບີໂທ</label>
                        <input type="text" name="phone_number" required>
                    </div>

                    <div class="form-group">
                        <label>ຕຳແໜ່ງ</label>

                        <select name="role" required>
                            <option value="">-- ເລືອກຕຳແໜ່ງ --</option>
                            <option value="Admin">Admin</option>
                            <option value="Employee">Employee</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>ອີເມວ</label>
                    <input type="email" name="email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ລະຫັດຜ່ານ</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>ຢືນຢັນລະຫັດຜ່ານ</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn-reset">
                        ລ້າງຂໍ້ມູນ
                    </button>

                    <button type="submit" class="btn-primary">
                        ບັນທຶກ
                    </button>
                </div>

            </form>

        </div>

    </main>

</div>

</body>
</html>