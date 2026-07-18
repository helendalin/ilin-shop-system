<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Customer must login first */
if (!isset($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Please login first before viewing cart");
    exit();
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;
$totalItems = 0;
$cartItems = [];

if (!empty($cart) && is_array($cart)) {
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
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ກະຕ່າສິນຄ້າ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/cart.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="cart-page">

    <section class="cart-hero">
        <div>
            <span class="cart-badge">
                <i class="fa-solid fa-cart-shopping"></i>
                ILIN SHOP
            </span>
            <h1>ກະຕ່າສິນຄ້າ</h1>
            <p>ກວດສອບລາຍການສິນຄ້າກ່ອນດຳເນີນການສັ່ງຊື້</p>
        </div>

        <div class="cart-hero-icon">
            <i class="fa-solid fa-bag-shopping"></i>
        </div>
    </section>

    <?php if (!empty($cartItems)): ?>

        <section class="cart-layout">

            <div class="cart-card">
                <div class="cart-card-header">
                    <div>
                        <h2>ລາຍການສິນຄ້າ</h2>
                        <p>ທ່ານມີສິນຄ້າ <?= number_format($totalItems); ?> ລາຍການໃນກະຕ່າ</p>
                    </div>

                    <a href="<?= BASE_URL ?>/customer/products.php" class="continue-link">
                        <i class="fa-solid fa-arrow-left"></i>
                        ເລືອກຊື້ຕໍ່
                    </a>
                </div>

                <div class="cart-table-wrap">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>ສິນຄ້າ</th>
                                <th>ລາຄາ</th>
                                <th>ຈຳນວນ</th>
                                <th>ລວມ</th>
                                <th>ຈັດການ</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td data-label="ສິນຄ້າ">
                                        <div class="cart-product">
                                            <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= intval($item['product_id']); ?>" class="cart-img">
                                                <?php if (!empty($item['image'])): ?>
                                                    <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($item['image']); ?>" alt="<?= htmlspecialchars($item['product_name']); ?>">
                                                <?php else: ?>
                                                    <img src="<?= BASE_URL ?>/assets/images/no-product.png" alt="<?= htmlspecialchars($item['product_name']); ?>">
                                                <?php endif; ?>
                                            </a>

                                            <div class="cart-product-info">
                                                <h3>
                                                    <a href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= intval($item['product_id']); ?>">
                                                        <?= htmlspecialchars($item['product_name']); ?>
                                                    </a>
                                                </h3>
                                                <span>ສິນຄ້າສຳລັບແມ່ ແລະ ເດັກ</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td data-label="ລາຄາ">
                                        <span class="cart-price"><?= number_format($item['price']); ?> ₭</span>
                                    </td>

                                    <td data-label="ຈຳນວນ">
                                        <span class="qty-pill">
                                            <i class="fa-solid fa-xmark"></i>
                                            <?= intval($item['qty']); ?>
                                        </span>
                                    </td>

                                    <td data-label="ລວມ">
                                        <strong class="cart-subtotal"><?= number_format($item['sub_total']); ?> ₭</strong>
                                    </td>

                                    <td data-label="ຈັດການ">
                                        <a href="<?= BASE_URL ?>/customer/remove_cart.php?id=<?= intval($item['product_id']); ?>"
                                           class="remove-cart-btn"
                                           >
                                            <i class="fa-regular fa-trash-can"></i>
                                            ລຶບ
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <aside class="cart-summary">
                <div class="summary-card">
                    <h2>ສະຫຼຸບການສັ່ງຊື້</h2>

                    <div class="summary-row">
                        <span>ຈຳນວນສິນຄ້າ</span>
                        <strong><?= number_format($totalItems); ?> ລາຍການ</strong>
                    </div>

                    <div class="summary-row">
                        <span>ລາຄາສິນຄ້າ</span>
                        <strong><?= number_format($total); ?> ₭</strong>
                    </div>

                    <div class="summary-row">
                        <span>ຄ່າຈັດສົ່ງ</span>
                        <strong>ຄິດໄລ່ໃນ Checkout</strong>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-total">
                        <span>ລວມທັງໝົດ</span>
                        <strong><?= number_format($total); ?> ₭</strong>
                    </div>

                    <a href="<?= BASE_URL ?>/customer/checkout.php" class="checkout-btn">
                        ດຳເນີນການສັ່ງຊື້
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>

                    <div class="summary-note">
                        <i class="fa-solid fa-shield-heart"></i>
                        <span>ການສັ່ງຊື້ປອດໄພ ແລະ ກວດສອບຂໍ້ມູນໄດ້</span>
                    </div>
                </div>
            </aside>

        </section>

    <?php else: ?>

        <section class="empty-cart">
            <div class="empty-icon">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>

            <h2>ກະຕ່າຍັງວ່າງ</h2>
            <p>ເລືອກສິນຄ້າສຳລັບແມ່ ແລະ ເດັກກ່ອນດຳເນີນການສັ່ງຊື້</p>

            <a href="<?= BASE_URL ?>/customer/products.php" class="empty-btn">
                <i class="fa-solid fa-bag-shopping"></i>
                ໄປເລືອກສິນຄ້າ
            </a>
        </section>

    <?php endif; ?>

</main>

<?php include 'footer.php'; ?>

</body>
</html>