<?php
include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../includes/session_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function bindStatementParams(mysqli_stmt $stmt, string $types, array &$params): void {
    if ($types === '' || empty($params)) {
        return;
    }

    $bindParams = [];
    $bindParams[] = $types;

    foreach ($params as $key => &$value) {
        $bindParams[] = &$value;
    }

    call_user_func_array([$stmt, 'bind_param'], $bindParams);
}

function statusLabel($status) {
    switch ($status) {
        case 'new':
            return 'ຂໍ້ຄວາມໃໝ່';
        case 'read':
            return 'ອ່ານແລ້ວ';
        case 'replied':
            return 'ຕອບກັບແລ້ວ';
        case 'closed':
            return 'ປິດງານແລ້ວ';
        default:
            return 'ບໍ່ຮູ້ສະຖານະ';
    }
}

function statusClass($status) {
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

function safeReturnUrl($url) {
    if ($url === '' || strpos($url, "\n") !== false || strpos($url, "\r") !== false) {
        return BASE_URL . '/admin/contact/contact_messages.php';
    }

    return $url;
}

function pageUrl($pageNumber, $queryForPagination) {
    $queryForPagination['page'] = $pageNumber;
    return '?' . http_build_query($queryForPagination);
}

$allowedStatuses = ['all', 'new', 'read', 'replied', 'closed'];

/* POST Actions */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $messageId = intval($_POST['message_id'] ?? 0);
    $redirectUrl = safeReturnUrl($_POST['return_url'] ?? ($_SERVER['REQUEST_URI'] ?? ''));

    if ($messageId <= 0) {
        $_SESSION['contact_admin_flash'] = [
            'status' => 'error',
            'message' => 'ບໍ່ພົບລະຫັດຂໍ້ຄວາມ'
        ];

        header("Location: " . $redirectUrl);
        exit();
    }

    if ($action === 'update_status') {
        $newStatus = $_POST['status'] ?? '';

        if (!in_array($newStatus, ['new', 'read', 'replied', 'closed'], true)) {
            $_SESSION['contact_admin_flash'] = [
                'status' => 'error',
                'message' => 'ສະຖານະບໍ່ຖືກຕ້ອງ'
            ];

            header("Location: " . $redirectUrl);
            exit();
        }

        $updateStmt = $conn->prepare("
            UPDATE tb_contact_message
            SET status = ?, updated_at = CURRENT_TIMESTAMP
            WHERE message_id = ?
            LIMIT 1
        ");

        if ($updateStmt) {
            $updateStmt->bind_param("si", $newStatus, $messageId);

            if ($updateStmt->execute()) {
                $_SESSION['contact_admin_flash'] = [
                    'status' => 'success',
                    'message' => 'ອັບເດດສະຖານະສຳເລັດ'
                ];
            } else {
                $_SESSION['contact_admin_flash'] = [
                    'status' => 'error',
                    'message' => 'ອັບເດດສະຖານະບໍ່ສຳເລັດ'
                ];
            }
        }
    }

    if ($action === 'save_note') {
        $adminNote = trim($_POST['admin_note'] ?? '');

        if (mb_strlen($adminNote, 'UTF-8') > 2000) {
            $_SESSION['contact_admin_flash'] = [
                'status' => 'error',
                'message' => 'ໝາຍເຫດຍາວເກີນໄປ'
            ];

            header("Location: " . $redirectUrl);
            exit();
        }

        $noteStmt = $conn->prepare("
            UPDATE tb_contact_message
            SET admin_note = ?, updated_at = CURRENT_TIMESTAMP
            WHERE message_id = ?
            LIMIT 1
        ");

        if ($noteStmt) {
            $noteStmt->bind_param("si", $adminNote, $messageId);

            if ($noteStmt->execute()) {
                $_SESSION['contact_admin_flash'] = [
                    'status' => 'success',
                    'message' => 'ບັນທຶກໝາຍເຫດສຳເລັດ'
                ];
            } else {
                $_SESSION['contact_admin_flash'] = [
                    'status' => 'error',
                    'message' => 'ບັນທຶກໝາຍເຫດບໍ່ສຳເລັດ'
                ];
            }
        }
    }

    header("Location: " . $redirectUrl);
    exit();
}

/* Flash message */
$flashStatus = '';
$flashMessage = '';

if (isset($_SESSION['contact_admin_flash'])) {
    $flashStatus = $_SESSION['contact_admin_flash']['status'] ?? '';
    $flashMessage = $_SESSION['contact_admin_flash']['message'] ?? '';
    unset($_SESSION['contact_admin_flash']);
}

/* Filters */
$status = $_GET['status'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo = trim($_GET['date_to'] ?? '');

if (!in_array($status, $allowedStatuses, true)) {
    $status = 'all';
}

if ($dateFrom !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
    $dateFrom = '';
}

if ($dateTo !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    $dateTo = '';
}

/* Pagination */
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 7;
$offset = ($page - 1) * $limit;

/* Build WHERE */
$where = [];
$types = '';
$params = [];

$where[] = "1 = 1";

if ($status !== 'all') {
    $where[] = "cm.status = ?";
    $types .= "s";
    $params[] = $status;
}

if ($q !== '') {
    $where[] = "(
        cm.name LIKE ?
        OR cm.email LIKE ?
        OR cm.phone LIKE ?
        OR cm.message LIKE ?
        OR cm.admin_note LIKE ?
        OR CAST(cm.message_id AS CHAR) LIKE ?
    )";

    $searchValue = "%" . $q . "%";

    for ($i = 0; $i < 6; $i++) {
        $types .= "s";
        $params[] = $searchValue;
    }
}

if ($dateFrom !== '') {
    $where[] = "DATE(cm.created_at) >= ?";
    $types .= "s";
    $params[] = $dateFrom;
}

if ($dateTo !== '') {
    $where[] = "DATE(cm.created_at) <= ?";
    $types .= "s";
    $params[] = $dateTo;
}

$whereSql = implode(" AND ", $where);

/* Stats */
$stats = [
    'total' => 0,
    'new_count' => 0,
    'read_count' => 0,
    'replied_count' => 0,
    'closed_count' => 0
];

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END) AS new_count,
        SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) AS read_count,
        SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) AS replied_count,
        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_count
    FROM tb_contact_message
