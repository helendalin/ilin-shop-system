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

/* Check optional status column */
$hasStatusColumn = false;
$statusCheck = $conn->query("SHOW COLUMNS FROM tb_import LIKE 'status'");

if ($statusCheck && $statusCheck->num_rows > 0) {
    $hasStatusColumn = true;
}

/* Pagination */
$perPage = 7;
$currentPageNum = isset($_GET['page']) ? intval($_GET['page']) : 1;

if ($currentPageNum < 1) {
    $currentPageNum = 1;
}

/* Filters */
$search = trim($_GET['search'] ?? '');
$supplierId = intval($_GET['supplier_id'] ?? 0);
$status = trim($_GET['status'] ?? '');
$fromDate = trim($_GET['from_date'] ?? '');
$toDate = trim($_GET['to_date'] ?? '');

$allowedStatuses = ['pending', 'received', 'completed', 'cancelled'];

if (!$hasStatusColumn || !in_array($status, $allowedStatuses, true)) {
    $status = '';
}

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

if ($search !== '') {
    $keyword = '%' . $search . '%';
    $numberSearch = intval(preg_replace('/\D+/', '', $search));

    $where[] = "(
        CAST(i.import_id AS CHAR) LIKE ?
        OR CAST(o.order_id AS CHAR) LIKE ?
        OR i.import_id = ?
        OR o.order_id = ?
        OR s.supplier_name LIKE ?
        OR CONCAT(COALESCE(e.first_name, ''), ' ', COALESCE(e.last_name, '')) LIKE ?
    )";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $numberSearch;
    $params[] = $numberSearch;
    $params[] = $keyword;
    $params[] = $keyword;
    $types .= 'ssiiss';
}

if ($supplierId > 0) {
    $where[] = "o.supplier_id = ?";
    $params[] = $supplierId;
    $types .= 'i';
}

if ($hasStatusColumn && $status !== '') {
    $where[] = "i.status = ?";
    $params[] = $status;
    $types .= 's';
}

if ($fromDate !== '') {
    $where[] = "DATE(i.import_date) >= ?";
    $params[] = $fromDate;
    $types .= 's';
}

if ($toDate !== '') {
    $where[] = "DATE(i.import_date) <= ?";
    $params[] = $toDate;
    $types .= 's';
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

/* Stats */
$stats = [
    'total' => 0,
    'today' => 0,
    'this_month' => 0
];

$statsResult = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN DATE(import_date) = CURDATE() THEN 1 ELSE 0 END) AS today,
        SUM(CASE WHEN YEAR(import_date) = YEAR(CURDATE()) AND MONTH(import_date) = MONTH(CURDATE()) THEN 1 ELSE 0 END) AS this_month
    FROM tb_import
");

if ($statsResult) {
    $statsRow = $statsResult->fetch_assoc();

    $stats['total'] = intval($statsRow['total'] ?? 0);
    $stats['today'] = intval($statsRow['today'] ?? 0);
    $stats['this_month'] = intval($statsRow['this_month'] ?? 0);
}

/* Count total rows */
$countSql = "
    SELECT COUNT(*) AS total
    FROM tb_import i
    LEFT JOIN tb_order o ON i.order_id = o.order_id
    LEFT JOIN tb_supplier s ON o.supplier_id = s.supplier_id
    LEFT JOIN tb_employee e ON i.emp_id = e.emp_id
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

/* Fetch imports */
$statusSelect = $hasStatusColumn ? "i.status," : "";

