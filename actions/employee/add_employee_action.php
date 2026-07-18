<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/employee/add_employee.php");
    exit();
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$birth_date = trim($_POST['birth_date'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$role = trim($_POST['role'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');

if (
    empty($first_name) || empty($last_name) || empty($gender) ||
    empty($birth_date) || empty($address) || empty($phone_number) ||
    empty($role) || empty($email) || empty($password) || empty($confirm_password)
) {
    header("Location: ../../admin/employee/add_employee.php?error=Please fill all fields");
    exit();
}

if ($password !== $confirm_password) {
    header("Location: ../../admin/employee/add_employee.php?error=Passwords do not match");
    exit();
}

$check = $conn->prepare("SELECT emp_id FROM tb_employee WHERE email = ? LIMIT 1");
$check->bind_param("s", $email);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    header("Location: ../../admin/employee/add_employee.php?error=Email already exists");
    exit();
}

$hashedPassword = md5($password);

$sql = "INSERT INTO tb_employee 
(first_name, last_name, gender, birth_date, address, phone_number, role, email, password)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "sssssssss",
    $first_name,
    $last_name,
    $gender,
    $birth_date,
    $address,
    $phone_number,
    $role,
    $email,
    $hashedPassword
);

if ($stmt->execute()) {
    header("Location: ../../admin/employee/employee.php");
    exit();
}

header("Location: ../../admin/employee/add_employee.php?error=Failed to add employee");
exit();
?>