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

<header class="topbar">

    <div>
        <h1>ໜ້າຫຼັກ</h1>

        <p>
            ຍິນດີຕ້ອນຮັບ,
            <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?>
        </p>
    </div>

    <div class="admin-dropdown">

        <button type="button" class="admin-btn" onclick="toggleAdminMenu()">
            <?= htmlspecialchars($_SESSION['role'] ?? 'Admin'); ?>
        </button>

        <div class="admin-menu" id="adminMenu">

            <div class="admin-header">
                <strong><?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin User'); ?></strong>
                <small><?= htmlspecialchars($_SESSION['email'] ?? '-'); ?></small>
            </div>

            <a href="<?= BASE_URL ?>/admin/employee/profile.php">
                👤 ຂໍ້ມູນບັນຊີ
            </a>

            <a href="<?= BASE_URL ?>/admin/employee/change_password.php">
                🔒 ປ່ຽນລະຫັດຜ່ານ
            </a>

            <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php"
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
            </a>
            <a href="<?= BASE_URL ?>/auth/logout.php" class="logout-link">
                🚪 ອອກຈາກລະບົບ
            </a>

        </div>

    </div>

</header>

<script>
function toggleAdminMenu() {
    document.getElementById("adminMenu").classList.toggle("show-menu");
}

document.addEventListener("click", function(e) {
    const dropdown = document.querySelector(".admin-dropdown");

    if (dropdown && !dropdown.contains(e.target)) {
        document.getElementById("adminMenu").classList.remove("show-menu");
    }
});
</script>