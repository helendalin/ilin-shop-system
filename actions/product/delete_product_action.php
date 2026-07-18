<?php
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../admin/product/product.php");
    exit();
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT image FROM tb_product WHERE product_id = ? LIMIT 1");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

$image = '';

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $image = $row['image'];
}

$delete = $conn->prepare("DELETE FROM tb_product WHERE product_id = ?");
$delete->bind_param("i", $product_id);

if ($delete->execute()) {
    $uploadDir = '../../assets/images/';

    if (!empty($image) && file_exists($uploadDir . $image)) {
        unlink($uploadDir . $image);
    }

    header("Location: ../../admin/product/product.php");
    exit();
}

header("Location: ../../admin/product/product.php?error=Cannot delete product");
exit();
?>