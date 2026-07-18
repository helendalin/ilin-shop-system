<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ../../admin/supplier/add_supplier.php");
    exit();
}

$supplier_name = trim($_POST['supplier_name'] ?? '');
$phone_number = trim($_POST['phone_number'] ?? '');
$address = trim($_POST['address'] ?? '');

if (
    empty($supplier_name) ||
    empty($phone_number) ||
    empty($address)
) {

    header("
        Location:
        ../../admin/supplier/add_supplier.php?error=Please fill all fields
    ");

    exit();
}

$sql = "
    INSERT INTO tb_supplier
    (
        supplier_name,
        phone_number,
        address
    )
    VALUES (?, ?, ?)
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sss",
    $supplier_name,
    $phone_number,
    $address
);

$stmt->execute();

header("Location: ../../admin/supplier/supplier.php");
exit();
?>