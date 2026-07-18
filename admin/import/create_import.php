<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$orders = $conn->query("
    SELECT o.order_id, o.order_date, s.supplier_name
    FROM tb_order o
    LEFT JOIN tb_supplier s ON o.supplier_id = s.supplier_id
    ORDER BY o.order_id DESC
");

$order_id = intval($_GET['order_id'] ?? 0);
$orderDetails = null;

if ($order_id > 0) {
    $stmt = $conn->prepare("
        SELECT od.*, p.product_name
        FROM tb_order_detail od
        LEFT JOIN tb_product p ON od.product_id = p.product_id
        WHERE od.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $orderDetails = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ສ້າງການນຳເຂົ້າ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/import.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">
            <div class="form-header">
                <h1>ສ້າງການນຳເຂົ້າສິນຄ້າ</h1>
                <a href="import.php" class="btn-back">ກັບຄືນ</a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form method="GET" class="form-group">
                <label>ເລືອກຄຳສັ່ງຊື້</label>
                <select name="order_id" onchange="this.form.submit()" required>
                    <option value="">-- ເລືອກຄຳສັ່ງຊື້ --</option>
                    <?php while ($o = $orders->fetch_assoc()): ?>
                        <option value="<?php echo $o['order_id']; ?>" <?php if ($order_id == $o['order_id']) echo 'selected'; ?>>
                            <?php echo "ORD-" . str_pad($o['order_id'], 4, "0", STR_PAD_LEFT); ?>
                            -
                            <?php echo htmlspecialchars($o['supplier_name']); ?>
                            -
                            <?php echo htmlspecialchars($o['order_date']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>

            <?php if ($order_id > 0 && $orderDetails): ?>
                <form action="../../actions/import/create_import_action.php" method="POST">
                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">

                    <h3 class="section-title">ລາຍການສິນຄ້າທີ່ຈະນຳເຂົ້າ</h3>

                    <div class="table-card inside">
                        <table>
                            <thead>
                                <tr>
                                    <th>ສິນຄ້າ</th>
                                    <th>ຈຳນວນທີ່ສັ່ງ</th>
                                    <th>ຈຳນວນຮັບເຂົ້າ</th>
                                    <th>ລາຄາທຶນ</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php while ($row = $orderDetails->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($row['product_name']); ?>
                                            <input type="hidden" name="product_id[]" value="<?php echo $row['product_id']; ?>">
                                        </td>

                                        <td><?php echo htmlspecialchars($row['qty']); ?></td>

                                        <td>
                                            <input
                                                type="number"
                                                name="qty[]"
                                                min="1"
                                                value="<?php echo htmlspecialchars($row['qty']); ?>"
                                                required
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="number"
                                                name="cost_price[]"
                                                min="0"
                                                value="<?php echo htmlspecialchars($row['price']); ?>"
                                                required
                                            >
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-primary">ບັນທຶກການນຳເຂົ້າ</button>
                    </div>
                </form>
            <?php endif; ?>

        </div>

    </main>
</div>

</body>
</html>