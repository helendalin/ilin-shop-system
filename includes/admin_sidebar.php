<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('BASE_URL') || !isset($conn)) {
    include_once __DIR__ . '/../config/db.php';
}

$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir = basename(dirname($_SERVER['PHP_SELF']));

$adminUnreadContactCount = 0;

if (isset($conn)) {
    $unreadContactStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM tb_contact_message
        WHERE status = 'new'
    ");

    if ($unreadContactStmt) {
        $unreadContactStmt->execute();
        $unreadContactResult = $unreadContactStmt->get_result()->fetch_assoc();
        $adminUnreadContactCount = intval($unreadContactResult['total'] ?? 0);
    }
}
?>

<aside class="sidebar">

    <div class="logo-box">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="admin-logo-link">
            <img
                src="<?= BASE_URL ?>/assets/images/logo1.jpg"
                alt="ILIN SHOP"
                class="admin-sidebar-logo"
            >

            <div>
                <h2>ILIN SHOP</h2>
                <p>ລະບົບຈັດການຮ້ານ</p>
            </div>
        </a>
    </div>

    <nav class="menu">

        <a href="<?= BASE_URL ?>/admin/dashboard.php"
           class="menu-link <?= ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <span>
                <span class="menu-sticker">🏠</span>
                ໜ້າຫຼັກ
            </span>
        </a>

        <a href="<?= BASE_URL ?>/admin/order/order.php"
           class="menu-link <?= ($currentDir === 'order') ? 'active' : ''; ?>">
            <span>
                <span class="menu-sticker">🧾</span>
                ສັ່ງຊື້ສິນຄ້າ
            </span>
        </a>

        <a href="<?= BASE_URL ?>/admin/import/import.php"
           class="menu-link <?= ($currentDir === 'import') ? 'active' : ''; ?>">
            <span>
                <span class="menu-sticker">📦</span>
                ນຳເຂົ້າສິນຄ້າ
            </span>
        </a>

        <a href="<?= BASE_URL ?>/admin/sale/sale.php"
           class="menu-link <?= ($currentDir === 'sale') ? 'active' : ''; ?>">
            <span>
                <span class="menu-sticker">💰</span>
                ຂາຍສິນຄ້າ
            </span>
        </a>

        <!-- <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php"
           class="menu-link <?= ($currentDir === 'contact') ? 'active' : ''; ?>">
            <span>
                <span class="menu-sticker">💬</span>
                ຂໍ້ຄວາມລູກຄ້າ
            </span>

            <?php if ($adminUnreadContactCount > 0): ?>
                <em class="sidebar-badge">
                    <?= $adminUnreadContactCount > 99 ? '99+' : $adminUnreadContactCount; ?>
                </em>
            <?php endif; ?>
        </a> -->

        <div class="menu-group <?= in_array($currentDir, ['employee', 'customer', 'supplier', 'category', 'unit', 'product'], true) ? 'open' : ''; ?>">

            <button class="menu-toggle" type="button">
                <span>
                    <span class="menu-sticker">🗂️</span>
                    ຈັດການຂໍ້ມູນ
                </span>
                <span class="arrow">›</span>
            </button>

            <div class="submenu">

                <a href="<?= BASE_URL ?>/admin/employee/employee.php"
                   class="<?= ($currentDir === 'employee') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">👨‍💼</span>
                    ຈັດການພະນັກງານ
                </a>

                <a href="<?= BASE_URL ?>/admin/customer/customer.php"
                   class="<?= ($currentDir === 'customer') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">👥</span>
                    ຈັດການລູກຄ້າ
                </a>

                <a href="<?= BASE_URL ?>/admin/supplier/supplier.php"
                   class="<?= ($currentDir === 'supplier') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">🚚</span>
                    ຈັດການຜູ້ສະໜອງ
                </a>

                <a href="<?= BASE_URL ?>/admin/category/category.php"
                   class="<?= ($currentDir === 'category') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">🏷️</span>
                    ຈັດການປະເພດສິນຄ້າ
                </a>

                <a href="<?= BASE_URL ?>/admin/unit/unit.php"
                   class="<?= ($currentDir === 'unit') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">📏</span>
                    ຈັດການຫົວໜ່ວຍ
                </a>

                <a href="<?= BASE_URL ?>/admin/product/product.php"
                   class="<?= ($currentDir === 'product') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">🛍️</span>
                    ຈັດການສິນຄ້າ
                </a>

            </div>
        </div>

        <div class="menu-group <?= ($currentDir === 'report') ? 'open' : ''; ?>">

            <button class="menu-toggle" type="button">
                <span>
                    <span class="menu-sticker">📊</span>
                    ລາຍງານ
                </span>
                <span class="arrow">›</span>
            </button>

            <div class="submenu">

                <a href="<?= BASE_URL ?>/admin/report/import_report.php"
                   class="<?= ($currentPage === 'import_report.php') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">📥</span>
                    ລາຍງານນຳເຂົ້າສິນຄ້າ
                </a>

                <a href="<?= BASE_URL ?>/admin/report/product_report.php"
                   class="<?= ($currentPage === 'product_report.php') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">📦</span>
                    ລາຍງານສິນຄ້າ
                </a>

                <a href="<?= BASE_URL ?>/admin/report/sale_report.php"
                   class="<?= ($currentPage === 'sale_report.php') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">📈</span>
                    ລາຍງານການຂາຍສິນຄ້າ
                </a>

                <a href="<?= BASE_URL ?>/admin/report/order_report.php"
                   class="<?= ($currentPage === 'order_report.php') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">🧾</span>
                    ລາຍງານສັ່ງຊື້ສິນຄ້າ
                </a>

                <a href="<?= BASE_URL ?>/admin/report/supplier_report.php"
                   class="<?= ($currentPage === 'supplier_report.php') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">🚛</span>
                    ລາຍງານຜູ້ສະໜອງ
                </a>

                <a href="<?= BASE_URL ?>/admin/report/income_report.php"
                   class="<?= ($currentPage === 'income_report.php') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">💵</span>
                    ລາຍງານລາຍຮັບ
                </a>

                <a href="<?= BASE_URL ?>/admin/report/expense_report.php"
                   class="<?= ($currentPage === 'expense_report.php') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">💸</span>
                    ລາຍງານລາຍຈ່າຍ
                </a>

                <a href="<?= BASE_URL ?>/admin/report/popular_product_report.php"
                   class="<?= ($currentPage === 'popular_product_report.php') ? 'active-sub' : ''; ?>">
                    <span class="submenu-sticker">🔥</span>
                    ລາຍງານສິນຄ້າຂາຍດີ
                </a>

            </div>
        </div>

    </nav>

    <a href="<?= BASE_URL ?>/auth/logout.php" class="logout-btn">
        <span class="menu-sticker">🚪</span>
        ອອກຈາກລະບົບ
    </a>

</aside>

<script src="<?= BASE_URL ?>/assets/js/sidebar.js"></script>