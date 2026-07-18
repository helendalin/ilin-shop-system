<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$result = $conn->query("
    SELECT 
        i.import_id,
        i.import_date,
        SUM(id.qty * id.cost_price) AS total_expense
    FROM tb_import i
    LEFT JOIN tb_import_detail id ON i.import_id = id.import_id
    GROUP BY i.import_id
    ORDER BY i.import_date DESC
");

$totalExpense = 0;
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານລາຍຈ່າຍ</title>

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
                <h1>ລາຍງານລາຍຈ່າຍ</h1>

                <p>
                    ສະຫຼຸບລາຍຈ່າຍຈາກການນຳເຂົ້າສິນຄ້າ
                </p>
            </div>

            <button
                onclick="window.print()"
                class="btn-print"
            >
                ພິມລາຍງານ
            </button>

        </div>

        <div class="report-table-card">

            <table>

                <thead>
                    <tr>
                        <th>ລະຫັດນຳເຂົ້າ</th>
                        <th>ວັນທີ</th>
                        <th>ລາຍຈ່າຍ</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <?php
                            $totalExpense += $row['total_expense'];
                            ?>

                            <tr>

                                <td>
                                    <?php
                                    echo "IMP-" . str_pad(
                                        $row['import_id'],
                                        4,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['import_date']); ?>
                                </td>

                                <td>
                                    <?php
                                    echo number_format(
                                        $row['total_expense']
                                    );
                                    ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="3" class="no-data">
                                ບໍ່ມີຂໍ້ມູນ
                            </td>
                        </tr>

                    <?php endif; ?>

                </tbody>

                <tfoot>

                    <tr>
                        <th colspan="2">
                            ລວມລາຍຈ່າຍທັງໝົດ
                        </th>

                        <th>
                            <?php echo number_format($totalExpense); ?>
                        </th>
                    </tr>

                </tfoot>

            </table>

        </div>

    </main>

</div>

</body>
</html>