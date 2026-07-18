<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$result = $conn->query("
    SELECT 
        o.order_id,
        o.order_date,
        s.supplier_name,
        e.first_name,
        e.last_name,
        SUM(od.qty * od.price) AS total_amount
    FROM tb_order o
    LEFT JOIN tb_supplier s ON o.supplier_id = s.supplier_id
    LEFT JOIN tb_employee e ON o.emp_id = e.emp_id
    LEFT JOIN tb_order_detail od ON o.order_id = od.order_id
    GROUP BY o.order_id
    ORDER BY o.order_id DESC
");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານການສັ່ງຊື້</title>
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
                <h1>ລາຍງານການສັ່ງຊື້ສິນຄ້າ</h1>
                <p>
                    ຈຳນວນຄຳສັ່ງຊື້ທັງໝົດ:
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
                        <th>ລະຫັດ</th>
                        <th>ຜູ້ສະໜອງ</th>
                        <th>ພະນັກງານ</th>
                        <th>ວັນທີ</th>
                        <th>ມູນຄ່າລວມ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo "ORD-" . str_pad($row['order_id'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['order_date']); ?></td>
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