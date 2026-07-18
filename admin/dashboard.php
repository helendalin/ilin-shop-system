<?php
include '../includes/session_check.php';
include '../config/db.php';

// if ($_SESSION['user_type'] !== 'employee') {
//     header("Location: ../customer/home.php");
//     exit();
// }

function countRows($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) AS total FROM $table");
    return $result ? ($result->fetch_assoc()['total'] ?? 0) : 0;
}

function countSaleStatus($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tb_sale WHERE status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}

function countPaymentStatus($conn, $status) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM tb_sale WHERE payment_status = ?");
    $stmt->bind_param("s", $status);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['total'] ?? 0;
}

$totalEmployees = countRows($conn, "tb_employee");
$totalCustomers = countRows($conn, "tb_customer");
$totalSuppliers = countRows($conn, "tb_supplier");
$totalProducts  = countRows($conn, "tb_product");
$totalOrders    = countRows($conn, "tb_order");
$totalSales     = countRows($conn, "tb_sale");

$pendingOrders   = countSaleStatus($conn, "pending");
$packingOrders   = countSaleStatus($conn, "packing");
$shippingOrders  = countSaleStatus($conn, "shipping");
$completedOrders = countSaleStatus($conn, "completed");
$cancelledOrders = countSaleStatus($conn, "cancelled");
$paidOrders      = countPaymentStatus($conn, "paid");

