<?php
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../admin/unit/unit.php");
    exit();
}

$unit_id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM tb_unit WHERE unit_id = ?");
$stmt->bind_param("i", $unit_id);

if ($stmt->execute()) {
    header("Location: ../../admin/unit/unit.php");
    exit();
}

header("Location: ../../admin/unit/unit.php?error=Cannot delete this unit");
exit();
?>