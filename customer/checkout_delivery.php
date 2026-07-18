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

/* Save shipping information from checkout.php */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($firstName === '' || $lastName === '' || $phoneNumber === '' || $email === '' || $address === '') {
        header("Location: " . BASE_URL . "/customer/checkout.php?error=Please complete all shipping information");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: " . BASE_URL . "/customer/checkout.php?error=Invalid email address");
        exit();
    }

    $_SESSION['checkout_shipping'] = [
        'first_name' => $firstName,
        'last_name' => $lastName,
        'phone_number' => $phoneNumber,
        'email' => $email,
        'address' => $address
    ];
}

/* Customer cannot access delivery page without shipping information */
if (empty($_SESSION['checkout_shipping'])) {
    header("Location: " . BASE_URL . "/customer/checkout.php");
    exit();
}

$shipping = $_SESSION['checkout_shipping'];

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

$deliveryMethods = [
    'standard' => [
        'title' => 'ຈັດສົ່ງປົກກະຕິ',
        'description' => 'ໄດ້ຮັບສິນຄ້າປະມານ 1 - 3 ມື້',
        'fee' => 20000,
        'icon' => 'fa-truck'
    ],
    'express' => [
        'title' => 'ຈັດສົ່ງດ່ວນ',
        'description' => 'ໄດ້ຮັບສິນຄ້າພາຍໃນ 24 ຊົ່ວໂມງ',
        'fee' => 35000,
        'icon' => 'fa-truck-fast'
    ],
    'pickup' => [
        'title' => 'ຮັບສິນຄ້າທີ່ຮ້ານ',
        'description' => 'ລູກຄ້າສາມາດມາຮັບສິນຄ້າໄດ້ທີ່ຮ້ານ',
        'fee' => 0,
        'icon' => 'fa-store'
    ]
];

$defaultDelivery = 'standard';
$defaultDeliveryFee = $deliveryMethods[$defaultDelivery]['fee'];
$grandTotal = $total + $defaultDeliveryFee;
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ວິທີການຈັດສົ່ງ - ILIN SHOP</title>

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
                <i class="fa-solid fa-truck-fast"></i>
                Delivery Method
            </span>
            <h1>ວິທີການຈັດສົ່ງ</h1>
            <p>ເລືອກຮູບແບບການຈັດສົ່ງທີ່ເໝາະສົມກັບທ່ານ</p>
        </div>

        <div class="checkout-hero-icon">
            <i class="fa-solid fa-box-open"></i>
        </div>
    </section>

    <section class="checkout-steps">
        <div class="steps-row">
            <div class="step completed">
                <span><i class="fa-solid fa-check"></i></span>
                <p>ຂໍ້ມູນຈັດສົ່ງ</p>
            </div>

            <div class="step-line active-line"></div>

            <div class="step active">
                <span>2</span>
                <p>ວິທີຈັດສົ່ງ</p>
            </div>

            <div class="step-line active-line"></div>

            <div class="step">
                <span>3</span>
                <p>ຊຳລະເງິນ</p>
            </div>
        </div>
    </section>

    <form action="<?= BASE_URL ?>/customer/checkout_payment.php" method="POST" class="checkout-layout">

        <section class="checkout-card checkout-form-card">
            <div class="card-title">
                <div class="card-title-icon">
                    <i class="fa-solid fa-truck-fast"></i>
                </div>
                <div>
                    <h2>ເລືອກການຈັດສົ່ງ</h2>
                    <p>ລະບົບຈະຄິດໄລ່ຄ່າຈັດສົ່ງຕາມຮູບແບບທີ່ເລືອກ</p>
                </div>
            </div>

            <?php foreach ($deliveryMethods as $key => $method): ?>
                <label class="delivery-option">
                    <input
                        type="radio"
                        name="delivery_method"
                        value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>"
                        data-fee="<?= intval($method['fee']); ?>"
                        <?= $key === $defaultDelivery ? 'checked' : ''; ?>
                        required
                    >

                    <div>
                        <strong>
                            <i class="fa-solid <?= htmlspecialchars($method['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i>
                            <?= htmlspecialchars($method['title'], ENT_QUOTES, 'UTF-8'); ?>
                        </strong>
                        <p>
                            <?= htmlspecialchars($method['description'], ENT_QUOTES, 'UTF-8'); ?>
                            |
                            <?= $method['fee'] > 0 ? number_format($method['fee']) . ' ₭' : 'ບໍ່ມີຄ່າຈັດສົ່ງ'; ?>
                        </p>
                    </div>
                </label>
            <?php endforeach; ?>

            <div class="checkout-note">
                <i class="fa-solid fa-circle-info"></i>
                <span>ກະລຸນາກວດສອບທີ່ຢູ່ ແລະ ເບີໂທໃຫ້ຖືກຕ້ອງ ເພື່ອໃຫ້ການຈັດສົ່ງບໍ່ຜິດພາດ</span>
            </div>

            <div class="secure-box">
                <i class="fa-solid fa-location-dot"></i>
                <div>
                    <strong>ທີ່ຢູ່ຈັດສົ່ງ</strong>
                    <p>
                        <?= htmlspecialchars($shipping['first_name'] . ' ' . $shipping['last_name'], ENT_QUOTES, 'UTF-8'); ?>
                        |
                        <?= htmlspecialchars($shipping['phone_number'], ENT_QUOTES, 'UTF-8'); ?>
                        <br>
                        <?= htmlspecialchars($shipping['address'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
            </div>

            <div class="delivery-form-actions">
                <a href="<?= BASE_URL ?>/customer/checkout.php" class="secondary-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    ກັບໄປຂໍ້ມູນຈັດສົ່ງ
                </a>

                <button type="submit" class="primary-btn">
                    ໄປຂັ້ນຕອນຊຳລະເງິນ
                    <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
        </section>

        <aside class="checkout-card checkout-summary-card">
            <div class="card-title summary-title">
                <div class="card-title-icon">
                    <i class="fa-solid fa-receipt"></i>
                </div>
                <div>
                    <h2>ສະຫຼຸບການສັ່ງຊື້</h2>
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
                <strong id="deliveryFeeText"><?= number_format($defaultDeliveryFee); ?> ₭</strong>
            </div>

            <div class="summary-total">
                <span>ລວມທັງໝົດ</span>
                <strong id="grandTotalText"><?= number_format($grandTotal); ?> ₭</strong>
            </div>

            <div class="secure-box">
                <i class="fa-solid fa-shield-heart"></i>
                <div>
                    <strong>ການຈັດສົ່ງປອດໄພ</strong>
                    <p>ທີມງານ ILIN SHOP ຈະຕິດຕໍ່ລູກຄ້າກ່ອນຈັດສົ່ງສິນຄ້າ</p>
                </div>
            </div>
        </aside>

    </form>

</main>

<?php include 'footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const productTotal = <?= json_encode($total); ?>;
    const deliveryInputs = document.querySelectorAll('input[name="delivery_method"]');
    const deliveryFeeText = document.getElementById('deliveryFeeText');
    const grandTotalText = document.getElementById('grandTotalText');

    function formatKip(amount) {
        return new Intl.NumberFormat('en-US').format(amount) + ' ₭';
    }

    deliveryInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            const fee = parseInt(this.dataset.fee || '0', 10);
            const grandTotal = productTotal + fee;

            deliveryFeeText.textContent = fee > 0 ? formatKip(fee) : '0 ₭';
            grandTotalText.textContent = formatKip(grandTotal);
        });
    });
});
</script>

</body>
</html>