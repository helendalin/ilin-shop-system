<?php
session_start();
include '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../admin/sale/create_sale.php");
    exit();
}

$customer_id = intval($_POST['customer_id'] ?? 0);
$emp_id = intval($_SESSION['emp_id'] ?? 0);

$product_ids = $_POST['product_id'] ?? [];
$qtys = $_POST['qty'] ?? [];
$prices = $_POST['price'] ?? [];

if ($customer_id <= 0 || $emp_id <= 0 || empty($product_ids)) {
    header("Location: ../../admin/sale/create_sale.php?error=Please fill all fields");
    exit();
}

$conn->begin_transaction();

try {
    $total_amount = 0;

    for ($i = 0; $i < count($product_ids); $i++) {
        $qty = intval($qtys[$i]);
        $price = floatval($prices[$i]);

        if ($qty > 0 && $price >= 0) {
            $total_amount += $qty * $price;
        }
    }

    $status = 'completed';

    $saleStmt = $conn->prepare("
        INSERT INTO tb_sale (customer_id, emp_id, total_amount, status)
        VALUES (?, ?, ?, ?)
    ");

    $saleStmt->bind_param(
        "iids",
        $customer_id,
        $emp_id,
        $total_amount,
        $status
    );

    $saleStmt->execute();

    $sale_id = $conn->insert_id;

    $detailStmt = $conn->prepare("
        INSERT INTO tb_sale_detail (sale_id, product_id, qty, price, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stockCheckStmt = $conn->prepare("
        SELECT qty 
        FROM tb_product 
        WHERE product_id = ? 
        LIMIT 1
    ");

    $stockUpdateStmt = $conn->prepare("
        UPDATE tb_product
        SET qty = qty - ?
        WHERE product_id = ?
    ");

    for ($i = 0; $i < count($product_ids); $i++) {
        $product_id = intval($product_ids[$i]);
        $qty = intval($qtys[$i]);
        $price = floatval($prices[$i]);

        if ($product_id <= 0 || $qty <= 0 || $price < 0) {
            continue;
        }

        $stockCheckStmt->bind_param("i", $product_id);
        $stockCheckStmt->execute();
        $stockResult = $stockCheckStmt->get_result();
        $stockRow = $stockResult->fetch_assoc();

        if (!$stockRow || $stockRow['qty'] < $qty) {
            throw new Exception("Not enough stock");
        }

        $subtotal = $qty * $price;

        $detailStmt->bind_param(
            "iiidd",
            $sale_id,
            $product_id,
            $qty,
            $price,
            $subtotal
        );

        $detailStmt->execute();

        $stockUpdateStmt->bind_param("ii", $qty, $product_id);
        $stockUpdateStmt->execute();
    }

    $conn->commit();

    header("Location: ../../admin/sale/sale_detail.php?id=" . $sale_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();

    header("Location: ../../admin/sale/create_sale.php?error=Stock is not enough or sale failed");
    exit();
}
?>