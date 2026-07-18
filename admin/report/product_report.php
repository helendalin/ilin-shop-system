<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$result = $conn->query("
    SELECT p.*, c.category_name, u.unit_name
    FROM tb_product p
    LEFT JOIN tb_category c ON p.category_id = c.category_id
    LEFT JOIN tb_unit u ON p.unit_id = u.unit_id
    ORDER BY p.product_id DESC
");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານຂໍ້ມູນສິນຄ້າ</title>
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
                <h1>ລາຍງານຂໍ້ມູນສິນຄ້າ</h1>
                <p>ຈໍານວນສິນຄ້າທັງໝົດ: 
                    <strong><?php echo $result->num_rows; ?></strong>
                    ລາຍການ
                </p>
            </div>

            <button onclick="window.print()" class="btn-print">ພິມລາຍງານ</button>
        </div>

        <div class="report-table-card">
            <table>
                <thead>
                    <tr>
                        <th>ລະຫັດ</th>
                        <th>ຊື່ສິນຄ້າ</th>
                        <th>ປະເພດ</th>
                        <th>ຫົວໜ່ວຍ</th>
                        <th>ຈຳນວນ</th>
                        <th>ລາຄາ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo "PRO-" . str_pad($row['product_id'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['category_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['unit_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['qty']); ?></td>
                                <td><?php echo number_format($row['price']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="no-data">ບໍ່ມີຂໍ້ມູນ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>