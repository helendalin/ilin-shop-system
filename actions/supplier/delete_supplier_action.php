<?php
include '../../config/db.php';

if (!isset($_GET['id'])) {

    header("Location: ../../admin/supplier/supplier.php");
    exit();
}

$supplier_id = intval($_GET['id']);

$stmt = $conn->prepare("
    DELETE FROM tb_supplier
    WHERE supplier_id = ?
");

$stmt->bind_param("i", $supplier_id);
$stmt->execute();

header("Location: ../../admin/supplier/supplier.php");
exit();
?>