<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$result = $conn->query("
    SELECT 
        i.import_id,
        i.import_date,
        i.order_id,
        e.first_name,
        e.last_name,
        SUM(id.qty * id.cost_price) AS total_amount
    FROM tb_import i
    LEFT JOIN tb_employee e ON i.emp_id = e.emp_id
    LEFT JOIN tb_import_detail id ON i.import_id = id.import_id
    GROUP BY i.import_id
    ORDER BY i.import_id DESC
");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານນຳເຂົ້າ</title>
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
                <h1>ລາຍງານຂໍ້ມູນນຳເຂົ້າສິນຄ້າ</h1>
                <p>
                    ຈຳນວນການນຳເຂົ້າທັງໝົດ:
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
                        <th>ລະຫັດນຳເຂົ້າ</th>
                        <th>ອ້າງອີງຄຳສັ່ງຊື້</th>
                        <th>ພະນັກງານ</th>
                        <th>ວັນທີ</th>
                        <th>ມູນຄ່າລວມ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo "IMP-" . str_pad($row['import_id'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo "ORD-" . str_pad($row['order_id'], 4, "0", STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['import_date']); ?></td>
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