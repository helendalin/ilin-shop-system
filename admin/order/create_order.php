<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$suppliers = $conn->query("SELECT * FROM tb_supplier ORDER BY supplier_name ASC");
$products = $conn->query("SELECT * FROM tb_product ORDER BY product_name ASC");

$productOptions = [];
while ($p = $products->fetch_assoc()) {
    $productOptions[] = $p;
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ສ້າງສັ່ງຊື້ສິນຄ້າ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/order.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">
        <div class="form-card">
            <div class="form-header">
                <h1>ສ້າງສັ່ງຊື້ສິນຄ້າ</h1>
                <a href="order.php" class="btn-back">ກັບຄືນ</a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form action="../../actions/order/create_order_action.php" method="POST">

                <div class="form-group">
                    <label>ຜູ້ສະໜອງ</label>
                    <select name="supplier_id" required>
                        <option value="">-- ເລືອກຜູ້ສະໜອງ --</option>
                        <?php while ($s = $suppliers->fetch_assoc()): ?>
                            <option value="<?php echo $s['supplier_id']; ?>">
                                <?php echo htmlspecialchars($s['supplier_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <h3 class="section-title">ລາຍການສິນຄ້າ</h3>

                <div class="order-items" id="orderItems">
                    <div class="item-row">
                        <select name="product_id[]" required>
                            <option value="">-- ເລືອກສິນຄ້າ --</option>
                            <?php foreach ($productOptions as $p): ?>
                                <option value="<?php echo $p['product_id']; ?>">
                                    <?php echo htmlspecialchars($p['product_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="number" name="qty[]" min="1" placeholder="ຈຳນວນ" required>
                        <input type="number" name="price[]" min="0" placeholder="ລາຄາ" required>

                        <button type="button" class="btn-remove" onclick="removeRow(this)">ລຶບ</button>
                    </div>
                </div>

                <button type="button" class="btn-add-row" onclick="addRow()">+ ເພີ່ມລາຍການ</button>

                <div class="form-actions">
                    <button type="reset" class="btn-reset">ລ້າງຂໍ້ມູນ</button>
                    <button type="submit" class="btn-primary">ບັນທຶກ</button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
function addRow() {
    const container = document.getElementById("orderItems");
    const firstRow = container.querySelector(".item-row");
    const newRow = firstRow.cloneNode(true);

    newRow.querySelectorAll("select, input").forEach(el => {
        el.value = "";
    });

    container.appendChild(newRow);
}

function removeRow(button) {
    const rows = document.querySelectorAll(".item-row");

    if (rows.length > 1) {
        button.closest(".item-row").remove();
    }
}
</script>

</body>
</html>