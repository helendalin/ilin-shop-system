<?php
include_once __DIR__ . '/../../config/db.php';
include_once __DIR__ . '/../../includes/session_check.php';

function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function bindStatementParams($stmt, $types, &$params) {
    if ($types === '' || empty($params)) {
        return;
    }

    $refs = [];
    $refs[] = $types;

    foreach ($params as $key => &$value) {
        $refs[] = &$value;
    }

    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function paginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;

    return '?' . http_build_query($params);
}

/* Pagination */
$perPage = 7;
$currentPageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;

if ($currentPageNum < 1) {
    $currentPageNum = 1;
}

/* Filters */
$q = trim($_GET['q'] ?? '');
$status = trim($_GET['status'] ?? '');
$paymentStatus = trim($_GET['payment_status'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');

$allowedStatuses = ['pending', 'packing', 'shipping', 'completed', 'cancelled'];
$allowedPaymentStatuses = ['pending', 'paid', 'unpaid', 'rejected', 'failed', 'refunded'];

if (!in_array($status, $allowedStatuses, true)) {
    $status = '';
}

if (!in_array($paymentStatus, $allowedPaymentStatuses, true)) {
    $paymentStatus = '';
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
    $fromDate = '';
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
    $toDate = '';
}

/* Build WHERE */
$where = [];
$params = [];
$types = '';

if ($q !== '') {
    $like = '%' . $q . '%';
    $saleIdSearch = intval(preg_replace('/\D+/', '', $q));

    $where[] = "(
        CONCAT(COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) LIKE ?
        OR c.phone_number LIKE ?
        OR CAST(s.sale_id AS CHAR) LIKE ?
        OR s.delivery_method LIKE ?
        OR s.payment_method LIKE ?
        OR s.sale_id = ?
    )";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $saleIdSearch;
    $types .= 'sssssi';
}

if ($status !== '') {
    $where[] = "s.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($paymentStatus !== '') {
    $where[] = "s.payment_status = ?";
    $params[] = $paymentStatus;
    $types .= 's';
}

if ($fromDate !== '') {
    $where[] = "DATE(s.sale_date) >= ?";
    $params[] = $fromDate;
    $types .= 's';
}

if ($toDate !== '') {
    $where[] = "DATE(s.sale_date) <= ?";
    $params[] = $toDate;
    $types .= 's';
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

/* Count total rows */
$countSql = "
    SELECT COUNT(*) AS total
    FROM tb_sale s
    LEFT JOIN tb_customer c ON s.customer_id = c.customer_id
    LEFT JOIN tb_employee e ON s.emp_id = e.emp_id
    $whereSql
";

$countStmt = $conn->prepare($countSql);

if (!$countStmt) {
    die("Count query error: " . $conn->error);
}

$countParams = $params;
$countTypes = $types;
bindStatementParams($countStmt, $countTypes, $countParams);

$countStmt->execute();
$countResult = $countStmt->get_result();

$totalRows = 0;

if ($countResult) {
    $countRow = $countResult->fetch_assoc();
    $totalRows = intval($countRow['total'] ?? 0);
}

$totalPages = ($totalRows > 0) ? ceil($totalRows / $perPage) : 1;

if ($currentPageNum > $totalPages) {
    $currentPageNum = $totalPages;
}

$offset = ($currentPageNum - 1) * $perPage;

/* Get sale rows */
$listSql = "
    SELECT 
        s.*,
        c.first_name AS customer_first,
        c.last_name AS customer_last,
        c.phone_number,
        e.first_name AS emp_first,
        e.last_name AS emp_last
    FROM tb_sale s
    LEFT JOIN tb_customer c ON s.customer_id = c.customer_id
    LEFT JOIN tb_employee e ON s.emp_id = e.emp_id
    $whereSql
    ORDER BY s.sale_id DESC
    LIMIT ? OFFSET ?
";

$listStmt = $conn->prepare($listSql);

if (!$listStmt) {
    die("List query error: " . $conn->error);
}

$listParams = $params;
$listTypes = $types;

$listParams[] = $perPage;
$listParams[] = $offset;
$listTypes .= 'ii';

bindStatementParams($listStmt, $listTypes, $listParams);

$listStmt->execute();
$result = $listStmt->get_result();

$startItem = ($totalRows > 0) ? $offset + 1 : 0;
$endItem = min($offset + $perPage, $totalRows);
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <title>ອໍເດີລູກຄ້າ</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/sale.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>ອໍເດີລູກຄ້າ</h1>
                <p>ລາຍການຂາຍສິນຄ້າຈາກໜ້າເວັບ ແລະ ໜ້າຮ້ານ</p>
            </div>

            <!-- <a href="create_sale.php" class="btn-primary">+ ສ້າງການຂາຍ</a> -->
        </div>

        <div class="table-card">

            <form action="" method="GET" class="filter-box">

                <div class="filter-group">
                    <label>ຄົ້ນຫາ</label>
                    <input
                        type="text"
                        name="q"
                        value="<?= h($q); ?>"
                        placeholder="ລະຫັດ, ຊື່ລູກຄ້າ, ເບີໂທ..."
                    >
                </div>

                <div class="filter-group">
                    <label>ສະຖານະອໍເດີ</label>
                    <select name="status">
                        <option value="">ທັງໝົດ</option>
                        <option value="pending" <?= ($status === 'pending') ? 'selected' : ''; ?>>pending</option>
                        <option value="packing" <?= ($status === 'packing') ? 'selected' : ''; ?>>packing</option>
                        <option value="shipping" <?= ($status === 'shipping') ? 'selected' : ''; ?>>shipping</option>
                        <option value="completed" <?= ($status === 'completed') ? 'selected' : ''; ?>>completed</option>
                        <option value="cancelled" <?= ($status === 'cancelled') ? 'selected' : ''; ?>>cancelled</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>ສະຖານະເງິນ</label>
                    <select name="payment_status">
                        <option value="">ທັງໝົດ</option>
                        <option value="pending" <?= ($paymentStatus === 'pending') ? 'selected' : ''; ?>>pending</option>
                        <option value="paid" <?= ($paymentStatus === 'paid') ? 'selected' : ''; ?>>paid</option>
                        <option value="unpaid" <?= ($paymentStatus === 'unpaid') ? 'selected' : ''; ?>>unpaid</option>
                        <option value="rejected" <?= ($paymentStatus === 'rejected') ? 'selected' : ''; ?>>rejected</option>
                        <option value="failed" <?= ($paymentStatus === 'failed') ? 'selected' : ''; ?>>failed</option>
                        <option value="refunded" <?= ($paymentStatus === 'refunded') ? 'selected' : ''; ?>>refunded</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>ຈາກວັນທີ</label>
                    <input type="date" name="from_date" value="<?= h($fromDate); ?>">
                </div>

                <div class="filter-group">
                    <label>ຫາວັນທີ</label>
                    <input type="date" name="to_date" value="<?= h($toDate); ?>">
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        ຄົ້ນຫາ
                    </button>

                    <a href="<?= BASE_URL ?>/admin/sale/sale.php" class="btn-reset">
                        ລ້າງ
                    </a>
                </div>

            </form>

            <div class="table-info">
                <span>
                    ສະແດງ <?= $startItem; ?> - <?= $endItem; ?> ຈາກ <?= $totalRows; ?> ລາຍການ
                </span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ລະຫັດ</th>
                        <th>ລູກຄ້າ</th>
                        <th>ເບີໂທ</th>
                        <th>ວັນທີ</th>
                        <th>ລວມເງິນ</th>
                        <th>ຈັດສົ່ງ</th>
                        <th>ຊຳລະ</th>
                        <th>ສະຖານະເງິນ</th>
                        <th>ສະຖານະອໍເດີ</th>
                        <th>ສະລິບ</th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= "SAL-" . str_pad($row['sale_id'], 4, "0", STR_PAD_LEFT); ?></td>

                                <td>
                                    <?= h(trim(($row['customer_first'] ?? '') . ' ' . ($row['customer_last'] ?? '')) ?: '-'); ?>
                                </td>

                                <td><?= h($row['phone_number'] ?? '-'); ?></td>

                                <td><?= h($row['sale_date']); ?></td>

                                <td><?= number_format($row['total_amount']); ?> ₭</td>

                                <td><?= h($row['delivery_method'] ?? '-'); ?></td>

                                <td><?= h($row['payment_method'] ?? '-'); ?></td>

                                <td>
                                    <span class="status-badge status-<?= h($row['payment_status'] ?? 'pending'); ?>">
                                        <?= h($row['payment_status'] ?? 'pending'); ?>
                                    </span>
                                </td>

                                <td>
                                    <span class="status-badge status-<?= h($row['status'] ?? 'pending'); ?>">
                                        <?= h($row['status'] ?? 'pending'); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (!empty($row['payment_slip'])): ?>
                                        <span class="slip-yes">ມີ</span>
                                    <?php else: ?>
                                        <span class="slip-no">ບໍ່ມີ</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <a class="btn-view" href="sale_detail.php?id=<?= intval($row['sale_id']); ?>">ເບິ່ງ</a>

                                    <button
                                        type="button"
                                        class="btn-delete"
                                        onclick="openDeleteModal(<?= intval($row['sale_id']); ?>)"
                                    >
                                        ລຶບ
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="no-data">ບໍ່ມີຂໍ້ມູນ</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">

                    <?php if ($currentPageNum > 1): ?>
                        <a href="<?= paginationUrl($currentPageNum - 1); ?>">
                            « ກັບຄືນ
                        </a>
                    <?php endif; ?>

                    <?php
                    $startPage = max(1, $currentPageNum - 2);
                    $endPage = min($totalPages, $currentPageNum + 2);
                    ?>

                    <?php if ($startPage > 1): ?>
                        <a href="<?= paginationUrl(1); ?>">1</a>

                        <?php if ($startPage > 2): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <a href="<?= paginationUrl($i); ?>"
                           class="<?= ($i == $currentPageNum) ? 'active' : ''; ?>">
                            <?= $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span class="pagination-dots">...</span>
                        <?php endif; ?>

                        <a href="<?= paginationUrl($totalPages); ?>">
                            <?= $totalPages; ?>
                        </a>
                    <?php endif; ?>

                    <?php if ($currentPageNum < $totalPages): ?>
                        <a href="<?= paginationUrl($currentPageNum + 1); ?>">
                            ໜ້າຕໍ່ໄປ »
                        </a>
                    <?php endif; ?>

                </div>
            <?php endif; ?>

        </div>

    </main>
</div>

<div class="modal-overlay" id="deleteModal">
    <div class="modal-box">
        <div class="modal-icon">⚠️</div>
        <h2>ຢືນຢັນການລຶບ</h2>
        <p>ຖ້າລຶບການຂາຍ ຈຳນວນສິນຄ້າຈະຖືກບວກກັບຄືນ. ຕ້ອງການລຶບບໍ?</p>

        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">ຍົກເລີກ</button>
            <a href="#" id="deleteLink" class="btn-confirm">ລຶບ</a>
        </div>
    </div>
</div>

<script>
function openDeleteModal(id) {
    document.getElementById("deleteModal").classList.add("show");
    document.getElementById("deleteLink").href =
        "<?= BASE_URL ?>/actions/sale/delete_sale_action.php?id=" + id;
}

function closeDeleteModal() {
    document.getElementById("deleteModal").classList.remove("show");
}
</script>

</body>
</html>