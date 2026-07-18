<?php
include '../../config/db.php';
include '../../includes/session_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/admin/contact/contact_messages.php");
    exit();
}

$message_id = intval($_POST['message_id'] ?? 0);
$reply_message = trim($_POST['reply_message'] ?? '');
$return_url = $_POST['return_url'] ?? (BASE_URL . "/admin/contact/contact_messages.php");

/* Protect redirect URL */
if (strpos($return_url, "\n") !== false || strpos($return_url, "\r") !== false) {
    $return_url = BASE_URL . "/admin/contact/contact_messages.php";
}

if ($message_id <= 0) {
    $_SESSION['contact_admin_flash'] = [
        'status' => 'error',
        'message' => 'ບໍ່ພົບລະຫັດຂໍ້ຄວາມ'
    ];

    header("Location: " . $return_url);
    exit();
}

if ($reply_message === '') {
    $_SESSION['contact_admin_flash'] = [
        'status' => 'error',
        'message' => 'ກະລຸນາພິມຄຳຕອບກ່ອນ'
    ];

    header("Location: " . $return_url);
    exit();
}

if (mb_strlen($reply_message, 'UTF-8') > 3000) {
    $_SESSION['contact_admin_flash'] = [
        'status' => 'error',
        'message' => 'ຄຳຕອບຍາວເກີນໄປ'
    ];

    header("Location: " . $return_url);
    exit();
}

$emp_id = isset($_SESSION['emp_id']) ? intval($_SESSION['emp_id']) : null;

/* Check original message exists */
$checkStmt = $conn->prepare("
    SELECT message_id
    FROM tb_contact_message
    WHERE message_id = ?
    LIMIT 1
");

if (!$checkStmt) {
    $_SESSION['contact_admin_flash'] = [
        'status' => 'error',
        'message' => 'ລະບົບມີບັນຫາ ບໍ່ສາມາດກວດສອບຂໍ້ຄວາມໄດ້'
    ];

    header("Location: " . $return_url);
    exit();
}

$checkStmt->bind_param("i", $message_id);
$checkStmt->execute();
$messageRow = $checkStmt->get_result()->fetch_assoc();

if (!$messageRow) {
    $_SESSION['contact_admin_flash'] = [
        'status' => 'error',
        'message' => 'ບໍ່ພົບຂໍ້ຄວາມນີ້'
    ];

    header("Location: " . $return_url);
    exit();
}

/* Save admin reply */
$replyStmt = $conn->prepare("
    INSERT INTO tb_contact_reply
        (message_id, emp_id, reply_message, is_read_by_customer)
    VALUES
        (?, ?, ?, 0)
");

if (!$replyStmt) {
    $_SESSION['contact_admin_flash'] = [
        'status' => 'error',
        'message' => 'ບໍ່ສາມາດກຽມບັນທຶກຄຳຕອບໄດ້'
    ];

    header("Location: " . $return_url);
    exit();
}

$replyStmt->bind_param("iis", $message_id, $emp_id, $reply_message);

if ($replyStmt->execute()) {
    /* Update original message status */
    $updateStmt = $conn->prepare("
        UPDATE tb_contact_message
        SET status = 'replied',
            updated_at = CURRENT_TIMESTAMP
        WHERE message_id = ?
        LIMIT 1
    ");

    if ($updateStmt) {
        $updateStmt->bind_param("i", $message_id);
        $updateStmt->execute();
    }

    $_SESSION['contact_admin_flash'] = [
        'status' => 'success',
        'message' => 'ຕອບກັບລູກຄ້າສຳເລັດ'
    ];
} else {
    $_SESSION['contact_admin_flash'] = [
        'status' => 'error',
        'message' => 'ບໍ່ສາມາດບັນທຶກຄຳຕອບໄດ້'
    ];
}

header("Location: " . $return_url);
exit();
?>