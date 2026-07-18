<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header("Location: ../../admin/supplier/supplier.php");
    exit();
}

$supplier_id = intval($_POST['supplier_id']);
$supplier_name = trim($_POST['supplier_name']);
$phone_number = trim($_POST['phone_number']);
$address = trim($_POST['address']);

$sql = "
    UPDATE tb_supplier
    SET
        supplier_name = ?,
        phone_number = ?,
        address = ?
    WHERE supplier_id = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "sssi",
    $supplier_name,
    $phone_number,
    $address,
    $supplier_id
);

$stmt->execute();

header("Location: ../../admin/supplier/supplier.php");
exit();
?>