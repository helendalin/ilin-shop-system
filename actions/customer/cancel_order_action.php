<?php
include_once __DIR__ . '/../../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Customer must login first */
if (!isset($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Please login first");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/customer/order_history.php");
    exit();
}

$customerId = intval($_SESSION['customer_id']);
$saleId = intval($_POST['sale_id'] ?? 0);

if ($saleId <= 0) {
    header("Location: " . BASE_URL . "/customer/order_history.php?error=invalid_order");
    exit();
}

$conn->begin_transaction();

try {
    /*
        Lock this order first.
        This prevents double cancel and double stock return.
    */
    $orderStmt = $conn->prepare("
        SELECT sale_id, customer_id, status, payment_status
        FROM tb_sale
        WHERE sale_id = ?
        AND customer_id = ?
        LIMIT 1
        FOR UPDATE
    ");

    if (!$orderStmt) {
        throw new Exception("Cannot prepare order query");
    }

    $orderStmt->bind_param("ii", $saleId, $customerId);
    $orderStmt->execute();
    $order = $orderStmt->get_result()->fetch_assoc();

    if (!$order) {
        throw new Exception("Order not found");
    }

    $orderStatus = $order['status'] ?? '';
    $paymentStatus = $order['payment_status'] ?? '';

    /*
        Customer can cancel only pending order.
        If paid or verified, customer should contact admin instead.
    */
    $paidStatuses = ['paid', 'verified'];

    if ($orderStatus !== 'pending' || in_array($paymentStatus, $paidStatuses, true)) {
        $conn->rollback();
        header("Location: " . BASE_URL . "/customer/order_detail.php?sale_id=" . $saleId . "&error=cannot_cancel");
        exit();
    }

    /*
        Return stock back to product table.
        Example:
        Product stock now = 8
        Customer order qty = 2
        After cancel = 10
    */
    $restoreStockStmt = $conn->prepare("
        UPDATE tb_product p
        INNER JOIN tb_sale_detail sd ON p.product_id = sd.product_id
        SET p.qty = p.qty + sd.qty
        WHERE sd.sale_id = ?
    ");

    if (!$restoreStockStmt) {
        throw new Exception("Cannot prepare stock restore");
    }

    $restoreStockStmt->bind_param("i", $saleId);

    if (!$restoreStockStmt->execute()) {
        throw new Exception("Cannot restore stock");
    }

    /*
        Change order status to cancelled.
        This must happen after stock restore.
    */
    $cancelStmt = $conn->prepare("
        UPDATE tb_sale
        SET status = 'cancelled'
        WHERE sale_id = ?
        AND customer_id = ?
        AND status = 'pending'
        LIMIT 1
    ");

    if (!$cancelStmt) {
        throw new Exception("Cannot prepare cancel order");
    }

    $cancelStmt->bind_param("ii", $saleId, $customerId);

    if (!$cancelStmt->execute()) {
        throw new Exception("Cannot cancel order");
    }

    if ($cancelStmt->affected_rows === 0) {
        throw new Exception("Order already changed");
    }

    $conn->commit();

    header("Location: " . BASE_URL . "/customer/order_detail.php?sale_id=" . $saleId . "&success=cancelled");
    exit();

} catch (Exception $e) {
    $conn->rollback();

    header("Location: " . BASE_URL . "/customer/order_detail.php?sale_id=" . $saleId . "&error=cancel_failed");
    exit();
}
?>