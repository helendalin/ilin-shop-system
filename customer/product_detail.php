<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "/customer/products.php");
    exit();
}

$product_id = intval($_GET['id']);

$stmt = $conn->prepare("
    SELECT p.*, c.category_name
    FROM tb_product p
    LEFT JOIN tb_category c ON p.category_id = c.category_id
    WHERE p.product_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: " . BASE_URL . "/customer/products.php");
    exit();
}

$relatedStmt = $conn->prepare("
    SELECT product_id, product_name, price, image, qty
    FROM tb_product
    WHERE product_id != ?
    AND category_id = ?
    ORDER BY RAND()
    LIMIT 4
");
$relatedStmt->bind_param("ii", $product_id, $product['category_id']);
$relatedStmt->execute();
$related = $relatedStmt->get_result();

$qty = intval($product['qty']);

/* Rating summary */
$ratingStmt = $conn->prepare("
    SELECT 
        ROUND(AVG(rating), 1) AS average_rating,
        COUNT(*) AS rating_count
    FROM tb_product_rating
    WHERE product_id = ?
");
$ratingStmt->bind_param("i", $product_id);
$ratingStmt->execute();
$ratingSummary = $ratingStmt->get_result()->fetch_assoc();

$averageRating = $ratingSummary && $ratingSummary['average_rating'] !== null ? floatval($ratingSummary['average_rating']) : 0;
$ratingCount = $ratingSummary ? intval($ratingSummary['rating_count']) : 0;

/* Customer's own rating */
$customerRating = 0;

if (isset($_SESSION['customer_id'])) {
    $customer_id = intval($_SESSION['customer_id']);

    $customerRatingStmt = $conn->prepare("
        SELECT rating
        FROM tb_product_rating
        WHERE product_id = ? AND customer_id = ?
        LIMIT 1
    ");
    $customerRatingStmt->bind_param("ii", $product_id, $customer_id);
    $customerRatingStmt->execute();
    $customerRatingRow = $customerRatingStmt->get_result()->fetch_assoc();

    if ($customerRatingRow) {
        $customerRating = intval($customerRatingRow['rating']);
    }
}

$initialStarRating = $customerRating > 0 ? $customerRating : intval(round($averageRating));

if ($ratingCount > 0) {
    $ratingText = number_format($averageRating, 1) . ' | ' . number_format($ratingCount) . ' ຄະແນນ';
} else {
    $ratingText = 'ຍັງບໍ່ມີຄະແນນ | ກົດໃຫ້ຄະແນນ';
}

if ($customerRating > 0) {
    $ratingText .= ' | ຄະແນນຂອງທ່ານ ' . $customerRating;
}

$isLoggedIn = isset($_SESSION['customer_id']);
?>

<!DOCTYPE html>
<html lang="lo">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?> - ILIN SHOP</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/header.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/product-detail.css?v=<?= time(); ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/customer/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<?php include 'header.php'; ?>

<section class="detail-page">

    <div class="detail-layout">

        <div class="detail-image">

            <?php if (!empty($product['image'])): ?>
                <img
                    src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
                    alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            <?php else: ?>
                <img
                    src="<?= BASE_URL ?>/assets/images/no-product.png"
                    alt="<?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            <?php endif; ?>

            <?php if ($qty <= 0): ?>
                <span class="detail-badge out">ສິນຄ້າໝົດ</span>
            <?php elseif ($qty <= 5): ?>
                <span class="detail-badge low">ເຫຼືອນ້ອຍ</span>
            <?php endif; ?>

        </div>

        <div class="detail-info">

            <span class="product-category">
                <?= htmlspecialchars($product['category_name'] ?? 'ສິນຄ້າ', ENT_QUOTES, 'UTF-8'); ?>
            </span>

            <h1><?= htmlspecialchars($product['product_name'], ENT_QUOTES, 'UTF-8'); ?></h1>

            <div class="rating-row">
                <div
                    class="rating-stars"
                    id="ratingStars"
                    data-product-id="<?= intval($product['product_id']); ?>"
                    data-user-rating="<?= intval($customerRating); ?>"
                    data-display-rating="<?= intval($initialStarRating); ?>"
                >
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button
                            type="button"
                            class="star-btn <?= ($i <= $initialStarRating) ? 'active' : ''; ?>"
                            data-rating="<?= $i; ?>"
                            aria-label="<?= $i; ?> star"
                        >★</button>
                    <?php endfor; ?>
                </div>

                <small id="ratingText" class="<?= $customerRating > 0 ? 'rated' : ''; ?>">
                    <?= htmlspecialchars($ratingText, ENT_QUOTES, 'UTF-8'); ?>
                </small>
            </div>

            <input type="hidden" id="selectedRating" value="<?= intval($customerRating); ?>">

            <div class="detail-price">
                <?= number_format(floatval($product['price'])); ?> ₭
            </div>

            <?php if ($qty <= 0): ?>
                <p class="detail-stock out-stock">
                    ສິນຄ້າໝົດ
                </p>
            <?php elseif ($qty <= 5): ?>
                <p class="detail-stock low-stock">
                    ເຫຼືອອີກບໍ່ຫຼາຍ
                </p>
            <?php else: ?>
                <p class="detail-stock in-stock">
                    ມີສິນຄ້າພ້ອມຈັດສົ່ງ
                </p>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/customer/add_to_cart.php" method="POST">

                <input type="hidden" name="product_id" value="<?= intval($product['product_id']); ?>">

                <div class="quantity-wrapper">

                    <label>ຈຳນວນ</label>

                    <div class="quantity-box">

                        <button
                            type="button"
                            class="qty-btn"
                            onclick="changeQty(-1)"
                            <?= ($qty <= 0) ? 'disabled' : ''; ?>
                        >
                            −
                        </button>

                        <input
                            type="number"
                            id="qty"
                            name="qty"
                            value="<?= ($qty <= 0) ? 0 : 1; ?>"
                            min="<?= ($qty <= 0) ? 0 : 1; ?>"
                            max="<?= max($qty, 0); ?>"
                            readonly
                        >

                        <button
                            type="button"
                            class="qty-btn"
                            onclick="changeQty(1)"
                            <?= ($qty <= 0) ? 'disabled' : ''; ?>
                        >
                            +
                        </button>

                    </div>

                    <p class="qty-error" id="qtyError"></p>

                    <?php if (isset($_GET['error']) && $_GET['error'] == "stock_not_enough"): ?>
                        <p class="qty-error">
                            ຈຳນວນສິນຄ້າບໍ່ພຽງພໍ
                        </p>
                    <?php endif; ?>

                </div>

                <div class="detail-description">

                    <h3>ລາຍລະອຽດສິນຄ້າ</h3>

                    <p>
                        <?= !empty($product['description'])
                            ? nl2br(htmlspecialchars($product['description'], ENT_QUOTES, 'UTF-8'))
                            : 'ບໍ່ມີລາຍລະອຽດ'; ?>
                    </p>

                </div>

                <div class="detail-actions">

                    <button
                        type="submit"
                        class="primary-btn"
                        <?= ($qty <= 0) ? 'disabled' : ''; ?>
                    >
                        <i class="fa-solid fa-cart-plus"></i>
                        ເພີ່ມໃສ່ກະຕ່າ
                    </button>

                    <a href="<?= BASE_URL ?>/customer/products.php" class="secondary-btn">
                        ກັບໄປສິນຄ້າ
                    </a>

                </div>

            </form>

        </div>

    </div>

</section>

<section class="related-section">

    <div class="section-title">
        <h2>ສິນຄ້າທີ່ກ່ຽວຂ້ອງ</h2>
    </div>

    <div class="related-grid">

        <?php while ($row = $related->fetch_assoc()): ?>

            <a class="related-card" href="<?= BASE_URL ?>/customer/product_detail.php?id=<?= intval($row['product_id']); ?>">

                <div class="related-image">

                    <?php if (!empty($row['image'])): ?>
                        <img
                            src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>"
                            alt="<?= htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    <?php else: ?>
                        <img
                            src="<?= BASE_URL ?>/assets/images/no-product.png"
                            alt="<?= htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    <?php endif; ?>

                </div>

                <div class="related-info">

                    <h3><?= htmlspecialchars($row['product_name'], ENT_QUOTES, 'UTF-8'); ?></h3>

                    <strong><?= number_format(floatval($row['price'])); ?> ₭</strong>

                    <?php if (intval($row['qty']) <= 0): ?>
                        <span class="related-stock out">ສິນຄ້າໝົດ</span>
                    <?php elseif (intval($row['qty']) <= 5): ?>
                        <span class="related-stock low">ເຫຼືອນ້ອຍ</span>
                    <?php endif; ?>

                </div>

            </a>

        <?php endwhile; ?>

    </div>

</section>

<?php include 'footer.php'; ?>

<script>
function changeQty(step) {
    const input = document.getElementById("qty");
    const error = document.getElementById("qtyError");

    if (!input) {
        return;
    }

    let value = parseInt(input.value, 10);
    let max = parseInt(input.max, 10);

    if (isNaN(value)) {
        value = 1;
    }

    if (isNaN(max)) {
        max = 1;
    }

    value += step;

    if (max <= 0) {
        input.value = 0;
        if (error) {
            error.innerHTML = "ສິນຄ້າໝົດ";
        }
        return;
    }

    if (value < 1) {
        value = 1;
    }

    if (value > max) {
        value = max;
        if (error) {
            error.innerHTML = "ຈຳນວນສິນຄ້າບໍ່ພຽງພໍ";
        }
    } else {
        if (error) {
            error.innerHTML = "";
        }
    }

    input.value = value;
}

document.addEventListener('DOMContentLoaded', function () {
    const ratingStars = document.getElementById('ratingStars');
    const stars = document.querySelectorAll('.star-btn');
    const ratingText = document.getElementById('ratingText');
    const selectedRating = document.getElementById('selectedRating');

    if (!ratingStars || stars.length === 0 || !ratingText) {
        return;
    }

    const isLoggedIn = <?= $isLoggedIn ? 'true' : 'false'; ?>;
    const loginUrl = "<?= BASE_URL ?>/auth/customer/login.php?error=Please login first before rating";
    const saveRatingUrl = "<?= BASE_URL ?>/customer/save_rating.php";

    const productId = parseInt(ratingStars.dataset.productId || '0', 10);

    let currentRating = parseInt(ratingStars.dataset.userRating || '0', 10);
    let displayRating = parseInt(ratingStars.dataset.displayRating || '0', 10);
    let defaultText = ratingText.textContent;

    function updateStars(rating) {
        stars.forEach(function (star) {
            const starValue = parseInt(star.dataset.rating, 10);

            if (starValue <= rating) {
                star.classList.add('active');
            } else {
                star.classList.remove('active');
            }
        });
    }

    function restoreStars() {
        if (currentRating > 0) {
            updateStars(currentRating);
        } else {
            updateStars(displayRating);
        }

        ratingText.textContent = defaultText;
    }

    function setStarsDisabled(disabled) {
        stars.forEach(function (star) {
            star.disabled = disabled;
        });
    }

    stars.forEach(function (star) {
        star.addEventListener('mouseenter', function () {
            const hoverRating = parseInt(this.dataset.rating, 10);
            updateStars(hoverRating);
            ratingText.textContent = hoverRating + ' ດາວ';
        });

        star.addEventListener('mouseleave', function () {
            restoreStars();
        });

        star.addEventListener('click', function () {
            const clickedRating = parseInt(this.dataset.rating, 10);

            if (!isLoggedIn) {
                const goLogin = confirm('ກະລຸນາເຂົ້າສູ່ລະບົບກ່ອນໃຫ້ຄະແນນ. ຕ້ອງການໄປໜ້າ Login ບໍ?');

                if (goLogin) {
                    window.location.href = loginUrl;
                }

                return;
            }

            if (productId <= 0 || clickedRating < 1 || clickedRating > 5) {
                alert('Invalid rating');
                return;
            }

            setStarsDisabled(true);
            ratingText.textContent = 'ກຳລັງບັນທຶກ...';

            const formData = new URLSearchParams();
            formData.append('product_id', productId);
            formData.append('rating', clickedRating);

            fetch(saveRatingUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.login_required) {
                    window.location.href = data.login_url;
                    return;
                }

                if (!data.success) {
                    ratingText.textContent = defaultText;
                    alert(data.message || 'Cannot save rating');
                    return;
                }

                currentRating = parseInt(data.user_rating, 10);
                displayRating = currentRating;

                if (selectedRating) {
                    selectedRating.value = currentRating;
                }

                const averageRating = parseFloat(data.average_rating || 0).toFixed(1);
                const ratingCount = parseInt(data.rating_count || 0, 10);

                defaultText = averageRating + ' | ' + ratingCount + ' ຄະແນນ | ຄະແນນຂອງທ່ານ ' + currentRating;

                ratingText.textContent = defaultText;
                ratingText.classList.add('rated');

                updateStars(currentRating);
            })
            .catch(function () {
                ratingText.textContent = defaultText;
                alert('Cannot connect to server');
            })
            .finally(function () {
                setStarsDisabled(false);
            });
        });
    });
});
</script>

</body>
</html>