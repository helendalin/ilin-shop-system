<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/*
    Support old session name if your old customer login used cus_id.
    But main customer session should be customer_id.
*/
if (!isset($_SESSION['customer_id']) && isset($_SESSION['cus_id'])) {
    $_SESSION['customer_id'] = $_SESSION['cus_id'];
}

if (!isset($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Please login first");
    exit();
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function messageStatusText($status) {
    switch ($status) {
        case 'new':
            return 'ຂໍ້ຄວາມໃໝ່';
        case 'read':
            return 'ອ່ານແລ້ວ';
        case 'replied':
            return 'ມີຄຳຕອບແລ້ວ';
        case 'closed':
            return 'ປິດແລ້ວ';
        default:
            return 'ບໍ່ຮູ້ສະຖານະ';
    }
}

function messageStatusClass($status) {
    switch ($status) {
        case 'new':
            return 'status-new';
        case 'read':
            return 'status-read';
        case 'replied':
            return 'status-replied';
        case 'closed':
            return 'status-closed';
        default:
            return 'status-default';
    }
}

$customer_id = intval($_SESSION['customer_id']);

/* Get logged-in customer */
$customerStmt = $conn->prepare("
    SELECT first_name, last_name, email
    FROM tb_customer
    WHERE customer_id = ?
    LIMIT 1
");

if (!$customerStmt) {
    die("Database error: cannot prepare customer query.");
}

$customerStmt->bind_param("i", $customer_id);
$customerStmt->execute();
$customer = $customerStmt->get_result()->fetch_assoc();

if (!$customer) {
    header("Location: " . BASE_URL . "/auth/customer/logout.php");
    exit();
}

$customerEmail = $customer['email'] ?? '';
$customerName = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));

/*
    Mark admin replies as read when customer opens this page.
    This supports:
    1. Messages sent while logged in using customer_id
    2. Guest messages using the same customer email
*/
$markReadStmt = $conn->prepare("
    UPDATE tb_contact_reply cr
    INNER JOIN tb_contact_message cm ON cr.message_id = cm.message_id
    SET cr.is_read_by_customer = 1
    WHERE cr.is_read_by_customer = 0
    AND (
        cm.customer_id = ?
        OR (cm.customer_id IS NULL AND cm.email = ?)
    )
");

if ($markReadStmt) {
    $markReadStmt->bind_param("is", $customer_id, $customerEmail);
    $markReadStmt->execute();
}

/* Get customer messages */
$messageStmt = $conn->prepare("
    SELECT
        message_id,
        customer_id,
        name,
        email,
        phone,
        message,
        status,
        created_at,
        updated_at
    FROM tb_contact_message
    WHERE customer_id = ?
    OR (customer_id IS NULL AND email = ?)
    ORDER BY created_at DESC
");

if (!$messageStmt) {
    die("Database error: cannot prepare message query.");
}

$messageStmt->bind_param("is", $customer_id, $customerEmail);
$messageStmt->execute();
$messagesResult = $messageStmt->get_result();

$messageRows = [];
$totalMessages = 0;
$repliedMessages = 0;
$closedMessages = 0;

while ($row = $messagesResult->fetch_assoc()) {
    $messageRows[] = $row;
    $totalMessages++;

    if (($row['status'] ?? '') === 'replied') {
        $repliedMessages++;
    }

    if (($row['status'] ?? '') === 'closed') {
        $closedMessages++;
    }
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຂໍ້ຄວາມຂອງຂ້ອຍ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/my_messages.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<?php include 'header.php'; ?>

<main class="my-message-page">

    <section class="my-message-hero">
        <div>
            <span class="message-badge">
                <i class="fa-solid fa-comments"></i>
                ILIN SHOP Support
            </span>

            <h1>ຂໍ້ຄວາມຂອງຂ້ອຍ</h1>

            <p>
                ເບິ່ງຂໍ້ຄວາມທີ່ທ່ານເຄີຍສົ່ງຫາຮ້ານ
                ແລະ ຄຳຕອບຈາກທີມງານ ILIN SHOP.
            </p>
        </div>

        <div class="hero-icon">
            <i class="fa-solid fa-headset"></i>
        </div>
    </section>

    <section class="message-stats">

        <div class="stat-card">
            <div class="stat-icon total">
                <i class="fa-solid fa-inbox"></i>
            </div>
            <div>
                <span>ຂໍ້ຄວາມທັງໝົດ</span>
                <strong><?= number_format($totalMessages); ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon replied">
                <i class="fa-solid fa-reply"></i>
            </div>
            <div>
                <span>ມີຄຳຕອບແລ້ວ</span>
                <strong><?= number_format($repliedMessages); ?></strong>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon closed">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span>ປິດແລ້ວ</span>
                <strong><?= number_format($closedMessages); ?></strong>
            </div>
        </div>

    </section>

    <section class="message-content">

        <div class="section-title">
            <div>
                <h2>ລາຍການຂໍ້ຄວາມ</h2>
                <p>
                    ບັນຊີ:
                    <?= h($customerName !== '' ? $customerName : 'Customer'); ?>
                    ·
                    <?= h($customerEmail); ?>
                </p>
            </div>

            <a href="<?= BASE_URL ?>/customer/contact.php">
                <i class="fa-solid fa-plus"></i>
                ສົ່ງຂໍ້ຄວາມໃໝ່
            </a>
        </div>

        <?php if (!empty($messageRows)): ?>

            <div class="message-list">

                <?php foreach ($messageRows as $msg): ?>

                    <?php
                    $messageId = intval($msg['message_id']);
                    $status = $msg['status'] ?? 'new';

                    $replyStmt = $conn->prepare("
                        SELECT
                            cr.reply_id,
                            cr.reply_message,
                            cr.created_at,
                            CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) AS employee_name
                        FROM tb_contact_reply cr
                        LEFT JOIN tb_employee e ON cr.emp_id = e.emp_id
                        WHERE cr.message_id = ?
                        ORDER BY cr.created_at ASC
                    ");

                    $replies = null;

                    if ($replyStmt) {
                        $replyStmt->bind_param("i", $messageId);
                        $replyStmt->execute();
                        $replies = $replyStmt->get_result();
                    }
                    ?>

                    <article class="message-card <?= messageStatusClass($status); ?>">

                        <div class="message-card-top">
                            <div>
                                <span class="status-pill <?= messageStatusClass($status); ?>">
                                    <?= h(messageStatusText($status)); ?>
                                </span>

                                <h3>ຂໍ້ຄວາມ #<?= str_pad($messageId, 5, '0', STR_PAD_LEFT); ?></h3>

                                <p>
                                    <i class="fa-regular fa-clock"></i>
                                    <?= date('d/m/Y H:i', strtotime($msg['created_at'])); ?>
                                </p>
                            </div>
                        </div>

                        <div class="customer-message-box">
                            <h4>
                                <i class="fa-regular fa-message"></i>
                                ຂໍ້ຄວາມຂອງທ່ານ
                            </h4>

                            <p><?= nl2br(h($msg['message'])); ?></p>
                        </div>

                        <?php if ($replies && $replies->num_rows > 0): ?>

                            <div class="reply-box">
                                <h4>
                                    <i class="fa-solid fa-reply"></i>
                                    ຄຳຕອບຈາກ ILIN SHOP
                                </h4>

                                <?php while ($reply = $replies->fetch_assoc()): ?>

                                    <?php
                                    $employeeName = trim($reply['employee_name'] ?? '');
                                    ?>

                                    <div class="reply-item">
                                        <div class="reply-top">
                                            <strong>
                                                <?= h($employeeName !== '' ? $employeeName : 'ILIN SHOP Admin'); ?>
                                            </strong>

                                            <span>
                                                <?= date('d/m/Y H:i', strtotime($reply['created_at'])); ?>
                                            </span>
                                        </div>

                                        <p><?= nl2br(h($reply['reply_message'])); ?></p>
                                    </div>

                                <?php endwhile; ?>

                            </div>

                        <?php else: ?>

                            <div class="no-reply-box">
                                <i class="fa-regular fa-clock"></i>
                                <span>
                                    ທີມງານກຳລັງກວດສອບ
                                    ແລະ ຈະຕອບກັບໃນໄວໆນີ້
                                </span>
                            </div>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fa-regular fa-envelope-open"></i>
                </div>

                <h3>ຍັງບໍ່ມີຂໍ້ຄວາມ</h3>

                <p>
                    ທ່ານຍັງບໍ່ເຄີຍສົ່ງຂໍ້ຄວາມຫາ ILIN SHOP.
                    ສາມາດສົ່ງຂໍ້ຄວາມໃໝ່ໄດ້ທີ່ໜ້າຕິດຕໍ່.
                </p>

                <a href="<?= BASE_URL ?>/customer/contact.php">
                    <i class="fa-solid fa-paper-plane"></i>
                    ສົ່ງຂໍ້ຄວາມ
                </a>
            </div>

        <?php endif; ?>

    </section>

</main>

<?php include 'footer.php'; ?>

</body>
</html>