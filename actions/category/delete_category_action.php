<?php
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../admin/category/category.php");
    exit();
}

$category_id = intval($_GET['id']);

/*
    Important:
    If this category is already used by products, MySQL may block delete
    because of foreign key.
*/

$stmt = $conn->prepare("DELETE FROM tb_category WHERE category_id = ?");
$stmt->bind_param("i", $category_id);

if ($stmt->execute()) {
    header("Location: ../../admin/category/category.php");
    exit();
}

header("Location: ../../admin/category/category.php?error=Cannot delete this category");
exit();
?>