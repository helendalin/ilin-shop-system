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

$cart = $_SESSION['cart'] ?? [];

if (empty($cart) || !is_array($cart)) {
    header("Location: " . BASE_URL . "/customer/cart.php");
    exit();
}

/* Save delivery method from checkout_delivery.php */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowedDeliveryMethods = ['standard', 'express', 'pickup'];
    $deliveryMethod = $_POST['delivery_method'] ?? 'standard';

    if (!in_array($deliveryMethod, $allowedDeliveryMethods, true)) {
        $deliveryMethod = 'standard';
    }

    $_SESSION['checkout_delivery'] = [
        'delivery_method' => $deliveryMethod
    ];
}

/* Customer cannot access payment page without shipping and delivery data */
if (empty($_SESSION['checkout_shipping']) || empty($_SESSION['checkout_delivery'])) {
    header("Location: " . BASE_URL . "/customer/checkout.php");
    exit();
}

$shipping = $_SESSION['checkout_shipping'];
$deliveryMethod = $_SESSION['checkout_delivery']['delivery_method'] ?? 'standard';

$deliveryMethods = [
    'standard' => [
        'title' => 'ຈັດສົ່ງປົກກະຕິ',
        'description' => 'ໄດ້ຮັບສິນຄ້າປະມານ 1 - 3 ມື້',
        'fee' => 20000
    ],
    'express' => [
        'title' => 'ຈັດສົ່ງດ່ວນ',
        'description' => 'ໄດ້ຮັບສິນຄ້າພາຍໃນ 24 ຊົ່ວໂມງ',
        'fee' => 35000
    ],
    'pickup' => [
        'title' => 'ຮັບສິນຄ້າທີ່ຮ້ານ',
        'description' => 'ລູກຄ້າສາມາດມາຮັບສິນຄ້າໄດ້ທີ່ຮ້ານ',
        'fee' => 0
    ]
];

if (!isset($deliveryMethods[$deliveryMethod])) {
    $deliveryMethod = 'standard';
    $_SESSION['checkout_delivery']['delivery_method'] = $deliveryMethod;
}

$deliveryTitle = $deliveryMethods[$deliveryMethod]['title'];
$deliveryDescription = $deliveryMethods[$deliveryMethod]['description'];
$deliveryFee = $deliveryMethods[$deliveryMethod]['fee'];

$total = 0;
$totalItems = 0;
$cartItems = [];

foreach ($cart as $item) {
    $productId = isset($item['product_id']) ? intval($item['product_id']) : 0;
    $productName = isset($item['product_name']) ? trim($item['product_name']) : 'ສິນຄ້າ';
    $price = isset($item['price']) ? floatval($item['price']) : 0;
    $qty = isset($item['qty']) ? intval($item['qty']) : 0;
    $image = isset($item['image']) ? trim($item['image']) : '';

    if ($productId <= 0 || $qty <= 0 || $price < 0) {
        continue;
    }

    $subTotal = $price * $qty;
    $total += $subTotal;
    $totalItems += $qty;

    $cartItems[] = [
        'product_id' => $productId,
        'product_name' => $productName,
        'price' => $price,
        'qty' => $qty,
        'image' => $image,
        'sub_total' => $subTotal
    ];
}

if (empty($cartItems)) {
    header("Location: " . BASE_URL . "/customer/cart.php");
    exit();
}

