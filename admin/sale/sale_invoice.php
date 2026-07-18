<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: sale.php");
    exit();
}

$sale_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT s.*, 
           c.first_name AS customer_first,
           c.last_name AS customer_last,
           c.phone_number,
           c.address,
           e.first_name AS emp_first,
           e.last_name AS emp_last
    FROM tb_sale s
    LEFT JOIN tb_customer c ON s.customer_id = c.customer_id
    LEFT JOIN tb_employee e ON s.emp_id = e.emp_id
    WHERE s.sale_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $sale_id);
$stmt->execute();
$saleResult = $stmt->get_result();

if ($saleResult->num_rows === 0) {
    header("Location: sale.php");
    exit();
}

$sale = $saleResult->fetch_assoc();

$detailStmt = $conn->prepare("
    SELECT sd.*, p.product_name
    FROM tb_sale_detail sd
    LEFT JOIN tb_product p ON sd.product_id = p.product_id
    WHERE sd.sale_id = ?
");

$detailStmt->bind_param("i", $sale_id);
$detailStmt->execute();
$details = $detailStmt->get_result();

$total = 0;
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ໃບບິນການຂາຍ</title>

    <link rel="stylesheet" href="../../assets/css/invoice.css">
</head>

<body>

<div class="invoice">

    <div class="invoice-header">

        <div>
            <div class="shop-name">
                ILIN SHOP
            </div>

            <p>
                ລະບົບຈັດການຮ້ານ
            </p>
        </div>

        <div class="invoice-title">

            <h2>
                ໃບບິນການຂາຍ
            </h2>

            <p>
                <?php
                echo "SAL-" . str_pad(
                    $sale['sale_id'],
                    4,
                    "0",
                    STR_PAD_LEFT
                );
                ?>
            </p>

        </div>

    </div>

    <div class="info">

        <div class="info-box">

            <h3>ຂໍ້ມູນລູກຄ້າ</h3>

            <p>
                <strong>ຊື່:</strong>

                <?php
                echo htmlspecialchars(
                    $sale['customer_first'] . ' ' .
                    $sale['customer_last']
                );
                ?>
            </p>

            <p>
                <strong>ເບີໂທ:</strong>

                <?php
                echo htmlspecialchars(
                    $sale['phone_number'] ?? '-'
                );
                ?>
            </p>

            <p>
                <strong>ທີ່ຢູ່:</strong>

                <?php
                echo htmlspecialchars(
                    $sale['address'] ?? '-'
                );
                ?>
            </p>

        </div>

        <div class="info-box">

            <h3>ຂໍ້ມູນການຂາຍ</h3>

            <p>
                <strong>ພະນັກງານ:</strong>

                <?php
                echo htmlspecialchars(
                    $sale['emp_first'] . ' ' .
                    $sale['emp_last']
                );
                ?>
            </p>

            <p>
                <strong>ວັນທີ:</strong>

                <?php
                echo htmlspecialchars(
                    $sale['sale_date']
                );
                ?>
            </p>

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
                $subTotal =
                    $row['qty'] *
                    $row['price'];

                $total += $subTotal;
                ?>

                <tr>

                    <td>
                        <?php echo $i++; ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $row['product_name']
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo htmlspecialchars(
                            $row['qty']
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $row['price']
                        );
                        ?>
                    </td>

                    <td>
                        <?php
                        echo number_format(
                            $subTotal
                        );
                        ?>
                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>

        <tfoot>

            <tr>

                <th colspan="4">
                    ລວມທັງໝົດ
                </th>

                <th>
                    <?php
                    echo number_format($total);
                    ?>
                </th>

            </tr>

        </tfoot>

    </table>

</div>

<div class="actions">

    <button
        onclick="window.print()"
        class="btn btn-print"
    >
        ພິມໃບບິນ
    </button>

    <a
        href="sale_detail.php?id=<?php echo $sale_id; ?>"
        class="btn btn-back"
    >
        ກັບຄືນ
    </a>

</div>

</body>
</html>