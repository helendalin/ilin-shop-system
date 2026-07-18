<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: order.php");
    exit();
}

$order_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT o.*, s.supplier_name, s.phone_number, s.address,
           e.first_name AS emp_first, e.last_name AS emp_last
    FROM tb_order o
    LEFT JOIN tb_supplier s ON o.supplier_id = s.supplier_id
    LEFT JOIN tb_employee e ON o.emp_id = e.emp_id
    WHERE o.order_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$orderResult = $stmt->get_result();

if ($orderResult->num_rows === 0) {
    header("Location: order.php");
    exit();
}

$order = $orderResult->fetch_assoc();

$detailStmt = $conn->prepare("
    SELECT od.*, p.product_name
    FROM tb_order_detail od
    LEFT JOIN tb_product p ON od.product_id = p.product_id
    WHERE od.order_id = ?
");
$detailStmt->bind_param("i", $order_id);
$detailStmt->execute();
$details = $detailStmt->get_result();

$total = 0;
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ໃບສັ່ງຊື້ສິນຄ້າ</title>
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
            <h2>ໃບສັ່ງຊື້ສິນຄ້າ</h2>
            <p><?php echo "ORD-" . str_pad($order['order_id'], 4, "0", STR_PAD_LEFT); ?></p>
        </div>
    </div>

    <div class="info">
        <div class="info-box">
            <h3>ຂໍ້ມູນຜູ້ສະໜອງ</h3>
            <p><strong>ຊື່:</strong> <?php echo htmlspecialchars($order['supplier_name']); ?></p>
            <p><strong>ເບີໂທ:</strong> <?php echo htmlspecialchars($order['phone_number'] ?? '-'); ?></p>
            <p><strong>ທີ່ຢູ່:</strong> <?php echo htmlspecialchars($order['address'] ?? '-'); ?></p>
        </div>

        <div class="info-box">
            <h3>ຂໍ້ມູນການສັ່ງຊື້</h3>
            <p><strong>ລະຫັດ:</strong> <?php echo "ORD-" . str_pad($order['order_id'], 4, "0", STR_PAD_LEFT); ?></p>
            <p><strong>ພະນັກງານ:</strong> <?php echo htmlspecialchars($order['emp_first'] . ' ' . $order['emp_last']); ?></p>
            <p><strong>ວັນທີ:</strong> <?php echo htmlspecialchars($order['order_date']); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ລຳດັບ</th>
                <th>ສິນຄ້າ</th>
                <th>ຈຳນວນ</th>
                <th>ລາຄາ</th>
                <th>ລວມ</th>
            </tr>
        </thead>

        <tbody>
            <?php $i = 1; ?>
            <?php while ($row = $details->fetch_assoc()): ?>
                <?php
                    $subTotal = $row['qty'] * $row['price'];
                    $total += $subTotal;
                ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['qty']); ?></td>
                    <td><?php echo number_format($row['price']); ?></td>
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
    <button onclick="window.print()" class="btn btn-print">
        ພິມໃບສັ່ງຊື້
    </button>

    <a href="order_detail.php?id=<?php echo $order_id; ?>" class="btn btn-back">
        ກັບຄືນ
    </a>
</div>

</body>
</html>