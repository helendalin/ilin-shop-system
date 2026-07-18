<?php
include '../../includes/session_check.php';
include '../../config/db.php';

if (!isset($_GET['id'])) {
    header("Location: product.php");
    exit();
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT p.*, c.category_name, u.unit_name
    FROM tb_product p
    LEFT JOIN tb_category c ON p.category_id = c.category_id
    LEFT JOIN tb_unit u ON p.unit_id = u.unit_id
    WHERE p.product_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: product.php");
    exit();
}

$row = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ລາຍລະອຽດສິນຄ້າ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/product.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="detail-card">
            <div class="form-header">
                <h1>ລາຍລະອຽດສິນຄ້າ</h1>
                <a href="product.php" class="btn-back">ກັບຄືນ</a>
            </div>

            <div class="detail-layout">
                <div>
                    <?php if (!empty($row['image'])): ?>
                        <img class="detail-img" src="../../assets/images/<?php echo htmlspecialchars($row['image']); ?>">
                    <?php else: ?>
                        <div class="detail-no-img">No Image</div>
                    <?php endif; ?>
                </div>

                <div class="detail-info">
                    <p><strong>ລະຫັດ:</strong> <?php echo "PRO-" . str_pad($row['product_id'], 4, "0", STR_PAD_LEFT); ?></p>
                    <p><strong>ຊື່ສິນຄ້າ:</strong> <?php echo htmlspecialchars($row['product_name']); ?></p>
                    <p><strong>ປະເພດ:</strong> <?php echo htmlspecialchars($row['category_name']); ?></p>
                    <p><strong>ຫົວໜ່ວຍ:</strong> <?php echo htmlspecialchars($row['unit_name']); ?></p>
                    <p><strong>ຈຳນວນ:</strong> <?php echo htmlspecialchars($row['qty']); ?></p>
                    <p><strong>ລາຄາ:</strong> <?php echo number_format($row['price']); ?></p>
                    <p><strong>ລາຍລະອຽດ:</strong><br><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                </div>
            </div>
        </div>

    </main>
</div>

</body>
</html>