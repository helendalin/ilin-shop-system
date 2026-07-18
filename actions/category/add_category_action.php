<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/category/add_category.php");
    exit();
}

$category_name = trim($_POST['category_name'] ?? '');

if (empty($category_name)) {
    header("Location: ../../admin/category/add_category.php?error=Please fill category name");
    exit();
}

$check = $conn->prepare("SELECT category_id FROM tb_category WHERE category_name = ? LIMIT 1");
$check->bind_param("s", $category_name);
$check->execute();
$checkResult = $check->get_result();

if ($checkResult->num_rows > 0) {
    header("Location: ../../admin/category/add_category.php?error=Category already exists");
    exit();
}

$stmt = $conn->prepare("INSERT INTO tb_category (category_name) VALUES (?)");
$stmt->bind_param("s", $category_name);

if ($stmt->execute()) {
    header("Location: ../../admin/category/category.php");
    exit();
}

header("Location: ../../admin/category/add_category.php?error=Failed to add category");
exit();
?>