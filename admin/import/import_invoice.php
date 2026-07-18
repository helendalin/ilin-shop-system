<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: import.php");
    exit();
}

$import_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT i.*, o.order_id,
           e.first_name AS emp_first,
           e.last_name AS emp_last
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
    <title>ໃບນຳເຂົ້າສິນຄ້າ</title>
    <link rel="stylesheet" href="../../assets/css/invoice.css">
</head>

<body>

<div class="invoice">

    <div class="invoice-header">
        <div>
            <div class="shop-name">ILIN SHOP</div>
            <p>ລະບົບຈັດການຮ້ານ</p>
        </div>

        <div class="invoice-title">
            <h2>ໃບນຳເຂົ້າສິນຄ້າ</h2>
            <p><?php echo "IMP-" . str_pad($import['import_id'], 4, "0", STR_PAD_LEFT); ?></p>
        </div>
    </div>

    <div class="info">
        <div class="info-box">
            <h3>ຂໍ້ມູນການນຳເຂົ້າ</h3>
            <p><strong>ລະຫັດນຳເຂົ້າ:</strong> <?php echo "IMP-" . str_pad($import['import_id'], 4, "0", STR_PAD_LEFT); ?></p>
            <p><strong>ອ້າງອີງສັ່ງຊື້:</strong> <?php echo "ORD-" . str_pad($import['order_id'], 4, "0", STR_PAD_LEFT); ?></p>
            <p><strong>ວັນທີ:</strong> <?php echo htmlspecialchars($import['import_date']); ?></p>
        </div>

        <div class="info-box">
            <h3>ຜູ້ດຳເນີນການ</h3>
            <p><strong>ພະນັກງານ:</strong> <?php echo htmlspecialchars($import['emp_first'] . ' ' . $import['emp_last']); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ລຳດັບ</th>
                <th>ສິນຄ້າ</th>
                <th>ຈຳນວນ</th>
                <th>ລາຄາທຶນ</th>
                <th>ລວມ</th>
            </tr>
        </thead>

        <tbody>
            <?php $i = 1; ?>
            <?php while ($row = $details->fetch_assoc()): ?>
                <?php
                    $subTotal = $row['qty'] * $row['cost_price'];
                    $total += $subTotal;
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['qty']); ?></td>
                    <td><?php echo number_format($row['cost_price']); ?></td>
                    <td><?php echo number_format($subTotal); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>

        <tfoot>
            <tr>
                <th colspan="4">ລວມທັງໝົດ</th>
                <th><?php echo number_format($total); ?></th>
            </tr>
        </tfoot>
    </table>

</div>

<div class="actions">
    <button onclick="window.print()" class="btn btn-print">ພິມໃບນຳເຂົ້າ</button>
    <a href="import_detail.php?id=<?php echo $import_id; ?>" class="btn btn-back">ກັບຄືນ</a>
</div>

</body>
</html>