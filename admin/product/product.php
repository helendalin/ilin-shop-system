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

    foreach ($params as &$value) {
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
$search = trim($_GET['search'] ?? '');
$categoryId = intval($_GET['category_id'] ?? 0);
$stockFilter = trim($_GET['stock'] ?? '');

$allowedStockFilters = ['available', 'low', 'out'];

if (!in_array($stockFilter, $allowedStockFilters, true)) {
    $stockFilter = '';
}

/* Category dropdown */
$categories = $conn->query("
    SELECT category_id, category_name
    FROM tb_category
    ORDER BY category_name ASC
");

/* Build WHERE */
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $like = '%' . $search . '%';
    $productIdSearch = intval(preg_replace('/\D+/', '', $search));

    $where[] = "(
        p.product_name LIKE ?
        OR c.category_name LIKE ?
        OR u.unit_name LIKE ?
        OR CAST(p.product_id AS CHAR) LIKE ?
        OR p.product_id = ?
    )";

    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $productIdSearch;
    $types .= 'ssssi';
}

if ($categoryId > 0) {
    $where[] = "p.category_id = ?";
    $params[] = $categoryId;
    $types .= 'i';
}

if ($stockFilter === 'available') {
    $where[] = "p.qty > 5";
}

if ($stockFilter === 'low') {
    $where[] = "p.qty > 0 AND p.qty <= 5";
}

if ($stockFilter === 'out') {
    $where[] = "p.qty <= 0";
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

/* Count total products */
$countSql = "
    SELECT COUNT(*) AS total
    FROM tb_product p
    LEFT JOIN tb_category c ON p.category_id = c.category_id
    LEFT JOIN tb_unit u ON p.unit_id = u.unit_id
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

/* Get products */
$listSql = "
    SELECT 
        p.*,
        c.category_name,
        u.unit_name
    FROM tb_product p
    LEFT JOIN tb_category c ON p.category_id = c.category_id
    LEFT JOIN tb_unit u ON p.unit_id = u.unit_id
    $whereSql
    ORDER BY p.product_id DESC
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
    <title>ຈັດການສິນຄ້າ</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/product.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">
    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <div class="page-header">
            <div>
                <h1>ຈັດການສິນຄ້າ</h1>
                <p>ລາຍຊື່ສິນຄ້າທັງໝົດ</p>
            </div>

            <a href="add_product.php" class="btn-primary">
                + ເພີ່ມສິນຄ້າ
            </a>
        </div>

        <div class="table-card">

            <form method="GET" class="filter-box">

                <div class="filter-group">
                    <label>ຄົ້ນຫາ</label>
                    <input
                        type="text"
                        name="search"
                        placeholder="ຄົ້ນຫາຊື່ສິນຄ້າ, ປະເພດ, ຫົວໜ່ວຍ..."
                        value="<?= h($search); ?>"
                    >
                </div>

                <div class="filter-group">
                    <label>ປະເພດສິນຄ້າ</label>
                    <select name="category_id">
                        <option value="0">ທັງໝົດ</option>

                        <?php if ($categories && $categories->num_rows > 0): ?>
                            <?php while ($category = $categories->fetch_assoc()): ?>
                                <option
                                    value="<?= intval($category['category_id']); ?>"
                                    <?= ($categoryId === intval($category['category_id'])) ? 'selected' : ''; ?>
                                >
                                    <?= h($category['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>ສະຖານະສະຕັອກ</label>
                    <select name="stock">
                        <option value="">ທັງໝົດ</option>
                        <option value="available" <?= ($stockFilter === 'available') ? 'selected' : ''; ?>>
                            ມີສິນຄ້າ
                        </option>
                        <option value="low" <?= ($stockFilter === 'low') ? 'selected' : ''; ?>>
                            ໃກ້ໝົດ
                        </option>
                        <option value="out" <?= ($stockFilter === 'out') ? 'selected' : ''; ?>>
                            ໝົດສະຕັອກ
                        </option>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        ຄົ້ນຫາ
                    </button>

                    <a href="<?= BASE_URL ?>/admin/product/product.php" class="btn-reset">
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
                        <th>ຮູບ</th>
                        <th>ລະຫັດ</th>
                        <th>ຊື່ສິນຄ້າ</th>
                        <th>ປະເພດ</th>
                        <th>ຫົວໜ່ວຍ</th>
                        <th>ຈຳນວນ</th>
                        <th>ລາຄາ</th>
                        <th>ຈັດການ</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['image'])): ?>
                                        <img
                                            class="product-img"
                                            src="<?= BASE_URL ?>/assets/images/<?= h($row['image']); ?>"
                                            alt="<?= h($row['product_name']); ?>"
                                        >
                                    <?php else: ?>
                                        <div class="no-img">No Image</div>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= "PRO-" . str_pad($row['product_id'], 4, "0", STR_PAD_LEFT); ?>
                                </td>

                                <td><?= h($row['product_name']); ?></td>

                                <td><?= h($row['category_name'] ?? '-'); ?></td>

                                <td><?= h($row['unit_name'] ?? '-'); ?></td>

                                <td>
                                    <?php
                                    $qty = intval($row['qty']);
                                    $stockClass = 'stock-available';
                                    $stockText = $qty;

                                    if ($qty <= 0) {
                                        $stockClass = 'stock-out';
                                    } elseif ($qty <= 5) {
                                        $stockClass = 'stock-low';
                                    }
                                    ?>

                                    <span class="stock-badge <?= $stockClass; ?>">
                                        <?= $stockText; ?>
                                    </span>
                                </td>

                                <td><?= number_format($row['price']); ?> ₭</td>

                                <td>
                                    <a class="btn-view" href="product_detail.php?id=<?= intval($row['product_id']); ?>">
                                        ເບິ່ງ
                                    </a>

                                    <a class="btn-edit" href="edit_product.php?id=<?= intval($row['product_id']); ?>">
                                        ແກ້ໄຂ
                                    </a>

                                    <button
                                        type="button"
                                        class="btn-delete"
                                        onclick="openDeleteModal(<?= intval($row['product_id']); ?>)"
                                    >
                                        ລຶບ
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="no-data">
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
        <p>ທ່ານຕ້ອງການລຶບສິນຄ້ານີ້ບໍ?</p>

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
        "<?= BASE_URL ?>/actions/product/delete_product_action.php?id=" + id;
}

function closeDeleteModal() {
    document.getElementById("deleteModal").classList.remove("show");
}
</script>

</body>
</html>