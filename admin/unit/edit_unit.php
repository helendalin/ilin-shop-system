<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: unit.php");
    exit();
}

$unit_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT *
    FROM tb_unit
    WHERE unit_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $unit_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: unit.php");
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ແກ້ໄຂຫົວໜ່ວຍ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/unit.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">
            <div class="form-header">
                <h1>ແກ້ໄຂຫົວໜ່ວຍ</h1>

                <a href="unit.php" class="btn-back">
                    ກັບຄືນ
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="../../actions/unit/update_unit_action.php" method="POST">

                <input
                    type="hidden"
                    name="unit_id"
                    value="<?php echo $row['unit_id']; ?>"
                >

                <div class="form-group">
                    <label>ລະຫັດຫົວໜ່ວຍ</label>

                    <input
                        type="text"
                        disabled
                        value="<?php echo 'UNT-' . str_pad($row['unit_id'], 4, '0', STR_PAD_LEFT); ?>"
                    >
                </div>

                <div class="form-group">
                    <label>ຊື່ຫົວໜ່ວຍ</label>

                    <input
                        type="text"
                        name="unit_name"
                        value="<?php echo htmlspecialchars($row['unit_name']); ?>"
                        required
                    >
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        ອັບເດດ
                    </button>
                </div>

            </form>
        </div>

    </main>
</div>

</body>
</html>