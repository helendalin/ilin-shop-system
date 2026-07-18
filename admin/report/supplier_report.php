<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$result = $conn->query("
    SELECT *
    FROM tb_supplier
    ORDER BY supplier_id DESC
");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍງານຜູ້ສະໜອງ</title>

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
                <h1>ລາຍງານຂໍ້ມູນຜູ້ສະໜອງ</h1>

                <p>
                    ຈຳນວນຜູ້ສະໜອງທັງໝົດ:
                    <strong>
                        <?php echo $result->num_rows; ?>
                    </strong>
                    ລາຍການ
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
                        <th>ລະຫັດ</th>
                        <th>ຊື່ຜູ້ສະໜອງ</th>
                        <th>ເບີໂທ</th>
                        <th>ທີ່ຢູ່</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($result && $result->num_rows > 0): ?>

                        <?php while ($row = $result->fetch_assoc()): ?>

                            <tr>

                                <td>
                                    <?php
                                    echo "SUP-" . str_pad(
                                        $row['supplier_id'],
                                        4,
                                        "0",
                                        STR_PAD_LEFT
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['supplier_name']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['phone_number']); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($row['address']); ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" class="no-data">
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