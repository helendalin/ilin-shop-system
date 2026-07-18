<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$sale_id = intval($_GET['id'] ?? 0);
$status = trim($_GET['status'] ?? '');
$emp_id = intval($_SESSION['emp_id'] ?? 0);

if ($sale_id <= 0 || empty($status)) {
    header("Location: ../../admin/sale/sale.php");
    exit();
}

$allowed = ['paid', 'packing', 'shipping', 'completed', 'cancelled'];

if (!in_array($status, $allowed)) {
    header("Location: ../../admin/sale/sale.php");
    exit();
}

/* Check current sale status first */
$checkStmt = $conn->prepare("
    SELECT status, payment_status
    FROM tb_sale
    WHERE sale_id = ?
    LIMIT 1
");
$checkStmt->bind_param("i", $sale_id);
$checkStmt->execute();
$currentSale = $checkStmt->get_result()->fetch_assoc();

if (!$currentSale) {
    header("Location: ../../admin/sale/sale.php");
    exit();
}

/* If already cancelled, do not allow any update */
if (($currentSale['status'] ?? '') === 'cancelled') {
    header("Location: ../../admin/sale/sale_detail.php?id=" . $sale_id);
    exit();
}

/* If already paid, do not allow cancel */
if ($status === 'cancelled' && ($currentSale['payment_status'] ?? '') === 'paid') {
    header("Location: ../../admin/sale/sale_detail.php?id=" . $sale_id . "&error=cannot_cancel_paid_order");
    exit();
}

$conn->begin_transaction();

try {

    if ($status === 'paid') {
        $stmt = $conn->prepare("
            UPDATE tb_sale
            SET payment_status = 'paid',
                approved_by = ?,
                approved_at = NOW()
            WHERE sale_id = ?
        ");
        $stmt->bind_param("ii", $emp_id, $sale_id);
        $stmt->execute();
    }

    if (in_array($status, ['packing', 'shipping', 'completed'])) {
        $stmt = $conn->prepare("
            UPDATE tb_sale
            SET status = ?
            WHERE sale_id = ?
        ");
        $stmt->bind_param("si", $status, $sale_id);
        $stmt->execute();
    }

    if ($status === 'cancelled') {
        $saleItems = $conn->prepare("
            SELECT product_id, qty
            FROM tb_sale_detail
            WHERE sale_id = ?
        ");
        $saleItems->bind_param("i", $sale_id);
        $saleItems->execute();
        $items = $saleItems->get_result();

        while ($item = $items->fetch_assoc()) {
            $restoreStock = $conn->prepare("
                UPDATE tb_product
                SET qty = qty + ?
                WHERE product_id = ?
            ");
            $restoreStock->bind_param("ii", $item['qty'], $item['product_id']);
            $restoreStock->execute();
        }

        $stmt = $conn->prepare("
            UPDATE tb_sale
            SET status = 'cancelled'
            WHERE sale_id = ?
        ");
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
    }

    $conn->commit();

} catch (Exception $e) {
    $conn->rollback();
}

header("Location: ../../admin/sale/sale_detail.php?id=" . $sale_id);
exit();
?>