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
$sale_id = intval($_GET['sale_id'] ?? 0);

if ($sale_id <= 0) {
    header("Location: " . BASE_URL . "/customer/home.php");
    exit();
}

/* Get order only if it belongs to this customer */
$stmt = $conn->prepare("
    SELECT 
        sale_id,
        customer_id,
        sale_date,
        total_amount,
        status,
        delivery_method,
        delivery_fee,
        payment_method,
        payment_status,
        payment_slip
    FROM tb_sale
    WHERE sale_id = ? AND customer_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $sale_id, $customer_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: " . BASE_URL . "/customer/order_history.php");
    exit();
}

/* Count order items */
$itemStmt = $conn->prepare("
    SELECT 
        COUNT(*) AS product_count,
        COALESCE(SUM(qty), 0) AS total_qty
    FROM tb_sale_detail
    WHERE sale_id = ?
");
$itemStmt->bind_param("i", $sale_id);
$itemStmt->execute();
$itemSummary = $itemStmt->get_result()->fetch_assoc();

$product_count = intval($itemSummary['product_count'] ?? 0);
$total_qty = intval($itemSummary['total_qty'] ?? 0);

function statusText($status) {
    switch ($status) {
        case 'pending':
            return 'ລໍຖ້າດຳເນີນການ';
        case 'packing':
            return 'ກຳລັງແພັກສິນຄ້າ';
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
            return 'ໂອນເງິນຜ່ານທະນາຄານ';
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

$orderDate = !empty($order['sale_date']) ? date("d/m/Y H:i", strtotime($order['sale_date'])) : '-';
$orderCode = 'SAL-' . str_pad($order['sale_id'], 5, "0", STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ສັ່ງຊື້ສຳເລັດ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/order_success.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="success-page">

    <section class="success-card">

        <div class="success-top">
            <div class="success-icon">
                <i class="fa-solid fa-check"></i>
            </div>

            <span class="success-badge">
                <i class="fa-solid fa-shield-heart"></i>
                Order Confirmed
            </span>

            <h1>ສັ່ງຊື້ສຳເລັດ</h1>
            <p class="success-text">
                ຂອບໃຈທີ່ສັ່ງຊື້ກັບ ILIN SHOP. ທີມງານຈະກວດສອບ ແລະ ຕິດຕໍ່ກັບທ່ານໃນໄວໆນີ້.
            </p>
        </div>

        <div class="order-code-box">
            <span>ລະຫັດອໍເດີ</span>
            <strong><?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8'); ?></strong>
        </div>

        <div class="success-info">

            <div class="info-box">
                <div class="info-icon">
                    <i class="fa-regular fa-calendar"></i>
                </div>
                <div>
                    <span>ວັນທີສັ່ງຊື້</span>
                    <strong><?= htmlspecialchars($orderDate, ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            </div>

            <div class="info-box">
                <div class="info-icon">
                    <i class="fa-solid fa-box"></i>
                </div>
                <div>
                    <span>ຈຳນວນສິນຄ້າ</span>
                    <strong><?= number_format($total_qty); ?> ຊິ້ນ / <?= number_format($product_count); ?> ລາຍການ</strong>
                </div>
            </div>

            <div class="info-box">
                <div class="info-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div>
                    <span>ວິທີຈັດສົ່ງ</span>
                    <strong><?= htmlspecialchars(deliveryText($order['delivery_method'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            </div>

            <div class="info-box">
                <div class="info-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <span>ວິທີຊຳລະ</span>
                    <strong><?= htmlspecialchars(paymentText($order['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            </div>

            <div class="info-box">
                <div class="info-icon">
                    <i class="fa-solid fa-clipboard-check"></i>
                </div>
                <div>
                    <span>ສະຖານະອໍເດີ</span>
                    <strong class="status-pill <?= htmlspecialchars(statusClass($order['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                        <?= htmlspecialchars(statusText($order['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    </strong>
                </div>
            </div>

            <div class="info-box">
                <div class="info-icon">
                    <i class="fa-solid fa-money-check-dollar"></i>
                </div>
                <div>
                    <span>ສະຖານະຊຳລະ</span>
                    <strong><?= htmlspecialchars(paymentStatusText($order['payment_status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                </div>
            </div>

            <div class="total-row">
                <div>
                    <span>ຄ່າຈັດສົ່ງ</span>
                    <strong><?= number_format(floatval($order['delivery_fee'] ?? 0)); ?> ₭</strong>
                </div>

                <div>
                    <span>ລວມເງິນທັງໝົດ</span>
                    <strong><?= number_format(floatval($order['total_amount'])); ?> ₭</strong>
                </div>
            </div>

        </div>

        <div class="success-note">
            <i class="fa-solid fa-circle-info"></i>
            <p>
                ທ່ານສາມາດເບິ່ງລາຍລະອຽດອໍເດີ ຫຼື ກວດສອບປະຫວັດການສັ່ງຊື້ໄດ້ໃນໜ້າບັນຊີຂອງທ່ານ.
            </p>
        </div>

        <div class="success-actions">
            <a href="<?= BASE_URL ?>/customer/order_detail.php?sale_id=<?= intval($order['sale_id']); ?>" class="primary-btn">
                <i class="fa-solid fa-receipt"></i>
                ເບິ່ງລາຍລະອຽດອໍເດີ
            </a>

            <a href="<?= BASE_URL ?>/customer/order_history.php" class="secondary-btn">
                <i class="fa-solid fa-clock-rotate-left"></i>
                ປະຫວັດການສັ່ງຊື້
            </a>

            <a href="<?= BASE_URL ?>/customer/products.php" class="light-btn">
                <i class="fa-solid fa-bag-shopping"></i>
                ຊື້ສິນຄ້າຕໍ່
            </a>
        </div>

    </section>

</main>

<?php include 'footer.php'; ?>

</body>
</html>