");

if ($statsResult) {
    $statsRow = $statsResult->fetch_assoc();

    if ($statsRow) {
        $stats['total'] = intval($statsRow['total'] ?? 0);
        $stats['new_count'] = intval($statsRow['new_count'] ?? 0);
        $stats['read_count'] = intval($statsRow['read_count'] ?? 0);
        $stats['replied_count'] = intval($statsRow['replied_count'] ?? 0);
        $stats['closed_count'] = intval($statsRow['closed_count'] ?? 0);
    }
}

/* Count filtered rows */
$totalRows = 0;

$countSql = "
    SELECT COUNT(*) AS total
    FROM tb_contact_message cm
    LEFT JOIN tb_customer c ON cm.customer_id = c.customer_id
    WHERE $whereSql
";

$countStmt = $conn->prepare($countSql);

if ($countStmt) {
    $countParams = $params;
    $countTypes = $types;

    bindStatementParams($countStmt, $countTypes, $countParams);

    $countStmt->execute();
    $countResult = $countStmt->get_result()->fetch_assoc();
    $totalRows = intval($countResult['total'] ?? 0);
}

$totalPages = max(1, ceil($totalRows / $limit));

if ($page > $totalPages) {
    $page = $totalPages;
    $offset = ($page - 1) * $limit;
}

/* Fetch messages */
$listSql = "
    SELECT
        cm.message_id,
        cm.customer_id,
        cm.name,
        cm.email,
        cm.phone,
        cm.message,
        cm.status,
        cm.admin_note,
        cm.created_at,
        cm.updated_at,
        CONCAT(COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) AS customer_name
    FROM tb_contact_message cm
    LEFT JOIN tb_customer c ON cm.customer_id = c.customer_id
    WHERE $whereSql
    ORDER BY 
        CASE 
            WHEN cm.status = 'new' THEN 1
            WHEN cm.status = 'read' THEN 2
            WHEN cm.status = 'replied' THEN 3
            WHEN cm.status = 'closed' THEN 4
            ELSE 5
        END,
        cm.created_at DESC
    LIMIT ? OFFSET ?
