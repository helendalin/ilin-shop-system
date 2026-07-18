<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$emp_id = intval($_SESSION['emp_id'] ?? 0);

if ($emp_id <= 0) {
    header("Location: ../../auth/login.php");
    exit();
}

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ປ່ຽນລະຫັດຜ່ານ</title>

    <!-- <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/profile.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <section class="dashboard-hero">
            <div>
                <h1>ປ່ຽນລະຫັດຜ່ານ</h1>
                <p>ປ່ຽນລະຫັດຜ່ານບັນຊີຂອງທ່ານ</p>
            </div>

            <a href="profile.php" class="btn-back">ກັບຄືນ</a>
        </section>

        <section class="dashboard-panel">

            <form action="<?= BASE_URL ?>/actions/employee/change_password_action.php" method="POST" class="password-form">

                <?php if (!empty($success)): ?>
                    <div class="alert-success">ປ່ຽນລະຫັດຜ່ານສຳເລັດແລ້ວ</div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert-error">
                        <?php
                        if ($error === 'wrong_current') echo 'ລະຫັດຜ່ານເກົ່າບໍ່ຖືກຕ້ອງ';
                        elseif ($error === 'not_match') echo 'ລະຫັດຜ່ານໃໝ່ບໍ່ກົງກັນ';
                        elseif ($error === 'short') echo 'ລະຫັດຜ່ານຕ້ອງມີຢ່າງໜ້ອຍ 2 ຕົວອັກສອນ';
                        else echo 'ມີຂໍ້ຜິດພາດ';
                        ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>ລະຫັດຜ່ານເກົ່າ</label>
                    <input type="password" name="current_password" required>
                </div>

                <div class="form-group">
                    <label>ລະຫັດຜ່ານໃໝ່</label>
                    <input type="password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label>ຢືນຢັນລະຫັດຜ່ານໃໝ່</label>
                    <input type="password" name="confirm_password" required>
                </div>

                <button type="submit" class="btn-save">
                    ບັນທຶກລະຫັດຜ່ານ
                </button>

            </form>

        </section>

    </main>

</div>

</body>
</html>