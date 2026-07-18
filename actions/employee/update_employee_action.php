<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/employee/employee.php");
    exit();
}

$emp_id = intval($_POST['emp_id']);
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$birth_date = trim($_POST['birth_date'] ?? '');
$address = trim($_POST['address'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$role = trim($_POST['role'] ?? '');
$email = trim($_POST['email'] ?? '');

$sql = "UPDATE tb_employee 
        SET first_name=?, last_name=?, gender=?, birth_date=?, address=?, phone_number=?, role=?, email=?
        WHERE emp_id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ssssssssi",
    $first_name,
    $last_name,
    $gender,
    $birth_date,
    $address,
    $phone_number,
    $role,
    $email,
    $emp_id
);

$stmt->execute();

header("Location: ../../admin/employee/employee.php");
exit();
?>