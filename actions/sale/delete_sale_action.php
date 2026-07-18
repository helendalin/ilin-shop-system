<?php
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../admin/sale/sale.php");
    exit();
}

$sale_id = intval($_GET['id']);

$conn->begin_transaction();

try {
    $detailStmt = $conn->prepare("
        SELECT product_id, qty
        FROM tb_sale_detail
        WHERE sale_id = ?
    ");
    $detailStmt->bind_param("i", $sale_id);
    $detailStmt->execute();
    $details = $detailStmt->get_result();

    $stockStmt = $conn->prepare("
        UPDATE tb_product
        SET qty = qty + ?
        WHERE product_id = ?
    ");

    while ($row = $details->fetch_assoc()) {
        $qty = intval($row['qty']);
        $product_id = intval($row['product_id']);

        $stockStmt->bind_param("ii", $qty, $product_id);
        $stockStmt->execute();
    }

    $deleteDetail = $conn->prepare("DELETE FROM tb_sale_detail WHERE sale_id = ?");
    $deleteDetail->bind_param("i", $sale_id);
    $deleteDetail->execute();

    $deleteSale = $conn->prepare("DELETE FROM tb_sale WHERE sale_id = ?");
    $deleteSale->bind_param("i", $sale_id);
    $deleteSale->execute();

    $conn->commit();

    header("Location: ../../admin/sale/sale.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();

    header("Location: ../../admin/sale/sale.php?error=Cannot delete sale");
    exit();
}
?>