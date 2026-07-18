<?php
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/product/add_product.php");
    exit();
}

$product_name = trim($_POST['product_name'] ?? '');
$category_id = intval($_POST['category_id'] ?? 0);
$unit_id = intval($_POST['unit_id'] ?? 0);
$qty = intval($_POST['qty'] ?? 0);
$price = floatval($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');
$imageName = '';

if (empty($product_name) || $category_id <= 0 || $unit_id <= 0 || $price < 0) {
    header("Location: ../../admin/product/add_product.php?error=Please fill all required fields");
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
        header("Location: ../../admin/product/add_product.php?error=Invalid image type");
        exit();
    }

    $imageName = time() . '_' . rand(1000, 9999) . '.' . $fileExt;
    move_uploaded_file($fileTmp, $uploadDir . $imageName);
}

$sql = "INSERT INTO tb_product
(category_id, unit_id, product_name, qty, price, description, image)
VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "iisidss",
    $category_id,
    $unit_id,
    $product_name,
    $qty,
    $price,
    $description,
    $imageName
);

if ($stmt->execute()) {
    header("Location: ../../admin/product/product.php");
    exit();
}

header("Location: ../../admin/product/add_product.php?error=Failed to add product");
exit();
?>