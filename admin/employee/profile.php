<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$emp_id = intval($_SESSION['emp_id'] ?? 0);

if ($emp_id <= 0) {
    header("Location: ../../auth/login.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT emp_id, first_name, last_name, gender, birth_date, address, phone_number, role, email
    FROM tb_employee
    WHERE emp_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee) {
    header("Location: ../../admin/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ຂໍ້ມູນບັນຊີ</title>
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
                <h1>ຂໍ້ມູນບັນຊີ</h1>
                <p>ຂໍ້ມູນສ່ວນຕົວຂອງຜູ້ໃຊ້ລະບົບ</p>
            </div>

            <a href="../dashboard.php" class="btn-back">ກັບຄືນ</a>
        </section>

        <section class="dashboard-panel">

            <div class="profile-card">

                <div class="profile-avatar">
                    <?= strtoupper(substr($employee['first_name'], 0, 1)); ?>
                </div>

                <h2>
                    <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                </h2>

                <p class="profile-role">
                    <?= htmlspecialchars($employee['role']); ?>
                </p>

                <div class="profile-info">

                    <div>
                        <span>ລະຫັດພະນັກງານ</span>
                        <strong>EMP-<?= str_pad($employee['emp_id'], 4, "0", STR_PAD_LEFT); ?></strong>
                    </div>

                    <div>
                        <span>ຊື່</span>
                        <strong><?= htmlspecialchars($employee['first_name']); ?></strong>
                    </div>

                    <div>
                        <span>ນາມສະກຸນ</span>
                        <strong><?= htmlspecialchars($employee['last_name']); ?></strong>
                    </div>

                    <div>
                        <span>ເພດ</span>
                        <strong><?= htmlspecialchars($employee['gender'] ?? '-'); ?></strong>
                    </div>

                    <div>
                        <span>ວັນເກີດ</span>
                        <strong><?= htmlspecialchars($employee['birth_date'] ?? '-'); ?></strong>
                    </div>

                    <div>
                        <span>ເບີໂທ</span>
                        <strong><?= htmlspecialchars($employee['phone_number'] ?? '-'); ?></strong>
                    </div>

                    <div>
                        <span>Email</span>
                        <strong><?= htmlspecialchars($employee['email']); ?></strong>
                    </div>

                    <div>
                        <span>ທີ່ຢູ່</span>
                        <strong><?= htmlspecialchars($employee['address'] ?? '-'); ?></strong>
                    </div>

                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>