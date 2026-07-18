<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$result = $conn->query("
    SELECT 
        sale_id,
        sale_date,
        total_amount
    FROM tb_sale
    ORDER BY sale_date DESC
");

$totalIncome = 0;
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານລາຍຮັບ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/report.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header report-header">
            <div>
                <h1>ລາຍງານລາຍຮັບ</h1>
                <p>ສະຫຼຸບລາຍຮັບຈາກການຂາຍສິນຄ້າ</p>
            </div>

            <button onclick="window.print()" class="btn-print">
                ພິມລາຍງານ
            </button>
        </div>

        <div class="report-table-card">
            <table>
                <thead>
                    <tr>
                        <th>ລະຫັດການຂາຍ</th>
                        <th>ວັນທີ</th>
                        <th>ລາຍຮັບ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php $totalIncome += $row['total_amount']; ?>
                            <tr>
                                <td><?php echo "SAL-" . str_pad($row['sale_id'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                                <td><?php echo number_format($row['total_amount']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="no-data">ບໍ່ມີຂໍ້ມູນ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="2">ລວມລາຍຮັບທັງໝົດ</th>
                        <th><?php echo number_format($totalIncome); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </main>
</div>

</body>
</html>