<?php
include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: signup.php");
    exit();
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role = 'Admin';

if (
    empty($first_name) ||
    empty($last_name) ||
    empty($gender) ||
    empty($phone_number) ||
    empty($email) ||
    empty($password) ||
    empty($confirm_password)
) {
    header("Location: signup.php?error=ກະລຸນາປ້ອນຂໍ້ມູນໃຫ້ຄົບ");
    exit();
}

if ($password !== $confirm_password) {
    header("Location: signup.php?error=ລະຫັດຜ່ານບໍ່ກົງກັນ");
    exit();
}

$check = $conn->prepare("
    SELECT emp_id 
    FROM tb_employee 
    WHERE email = ? 
    LIMIT 1
");

$check->bind_param("s", $email);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    header("Location: signup.php?error=ອີເມວນີ້ຖືກໃຊ້ແລ້ວ");
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("
    INSERT INTO tb_employee
    (first_name, last_name, gender, phone_number, email, password, role)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "sssssss",
    $first_name,
    $last_name,
    $gender,
    $phone_number,
    $email,
    $hashedPassword,
    $role
);

if ($stmt->execute()) {
    header("Location: login.php?success=Admin account created");
    exit();
} else {
    header("Location: signup.php?error=ບໍ່ສາມາດສ້າງ Admin ໄດ້");
    exit();
}
?>