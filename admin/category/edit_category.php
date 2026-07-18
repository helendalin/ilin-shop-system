<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: category.php");
    exit();
}

$category_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT *
    FROM tb_category
    WHERE category_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $category_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: category.php");
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ແກ້ໄຂປະເພດສິນຄ້າ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/category.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">
            <div class="form-header">
                <h1>ແກ້ໄຂປະເພດສິນຄ້າ</h1>

                <a href="category.php" class="btn-back">
                    ກັບຄືນ
                </a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form action="../../actions/category/update_category_action.php" method="POST">

                <input
                    type="hidden"
                    name="category_id"
                    value="<?php echo $row['category_id']; ?>"
                >

                <div class="form-group">
                    <label>ລະຫັດປະເພດສິນຄ້າ</label>

                    <input
                        type="text"
                        disabled
                        value="<?php echo 'CAT-' . str_pad($row['category_id'], 4, '0', STR_PAD_LEFT); ?>"
                    >
                </div>

                <div class="form-group">
                    <label>ຊື່ປະເພດສິນຄ້າ</label>

                    <input
                        type="text"
                        name="category_name"
                        value="<?php echo htmlspecialchars($row['category_name']); ?>"
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