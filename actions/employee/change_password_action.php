<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$emp_id = intval($_SESSION['emp_id'] ?? 0);

$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($emp_id <= 0) {
    header("Location: ../../auth/login.php");
    exit();
}

if (strlen($new_password) < 2) {
    header("Location: ../../admin/employee/change_password.php?error=short");
    exit();
}

if ($new_password !== $confirm_password) {
    header("Location: ../../admin/employee/change_password.php?error=not_match");
    exit();
}

$stmt = $conn->prepare("
    SELECT password
    FROM tb_employee
    WHERE emp_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$employee = $stmt->get_result()->fetch_assoc();

if (!$employee || !password_verify($current_password, $employee['password'])) {
    header("Location: ../../admin/employee/change_password.php?error=wrong_current");
    exit();
}

$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

$update = $conn->prepare("
    UPDATE tb_employee
    SET password = ?
    WHERE emp_id = ?
");
$update->bind_param("si", $hashedPassword, $emp_id);
$update->execute();

header("Location: ../../admin/employee/change_password.php?success=1");
exit();
?>