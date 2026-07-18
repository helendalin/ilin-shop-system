<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/category/category.php");
    exit();
}

$category_id = intval($_POST['category_id'] ?? 0);
$category_name = trim($_POST['category_name'] ?? '');

if ($category_id <= 0 || empty($category_name)) {
    header("Location: ../../admin/category/edit_category.php?id=$category_id&error=Please fill category name");
    exit();
}

$check = $conn->prepare("
    SELECT category_id
    FROM tb_category
    WHERE category_name = ?
    AND category_id != ?
    LIMIT 1
");
$check->bind_param("si", $category_name, $category_id);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    header("Location: ../../admin/category/edit_category.php?id=$category_id&error=Category already exists");
    exit();
}

$stmt = $conn->prepare("
    UPDATE tb_category
    SET category_name = ?
    WHERE category_id = ?
");
$stmt->bind_param("si", $category_name, $category_id);
$stmt->execute();

header("Location: ../../admin/category/category.php");
exit();
?>