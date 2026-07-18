<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$result = $conn->query("
    SELECT 
        s.sale_id,
        s.sale_date,
        s.total_amount,
        c.first_name AS customer_first,
        c.last_name AS customer_last,
        e.first_name AS emp_first,
        e.last_name AS emp_last
    FROM tb_sale s
    LEFT JOIN tb_customer c ON s.customer_id = c.customer_id
    LEFT JOIN tb_employee e ON s.emp_id = e.emp_id
    ORDER BY s.sale_id DESC
");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານການຂາຍ</title>
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
                <h1>ລາຍງານການຂາຍສິນຄ້າ</h1>
                <p>
                    ຈຳນວນການຂາຍທັງໝົດ:
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
                        <th>ລະຫັດການຂາຍ</th>
                        <th>ລູກຄ້າ</th>
                        <th>ພະນັກງານ</th>
                        <th>ວັນທີ</th>
                        <th>ລວມເງິນ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo "SAL-" . str_pad($row['sale_id'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_first'] . ' ' . $row['customer_last']); ?></td>
                                <td><?php echo htmlspecialchars($row['emp_first'] . ' ' . $row['emp_last']); ?></td>
                                <td><?php echo htmlspecialchars($row['sale_date']); ?></td>
                                <td><?php echo number_format($row['total_amount'] ?? 0); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="no-data">ບໍ່ມີຂໍ້ມູນ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

</body>
</html>