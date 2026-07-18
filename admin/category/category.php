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

/* Search */
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $keyword = '%' . $search . '%';
    $categoryIdSearch = intval(preg_replace('/\D+/', '', $search));

    $where[] = "(
        category_name LIKE ?
        OR CAST(category_id AS CHAR) LIKE ?
        OR category_id = ?
    )";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $categoryIdSearch;
    $types .= 'ssi';
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

/* Count total categories */
$countSql = "
    SELECT COUNT(*) AS total
    FROM tb_category
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

/* Get categories */
$listSql = "
    SELECT category_id, category_name
    FROM tb_category
    $whereSql
    ORDER BY category_id DESC
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
    <title>ຈັດການປະເພດສິນຄ້າ</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/category.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>ຈັດການປະເພດສິນຄ້າ</h1>
                <p>ລາຍຊື່ປະເພດສິນຄ້າທັງໝົດ</p>
            </div>

            <a href="add_category.php" class="btn-primary">
                + ເພີ່ມປະເພດສິນຄ້າ
            </a>
        </div>

        <div class="table-card">

            <form method="GET" class="filter-box">

                <div class="filter-group">
                    <label>ຄົ້ນຫາ</label>
                    <input
                        type="text"
                        name="search"
                        placeholder="ຄົ້ນຫາລະຫັດ ຫຼື ຊື່ປະເພດສິນຄ້າ..."
                        value="<?= h($search); ?>"
                    >
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        ຄົ້ນຫາ
                    </button>

                    <a href="<?= BASE_URL ?>/admin/category/category.php" class="btn-reset">
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
                        <th>ຊື່ປະເພດສິນຄ້າ</th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?= "CAT-" . str_pad($row['category_id'], 4, "0", STR_PAD_LEFT); ?>
                                </td>

                                <td>
                                    <?= h($row['category_name'] ?? '-'); ?>
                                </td>

                                <td>
                                    <a
                                        class="btn-edit"
                                        href="edit_category.php?id=<?= intval($row['category_id']); ?>"
                                    >
                                        ແກ້ໄຂ
                                    </a>

                                    <button
                                        type="button"
                                        class="btn-delete"
                                        onclick="openDeleteModal(<?= intval($row['category_id']); ?>)"
                                    >
                                        ລຶບ
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="no-data">
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

        <p>ທ່ານຕ້ອງການລຶບປະເພດສິນຄ້ານີ້ບໍ?</p>

        <div class="modal-actions">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">
                ຍົກເລີກ
            </button>

            <a href="#" id="deleteLink" class="btn-confirm">
                ລຶບ
            </a>
        </div>
    </div>
</div>

<script>
function openDeleteModal(id)
{
    document.getElementById("deleteModal").classList.add("show");

    document.getElementById("deleteLink").href =
        "<?= BASE_URL ?>/actions/category/delete_category_action.php?id=" + id;
}

function closeDeleteModal()
{
    document.getElementById("deleteModal").classList.remove("show");
}
</script>

</body>
</html>