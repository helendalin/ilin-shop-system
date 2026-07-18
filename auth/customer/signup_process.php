<?php
session_start();
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.php");
    exit();
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$birth_date = trim($_POST['birth_date'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$address = trim($_POST['address'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (
    empty($first_name) ||
    empty($last_name) ||
    empty($gender) ||
    empty($birth_date) ||
    empty($phone_number) ||
    empty($address) ||
    empty($email) ||
    empty($password) ||
    empty($confirm_password)
) {
    header("Location: signup.php?error=Please fill all fields");
    exit();
}

if ($password !== $confirm_password) {
    header("Location: signup.php?error=Password does not match");
    exit();
}

$checkStmt = $conn->prepare("
    SELECT customer_id
    FROM tb_customer
    WHERE email = ?
    LIMIT 1
");

$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    header("Location: signup.php?error=Email already exists");
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO tb_customer
    (first_name, last_name, gender, birth_date, address, phone_number, email, password)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "ssssssss",
    $first_name,
    $last_name,
    $gender,
    $birth_date,
    $address,
    $phone_number,
    $email,
    $hashed_password
);

if ($stmt->execute()) {
    header("Location: login.php?success=Account created successfully");
    exit();
}

header("Location: signup.php?error=Signup failed");
exit();