$grandTotal = $total + $deliveryFee;
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຊຳລະເງິນ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/checkout.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="checkout-main">

    <section class="checkout-hero">
        <div>
            <span class="checkout-badge">
                <i class="fa-solid fa-lock"></i>
                Secure Payment
            </span>
            <h1>ຊຳລະເງິນ ແລະ ຢືນຢັນອໍເດີ</h1>
            <p>ເລືອກວິທີຊຳລະເງິນ ແລະ ກວດສອບຂໍ້ມູນກ່ອນຢືນຢັນການສັ່ງຊື້</p>
        </div>

        <div class="checkout-hero-icon">
            <i class="fa-solid fa-credit-card"></i>
        </div>
    </section>

    <section class="checkout-steps">
        <div class="steps-row">
            <div class="step completed">
                <span><i class="fa-solid fa-check"></i></span>
                <p>ຂໍ້ມູນຈັດສົ່ງ</p>
            </div>

            <div class="step-line active-line"></div>

            <div class="step completed">
                <span><i class="fa-solid fa-check"></i></span>
                <p>ວິທີຈັດສົ່ງ</p>
            </div>

            <div class="step-line active-line"></div>

            <div class="step active">
                <span>3</span>
                <p>ຊຳລະເງິນ</p>
            </div>
        </div>
    </section>

    <?php if (isset($_GET['error']) && trim($_GET['error']) !== ''): ?>
        <div class="checkout-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?= htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <form
        action="<?= BASE_URL ?>/customer/checkout_process.php"
        method="POST"
        enctype="multipart/form-data"
        class="checkout-layout"
    >

        <section class="checkout-card checkout-form-card">
            <div class="card-title">
                <div class="card-title-icon">
                    <i class="fa-solid fa-wallet"></i>
                </div>
                <div>
                    <h2>ວິທີຊຳລະເງິນ</h2>
                    <p>ເລືອກວິທີຊຳລະເງິນທີ່ສະດວກສຳລັບທ່ານ</p>
                </div>
            </div>

            <label class="delivery-option">
                <input type="radio" name="payment_method" value="cod" checked required>
                <div>
                    <strong>
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        ຈ່າຍປາຍທາງ
                    </strong>
                    <p>ຊຳລະເງິນເມື່ອໄດ້ຮັບສິນຄ້າ</p>
                </div>
            </label>

            <label class="delivery-option">
                <input type="radio" name="payment_method" value="bank_transfer" required>
                <div>
                    <strong>
                        <i class="fa-solid fa-building-columns"></i>
                        ໂອນເງິນຜ່ານທະນາຄານ
                    </strong>
                    <p>BCEL / JDB / LDB / ພ້ອມອັບໂຫຼດສະລິບ</p>
                </div>
            </label>

            <div class="bank-info" id="bankInfo">
                <h4>
                    <i class="fa-solid fa-building-columns"></i>
                    ຂໍ້ມູນບັນຊີທະນາຄານ
                </h4>

                <p><strong>BCEL:</strong> 020-12-00-12345678-001</p>
                <p><strong>JDB:</strong> 123-456-789</p>
                <p><strong>LDB:</strong> 010-00-987654321</p>
                <p><strong>Account Name:</strong> ILIN SHOP</p>

                <label for="paymentSlip" class="slip-label">ອັບໂຫຼດສະລິບໂອນເງິນ</label>
                <input type="file" name="payment_slip" id="paymentSlip" accept="image/*">
            </div>

            <div class="checkout-note">
                <i class="fa-solid fa-circle-info"></i>
                <span>ຖ້າເລືອກໂອນເງິນ ກະລຸນາອັບໂຫຼດສະລິບໃຫ້ຊັດເຈນ ເພື່ອໃຫ້ທີມງານກວດສອບໄດ້ໄວ</span>
            </div>

            <div class="secure-box">
                <i class="fa-solid fa-truck-fast"></i>
                <div>
                    <strong>ວິທີຈັດສົ່ງທີ່ເລືອກ</strong>
                    <p>
                        <?= htmlspecialchars($deliveryTitle, ENT_QUOTES, 'UTF-8'); ?>
                        -
                        <?= htmlspecialchars($deliveryDescription, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            </div>

            <div class="secure-box">
                <i class="fa-solid fa-location-dot"></i>
                <div>
                    <strong>ທີ່ຢູ່ຈັດສົ່ງ</strong>
                    <p>
                        <?= htmlspecialchars(($shipping['first_name'] ?? '') . ' ' . ($shipping['last_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        |
                        <?= htmlspecialchars($shipping['phone_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        <br>
                        <?= htmlspecialchars($shipping['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            </div>

            <div class="payment-form-actions">
                <a href="<?= BASE_URL ?>/customer/checkout_delivery.php" class="secondary-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    ກັບໄປວິທີຈັດສົ່ງ
                </a>

                <button type="submit" class="primary-btn">
                    ຢືນຢັນການສັ່ງຊື້
                    <i class="fa-solid fa-check"></i>
                </button>
            </div>
        </section>

        <aside class="checkout-card checkout-summary-card">
            <div class="card-title summary-title">
                <div class="card-title-icon">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <h2>ສະຫຼຸບອໍເດີ</h2>
                    <p><?= number_format($totalItems); ?> ລາຍການໃນກະຕ່າ</p>
                </div>
            </div>

            <div class="checkout-items">
                <?php foreach ($cartItems as $item): ?>
                    <div class="checkout-item">
                        <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= intval($item['product_id']); ?>" class="checkout-item-img">
                            <?php if (!empty($item['image'])): ?>
                                <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php else: ?>
                                <img src="<?= BASE_URL ?>/assets/images/no-product.png" alt="<?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php endif; ?>
                        </a>

                        <div class="checkout-item-info">
                            <h3><?= htmlspecialchars($item['product_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <span><?= number_format($item['price']); ?> ₭ × <?= intval($item['qty']); ?></span>
                        </div>

                        <strong><?= number_format($item['sub_total']); ?> ₭</strong>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="summary-line">
                <span>ລວມສິນຄ້າ</span>
                <strong><?= number_format($total); ?> ₭</strong>
            </div>

            <div class="summary-line">
                <span>ຄ່າຈັດສົ່ງ</span>
                <strong><?= number_format($deliveryFee); ?> ₭</strong>
            </div>

            <div class="summary-total">
                <span>ລວມທັງໝົດ</span>
                <strong><?= number_format($grandTotal); ?> ₭</strong>
            </div>

            <div class="secure-box">
                <i class="fa-solid fa-shield-heart"></i>
                <div>
                    <strong>ການສັ່ງຊື້ປອດໄພ</strong>
                    <p>ກວດສອບຂໍ້ມູນກ່ອນຢືນຢັນ ແລະ ທີມງານຈະຕິດຕໍ່ກັບລູກຄ້າ</p>
                </div>
            </div>
        </aside>

    </form>

</main>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    const bankInfo = document.getElementById('bankInfo');
    const paymentSlip = document.getElementById('paymentSlip');

    function toggleBankInfo() {
        const selectedPayment = document.querySelector('input[name="payment_method"]:checked');

        if (!selectedPayment || !bankInfo || !paymentSlip) {
            return;
        }

        if (selectedPayment.value === 'bank_transfer') {
            bankInfo.style.display = 'block';
            paymentSlip.required = true;
        } else {
            bankInfo.style.display = 'none';
            paymentSlip.required = false;
            paymentSlip.value = '';
        }
    }

    paymentRadios.forEach(function (radio) {
        radio.addEventListener('change', toggleBankInfo);
    });

    toggleBankInfo();
});
</script>

</body>
</html>