<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
    exit();
}

if (!isset($_SESSION['customer_id'])) {
    echo json_encode([
        'success' => false,
        'login_required' => true,
        'login_url' => BASE_URL . '/auth/customer/login.php?error=Please login first before rating'
    ]);
    exit();
}

$customer_id = intval($_SESSION['customer_id']);
$product_id = intval($_POST['product_id'] ?? 0);
$rating = intval($_POST['rating'] ?? 0);

if ($product_id <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid rating'
    ]);
    exit();
}

/* Check product exists */
$productStmt = $conn->prepare("
    SELECT product_id
    FROM tb_product
    WHERE product_id = ?
    LIMIT 1
");
$productStmt->bind_param("i", $product_id);
$productStmt->execute();
$product = $productStmt->get_result()->fetch_assoc();

if (!$product) {
    echo json_encode([
        'success' => false,
        'message' => 'Product not found'
    ]);
    exit();
}

/* Insert or update customer rating */
$ratingStmt = $conn->prepare("
    INSERT INTO tb_product_rating (product_id, customer_id, rating)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
        rating = VALUES(rating),
        updated_at = CURRENT_TIMESTAMP
");
$ratingStmt->bind_param("iii", $product_id, $customer_id, $rating);

if (!$ratingStmt->execute()) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot save rating'
    ]);
    exit();
}

/* Get new average rating */
$summaryStmt = $conn->prepare("
    SELECT 
        ROUND(AVG(rating), 1) AS average_rating,
        COUNT(*) AS rating_count
    FROM tb_product_rating
    WHERE product_id = ?
");
$summaryStmt->bind_param("i", $product_id);
$summaryStmt->execute();
$summary = $summaryStmt->get_result()->fetch_assoc();

$averageRating = $summary && $summary['average_rating'] !== null ? floatval($summary['average_rating']) : 0;
$ratingCount = $summary ? intval($summary['rating_count']) : 0;

echo json_encode([
    'success' => true,
    'message' => 'Rating saved',
    'average_rating' => $averageRating,
    'rating_count' => $ratingCount,
    'user_rating' => $rating
]);
exit();
?>