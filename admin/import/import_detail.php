<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: import.php");
    exit();
}

$import_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT i.*, o.order_id, e.first_name, e.last_name
    FROM tb_import i
    LEFT JOIN tb_order o ON i.order_id = o.order_id
    LEFT JOIN tb_employee e ON i.emp_id = e.emp_id
    WHERE i.import_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $import_id);
$stmt->execute();
$importResult = $stmt->get_result();

if ($importResult->num_rows === 0) {
    header("Location: import.php");
    exit();
}

$import = $importResult->fetch_assoc();

$detailStmt = $conn->prepare("
    SELECT id.*, p.product_name
    FROM tb_import_detail id
    LEFT JOIN tb_product p ON id.product_id = p.product_id
    WHERE id.import_id = ?
");
$detailStmt->bind_param("i", $import_id);
$detailStmt->execute();
$details = $detailStmt->get_result();

$total = 0;
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍລະອຽດນຳເຂົ້າ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/import.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="detail-card">
            <div class="form-header">
                <h1>ລາຍລະອຽດນຳເຂົ້າ</h1>
                <a href="import_invoice.php?id=<?php echo $import_id; ?>" class="btn-primary">
    ພິມໃບນຳເຂົ້າ
</a>
                <a href="import.php" class="btn-back">ກັບຄືນ</a>
            </div>

            <div class="info-box">
                <p><strong>ລະຫັດນຳເຂົ້າ:</strong> <?php echo "IMP-" . str_pad($import['import_id'], 4, "0", STR_PAD_LEFT); ?></p>
                <p><strong>ອ້າງອີງຄຳສັ່ງຊື້:</strong> <?php echo "ORD-" . str_pad($import['order_id'], 4, "0", STR_PAD_LEFT); ?></p>
                <p><strong>ພະນັກງານ:</strong> <?php echo htmlspecialchars($import['first_name'] . ' ' . $import['last_name']); ?></p>
                <p><strong>ວັນທີ:</strong> <?php echo htmlspecialchars($import['import_date']); ?></p>
            </div>

            <div class="table-card inside">
                <table>
                    <thead>
                        <tr>
                            <th>ສິນຄ້າ</th>
                            <th>ຈຳນວນ</th>
                            <th>ລາຄາທຶນ</th>
                            <th>ລວມ</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $details->fetch_assoc()): ?>
                            <?php
                                $subTotal = $row['qty'] * $row['cost_price'];
                                $total += $subTotal;
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['qty']); ?></td>
                                <td><?php echo number_format($row['cost_price']); ?></td>
                                <td><?php echo number_format($subTotal); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3">ລວມທັງໝົດ</th>
                            <th><?php echo number_format($total); ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

    </main>
</div>

</body>
</html>