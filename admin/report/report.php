<?php
include '../../includes/session_check.php';
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/report.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>ລາຍງານ</h1>
                <p>ເລືອກປະເພດລາຍງານທີ່ຕ້ອງການເບິ່ງ</p>
            </div>
        </div>

        <div class="report-grid">

            <a href="product_report.php" class="report-card">
                <h3>ລາຍງານຂໍ້ມູນສິນຄ້າ</h3>
                <p>ສະແດງລາຍຊື່ສິນຄ້າ, ປະເພດ, ຫົວໜ່ວຍ, ຈຳນວນ ແລະ ລາຄາ</p>
            </a>

            <a href="supplier_report.php" class="report-card">
                <h3>ລາຍງານຂໍ້ມູນຜູ້ສະໜອງ</h3>
                <p>ສະແດງຂໍ້ມູນຜູ້ສະໜອງທັງໝົດ</p>
            </a>

            <a href="order_report.php" class="report-card">
                <h3>ລາຍງານການສັ່ງຊື້ສິນຄ້າ</h3>
                <p>ສະແດງລາຍການຄຳສັ່ງຊື້ ແລະ ລາຍລະອຽດສິນຄ້າ</p>
            </a>

            <a href="import_report.php" class="report-card">
                <h3>ລາຍງານຂໍ້ມູນນຳເຂົ້າສິນຄ້າ</h3>
                <p>ສະແດງລາຍການນຳເຂົ້າ ແລະ ຕົ້ນທຶນ</p>
            </a>

            <a href="sale_report.php" class="report-card">
                <h3>ລາຍງານການຂາຍສິນຄ້າ</h3>
                <p>ສະແດງລາຍການຂາຍສິນຄ້າ</p>
            </a>

            <a href="income_report.php" class="report-card">
                <h3>ລາຍງານລາຍຮັບ</h3>
                <p>ສະຫຼຸບລາຍຮັບຈາກການຂາຍ</p>
            </a>

            <a href="expense_report.php" class="report-card">
                <h3>ລາຍງານລາຍຈ່າຍ</h3>
                <p>ສະຫຼຸບລາຍຈ່າຍຈາກການນຳເຂົ້າ</p>
            </a>

            <a href="popular_product_report.php" class="report-card">
                <h3>ລາຍງານສິນຄ້າຂາຍດີ</h3>
                <p>ສະແດງສິນຄ້າທີ່ຂາຍໄດ້ຫຼາຍທີ່ສຸດ</p>
            </a>

        </div>

    </main>
</div>

</body>
</html>