<?php
session_start();
include '../config/db.php';

/* Customer must login */
if (!isset($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Please login first before buying products");
    exit();
}

/* Allow POST only */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/customer/home.php");
    exit();
}

$product_id = intval($_POST['product_id'] ?? 0);
$qty = intval($_POST['qty'] ?? 1);

if ($product_id <= 0) {
    header("Location: " . BASE_URL . "/customer/products.php");
    exit();
}

/* Minimum quantity */
if ($qty < 1) {
    $qty = 1;
}

/* Get product */
$stmt = $conn->prepare("
    SELECT product_id, product_name, price, image, qty
    FROM tb_product
    WHERE product_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: " . BASE_URL . "/customer/products.php");
    exit();
}

$product = $result->fetch_assoc();

/* Out of stock */
if ($product['qty'] <= 0) {
    header("Location: " . BASE_URL . "/customer/product_detail.php?id=$product_id&error=out_of_stock");
    exit();
}

/* Create cart */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* Existing quantity in cart */
$currentQty = $_SESSION['cart'][$product_id]['qty'] ?? 0;

/* Total after adding */
$newQty = $currentQty + $qty;

/* Prevent exceeding stock */
if ($newQty > $product['qty']) {
    header("Location: " . BASE_URL . "/customer/product_detail.php?id=$product_id&error=max_stock");
    exit();
}

/* Add or update cart */
if (isset($_SESSION['cart'][$product_id])) {

    $_SESSION['cart'][$product_id]['qty'] = $newQty;

} else {

    $_SESSION['cart'][$product_id] = [
        'product_id'   => $product['product_id'],
        'product_name' => $product['product_name'],
        'price'        => $product['price'],
        'image'        => $product['image'],
        'qty'          => $qty
    ];

}

/* Redirect to cart */
header("Location: " . BASE_URL . "/customer/cart.php");
exit();