$todaySalesResult = $conn->query("
    SELECT SUM(total_amount) AS total 
    FROM tb_sale 
    WHERE DATE(sale_date) = CURDATE()
    AND status != 'cancelled'
");
$todaySales = $todaySalesResult->fetch_assoc()['total'] ?? 0;

$monthSalesResult = $conn->query("
    SELECT SUM(total_amount) AS total 
    FROM tb_sale 
    WHERE MONTH(sale_date) = MONTH(CURDATE())
    AND YEAR(sale_date) = YEAR(CURDATE())
    AND status != 'cancelled'
");
$monthSales = $monthSalesResult->fetch_assoc()['total'] ?? 0;

$incomeResult = $conn->query("
    SELECT SUM(total_amount) AS total 
    FROM tb_sale 
    WHERE status != 'cancelled'
");
$totalIncome = $incomeResult->fetch_assoc()['total'] ?? 0;

$expenseResult = $conn->query("
    SELECT SUM(qty * cost_price) AS total
    FROM tb_import_detail
");
$totalExpense = $expenseResult->fetch_assoc()['total'] ?? 0;

$popularResult = $conn->query("
    SELECT p.product_name, SUM(sd.qty) AS total_sold
    FROM tb_sale_detail sd
    LEFT JOIN tb_product p ON sd.product_id = p.product_id
    GROUP BY sd.product_id
    ORDER BY total_sold DESC
    LIMIT 1
");

$popularProduct = $popularResult->fetch_assoc();
$popularProductName = $popularProduct['product_name'] ?? 'ຍັງບໍ່ມີ';
$popularProductQty = $popularProduct['total_sold'] ?? 0;

$lowStockResult = $conn->query("
    SELECT product_name, qty
    FROM tb_product
    WHERE qty <= 5
    ORDER BY qty ASC
    LIMIT 6
");

$latestOrders = $conn->query("
    SELECT 
        s.sale_id,
        s.sale_date,
        s.total_amount,
        s.status,
        s.payment_status,
        c.first_name,
        c.last_name
    FROM tb_sale s
    LEFT JOIN tb_customer c ON s.customer_id = c.customer_id
    ORDER BY s.sale_id DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ໜ້າຫຼັກຜູ້ດູແລ - ILIN SHOP</title>
    <!-- <link rel="stylesheet" href="../assets/css/dashboard.css"> -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">

    <?php include '../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <?php include '../includes/admin_navbar.php'; ?>

        <!-- <section class="dashboard-hero">
            <div>
                <h1>ໜ້າຫຼັກຜູ້ດູແລ</h1>
                <p>ຍິນດີຕ້ອນຮັບ, <?= htmlspecialchars($_SESSION['full_name'] ?? 'Admin'); ?></p>
            </div>

            <div class="hero-badge">
                <?= htmlspecialchars($_SESSION['role'] ?? 'Admin'); ?>
            </div>
        </section> -->

        <section class="cards dashboard-cards">

            <div class="card"><span>ພະນັກງານ</span><p><?= $totalEmployees; ?></p></div>
            <div class="card"><span>ລູກຄ້າ</span><p><?= $totalCustomers; ?></p></div>
            <div class="card"><span>ຜູ້ສະໜອງ</span><p><?= $totalSuppliers; ?></p></div>
            <div class="card"><span>ສິນຄ້າ</span><p><?= $totalProducts; ?></p></div>
            <div class="card"><span>ຄຳສັ່ງຊື້</span><p><?= $totalOrders; ?></p></div>

            <div class="card"><span>ອໍເດີທັງໝົດ</span><p><?= $totalSales; ?></p></div>
            <div class="card status-card"><span>ລໍຖ້າດຳເນີນການ</span><p><?= $pendingOrders; ?></p></div>
            <div class="card status-card"><span>ຊຳລະແລ້ວ</span><p><?= $paidOrders; ?></p></div>
            <div class="card status-card"><span>ກຳລັງແພັກ</span><p><?= $packingOrders; ?></p></div>
            <div class="card status-card"><span>ກຳລັງຈັດສົ່ງ</span><p><?= $shippingOrders; ?></p></div>
            <div class="card status-card"><span>ສຳເລັດແລ້ວ</span><p><?= $completedOrders; ?></p></div>
            <div class="card danger-card"><span>ຍົກເລີກ</span><p><?= $cancelledOrders; ?></p></div>

            <div class="card money-card"><span>ຍອດຂາຍມື້ນີ້</span><p><?= number_format($todaySales); ?> ₭</p></div>
            <div class="card money-card"><span>ຍອດຂາຍເດືອນນີ້</span><p><?= number_format($monthSales); ?> ₭</p></div>
            <div class="card money-card"><span>ລາຍຮັບລວມ</span><p><?= number_format($totalIncome); ?> ₭</p></div>
            <div class="card money-card"><span>ລາຍຈ່າຍ</span><p><?= number_format($totalExpense); ?> ₭</p></div>

            <div class="card popular-card">
                <span>ສິນຄ້າຂາຍດີ</span>
                <p class="popular-name"><?= htmlspecialchars($popularProductName); ?></p>
                <small>ຂາຍແລ້ວ <?= $popularProductQty; ?> ລາຍການ</small>
            </div>

        </section>

        <section class="dashboard-row">

            <div class="dashboard-panel">
                <div class="section-header">
                    <h2>ອໍເດີລ່າສຸດ</h2>
                    <a href="sale/sale.php">ເບິ່ງທັງໝົດ</a>
                </div>

                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>ລະຫັດ</th>
                            <th>ລູກຄ້າ</th>
                            <th>ຍອດລວມ</th>
                            <th>ສະຖານະ</th>
                            <th>ວັນທີ</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($latestOrders && $latestOrders->num_rows > 0): ?>
                            <?php while ($row = $latestOrders->fetch_assoc()): ?>
                                <tr>
                                    <td>SAL-<?= str_pad($row['sale_id'], 4, "0", STR_PAD_LEFT); ?></td>
                                    <td><?= htmlspecialchars(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: '-'); ?></td>
                                    <td><?= number_format($row['total_amount']); ?> ₭</td>
                                    <td><?= htmlspecialchars($row['status']); ?></td>
                                    <td><?= htmlspecialchars($row['sale_date']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">ຍັງບໍ່ມີອໍເດີ</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="dashboard-panel">
                <div class="section-header">
                    <h2>⚠️ ສິນຄ້າໃກ້ໝົດ</h2>
                    <a href="product/product.php">ຈັດການສິນຄ້າ</a>
                </div>

                <div class="low-stock-grid">
                    <?php if ($lowStockResult && $lowStockResult->num_rows > 0): ?>
                        <?php while ($row = $lowStockResult->fetch_assoc()): ?>
                            <div class="low-stock-card">
                                <h3><?= htmlspecialchars($row['product_name']); ?></h3>
                                <p>ເຫຼືອ <strong><?= $row['qty']; ?></strong> ລາຍການ</p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="no-low-stock">ບໍ່ມີສິນຄ້າໃກ້ໝົດ</div>
                    <?php endif; ?>
                </div>
            </div>

        </section>

        <section class="quick-section">
            <h2>ເມນູດ່ວນ</h2>

            <div class="quick-grid">
                <a href="employee/employee.php">ຈັດການພະນັກງານ</a>
                <a href="customer/customer.php">ຈັດການລູກຄ້າ</a>
                <a href="supplier/supplier.php">ຈັດການຜູ້ສະໜອງ</a>
                <a href="product/product.php">ຈັດການສິນຄ້າ</a>
                <a href="order/order.php">ຄຳສັ່ງຊື້ຈາກຜູ້ສະໜອງ</a>
                <a href="sale/sale.php">ອໍເດີລູກຄ້າ</a>
                <a href="import/create_import.php">ສ້າງການນຳເຂົ້າ</a>
                <a href="report/report.php">ເບິ່ງລາຍງານ</a>
            </div>
        </section>

    </main>
</div>

</body>
</html>