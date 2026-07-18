<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/unit/add_unit.php");
    exit();
}

$unit_name = trim($_POST['unit_name'] ?? '');

if (empty($unit_name)) {
    header("Location: ../../admin/unit/add_unit.php?error=Please fill unit name");
    exit();
}

$check = $conn->prepare("SELECT unit_id FROM tb_unit WHERE unit_name = ? LIMIT 1");
$check->bind_param("s", $unit_name);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    header("Location: ../../admin/unit/add_unit.php?error=Unit already exists");
    exit();
}

$stmt = $conn->prepare("INSERT INTO tb_unit (unit_name) VALUES (?)");
$stmt->bind_param("s", $unit_name);

if ($stmt->execute()) {
    header("Location: ../../admin/unit/unit.php");
    exit();
}

header("Location: ../../admin/unit/add_unit.php?error=Failed to add unit");
exit();
?>