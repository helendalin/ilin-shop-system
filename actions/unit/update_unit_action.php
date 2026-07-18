<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/unit/unit.php");
    exit();
}

$unit_id = intval($_POST['unit_id'] ?? 0);
$unit_name = trim($_POST['unit_name'] ?? '');

if ($unit_id <= 0 || empty($unit_name)) {
    header("Location: ../../admin/unit/edit_unit.php?id=$unit_id&error=Please fill unit name");
    exit();
}

$check = $conn->prepare("
    SELECT unit_id
    FROM tb_unit
    WHERE unit_name = ?
    AND unit_id != ?
    LIMIT 1
");
$check->bind_param("si", $unit_name, $unit_id);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    header("Location: ../../admin/unit/edit_unit.php?id=$unit_id&error=Unit already exists");
    exit();
}

$stmt = $conn->prepare("
    UPDATE tb_unit
    SET unit_name = ?
    WHERE unit_id = ?
");
$stmt->bind_param("si", $unit_name, $unit_id);
$stmt->execute();

header("Location: ../../admin/unit/unit.php");
exit();
?>