<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Customer must login first */
if (!isset($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Please login first before checkout");
    exit();
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart) || !is_array($cart)) {
    header("Location: " . BASE_URL . "/customer/cart.php");
    exit();
}

$customer_id = intval($_SESSION['customer_id']);

$stmt = $conn->prepare("
    SELECT first_name, last_name, phone_number, email, address
    FROM tb_customer
    WHERE customer_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();

if (!$customer) {
    $customer = [
        'first_name' => '',
        'last_name' => '',
        'phone_number' => '',
        'email' => '',
        'address' => ''
    ];
}

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
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຂໍ້ມູນການສັ່ງຊື້ - ILIN SHOP</title>

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
                <i class="fa-solid fa-shield-heart"></i>
                Secure Checkout
            </span>
            <h1>ຂໍ້ມູນການສັ່ງຊື້</h1>
            <p>ກວດສອບຂໍ້ມູນລູກຄ້າ ແລະ ທີ່ຢູ່ຈັດສົ່ງກ່ອນໄປຂັ້ນຕອນຖັດໄປ</p>
        </div>

        <div class="checkout-hero-icon">
            <i class="fa-solid fa-truck-fast"></i>
        </div>
    </section>

    <section class="checkout-steps">
        <div class="steps-row">
            <div class="step active">
                <span>1</span>
                <p>ຂໍ້ມູນຈັດສົ່ງ</p>
            </div>

            <div class="step-line active-line"></div>

            <div class="step">
                <span>2</span>
                <p>ວິທີຈັດສົ່ງ</p>
            </div>

            <div class="step-line"></div>

            <div class="step">
                <span>3</span>
                <p>ຊຳລະເງິນ</p>
            </div>
        </div>
    </section>

    <form action="<?= BASE_URL ?>/customer/checkout_delivery.php" method="POST" class="checkout-layout">

        <section class="checkout-card checkout-form-card">
            <div class="card-title">
                <div class="card-title-icon">
                    <i class="fa-regular fa-user"></i>
                </div>
                <div>
                    <h2>ຂໍ້ມູນລູກຄ້າ</h2>
                    <p>ກະລຸນາກວດສອບຊື່, ເບີໂທ ແລະ ທີ່ຢູ່ໃຫ້ຖືກຕ້ອງ</p>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name">ຊື່</label>
                    <div class="input-box">
                        <i class="fa-regular fa-user"></i>
                        <input
                            type="text"
                            id="first_name"
                            name="first_name"
                            placeholder="ປ້ອນຊື່"
                            value="<?= htmlspecialchars($customer['first_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="100"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="last_name">ນາມສະກຸນ</label>
                    <div class="input-box">
                        <i class="fa-regular fa-user"></i>
                        <input
                            type="text"
                            id="last_name"
                            name="last_name"
                            placeholder="ປ້ອນນາມສະກຸນ"
                            value="<?= htmlspecialchars($customer['last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="100"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="phone_number">ເບີໂທ</label>
                    <div class="input-box">
                        <i class="fa-solid fa-phone"></i>
                        <input
                            type="tel"
                            id="phone_number"
                            name="phone_number"
                            placeholder="020xxxxxxxx"
                            value="<?= htmlspecialchars($customer['phone_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="30"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">ອີເມວ</label>
                    <div class="input-box">
                        <i class="fa-regular fa-envelope"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="example@email.com"
                            value="<?= htmlspecialchars($customer['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="150"
                            required
                        >
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="address">ທີ່ຢູ່ຈັດສົ່ງ</label>
                <div class="textarea-box">
                    <i class="fa-solid fa-location-dot"></i>
                    <textarea
                        id="address"
                        name="address"
                        placeholder="ປ້ອນບ້ານ, ເມືອງ, ແຂວງ ແລະ ຈຸດສັງເກດສຳລັບຈັດສົ່ງ"
                        maxlength="500"
                        required
                    ><?= htmlspecialchars($customer['address'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>
            </div>

            <div class="checkout-note">
                <i class="fa-solid fa-circle-info"></i>
                <span>ຂໍ້ມູນນີ້ຈະຖືກໃຊ້ສຳລັບການຕິດຕໍ່ ແລະ ຈັດສົ່ງສິນຄ້າເທົ່ານັ້ນ</span>
            </div>

            <div class="checkout-form-actions">
                <a href="<?= BASE_URL ?>/customer/cart.php" class="secondary-btn">
                    <i class="fa-solid fa-arrow-left"></i>
                    ກັບໄປກະຕ່າ
                </a>

                <button type="submit" class="primary-btn">
                    ໄປຂັ້ນຕອນຖັດໄປ
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
                    <h2>ສະຫຼຸບກະຕ່າ</h2>
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
                <strong>ຄິດໄລ່ຂັ້ນຕອນຖັດໄປ</strong>
            </div>

            <div class="summary-total">
                <span>ລວມຊົ່ວຄາວ</span>
                <strong><?= number_format($total); ?> ₭</strong>
            </div>

            <div class="secure-box">
                <i class="fa-solid fa-lock"></i>
                <div>
                    <strong>ປອດໄພ</strong>
                    <p>ກວດສອບຂໍ້ມູນກ່ອນຊຳລະເງິນໄດ້ທຸກຂັ້ນຕອນ</p>
                </div>
            </div>
        </aside>

    </form>

</main>

<?php include 'footer.php'; ?>

</body>
</html>