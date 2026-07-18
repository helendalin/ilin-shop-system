<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/product/product.php");
    exit();
}

$product_id = intval($_POST['product_id'] ?? 0);
$product_name = trim($_POST['product_name'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$unit_id = intval($_POST['unit_id'] ?? 0);
$qty = intval($_POST['qty'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');
$oldImage = trim($_POST['old_image'] ?? '');
$imageName = $oldImage;

if ($product_id <= 0 || empty($product_name) || $category_id <= 0 || $unit_id <= 0) {
    header("Location: ../../admin/product/edit_product.php?id=$product_id&error=Please fill required fields");
    exit();
}

if (!empty($_FILES['image']['name'])) {
    $uploadDir = '../../assets/images/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = $_FILES['image']['name'];
    $fileTmp = $_FILES['image']['tmp_name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    if (!in_array($fileExt, $allowed)) {
        header("Location: ../../admin/product/edit_product.php?id=$product_id&error=Invalid image type");
        exit();
    }

    $imageName = time() . '_' . rand(1000, 9999) . '.' . $fileExt;

    if (move_uploaded_file($fileTmp, $uploadDir . $imageName)) {
        if (!empty($oldImage) && file_exists($uploadDir . $oldImage)) {
            unlink($uploadDir . $oldImage);
        }
    }
}

$sql = "UPDATE tb_product
        SET category_id=?, unit_id=?, product_name=?, qty=?, price=?, description=?, image=?
        WHERE product_id=?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisidssi",
    $category_id,
    $unit_id,
    $product_name,
    $qty,
    $price,
    $description,
    $imageName,
    $product_id
);

$stmt->execute();

header("Location: ../../admin/product/product.php");
exit();
?>