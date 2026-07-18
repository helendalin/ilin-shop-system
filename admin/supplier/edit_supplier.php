<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {

    header("Location: supplier.php");
    exit();
}

$supplier_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT *
    FROM tb_supplier
    WHERE supplier_id = ?
");

$stmt->bind_param("i", $supplier_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    header("Location: supplier.php");
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ແກ້ໄຂຜູ້ສະໜອງ</title>

    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/supplier.css">
</head>
<body>

<div class="dashboard-layout">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">

            <div class="form-header">

                <h1>ແກ້ໄຂຜູ້ສະໜອງ</h1>

                <a href="supplier.php" class="btn-back">
                    ກັບຄືນ
                </a>

            </div>

            <form
                action="../../actions/supplier/update_supplier_action.php"
                method="POST"
            >

                <input
                    type="hidden"
                    name="supplier_id"
                    value="<?php echo $row['supplier_id']; ?>"
                >

                <div class="form-group">

                    <label>ລະຫັດຜູ້ສະໜອງ</label>

                    <input
                        type="text"
                        disabled
                        value="<?php
                        echo 'SUP-' .
                        str_pad(
                            $row['supplier_id'],
                            4,
                            '0',
                            STR_PAD_LEFT
                        );
                        ?>"
                    >

                </div>

                <div class="form-group">

                    <label>ຊື່ຜູ້ສະໜອງ</label>

                    <input
                        type="text"
                        name="supplier_name"
                        value="<?php echo htmlspecialchars($row['supplier_name']); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>ເບີໂທ</label>

                    <input
                        type="text"
                        name="phone_number"
                        value="<?php echo htmlspecialchars($row['phone_number']); ?>"
                        required
                    >

                </div>

                <div class="form-group">

                    <label>ທີ່ຢູ່</label>

                    <textarea
                        name="address"
                        rows="5"
                        required
                    ><?php echo htmlspecialchars($row['address']); ?></textarea>

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