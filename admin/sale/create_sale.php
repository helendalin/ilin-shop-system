<?php
include '../../includes/session_check.php';
include '../../config/db.php';

$customers = $conn->query("SELECT * FROM tb_customer ORDER BY first_name ASC");

$products = $conn->query("
    SELECT * FROM tb_product
    WHERE qty > 0
    ORDER BY product_name ASC
");

$productOptions = [];
while ($p = $products->fetch_assoc()) {
    $productOptions[] = $p;
}
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ສ້າງການຂາຍ</title>
    <!-- <link rel="stylesheet" href="../../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="../../assets/css/sale.css">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="form-card">
            <div class="form-header">
                <h1>ສ້າງການຂາຍສິນຄ້າ</h1>
                <a href="sale.php" class="btn-back">ກັບຄືນ</a>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <form action="../../actions/sale/create_sale_action.php" method="POST">

                <div class="form-group">
                    <label>ລູກຄ້າ</label>
                    <select name="customer_id" required>
                        <option value="">-- ເລືອກລູກຄ້າ --</option>
                        <?php while ($c = $customers->fetch_assoc()): ?>
                            <option value="<?php echo $c['customer_id']; ?>">
                                <?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <h3 class="section-title">ລາຍການສິນຄ້າ</h3>

                <div class="sale-items" id="saleItems">
                    <div class="item-row">
                        <select name="product_id[]" class="product-select" onchange="setPrice(this)" required>
                            <option value="">-- ເລືອກສິນຄ້າ --</option>

                            <?php foreach ($productOptions as $p): ?>
                                <option
                                    value="<?php echo $p['product_id']; ?>"
                                    data-price="<?php echo $p['price']; ?>"
                                    data-stock="<?php echo $p['qty']; ?>"
                                >
                                    <?php echo htmlspecialchars($p['product_name']); ?>
                                    (Stock: <?php echo $p['qty']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <input type="number" name="qty[]" min="1" placeholder="ຈຳນວນ" oninput="calculateTotal()" required>
                        <input type="number" name="price[]" min="0" placeholder="ລາຄາ" oninput="calculateTotal()" required>

                        <button type="button" class="btn-remove" onclick="removeRow(this)">ລຶບ</button>
                    </div>
                </div>

                <button type="button" class="btn-add-row" onclick="addRow()">+ ເພີ່ມລາຍການ</button>

                <div class="total-box">
                    <strong>ລວມເງິນ:</strong>
                    <span id="totalAmount">0</span>
                </div>

                <div class="form-actions">
                    <button type="reset" class="btn-reset" onclick="calculateTotal()">ລ້າງຂໍ້ມູນ</button>
                    <button type="submit" class="btn-primary">ບັນທຶກການຂາຍ</button>
                </div>

            </form>
        </div>

    </main>
</div>

<script>
function addRow() {
    const container = document.getElementById("saleItems");
    const firstRow = container.querySelector(".item-row");
    const newRow = firstRow.cloneNode(true);

    newRow.querySelectorAll("select, input").forEach(el => {
        el.value = "";
    });

    container.appendChild(newRow);
    calculateTotal();
}

function removeRow(button) {
    const rows = document.querySelectorAll(".item-row");

    if (rows.length > 1) {
        button.closest(".item-row").remove();
        calculateTotal();
    }
}

function setPrice(select) {
    const selected = select.options[select.selectedIndex];
    const price = selected.getAttribute("data-price");
    const stock = selected.getAttribute("data-stock");

    const row = select.closest(".item-row");
    const priceInput = row.querySelector('input[name="price[]"]');
    const qtyInput = row.querySelector('input[name="qty[]"]');

    if (price) {
        priceInput.value = price;
    }

    if (stock) {
        qtyInput.max = stock;
    }

    calculateTotal();
}

function calculateTotal() {
    let total = 0;

    document.querySelectorAll(".item-row").forEach(row => {
        const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name="price[]"]').value) || 0;

        total += qty * price;
    });

    document.getElementById("totalAmount").innerText = total.toLocaleString();
}
</script>

</body>
</html>