";

$listStmt = $conn->prepare($listSql);

$messageRows = [];
$messageIds = [];

if ($listStmt) {
    $listTypes = $types . "ii";
    $listParams = $params;
    $listParams[] = $limit;
    $listParams[] = $offset;

    bindStatementParams($listStmt, $listTypes, $listParams);

    $listStmt->execute();
    $messagesResult = $listStmt->get_result();

    while ($messageRow = $messagesResult->fetch_assoc()) {
        $messageRows[] = $messageRow;
        $messageIds[] = intval($messageRow['message_id']);
    }
}

/* Fetch replies for current page messages */
$replyMap = [];

if (!empty($messageIds)) {
    $placeholders = implode(',', array_fill(0, count($messageIds), '?'));

    $replySql = "
        SELECT
            cr.reply_id,
            cr.message_id,
            cr.reply_message,
            cr.is_read_by_customer,
            cr.created_at,
            CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) AS employee_name
        FROM tb_contact_reply cr
        LEFT JOIN tb_employee e ON cr.emp_id = e.emp_id
        WHERE cr.message_id IN ($placeholders)
        ORDER BY cr.created_at ASC
    ";

    $replyStmt = $conn->prepare($replySql);

    if ($replyStmt) {
        $replyTypes = str_repeat('i', count($messageIds));
        $replyParams = $messageIds;

        bindStatementParams($replyStmt, $replyTypes, $replyParams);

        $replyStmt->execute();
        $replyResult = $replyStmt->get_result();

        while ($replyRow = $replyResult->fetch_assoc()) {
            $replyMessageId = intval($replyRow['message_id']);

            if (!isset($replyMap[$replyMessageId])) {
                $replyMap[$replyMessageId] = [];
            }

            $replyMap[$replyMessageId][] = $replyRow;
        }
    }
}

$queryForPagination = $_GET;
unset($queryForPagination['page']);

$currentUrl = $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/admin/contact/contact_messages.php');

$startItem = ($totalRows > 0) ? $offset + 1 : 0;
$endItem = min($offset + $limit, $totalRows);
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ຂໍ້ຄວາມລູກຄ້າ - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/contact_messages.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

