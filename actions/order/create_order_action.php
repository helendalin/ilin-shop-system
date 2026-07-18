<?php
session_start();
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/order/create_order.php");
    exit();
}

$supplier_id = intval($_POST['supplier_id'] ?? 0);
$emp_id = intval($_SESSION['emp_id'] ?? 0);

$product_ids = $_POST['product_id'] ?? [];
$qtys = $_POST['qty'] ?? [];
$prices = $_POST['price'] ?? [];

if ($supplier_id <= 0 || $emp_id <= 0 || empty($product_ids)) {
    header("Location: ../../admin/order/create_order.php?error=Please fill all fields");
    exit();
}

$conn->begin_transaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO tb_order (emp_id, supplier_id)
        VALUES (?, ?)
    ");
    $stmt->bind_param("ii", $emp_id, $supplier_id);
    $stmt->execute();

    $order_id = $conn->insert_id;

    $detailStmt = $conn->prepare("
        INSERT INTO tb_order_detail (order_id, product_id, qty, price)
        VALUES (?, ?, ?, ?)
    ");

    for ($i = 0; $i < count($product_ids); $i++) {
        $product_id = intval($product_ids[$i]);
        $qty = intval($qtys[$i]);
        $price = floatval($prices[$i]);

        if ($product_id <= 0 || $qty <= 0 || $price < 0) {
            continue;
        }

        $detailStmt->bind_param("iiid", $order_id, $product_id, $qty, $price);
        $detailStmt->execute();
    }

    $conn->commit();

    header("Location: ../../admin/order/order_detail.php?id=" . $order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();

    header("Location: ../../admin/order/create_order.php?error=Failed to create order");
    exit();
}
?>