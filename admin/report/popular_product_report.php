<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$result = $conn->query("
    SELECT 
        p.product_id,
        p.product_name,
        c.category_name,
        u.unit_name,
        SUM(sd.qty) AS total_sold,
        SUM(sd.qty * sd.price) AS total_amount
    FROM tb_sale_detail sd
    LEFT JOIN tb_product p ON sd.product_id = p.product_id
    LEFT JOIN tb_category c ON p.category_id = c.category_id
    LEFT JOIN tb_unit u ON p.unit_id = u.unit_id
    GROUP BY p.product_id
    ORDER BY total_sold DESC
");

$rank = 1;
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານສິນຄ້າຂາຍດີ</title>

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
                <h1>ລາຍງານສິນຄ້າຂາຍດີ</h1>

                <p>
                    ຈຳນວນສິນຄ້າທີ່ມີການຂາຍ:
                    <strong><?php echo $result->num_rows; ?></strong>
                    ລາຍການ
                </p>
            </div>

            <button onclick="window.print()" class="btn-print">
                ພິມລາຍງານ
            </button>

        </div>

        <div class="report-table-card">

            <table>

                <thead>
                    <tr>
                        <th>ອັນດັບ</th>
                        <th>ລະຫັດສິນຄ້າ</th>
                        <th>ຊື່ສິນຄ້າ</th>
                        <th>ປະເພດ</th>
                        <th>ຫົວໜ່ວຍ</th>
                        <th>ຈຳນວນຂາຍ</th>
                        <th>ຍອດຂາຍລວມ</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>
                                <td><?php echo $rank++; ?></td>

                                <td>
                                    <?php
                                    echo "PRO-" . str_pad(
                                        $row['product_id'],
                                        4,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['product_name']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['category_name'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['unit_name'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['total_sold']); ?>
                                </td>

                                <td>
                                    <?php echo number_format($row['total_amount'] ?? 0); ?>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="7" class="no-data">
                                ບໍ່ມີຂໍ້ມູນ
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </main>

</div>

</body>
</html>