<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/auth/login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    header("Location: " . BASE_URL . "/auth/login.php?error=ກະລຸນາປ້ອນອີເມວ ແລະ ລະຫັດຜ່ານ");
    exit();
}

/*
    ADMIN / EMPLOYEE LOGIN ONLY
    Do not check tb_customer here.
    Do not clear customer session here.
*/
$stmtEmp = $conn->prepare("
    SELECT emp_id, first_name, last_name, email, password, role
    FROM tb_employee
    WHERE email = ?
    LIMIT 1
");

if (!$stmtEmp) {
    header("Location: " . BASE_URL . "/auth/login.php?error=ລະບົບມີບັນຫາ ກະລຸນາລອງໃໝ່");
    exit();
}

$stmtEmp->bind_param("s", $email);
$stmtEmp->execute();
$resultEmp = $stmtEmp->get_result();

if ($resultEmp && $resultEmp->num_rows === 1) {
    $row = $resultEmp->fetch_assoc();

    if (password_verify($password, $row['password'])) {
        session_regenerate_id(true);

        $role = strtolower(trim($row['role'] ?? 'employee'));

        $_SESSION['user_type'] = ($role === 'admin') ? 'admin' : 'employee';
        $_SESSION['emp_id'] = intval($row['emp_id']);
        $_SESSION['full_name'] = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $_SESSION['role'] = $row['role'] ?? 'employee';
        $_SESSION['email'] = $row['email'];

        header("Location: " . BASE_URL . "/admin/dashboard.php");
        exit();
    }
}

header("Location: " . BASE_URL . "/auth/login.php?error=ອີເມວ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ");
exit();
?>