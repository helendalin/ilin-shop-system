<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: sale.php");
    exit();
}

$sale_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT 
        s.*,
        c.first_name AS customer_first, 
        c.last_name AS customer_last,
        c.phone_number,
        c.email,
        c.address,
        e.first_name AS emp_first, 
        e.last_name AS emp_last,
        ap.first_name AS approved_first,
        ap.last_name AS approved_last
    FROM tb_sale s
    LEFT JOIN tb_customer c ON s.customer_id = c.customer_id
    LEFT JOIN tb_employee e ON s.emp_id = e.emp_id
    LEFT JOIN tb_employee ap ON s.approved_by = ap.emp_id
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
    <title>ລາຍລະອຽດອໍເດີ</title>

    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/sale.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="detail-card">

            <div class="form-header">
                <h1>ລາຍລະອຽດອໍເດີ</h1>

                <div style="display:flex; gap:10px;">
                    <a href="sale_invoice.php?id=<?= $sale_id; ?>" class="btn-primary">
                        ພິມໃບບິນ
                    </a>

                    <a href="sale.php" class="btn-back">
                        ກັບຄືນ
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['error']) && $_GET['error'] === 'cannot_cancel_paid_order'): ?>
                <div style="background:#ffe8e8;color:#d80000;padding:14px 18px;border-radius:14px;margin-bottom:18px;font-weight:800;">
                    ອໍເດີນີ້ຊຳລະເງິນແລ້ວ ບໍ່ສາມາດຍົກເລີກໄດ້
                </div>
            <?php endif; ?>

            <div class="info-box">
                <p><strong>ລະຫັດ:</strong> <?= "SAL-" . str_pad($sale['sale_id'], 4, "0", STR_PAD_LEFT); ?></p>
                <p><strong>ລູກຄ້າ:</strong> <?= htmlspecialchars(trim(($sale['customer_first'] ?? '') . ' ' . ($sale['customer_last'] ?? '')) ?: '-'); ?></p>
                <p><strong>ເບີໂທ:</strong> <?= htmlspecialchars($sale['phone_number'] ?? '-'); ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($sale['email'] ?? '-'); ?></p>
                <p><strong>ທີ່ຢູ່:</strong> <?= nl2br(htmlspecialchars($sale['address'] ?? '-')); ?></p>
                <p><strong>ພະນັກງານ:</strong> <?= htmlspecialchars(trim(($sale['emp_first'] ?? '') . ' ' . ($sale['emp_last'] ?? '')) ?: '-'); ?></p>
                <p><strong>ວັນທີ:</strong> <?= htmlspecialchars($sale['sale_date']); ?></p>
            </div>

            <div class="info-box">
                <h3>ຂໍ້ມູນຈັດສົ່ງ ແລະ ຊຳລະເງິນ</h3>

                <p><strong>ວິທີຈັດສົ່ງ:</strong> <?= htmlspecialchars($sale['delivery_method'] ?? '-'); ?></p>
                <p><strong>ຄ່າຈັດສົ່ງ:</strong> <?= number_format($sale['delivery_fee'] ?? 0); ?> ₭</p>
                <p><strong>ວິທີຊຳລະ:</strong> <?= htmlspecialchars($sale['payment_method'] ?? '-'); ?></p>
                <p><strong>ສະຖານະເງິນ:</strong> <?= htmlspecialchars($sale['payment_status'] ?? '-'); ?></p>
                <p><strong>ສະຖານະອໍເດີ:</strong> <?= htmlspecialchars($sale['status'] ?? '-'); ?></p>
                <p><strong>ຜູ້ຢືນຢັນຊຳລະ:</strong> <?= htmlspecialchars(trim(($sale['approved_first'] ?? '') . ' ' . ($sale['approved_last'] ?? '')) ?: '-'); ?></p>
                <p><strong>ວັນທີຢືນຢັນ:</strong> <?= htmlspecialchars($sale['approved_at'] ?? '-'); ?></p>
                <p><strong>Tracking No:</strong> <?= htmlspecialchars($sale['tracking_number'] ?? '-'); ?></p>

                <?php if (!empty($sale['payment_slip'])): ?>
                    <p><strong>ສະລິບໂອນເງິນ:</strong></p>
                    <a href="<?= BASE_URL ?>/assets/images/payments/<?= htmlspecialchars($sale['payment_slip']); ?>" target="_blank">
                        <img
                            src="<?= BASE_URL ?>/assets/images/payments/<?= htmlspecialchars($sale['payment_slip']); ?>"
                            alt="Payment Slip"
                            style="max-width:280px;border-radius:14px;border:1px solid #ddd;"
                        >
                    </a>
                <?php else: ?>
                    <p><strong>ສະລິບໂອນເງິນ:</strong> ບໍ່ມີ</p>
                <?php endif; ?>

                <hr style="margin:20px 0;">

                <h3>ຈັດການອໍເດີ</h3>

                <?php if (($sale['status'] ?? '') === 'cancelled'): ?>
                    <p style="color:#d80000;font-weight:800;">ອໍເດີນີ້ຖືກຍົກເລີກແລ້ວ</p>
                <?php else: ?>

                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:15px;">

                        <?php if (($sale['payment_status'] ?? '') !== 'paid'): ?>
                            <a class="btn-primary"
                               href="../../actions/sale/update_sale_status.php?id=<?= $sale['sale_id']; ?>&status=paid">
                                ຢືນຢັນຊຳລະ
                            </a>
                        <?php endif; ?>

                        <a class="btn-primary"
                           href="../../actions/sale/update_sale_status.php?id=<?= $sale['sale_id']; ?>&status=packing">
                            ກຳລັງແພັກ
                        </a>

                        <a class="btn-primary"
                           href="../../actions/sale/update_sale_status.php?id=<?= $sale['sale_id']; ?>&status=shipping">
                            ກຳລັງຈັດສົ່ງ
                        </a>

                        <a class="btn-primary"
                           href="../../actions/sale/update_sale_status.php?id=<?= $sale['sale_id']; ?>&status=completed">
                            ສຳເລັດ
                        </a>

                        <?php if (($sale['payment_status'] ?? '') !== 'paid'): ?>
                            <a class="btn-delete"
                               href="../../actions/sale/update_sale_status.php?id=<?= $sale['sale_id']; ?>&status=cancelled"
                               onclick="return confirm('ຢືນຢັນຍົກເລີກອໍເດີນີ້ບໍ?');">
                                ຍົກເລີກ
                            </a>
                        <?php endif; ?>

                    </div>

                <?php endif; ?>
            </div>

            <div class="table-card inside">
                <table>
                    <thead>
                        <tr>
                            <th>ສິນຄ້າ</th>
                            <th>ຈຳນວນ</th>
                            <th>ລາຄາ</th>
                            <th>ລວມ</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($row = $details->fetch_assoc()): ?>
                            <?php
                            $subTotal = $row['qty'] * $row['price'];
                            $total += $subTotal;
                            ?>

                            <tr>
                                <td><?= htmlspecialchars($row['product_name']); ?></td>
                                <td><?= intval($row['qty']); ?></td>
                                <td><?= number_format($row['price']); ?> ₭</td>
                                <td><?= number_format($subTotal); ?> ₭</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3">ລວມສິນຄ້າ</th>
                            <th><?= number_format($total); ?> ₭</th>
                        </tr>
                        <tr>
                            <th colspan="3">ຄ່າຈັດສົ່ງ</th>
                            <th><?= number_format($sale['delivery_fee'] ?? 0); ?> ₭</th>
                        </tr>
                        <tr>
                            <th colspan="3">ລວມທັງໝົດ</th>
                            <th><?= number_format($sale['total_amount']); ?> ₭</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

        </div>

    </main>
</div>

</body>
</html>