<?php
session_start();
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/import/create_import.php");
    exit();
}

$order_id = intval($_POST['order_id'] ?? 0);
$emp_id = intval($_SESSION['emp_id'] ?? 0);

$product_ids = $_POST['product_id'] ?? [];
$qtys = $_POST['qty'] ?? [];
$cost_prices = $_POST['cost_price'] ?? [];

if ($order_id <= 0 || $emp_id <= 0 || empty($product_ids)) {
    header("Location: ../../admin/import/create_import.php?error=Please select order");
    exit();
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO tb_import (order_id, emp_id)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $order_id, $emp_id);
    $stmt->execute();

    $import_id = $conn->insert_id;

    $detailStmt = $conn->prepare("
        INSERT INTO tb_import_detail (import_id, product_id, qty, cost_price)
        VALUES (?, ?, ?, ?)
    ");

    $stockStmt = $conn->prepare("
        UPDATE tb_product
        SET qty = qty + ?
        WHERE product_id = ?
    ");

    for ($i = 0; $i < count($product_ids); $i++) {
        $product_id = intval($product_ids[$i]);
        $qty = intval($qtys[$i]);
        $cost_price = floatval($cost_prices[$i]);

        if ($product_id <= 0 || $qty <= 0 || $cost_price < 0) {
            continue;
        }

        $detailStmt->bind_param("iiid", $import_id, $product_id, $qty, $cost_price);
        $detailStmt->execute();

        $stockStmt->bind_param("ii", $qty, $product_id);
        $stockStmt->execute();
    }

    $conn->commit();

    header("Location: ../../admin/import/import_detail.php?id=" . $import_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();

    header("Location: ../../admin/import/create_import.php?error=Failed to create import");
    exit();
}
?>