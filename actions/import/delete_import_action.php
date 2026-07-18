<?php
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../admin/import/import.php");
    exit();
}

$import_id = intval($_GET['id']);

$conn->begin_transaction();

try {
    $detailStmt = $conn->prepare("
        SELECT product_id, qty
        FROM tb_import_detail
        WHERE import_id = ?
    ");
    $detailStmt->bind_param("i", $import_id);
    $detailStmt->execute();
    $details = $detailStmt->get_result();

    $stockStmt = $conn->prepare("
        UPDATE tb_product
        SET qty = qty - ?
        WHERE product_id = ?
    ");

    while ($row = $details->fetch_assoc()) {
        $qty = intval($row['qty']);
        $product_id = intval($row['product_id']);

        $stockStmt->bind_param("ii", $qty, $product_id);
        $stockStmt->execute();
    }

    $deleteDetail = $conn->prepare("DELETE FROM tb_import_detail WHERE import_id = ?");
    $deleteDetail->bind_param("i", $import_id);
    $deleteDetail->execute();

    $deleteImport = $conn->prepare("DELETE FROM tb_import WHERE import_id = ?");
    $deleteImport->bind_param("i", $import_id);
    $deleteImport->execute();

    $conn->commit();

    header("Location: ../../admin/import/import.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();

    header("Location: ../../admin/import/import.php?error=Cannot delete import");
    exit();
}
?>