$listSql = "
    SELECT
        i.import_id,
        i.order_id,
        i.emp_id,
        i.import_date,
        $statusSelect
        o.order_id AS po_order_id,
        s.supplier_name,
        e.first_name,
        e.last_name
    FROM tb_import i
    LEFT JOIN tb_order o ON i.order_id = o.order_id
    LEFT JOIN tb_supplier s ON o.supplier_id = s.supplier_id
    LEFT JOIN tb_employee e ON i.emp_id = e.emp_id
    $whereSql
    ORDER BY i.import_id DESC
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
    <title>ນຳເຂົ້າສິນຄ້າ</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/import.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>ນຳເຂົ້າສິນຄ້າ</h1>
                <p>ລາຍການຮັບສິນຄ້າເຂົ້າສະຕັອກຈາກຜູ້ສະໜອງ</p>
            </div>

            <a href="create_import.php" class="btn-primary">
                + ສ້າງການນຳເຂົ້າ
            </a>
        </div>

        <!-- <div class="import-stats">
            <div class="import-stat-card">
                <span>📦 ນຳເຂົ້າທັງໝົດ</span>
                <strong><?= number_format($stats['total']); ?></strong>
            </div>

            <div class="import-stat-card">
                <span>📅 ນຳເຂົ້າມື້ນີ້</span>
                <strong><?= number_format($stats['today']); ?></strong>
            </div>

            <div class="import-stat-card">
                <span>🗓️ ນຳເຂົ້າເດືອນນີ້</span>
                <strong><?= number_format($stats['this_month']); ?></strong>
            </div>
        </div> -->

        <div class="table-card">

            <form method="GET" class="filter-box">

                <div class="filter-group">
                    <label>ຄົ້ນຫາ</label>
                    <input
                        type="text"
                        name="search"
                        placeholder="ຄົ້ນຫາ IMP, ORD, ຜູ້ສະໜອງ, ພະນັກງານ..."
                        value="<?= h($search); ?>"
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

                <?php if ($hasStatusColumn): ?>
                    <div class="filter-group">
                        <label>ສະຖານະ</label>
                        <select name="status">
                            <option value="">ທັງໝົດ</option>
                            <option value="pending" <?= ($status === 'pending') ? 'selected' : ''; ?>>pending</option>
                            <option value="received" <?= ($status === 'received') ? 'selected' : ''; ?>>received</option>
                            <option value="completed" <?= ($status === 'completed') ? 'selected' : ''; ?>>completed</option>
                            <option value="cancelled" <?= ($status === 'cancelled') ? 'selected' : ''; ?>>cancelled</option>
                        </select>
                    </div>
                <?php endif; ?>

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

                    <a href="<?= BASE_URL ?>/admin/import/import.php" class="btn-reset">
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
                        <th>ລະຫັດນຳເຂົ້າ</th>
                        <th>ອ້າງອີງຄຳສັ່ງຊື້</th>
                        <th>ຜູ້ສະໜອງ</th>
                        <th>ພະນັກງານ</th>
                        <th>ວັນທີ</th>

                        <?php if ($hasStatusColumn): ?>
                            <th>ສະຖານະ</th>
                        <?php endif; ?>

                        <th>ຈັດການ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="code-badge">
                                        <?= "IMP-" . str_pad($row['import_id'], 4, "0", STR_PAD_LEFT); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php if (!empty($row['order_id'])): ?>
                                        <span class="po-badge">
                                            <?= "ORD-" . str_pad($row['order_id'], 4, "0", STR_PAD_LEFT); ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= h($row['supplier_name'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= h(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: '-'); ?>
                                </td>

                                <td>
                                    <?= !empty($row['import_date']) ? date("d-m-Y H:i", strtotime($row['import_date'])) : '-'; ?>
                                </td>

                                <?php if ($hasStatusColumn): ?>
                                    <td>
                                        <span class="status-badge status-<?= h($row['status'] ?? 'pending'); ?>">
                                            <?= h($row['status'] ?? 'pending'); ?>
                                        </span>
                                    </td>
                                <?php endif; ?>

                                <td>
                                    <a class="btn-view" href="import_detail.php?id=<?= intval($row['import_id']); ?>">
                                        ເບິ່ງ
                                    </a>

                                    <button
                                        type="button"
                                        class="btn-delete"
                                        onclick="openDeleteModal(<?= intval($row['import_id']); ?>)"
                                    >
                                        ລຶບ
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="<?= $hasStatusColumn ? '7' : '6'; ?>" class="no-data">
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
                        <a
                            href="<?= paginationUrl($i); ?>"
                            class="<?= ($i == $currentPageNum) ? 'active' : ''; ?>"
                        >
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
        <p>ຖ້າລຶບການນຳເຂົ້າ ຈຳນວນສິນຄ້າຈະຖືກຫັກກັບຄືນ. ຕ້ອງການລຶບບໍ?</p>

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
        "<?= BASE_URL ?>/actions/import/delete_import_action.php?id=" + id;
}

function closeDeleteModal() {
    document.getElementById("deleteModal").classList.remove("show");
}
</script>

</body>
</html>