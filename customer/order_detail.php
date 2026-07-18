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
    header("Location: " . BASE_URL . "/customer/order_history.php");
    exit();
}

/* Get order only if it belongs to this customer */
$orderStmt = $conn->prepare("
    SELECT 
        sale_id,
        customer_id,
        total_amount,
        status,
        sale_date,
        delivery_method,
        delivery_fee,
        payment_method,
        payment_status,
        payment_slip
    FROM tb_sale
    WHERE sale_id = ? AND customer_id = ?
    LIMIT 1
");
$orderStmt->bind_param("ii", $sale_id, $customer_id);
$orderStmt->execute();
$order = $orderStmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: " . BASE_URL . "/customer/order_history.php");
    exit();
}

/* Get order items */
$itemStmt = $conn->prepare("
    SELECT 
        sd.product_id,
        sd.qty,
        sd.price,
        sd.subtotal,
        p.product_name,
        p.image
    FROM tb_sale_detail sd
    LEFT JOIN tb_product p ON sd.product_id = p.product_id
    WHERE sd.sale_id = ?
    ORDER BY sd.product_id ASC
");
$itemStmt->bind_param("i", $sale_id);
$itemStmt->execute();
$itemResult = $itemStmt->get_result();

$items = [];
$productTotal = 0;
$totalQty = 0;

while ($item = $itemResult->fetch_assoc()) {
    $items[] = $item;
    $productTotal += floatval($item['subtotal']);
    $totalQty += intval($item['qty']);
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

$orderCode = 'SAL-' . str_pad($order['sale_id'], 5, "0", STR_PAD_LEFT);
$orderDate = !empty($order['sale_date']) ? date("d/m/Y H:i", strtotime($order['sale_date'])) : '-';
$deliveryFee = floatval($order['delivery_fee'] ?? 0);
$totalAmount = floatval($order['total_amount'] ?? 0);
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ລາຍລະອຽດອໍເດີ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/order_history.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="order-detail-page">

    <section class="detail-hero">
        <div>
            <span class="history-badge">
                <i class="fa-solid fa-receipt"></i>
                Order Detail
            </span>
            <h1>ລາຍລະອຽດອໍເດີ</h1>
            <p>ກວດສອບລາຍການສິນຄ້າ, ການຈັດສົ່ງ ແລະ ການຊຳລະເງິນ</p>
        </div>

        <div class="history-hero-icon">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </section>

    <section class="detail-header-card">
        <div>
            <span>ລະຫັດອໍເດີ</span>
            <h2><?= htmlspecialchars($orderCode, ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?= htmlspecialchars($orderDate, ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <div class="detail-header-actions">
            <a href="<?= BASE_URL ?>/customer/order_history.php" class="back-account-btn">
                <i class="fa-solid fa-arrow-left"></i>
                ກັບໄປປະຫວັດ
            </a>

            <a href="<?= BASE_URL ?>/customer/products.php" class="shop-now-btn">
                <i class="fa-solid fa-bag-shopping"></i>
                ຊື້ສິນຄ້າຕໍ່
            </a>
        </div>
    </section>

    <section class="detail-layout">

        <div class="detail-left">

            <div class="detail-card">
                <div class="detail-card-title">
                    <div class="detail-title-icon">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div>
                        <h3>ຂໍ້ມູນອໍເດີ</h3>
                        <p>ສະຖານະ ແລະ ຂໍ້ມູນການຈັດສົ່ງ</p>
                    </div>
                </div>

                <div class="detail-info-grid">
                    <div class="detail-info-box">
                        <span>ສະຖານະອໍເດີ</span>
                        <strong class="order-status <?= htmlspecialchars(statusClass($order['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                            <?= htmlspecialchars(statusText($order['status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </strong>
                    </div>

                    <div class="detail-info-box">
                        <span>ວິທີຈັດສົ່ງ</span>
                        <strong><?= htmlspecialchars(deliveryText($order['delivery_method'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>

                    <div class="detail-info-box">
                        <span>ວິທີຊຳລະ</span>
                        <strong><?= htmlspecialchars(paymentText($order['payment_method'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>

                    <div class="detail-info-box">
                        <span>ສະຖານະຊຳລະ</span>
                        <strong><?= htmlspecialchars(paymentStatusText($order['payment_status'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
                    </div>
                </div>
            </div>

            <div class="detail-card">
                <div class="detail-card-title">
                    <div class="detail-title-icon">
                        <i class="fa-solid fa-bag-shopping"></i>
                    </div>
                    <div>
                        <h3>ລາຍການສິນຄ້າ</h3>
                        <p><?= number_format($totalQty); ?> ຊິ້ນ / <?= number_format(count($items)); ?> ລາຍການ</p>
                    </div>
                </div>

                <?php if (!empty($items)): ?>
                    <div class="detail-table-wrap">
                        <table class="detail-order-table">
                            <thead>
                                <tr>
                                    <th>ສິນຄ້າ</th>
                                    <th>ຈຳນວນ</th>
                                    <th>ລາຄາ</th>
                                    <th>ລວມ</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td data-label="ສິນຄ້າ">
                                            <div class="detail-product">
                                                <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= intval($item['product_id']); ?>" class="detail-product-img">
                                                    <?php if (!empty($item['image'])): ?>
                                                        <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($item['product_name'] ?? 'Product', ENT_QUOTES, 'UTF-8'); ?>">
                                                    <?php else: ?>
                                                        <img src="<?= BASE_URL ?>/assets/images/no-product.png" alt="No Product">
                                                    <?php endif; ?>
                                                </a>

                                                <div>
                                                    <h4>
                                                        <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= intval($item['product_id']); ?>">
                                                            <?= htmlspecialchars($item['product_name'] ?? 'ສິນຄ້າ', ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    </h4>
                                                    <span>ສິນຄ້າສຳລັບແມ່ ແລະ ເດັກ</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td data-label="ຈຳນວນ">
                                            <span class="item-count"><?= intval($item['qty']); ?> ຊິ້ນ</span>
                                        </td>

                                        <td data-label="ລາຄາ">
                                            <strong><?= number_format(floatval($item['price'])); ?> ₭</strong>
                                        </td>

                                        <td data-label="ລວມ">
                                            <strong class="order-total"><?= number_format(floatval($item['subtotal'])); ?> ₭</strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-history">
                        <div class="empty-icon">
                            <i class="fa-solid fa-box-open"></i>
                        </div>
                        <h3>ບໍ່ພົບລາຍການສິນຄ້າ</h3>
                        <p>ອໍເດີນີ້ບໍ່ມີລາຍການສິນຄ້າ</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <aside class="detail-card detail-summary-card">
            <div class="detail-card-title">
                <div class="detail-title-icon">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <h3>ສະຫຼຸບອໍເດີ</h3>
                    <p>ລາຍລະອຽດຍອດເງິນ</p>
                </div>
            </div>

            <div class="detail-summary-row">
                <span>ລວມສິນຄ້າ</span>
                <strong><?= number_format($productTotal); ?> ₭</strong>
            </div>

            <div class="detail-summary-row">
                <span>ຄ່າຈັດສົ່ງ</span>
                <strong><?= number_format($deliveryFee); ?> ₭</strong>
            </div>

            <div class="detail-summary-row">
                <span>ຈຳນວນສິນຄ້າ</span>
                <strong><?= number_format($totalQty); ?> ຊິ້ນ</strong>
            </div>

            <div class="detail-summary-total">
                <span>ລວມທັງໝົດ</span>
                <strong><?= number_format($totalAmount); ?> ₭</strong>
            </div>

            <?php if (!empty($order['payment_slip'])): ?>
                <div class="payment-slip-box">
                    <span>ສະລິບໂອນເງິນ</span>
                    <a href="<?= BASE_URL ?>/assets/images/payments/<?= htmlspecialchars($order['payment_slip'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                        <i class="fa-regular fa-image"></i>
                        ເບິ່ງສະລິບ
                    </a>
                </div>
            <?php endif; ?>

            <div class="detail-note">
                <i class="fa-solid fa-shield-heart"></i>
                <p>ຖ້າມີບັນຫາກ່ຽວກັບອໍເດີ ກະລຸນາຕິດຕໍ່ ILIN SHOP ເພື່ອກວດສອບ.</p>
            </div>
        </aside>

    </section>

</main>

<?php include 'footer.php'; ?>

</body>
</html>