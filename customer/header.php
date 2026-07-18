<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL') || !isset($conn)) {
    include_once __DIR__ . '/../config/db.php';
}

/*
    Support old customer session name if it exists.
*/
if (!isset($_SESSION['customer_id']) && isset($_SESSION['cus_id'])) {
    $_SESSION['customer_id'] = $_SESSION['cus_id'];
}

$currentPage = basename($_SERVER['PHP_SELF']);

$cartCount = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += isset($item['qty']) ? (int)$item['qty'] : 0;
    }
}

/*
    Customer dropdown data
*/
$isCustomerLoggedIn = isset($_SESSION['customer_id']);
$headerCustomerName = 'Customer';
$headerCustomerEmail = '-';
$unreadReplyCount = 0;

if ($isCustomerLoggedIn) {
    $customer_id = intval($_SESSION['customer_id']);

    $customerStmt = $conn->prepare("
        SELECT first_name, last_name, email
        FROM tb_customer
        WHERE customer_id = ?
        LIMIT 1
    ");

    if ($customerStmt) {
        $customerStmt->bind_param("i", $customer_id);
        $customerStmt->execute();
        $customerData = $customerStmt->get_result()->fetch_assoc();

        if ($customerData) {
            $fullName = trim(($customerData['first_name'] ?? '') . ' ' . ($customerData['last_name'] ?? ''));

            $headerCustomerName = $fullName !== '' ? $fullName : 'Customer';
            $headerCustomerEmail = !empty($customerData['email']) ? $customerData['email'] : '-';

            $_SESSION['customer_name'] = $headerCustomerName;
            $_SESSION['customer_email'] = $headerCustomerEmail;

            /*
                Count unread admin replies.
                It supports:
                1. Messages sent while customer logged in by customer_id
                2. Guest messages using the same email
            */
            $unreadStmt = $conn->prepare("
                SELECT COUNT(*) AS unread_count
                FROM tb_contact_reply cr
                INNER JOIN tb_contact_message cm ON cr.message_id = cm.message_id
                WHERE cr.is_read_by_customer = 0
                AND (
                    cm.customer_id = ?
                    OR (cm.customer_id IS NULL AND cm.email = ?)
                )
            ");

            if ($unreadStmt) {
                $unreadStmt->bind_param("is", $customer_id, $headerCustomerEmail);
                $unreadStmt->execute();
                $unreadResult = $unreadStmt->get_result()->fetch_assoc();

                $unreadReplyCount = intval($unreadResult['unread_count'] ?? 0);
            }
        }
    }
}
?>

<header class="customer-header">

    <a href="<?= BASE_URL ?>/customer/home.php" class="brand">
        <img src="<?= BASE_URL ?>/assets/images/logo1.jpg" alt="ILIN SHOP">
        <span>ILIN SHOP</span>
    </a>

    <nav class="customer-nav">
        <a href="<?= BASE_URL ?>/customer/home.php"
           class="<?= ($currentPage == 'home.php') ? 'active' : '' ?>">
            ໜ້າຫຼັກ
        </a>

        <a href="<?= BASE_URL ?>/customer/products.php"
           class="<?= ($currentPage == 'products.php' || $currentPage == 'product_detail.php') ? 'active' : '' ?>">
            ສິນຄ້າ
        </a>

        <a href="<?= BASE_URL ?>/customer/about.php"
           class="<?= ($currentPage == 'about.php') ? 'active' : '' ?>">
            ກ່ຽວກັບ
        </a>

        <a href="<?= BASE_URL ?>/customer/contact.php"
           class="<?= ($currentPage == 'contact.php') ? 'active' : '' ?>">
            ຕິດຕໍ່
        </a>
    </nav>

    <div class="header-actions">

        <form action="<?= BASE_URL ?>/customer/products.php" method="GET" class="header-search">
            <input type="text" name="search" placeholder="ຄົ້ນຫາສິນຄ້າ...">
            <button type="submit">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </form>

        <?php if ($isCustomerLoggedIn): ?>
            <a href="<?= BASE_URL ?>/customer/my_messages.php" class="header-icon message-icon">
                <i class="fa-solid fa-comments"></i>

                <?php if ($unreadReplyCount > 0): ?>
                    <span><?= $unreadReplyCount > 99 ? '99+' : intval($unreadReplyCount); ?></span>
                <?php endif; ?>
            </a>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/customer/cart.php" class="header-icon cart-icon">
            <i class="fa-solid fa-cart-arrow-down"></i>

            <?php if ($cartCount > 0): ?>
                <span><?= intval($cartCount); ?></span>
            <?php endif; ?>
        </a>

        <?php if ($isCustomerLoggedIn): ?>

            <div class="customer-dropdown">

                <button type="button"
                        id="customerMenuBtn"
                        class="customer-icon-btn">
                    <i class="fa-solid fa-user"></i>
                </button>

                <div class="customer-menu" id="customerMenu">

                    <div class="customer-menu-header">
                        <strong><?= htmlspecialchars($headerCustomerName, ENT_QUOTES, 'UTF-8'); ?></strong>
                        <small><?= htmlspecialchars($headerCustomerEmail, ENT_QUOTES, 'UTF-8'); ?></small>
                    </div>

                    <a href="<?= BASE_URL ?>/customer/account.php">
                        👤 ຂໍ້ມູນບັນຊີ
                    </a>

                    <a href="<?= BASE_URL ?>/customer/order_history.php">
                        🧾 ປະຫວັດການສັ່ງຊື້
                    </a>

                    <!-- <a href="<?= BASE_URL ?>/customer/my_messages.php" class="customer-message-link"> -->
                        <!-- <span>💬 ຂໍ້ຄວາມຂອງຂ້ອຍ</span> -->

                        <!-- <?php if ($unreadReplyCount > 0): ?> -->
                            <!-- <em><?= $unreadReplyCount > 99 ? '99+' : intval($unreadReplyCount); ?></em> -->
                        <!-- <?php endif; ?> -->
                    <!-- </a> -->

                    <a href="<?= BASE_URL ?>/auth/customer/logout.php" class="customer-logout">
                        🚪 ອອກຈາກລະບົບ
                    </a>

                </div>

            </div>

        <?php else: ?>

            <a href="<?= BASE_URL ?>/auth/customer/login.php" class="header-icon">
                <i class="fa-solid fa-user"></i>
            </a>

        <?php endif; ?>

    </div>

</header>

<script>
(function () {
    const menuBtn = document.getElementById("customerMenuBtn");
    const menu = document.getElementById("customerMenu");

    if (menuBtn && menu) {
        menuBtn.addEventListener("click", function (e) {
            e.stopPropagation();
            menu.classList.toggle("show");
        });

        document.addEventListener("click", function (e) {
            if (!menu.contains(e.target) && !menuBtn.contains(e.target)) {
                menu.classList.remove("show");
            }
        });
    }
})();
</script>