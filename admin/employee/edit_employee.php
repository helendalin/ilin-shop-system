<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: employee.php");
    exit();
}

$emp_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM tb_employee WHERE emp_id = ? LIMIT 1");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: employee.php");
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ແກ້ໄຂພະນັກງານ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/employee.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">
            <div class="form-header">
                <h1>ແກ້ໄຂພະນັກງານ</h1>
                <a href="employee.php" class="btn-back">ກັບຄືນ</a>
            </div>

            <form action="../../actions/employee/update_employee_action.php" method="POST">

                <input type="hidden" name="emp_id" value="<?php echo $row['emp_id']; ?>">

                <div class="form-group">
                    <label>ລະຫັດພະນັກງານ</label>
                    <input type="text" value="<?php echo 'EMP-' . str_pad($row['emp_id'], 4, '0', STR_PAD_LEFT); ?>" disabled>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ຊື່</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($row['first_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>ນາມສະກຸນ</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($row['last_name']); ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ເພດ</label>
                        <select name="gender" required>
                            <option value="Male" <?php if ($row['gender'] == 'Male') echo 'selected'; ?>>ຊາຍ</option>
                            <option value="Female" <?php if ($row['gender'] == 'Female') echo 'selected'; ?>>ຍິງ</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ວັນເດືອນປີເກີດ</label>
                        <input type="date" name="birth_date" value="<?php echo htmlspecialchars($row['birth_date']); ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>ທີ່ຢູ່</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($row['address']); ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>ເບີໂທ</label>
                        <input type="text" name="phone_number" value="<?php echo htmlspecialchars($row['phone_number']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>ຕຳແໜ່ງ</label>
                        <select name="role" required>
                            <option value="Admin" <?php if ($row['role'] == 'Admin') echo 'selected'; ?>>Admin</option>
                            <option value="Employee" <?php if ($row['role'] == 'Employee') echo 'selected'; ?>>Employee</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>ອີເມວ</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">ອັບເດດ</button>
                </div>

            </form>
        </div>

    </main>
</div>

</body>
</html>