<?php
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../admin/employee/employee.php");
    exit();
}

$emp_id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM tb_employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();

header("Location: ../../admin/employee/employee.php");
exit();
?>