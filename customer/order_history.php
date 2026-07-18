<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Customer must login first */
if (!isset($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Please login first");
    exit();
}

$customer_id = intval($_SESSION['customer_id']);

$stmt = $conn->prepare("
    SELECT 
        s.sale_id,
        s.total_amount,
        s.status,
        s.sale_date,
        s.payment_method,
        s.payment_status,
        s.delivery_method,
        s.delivery_fee,
        COUNT(sd.product_id) AS product_count,
        COALESCE(SUM(sd.qty), 0) AS total_qty
    FROM tb_sale s
    LEFT JOIN tb_sale_detail sd ON s.sale_id = sd.sale_id
    WHERE s.customer_id = ?
    GROUP BY 
        s.sale_id,
        s.total_amount,
        s.status,
        s.sale_date,
        s.payment_method,
        s.payment_status,
        s.delivery_method,
        s.delivery_fee
    ORDER BY s.sale_id DESC
");

$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
$totalOrders = 0;
$pendingOrders = 0;
$completedOrders = 0;
$totalSpent = 0;

while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
    $totalOrders++;
    $totalSpent += floatval($row['total_amount']);

    if (($row['status'] ?? '') === 'pending') {
        $pendingOrders++;
    }

    if (($row['status'] ?? '') === 'completed') {
        $completedOrders++;
    }
}

function statusText($status) {
    switch ($status) {
        case 'pending':
            return 'ລໍຖ້າດຳເນີນການ';
        case 'packing':
            return 'ກຳລັງແພັກ';
        case 'shipping':
            return 'ກຳລັງຈັດສົ່ງ';
        case 'completed':
            return 'ສຳເລັດ';
        case 'cancelled':
            return 'ຍົກເລີກ';
        default:
            return $status ?: '-';
    }
}

function statusClass($status) {
    switch ($status) {
        case 'pending':
            return 'status-pending';
        case 'packing':
            return 'status-packing';
        case 'shipping':
            return 'status-shipping';
        case 'completed':
            return 'status-completed';
        case 'cancelled':
            return 'status-cancelled';
        default:
            return 'status-default';
    }
}

function paymentText($method) {
    switch ($method) {
        case 'cod':
            return 'ຈ່າຍປາຍທາງ';
        case 'bank_transfer':
            return 'ໂອນທະນາຄານ';
        case 'qr_payment':
            return 'ຊຳລະຜ່ານ QR';
        default:
            return $method ?: '-';
    }
}

function paymentStatusText($status) {
    switch ($status) {
        case 'cod_pending':
            return 'ລໍຖ້າຈ່າຍປາຍທາງ';
        case 'pending':
            return 'ລໍຖ້າກວດສອບ';
        case 'paid':
            return 'ຊຳລະແລ້ວ';
        case 'verified':
            return 'ກວດສອບແລ້ວ';
        case 'rejected':
            return 'ຖືກປະຕິເສດ';
        default:
            return $status ?: '-';
    }
}

function deliveryText($method) {
    switch ($method) {
        case 'standard':
            return 'ຈັດສົ່ງປົກກະຕິ';
        case 'express':
            return 'ຈັດສົ່ງດ່ວນ';
        case 'pickup':
            return 'ຮັບສິນຄ້າທີ່ຮ້ານ';
        default:
            return $method ?: '-';
    }
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ປະຫວັດການສັ່ງຊື້ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/order_history.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="order-history-page">

    <section class="history-hero">
        <div>
            <span class="history-badge">
                <i class="fa-solid fa-clock-rotate-left"></i>
                ILIN SHOP
            </span>
            <h1>ປະຫວັດການສັ່ງຊື້</h1>
            <p>ກວດສອບອໍເດີ, ສະຖານະການສັ່ງຊື້ ແລະ ລາຍລະອຽດການຊຳລະເງິນຂອງທ່ານ</p>
        </div>

        <div class="history-hero-icon">
            <i class="fa-solid fa-receipt"></i>
        </div>
    </section>

    <section class="history-summary">
        <div class="summary-box">
            <div class="summary-icon blue">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div>
                <span>ອໍເດີທັງໝົດ</span>
                <strong><?= number_format($totalOrders); ?></strong>
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-icon orange">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div>
                <span>ລໍຖ້າດຳເນີນການ</span>
                <strong><?= number_format($pendingOrders); ?></strong>
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-icon green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span>ສຳເລັດ</span>
                <strong><?= number_format($completedOrders); ?></strong>
            </div>
        </div>

        <div class="summary-box">
            <div class="summary-icon purple">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div>
                <span>ຍອດຊື້ລວມ</span>
                <strong><?= number_format($totalSpent); ?> ₭</strong>
            </div>
        </div>
    </section>

    <section class="order-history-card">

        <div class="history-header">
            <div>
                <h2>ລາຍການອໍເດີ</h2>
                <p>ລາຍການສັ່ງຊື້ທັງໝົດຂອງບັນຊີນີ້</p>
            </div>

            <div class="history-actions">
                <a href="<?= BASE_URL ?>/customer/account.php" class="back-account-btn">
                    <i class="fa-regular fa-user"></i>
                    ກັບໄປບັນຊີ
                </a>

                <a href="<?= BASE_URL ?>/customer/products.php" class="shop-now-btn">
                    <i class="fa-solid fa-bag-shopping"></i>
                    ຊື້ສິນຄ້າ
                </a>
            </div>
        </div>

        <?php if (!empty($orders)): ?>

            <div class="history-table-wrap">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>ເລກອໍເດີ</th>
                            <th>ສິນຄ້າ</th>
                            <th>ຍອດລວມ</th>
                            <th>ວິທີຈັດສົ່ງ</th>
                            <th>ວິທີຊຳລະ</th>
                            <th>ສະຖານະ</th>
                            <th>ວັນທີ</th>
                            <th>ລາຍລະອຽດ</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <?php
                                $orderCode = 'SAL-' . str_pad($order['sale_id'], 5, "0", STR_PAD_LEFT);
                                $saleDate = !empty($order['sale_date']) ? date("d/m/Y H:i", strtotime($order['sale_date'])) : '-';
                            ?>
                            <tr>
                                <td data-label="ເລກອໍເດີ">
                                    <strong class="order-code"><?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8'); ?></strong>
                                </td>

                                <td data-label="ສິນຄ້າ">
                                    <span class="item-count">
                                        <?= number_format(intval($order['total_qty'])); ?> ຊິ້ນ /
                                        <?= number_format(intval($order['product_count'])); ?> ລາຍການ
                                    </span>
                                </td>

                                <td data-label="ຍອດລວມ">
                                    <strong class="order-total"><?= number_format(floatval($order['total_amount'])); ?> ₭</strong>
                                </td>

                                <td data-label="ວິທີຈັດສົ່ງ">
                                    <?= htmlspecialchars(deliveryText($order['delivery_method'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td data-label="ວິທີຊຳລະ">
                                    <div class="payment-info">
                                        <span><?= htmlspecialchars(paymentText($order['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
                                        <small><?= htmlspecialchars(paymentStatusText($order['payment_status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></small>
                                    </div>
                                </td>

                                <td data-label="ສະຖານະ">
                                    <span class="order-status <?= htmlspecialchars(statusClass($order['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= htmlspecialchars(statusText($order['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                                    </span>
                                </td>

                                <td data-label="ວັນທີ">
                                    <span class="order-date"><?= htmlspecialchars($saleDate, ENT_QUOTES, 'UTF-8'); ?></span>
                                </td>

                                <td data-label="ລາຍລະອຽດ">
                                    <a class="view-order-btn" href="<?= BASE_URL ?>/customer/order_detail.php?sale_id=<?= intval($order['sale_id']); ?>">
                                        <i class="fa-regular fa-eye"></i>
                                        ເບິ່ງ
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="empty-history">
                <div class="empty-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <h3>ຍັງບໍ່ມີອໍເດີ</h3>
                <p>ເລືອກສິນຄ້າສຳລັບແມ່ ແລະ ເດັກ ແລ້ວເລີ່ມສັ່ງຊື້ໄດ້ເລີຍ</p>
                <a href="<?= BASE_URL ?>/customer/products.php">
                    <i class="fa-solid fa-bag-shopping"></i>
                    ໄປເລືອກສິນຄ້າ
                </a>
            </div>

        <?php endif; ?>

    </section>

</main>

<?php include 'footer.php'; ?>

</body>
</html>