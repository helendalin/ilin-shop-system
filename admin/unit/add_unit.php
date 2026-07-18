<?php
include '../../includes/session_check.php';
include '../../config/db.php';
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ເພີ່ມຫົວໜ່ວຍ</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/unit.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">

            <div class="form-header">
                <h1>ເພີ່ມຫົວໜ່ວຍ</h1>

                <a href="<?= BASE_URL ?>/admin/unit/unit.php" class="btn-back">
                    ກັບຄືນ
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?= htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/actions/unit/add_unit_action.php" method="POST">

                <div class="form-group">
                    <label>ຊື່ຫົວໜ່ວຍ</label>

                    <input
                        type="text"
                        name="unit_name"
                        placeholder="ປ້ອນຊື່ຫົວໜ່ວຍ"
                        required
                    >
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