<?php
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: ../../admin/order/order.php");
    exit();
}

$order_id = intval($_GET['id']);

$conn->begin_transaction();

try {
    $stmt1 = $conn->prepare("DELETE FROM tb_order_detail WHERE order_id = ?");
    $stmt1->bind_param("i", $order_id);
    $stmt1->execute();

    $stmt2 = $conn->prepare("DELETE FROM tb_order WHERE order_id = ?");
    $stmt2->bind_param("i", $order_id);
    $stmt2->execute();

    $conn->commit();

    header("Location: ../../admin/order/order.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();

    header("Location: ../../admin/order/order.php?error=Cannot delete order");
    exit();
}
?>