<div class="admin-wrapper">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="contact-admin-main">

        <div class="contact-admin-content">

            <section class="page-hero">
                <div>
                    <span class="hero-badge">
                        <i class="fa-solid fa-headset"></i>
                        Ecommerce Support Center
                    </span>

                    <h1>ສູນບໍລິການລູກຄ້າ</h1>

                    <p>
                        ຈັດການຂໍ້ຄວາມລູກຄ້າໃນຮູບແບບ Ticket.
                        ສາມາດອ່ານ, ຕອບກັບ, ບັນທຶກໝາຍເຫດ ແລະ ປ່ຽນສະຖານະໄດ້.
                    </p>
                </div>

                <div class="hero-icon">
                    <i class="fa-solid fa-headset"></i>
                </div>
            </section>

            <?php if ($flashStatus !== '' && $flashMessage !== ''): ?>
                <div class="admin-alert <?= $flashStatus === 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <i class="fa-solid <?= $flashStatus === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i>
                    <span><?= h($flashMessage); ?></span>
                </div>
            <?php endif; ?>

            <section class="stats-grid">

                <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php" class="stat-card <?= $status === 'all' ? 'active' : ''; ?>">
                    <div class="stat-icon total">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <div>
                        <span>ທັງໝົດ</span>
                        <strong><?= number_format($stats['total']); ?></strong>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php?status=new" class="stat-card <?= $status === 'new' ? 'active' : ''; ?>">
                    <div class="stat-icon new">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                    <div>
                        <span>ຂໍ້ຄວາມໃໝ່</span>
                        <strong><?= number_format($stats['new_count']); ?></strong>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php?status=read" class="stat-card <?= $status === 'read' ? 'active' : ''; ?>">
                    <div class="stat-icon read">
                        <i class="fa-solid fa-envelope-open"></i>
                    </div>
                    <div>
                        <span>ອ່ານແລ້ວ</span>
                        <strong><?= number_format($stats['read_count']); ?></strong>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php?status=replied" class="stat-card <?= $status === 'replied' ? 'active' : ''; ?>">
                    <div class="stat-icon replied">
                        <i class="fa-solid fa-reply"></i>
                    </div>
                    <div>
                        <span>ຕອບແລ້ວ</span>
                        <strong><?= number_format($stats['replied_count']); ?></strong>
                    </div>
                </a>

                <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php?status=closed" class="stat-card <?= $status === 'closed' ? 'active' : ''; ?>">
                    <div class="stat-icon closed">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div>
                        <span>ປິດງານ</span>
                        <strong><?= number_format($stats['closed_count']); ?></strong>
                    </div>
                </a>

            </section>

            <section class="filter-card">
                <form action="<?= BASE_URL ?>/admin/contact/contact_messages.php" method="GET">

                    <div class="filter-group search-group">
                        <label for="q">ຄົ້ນຫາ</label>
                        <div class="filter-input">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input
                                type="text"
                                id="q"
                                name="q"
                                value="<?= h($q); ?>"
                                placeholder="ລະຫັດ Ticket, ຊື່, Email, ເບີໂທ ຫຼື ຂໍ້ຄວາມ"
                            >
                        </div>
                    </div>

                    <div class="filter-group">
                        <label for="status">ສະຖານະ</label>
                        <select id="status" name="status">
                            <option value="all" <?= $status === 'all' ? 'selected' : ''; ?>>ທັງໝົດ</option>
                            <option value="new" <?= $status === 'new' ? 'selected' : ''; ?>>ຂໍ້ຄວາມໃໝ່</option>
                            <option value="read" <?= $status === 'read' ? 'selected' : ''; ?>>ອ່ານແລ້ວ</option>
                            <option value="replied" <?= $status === 'replied' ? 'selected' : ''; ?>>ຕອບກັບແລ້ວ</option>
                            <option value="closed" <?= $status === 'closed' ? 'selected' : ''; ?>>ປິດງານແລ້ວ</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="date_from">ຈາກວັນທີ</label>
                        <input type="date" id="date_from" name="date_from" value="<?= h($dateFrom); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="date_to">ຫາວັນທີ</label>
                        <input type="date" id="date_to" name="date_to" value="<?= h($dateTo); ?>">
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-btn">
                            <i class="fa-solid fa-filter"></i>
                            ກອງຂໍ້ມູນ
                        </button>

                        <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php" class="reset-btn">
                            <i class="fa-solid fa-rotate-left"></i>
                            ລ້າງ
                        </a>
                    </div>

                </form>
            </section>

            <section class="message-section">

                <div class="section-top">
                    <div>
                        <h2>ລາຍການ Ticket</h2>
                        <p>
                            ສະແດງ <?= number_format($startItem); ?> - <?= number_format($endItem); ?>
                            ຈາກ <?= number_format($totalRows); ?> ລາຍການ

                            <?php if ($status !== 'all'): ?>
                                · <?= h(statusLabel($status)); ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>

                <?php if (!empty($messageRows)): ?>

                    <div class="message-list">

                        <?php foreach ($messageRows as $row): ?>

                            <?php
                            $messageId = intval($row['message_id']);
                            $ticketCode = "TCK-" . str_pad($messageId, 5, "0", STR_PAD_LEFT);
                            $rowStatus = $row['status'] ?? 'new';
                            $customerName = trim($row['customer_name'] ?? '');
                            $replyList = $replyMap[$messageId] ?? [];
                            $replyCount = count($replyList);
                            $avatarLetter = !empty($row['name']) ? mb_substr($row['name'], 0, 1, 'UTF-8') : '?';
                            ?>

                            <article class="message-card <?= statusClass($rowStatus); ?>">

                                <div class="message-header">

                                    <div class="sender-block">
                                        <div class="sender-avatar">
                                            <?= h($avatarLetter); ?>
                                        </div>

                                        <div>
                                            <h3><?= h($row['name']); ?></h3>

                                            <div class="sender-meta">
                                                <a href="mailto:<?= h($row['email']); ?>">
                                                    <i class="fa-regular fa-envelope"></i>
                                                    <?= h($row['email']); ?>
                                                </a>

                                                <?php if (!empty($row['phone'])): ?>
                                                    <a href="tel:<?= h($row['phone']); ?>">
                                                        <i class="fa-solid fa-phone"></i>
                                                        <?= h($row['phone']); ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="message-status-area">

                                        <span class="ticket-code">
                                            <?= h($ticketCode); ?>
                                        </span>

                                        <span class="reply-count">
                                            <i class="fa-solid fa-comments"></i>
                                            <?= number_format($replyCount); ?> ຄຳຕອບ
                                        </span>

                                        <span class="status-pill <?= statusClass($rowStatus); ?>">
                                            <?= h(statusLabel($rowStatus)); ?>
                                        </span>

                                        <span class="message-date">
                                            <i class="fa-regular fa-clock"></i>
                                            <?= date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                        </span>
                                    </div>

                                </div>

                                <div class="customer-type">
                                    <?php if (!empty($row['customer_id'])): ?>
                                        <i class="fa-solid fa-user-check"></i>
                                        <span>
                                            ລູກຄ້າສະມາຊິກ
                                            <?php if ($customerName !== ''): ?>
                                                : <?= h($customerName); ?>
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <i class="fa-solid fa-user"></i>
                                        <span>ລູກຄ້າທົ່ວໄປ / ບໍ່ໄດ້ Login</span>
                                    <?php endif; ?>
                                </div>

                                <div class="message-body">
                                    <h4>
                                        <i class="fa-regular fa-message"></i>
                                        ຂໍ້ຄວາມຈາກລູກຄ້າ
                                    </h4>

                                    <p><?= nl2br(h($row['message'])); ?></p>
                                </div>

                                <?php if (!empty($replyList)): ?>
                                    <div class="reply-history">
                                        <h4>
                                            <i class="fa-solid fa-reply"></i>
                                            ປະຫວັດການຕອບກັບ
                                        </h4>

                                        <?php foreach ($replyList as $reply): ?>
                                            <div class="reply-item">
                                                <div class="reply-top">
                                                    <strong>
                                                        <?= h(trim($reply['employee_name']) !== '' ? trim($reply['employee_name']) : 'Admin'); ?>
                                                    </strong>

                                                    <span>
                                                        <?= date('d/m/Y H:i', strtotime($reply['created_at'])); ?>
                                                    </span>
                                                </div>

                                                <p><?= nl2br(h($reply['reply_message'])); ?></p>

                                                <div class="reply-read-status">
                                                    <?php if (intval($reply['is_read_by_customer']) === 1): ?>
                                                        <span class="reply-read">
                                                            <i class="fa-solid fa-eye"></i>
                                                            ລູກຄ້າອ່ານແລ້ວ
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="reply-unread">
                                                            <i class="fa-regular fa-eye-slash"></i>
                                                            ລູກຄ້າຍັງບໍ່ໄດ້ອ່ານ
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <form class="reply-form" action="<?= BASE_URL ?>/admin/contact/send_reply.php" method="POST">
                                    <input type="hidden" name="message_id" value="<?= $messageId; ?>">
                                    <input type="hidden" name="return_url" value="<?= h($currentUrl); ?>">

                                    <label for="reply_<?= $messageId; ?>">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        ຕອບກັບລູກຄ້າ
                                    </label>

                                    <textarea
                                        id="reply_<?= $messageId; ?>"
                                        name="reply_message"
                                        placeholder="ພິມຄຳຕອບກັບລູກຄ້າ..."
                                        maxlength="3000"
                                        required
                                    ></textarea>

                                    <div class="reply-bottom">
                                        <span>
                                            ຄຳຕອບນີ້ຈະຖືກບັນທຶກໃນລະບົບ ແລະ ລູກຄ້າຈະເຫັນໃນໜ້າຂໍ້ຄວາມຂອງຕົນ
                                        </span>

                                        <button type="submit">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            ຕອບກັບ
                                        </button>
                                    </div>
                                </form>

                                <div class="message-actions">

                                    <form class="status-form" action="<?= h($currentUrl); ?>" method="POST">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="message_id" value="<?= $messageId; ?>">
                                        <input type="hidden" name="return_url" value="<?= h($currentUrl); ?>">

                                        <label>ປ່ຽນສະຖານະ</label>

                                        <select name="status" onchange="this.form.submit()">
                                            <option value="new" <?= $rowStatus === 'new' ? 'selected' : ''; ?>>ຂໍ້ຄວາມໃໝ່</option>
                                            <option value="read" <?= $rowStatus === 'read' ? 'selected' : ''; ?>>ອ່ານແລ້ວ</option>
                                            <option value="replied" <?= $rowStatus === 'replied' ? 'selected' : ''; ?>>ຕອບກັບແລ້ວ</option>
                                            <option value="closed" <?= $rowStatus === 'closed' ? 'selected' : ''; ?>>ປິດງານແລ້ວ</option>
                                        </select>
                                    </form>

                                    <div class="quick-contact">
                                        <a href="mailto:<?= h($row['email']); ?>" class="contact-btn email-btn">
                                            <i class="fa-regular fa-envelope"></i>
                                            Email
                                        </a>

                                        <?php if (!empty($row['phone'])): ?>
                                            <a href="tel:<?= h($row['phone']); ?>" class="contact-btn phone-btn">
                                                <i class="fa-solid fa-phone"></i>
                                                Call
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                </div>

                                <form class="note-form" action="<?= h($currentUrl); ?>" method="POST">
                                    <input type="hidden" name="action" value="save_note">
                                    <input type="hidden" name="message_id" value="<?= $messageId; ?>">
                                    <input type="hidden" name="return_url" value="<?= h($currentUrl); ?>">

                                    <label for="note_<?= $messageId; ?>">
                                        <i class="fa-regular fa-note-sticky"></i>
                                        ໝາຍເຫດພາຍໃນ Admin
                                    </label>

                                    <textarea
                                        id="note_<?= $messageId; ?>"
                                        name="admin_note"
                                        placeholder="ພິມໝາຍເຫດສຳລັບທີມງານ..."
                                        maxlength="2000"
                                    ><?= h($row['admin_note'] ?? ''); ?></textarea>

                                    <div class="note-bottom">
                                        <span>
                                            ອັບເດດຫຼ້າສຸດ:
                                            <?= !empty($row['updated_at']) ? date('d/m/Y H:i', strtotime($row['updated_at'])) : '-'; ?>
                                        </span>

                                        <button type="submit">
                                            <i class="fa-solid fa-floppy-disk"></i>
                                            ບັນທຶກໝາຍເຫດ
                                        </button>
                                    </div>
                                </form>

                            </article>

                        <?php endforeach; ?>

                    </div>

                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">

                            <?php if ($page > 1): ?>
                                <a href="<?= h(pageUrl($page - 1, $queryForPagination)); ?>">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>

                            <?php
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            ?>

                            <?php if ($startPage > 1): ?>
                                <a href="<?= h(pageUrl(1, $queryForPagination)); ?>">1</a>

                                <?php if ($startPage > 2): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <a
                                    href="<?= h(pageUrl($i, $queryForPagination)); ?>"
                                    class="<?= $i === $page ? 'active' : ''; ?>"
                                >
                                    <?= $i; ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($endPage < $totalPages): ?>
                                <?php if ($endPage < $totalPages - 1): ?>
                                    <span class="pagination-dots">...</span>
                                <?php endif; ?>

                                <a href="<?= h(pageUrl($totalPages, $queryForPagination)); ?>">
                                    <?= $totalPages; ?>
                                </a>
                            <?php endif; ?>

                            <?php if ($page < $totalPages): ?>
                                <a href="<?= h(pageUrl($page + 1, $queryForPagination)); ?>">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>

                <?php else: ?>

                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fa-regular fa-envelope-open"></i>
                        </div>

                        <h3>ບໍ່ພົບຂໍ້ຄວາມ</h3>

                        <p>ຍັງບໍ່ມີຂໍ້ຄວາມຕາມເງື່ອນໄຂທີ່ຄົ້ນຫາ</p>

                        <a href="<?= BASE_URL ?>/admin/contact/contact_messages.php">
                            ເບິ່ງທັງໝົດ
                        </a>
                    </div>

                <?php endif; ?>

            </section>

        </div>

    </main>

</div>

</body>
</html>