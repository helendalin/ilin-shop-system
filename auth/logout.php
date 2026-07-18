<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config/db.php';

/*
    Admin / employee logout only.
    Do NOT clear customer session.
*/
unset($_SESSION['user_type']);
unset($_SESSION['emp_id']);
unset($_SESSION['full_name']);
unset($_SESSION['email']);
unset($_SESSION['role']);

header("Location: " . BASE_URL . "/auth/login.php");
exit();
?>