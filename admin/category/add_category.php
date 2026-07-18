<?php include '../../includes/session_check.php'; ?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ເພີ່ມປະເພດສິນຄ້າ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/category.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">
            <div class="form-header">
                <h1>ເພີ່ມປະເພດສິນຄ້າ</h1>

                <a href="category.php" class="btn-back">
                    ກັບຄືນ
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="../../actions/category/add_category_action.php" method="POST">
                <div class="form-group">
                    <label>ຊື່ປະເພດສິນຄ້າ</label>

                    <input
                        type="text"
                        name="category_name"
                        placeholder="ປ້ອນຊື່ປະເພດສິນຄ້າ"
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