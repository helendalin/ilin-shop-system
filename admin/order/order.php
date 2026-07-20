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

/* Check if tb_order has status column */
// $hasStatusColumn = false;
// $statusCheck = $conn->query("SHOW COLUMNS FROM tb_order LIKE 'status'");

// if ($statusCheck && $statusCheck->num_rows > 0) {
//     $hasStatusColumn = true;
// }

/* Pagination */
$perPage = 7;
$currentPageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;

if ($currentPageNum < 1) {
    $currentPageNum = 1;
}

/* Filters */
$q = trim($_GET['q'] ?? '');
$supplierId = intval($_GET['supplier_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');

$allowedStatuses = ['pending', 'ordered', 'received', 'completed', 'cancelled'];

// if (!$hasStatusColumn || !in_array($status, $allowedStatuses, true)) {
//     $status = '';
// }

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fromDate)) {
    $fromDate = '';
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $toDate)) {
    $toDate = '';
}

/* Supplier dropdown */
$suppliers = $conn->query("
    SELECT supplier_id, supplier_name
    FROM tb_supplier
    ORDER BY supplier_name ASC
");

/* Build WHERE */
$where = [];
$params = [];
$types = '';

if ($q !== '') {
    $like = '%' . $q . '%';
    $orderIdSearch = intval(preg_replace('/\D+/', '', $q));

    $where[] = "(
        s.supplier_name LIKE ?
        OR CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) LIKE ?
        OR CAST(o.order_id AS CHAR) LIKE ?
        OR o.order_id = ?
    )";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $orderIdSearch;
    $types .= 'sssi';
}

if ($supplierId > 0) {
    $where[] = "o.supplier_id = ?";
    $params[] = $supplierId;
    $types .= 'i';
}

// if ($hasStatusColumn && $status !== '') {
//     $where[] = "o.status = ?";
//     $params[] = $status;
//     $types .= 's';
// }

if ($fromDate !== '') {
    $where[] = "DATE(o.order_date) >= ?";
    $params[] = $fromDate;
    $types .= 's';
}

if ($toDate !== '') {
    $where[] = "DATE(o.order_date) <= ?";
    $params[] = $toDate;
    $types .= 's';
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

/* Count total */
$countSql = "
    SELECT COUNT(*) AS total
    FROM tb_order o
    LEFT JOIN tb_supplier s ON o.supplier_id = s.supplier_id
    LEFT JOIN tb_employee e ON o.emp_id = e.emp_id
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

/* Get order rows */
$listSql = "
    SELECT
        o.*,
        s.supplier_name,
        e.first_name,
        e.last_name
    FROM tb_order o
    LEFT JOIN tb_supplier s ON o.supplier_id = s.supplier_id
    LEFT JOIN tb_employee e ON o.emp_id = e.emp_id
    $whereSql
    ORDER BY o.order_id DESC
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
    <title>ສັ່ງຊື້ຈາກຜູ້ສະໜອງ</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/order.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>ສັ່ງຊື້ຈາກຜູ້ສະໜອງ</h1>
                <p>ລາຍການສັ່ງຊື້ສິນຄ້າຈາກຜູ້ສະໜອງ</p>
            </div>

            <a href="create_order.php" class="btn-primary">
                + ສ້າງສັ່ງຊື້ສິນຄ້າ
            </a>
        </div>

        <div class="table-card">

            <form action="" method="GET" class="filter-box">

                <div class="filter-group">
                    <label>ຄົ້ນຫາ</label>
                    <input
                        type="text"
                        name="q"
                        value="<?= h($q); ?>"
                        placeholder="ລະຫັດ, ຜູ້ສະໜອງ, ພະນັກງານ..."
                    >
                </div>

                <div class="filter-group">
                    <label>ຜູ້ສະໜອງ</label>
                    <select name="supplier_id">
                        <option value="0">ທັງໝົດ</option>

                        <?php if ($suppliers && $suppliers->num_rows > 0): ?>
                            <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                                <option
                                    value="<?= intval($supplier['supplier_id']); ?>"
                                    <?= ($supplierId === intval($supplier['supplier_id'])) ? 'selected' : ''; ?>
                                >
                                    <?= h($supplier['supplier_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- <?php if ($hasStatusColumn): ?>
                    <div class="filter-group">
                        <label>ສະຖານະ</label>
                        <select name="status">
                            <option value="">ທັງໝົດ</option>
                            <option value="pending" <?= ($status === 'pending') ? 'selected' : ''; ?>>pending</option>
                            <option value="ordered" <?= ($status === 'ordered') ? 'selected' : ''; ?>>ordered</option>
                            <option value="received" <?= ($status === 'received') ? 'selected' : ''; ?>>received</option>
                            <option value="completed" <?= ($status === 'completed') ? 'selected' : ''; ?>>completed</option>
                            <option value="cancelled" <?= ($status === 'cancelled') ? 'selected' : ''; ?>>cancelled</option>
                        </select>
                    </div>
                <?php endif; ?> -->

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

                    <a href="<?= BASE_URL ?>/admin/order/order.php" class="btn-reset">
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
                        <th>ຜູ້ສະໜອງ</th>
                        <th>ພະນັກງານ</th>
                        <th>ວັນທີ</th>

                        <!-- <?php if ($hasStatusColumn): ?>
                            <th>ສະຖານະ</th>
                        <?php endif; ?> -->

                        <th>ຈັດການ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= "ORD-" . str_pad($row['order_id'], 4, "0", STR_PAD_LEFT); ?>
                                </td>

                                <td>
                                    <?= h($row['supplier_name'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= h(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: '-'); ?>
                                </td>

                                <td>
                                    <?= h($row['order_date']); ?>
                                </td>

                                <!-- <?php if ($hasStatusColumn): ?>
                                    <td>
                                        <span class="status-badge status-<?= h($row['status'] ?? 'pending'); ?>">
                                            <?= h($row['status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                <?php endif; ?> -->

                                <td>
                                    <a class="btn-view" href="order_detail.php?id=<?= intval($row['order_id']); ?>">
                                        ເບິ່ງ
                                    </a>

                                    <button
                                        type="button"
                                        class="btn-delete"
                                        onclick="openDeleteModal(<?= intval($row['order_id']); ?>)"
                                    >
                                        ລຶບ
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $hasStatusColumn ? '6' : '5'; ?>" class="no-data">
                                ບໍ່ມີຂໍ້ມູນ
                            </td>
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
        <p>ທ່ານຕ້ອງການລຶບຄຳສັ່ງຊື້ນີ້ບໍ?</p>

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
        "<?= BASE_URL ?>/actions/order/delete_order_action.php?id=" + id;
}

function closeDeleteModal() {
    document.getElementById("deleteModal").classList.remove("show");
}
</script>

</body>
</html>