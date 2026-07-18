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
$gender = trim($_GET['gender'] ?? '');

/* Gender dropdown from real database values */
$genderList = $conn->query("
    SELECT DISTINCT gender
    FROM tb_customer
    WHERE gender IS NOT NULL
    AND gender != ''
    ORDER BY gender ASC
");

/* Build WHERE */
$where = [];
$params = [];
$types = '';

if ($search !== '') {
    $keyword = '%' . $search . '%';
    $customerIdSearch = intval(preg_replace('/\D+/', '', $search));

    $where[] = "(
        first_name LIKE ?
        OR last_name LIKE ?
        OR CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?
        OR phone_number LIKE ?
        OR email LIKE ?
        OR address LIKE ?
        OR CAST(customer_id AS CHAR) LIKE ?
        OR customer_id = ?
    )";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $customerIdSearch;
    $types .= 'sssssssi';
}

if ($gender !== '') {
    $where[] = "gender = ?";
    $params[] = $gender;
    $types .= 's';
}

$whereSql = '';

if (!empty($where)) {
    $whereSql = 'WHERE ' . implode(' AND ', $where);
}

/* Count total customers */
$countSql = "
    SELECT COUNT(*) AS total
    FROM tb_customer
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

/* Get customers */
$listSql = "
    SELECT customer_id, first_name, last_name, gender, birth_date, phone_number, email, address
    FROM tb_customer
    $whereSql
    ORDER BY customer_id DESC
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
    <title>ຈັດການລູກຄ້າ</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/dashboard.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer.css?v=<?= time(); ?>">
</head>
<body>

<div class="dashboard-layout">

    <?php include '../../includes/admin_sidebar.php'; ?>

    <main class="main-content">

        <section class="page-header-card">
            <div>
                <h1>ຈັດການລູກຄ້າ</h1>
                <p>ລາຍຊື່ລູກຄ້າທີ່ລົງທະບຽນໃນລະບົບ</p>
            </div>
        </section>

        <section class="table-card">

            <form method="GET" class="filter-box">

                <div class="filter-group">
                    <label>ຄົ້ນຫາ</label>
                    <input
                        type="text"
                        name="search"
                        placeholder="ຄົ້ນຫາຊື່, ເບີໂທ, Email, ທີ່ຢູ່..."
                        value="<?= h($search); ?>"
                    >
                </div>

                <div class="filter-group">
                    <label>ເພດ</label>
                    <select name="gender">
                        <option value="">ທັງໝົດ</option>

                        <?php if ($genderList && $genderList->num_rows > 0): ?>
                            <?php while ($genderRow = $genderList->fetch_assoc()): ?>
                                <?php $genderValue = $genderRow['gender']; ?>
                                <option
                                    value="<?= h($genderValue); ?>"
                                    <?= ($gender === $genderValue) ? 'selected' : ''; ?>
                                >
                                    <?= h($genderValue); ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn-filter">
                        ຄົ້ນຫາ
                    </button>

                    <a href="<?= BASE_URL ?>/admin/customer/customer.php" class="btn-reset">
                        ລ້າງ
                    </a>
                </div>

            </form>

            <div class="table-info">
                <span>
                    ສະແດງ <?= $startItem; ?> - <?= $endItem; ?> ຈາກ <?= $totalRows; ?> ລາຍການ
                </span>
            </div>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ລະຫັດ</th>
                        <th>ຊື່ລູກຄ້າ</th>
                        <th>ເພດ</th>
                        <th>ວັນເດືອນປີເກີດ</th>
                        <th>ເບີໂທ</th>
                        <th>Email</th>
                        <th>ທີ່ຢູ່</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    CUS-<?= str_pad($row['customer_id'], 4, "0", STR_PAD_LEFT); ?>
                                </td>

                                <td>
                                    <?= h(trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?: '-'); ?>
                                </td>

                                <td>
                                    <?= h($row['gender'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?php if (!empty($row['birth_date'])): ?>
                                        <?= date("d-m-Y", strtotime($row['birth_date'])); ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?= h($row['phone_number'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= h($row['email'] ?? '-'); ?>
                                </td>

                                <td>
                                    <?= h($row['address'] ?? '-'); ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="no-data">
                                ບໍ່ມີຂໍ້ມູນລູກຄ້າ
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

        </section>

    </main>

</div>

</body>
</html>