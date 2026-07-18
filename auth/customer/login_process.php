<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/auth/customer/login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Please fill all fields");
    exit();
}

/*
    CUSTOMER LOGIN ONLY
    Do not touch admin session here.
    Do not set user_type = customer.
*/
$stmt = $conn->prepare("
    SELECT customer_id, first_name, last_name, email, password
    FROM tb_customer
    WHERE email = ?
    LIMIT 1
");

if (!$stmt) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=System error, please try again");
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Email or password is incorrect");
    exit();
}

$customer = $result->fetch_assoc();

if (!password_verify($password, $customer['password'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Email or password is incorrect");
    exit();
}

session_regenerate_id(true);

/*
    Save customer session only.
    Do NOT set $_SESSION['user_type'] = 'customer';
    Do NOT unset admin session.
*/
$_SESSION['customer_id'] = intval($customer['customer_id']);
$_SESSION['customer_name'] = trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''));
$_SESSION['customer_email'] = $customer['email'];

header("Location: " . BASE_URL . "/customer/home.php");
exit();
?>