
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config/db.php';

/*
    Admin / Employee pages only.
    Customer login cannot open admin pages.
*/

if (
    !isset($_SESSION['user_type']) ||
    !in_array($_SESSION['user_type'], ['admin', 'employee'], true) ||
    !isset($_SESSION['emp_id']) ||
    intval($_SESSION['emp_id']) <= 0
) {
    header("Location: " . BASE_URL . "/auth/login.php?error=Please login first");
    exit();
}
?>