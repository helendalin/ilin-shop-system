<?php
session_start();
include '../config/db.php';

if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);

    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
    }
}

header("Location: " . BASE_URL . "/customer/cart.php");
exit();
?>