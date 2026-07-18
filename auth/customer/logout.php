<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../config/db.php';

/*
    Customer logout only.
    Do NOT clear admin / employee session.
*/
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);
unset($_SESSION['customer_email']);
unset($_SESSION['cus_id']);

header("Location: " . BASE_URL . "/auth/customer/login.php");
exit();
?>