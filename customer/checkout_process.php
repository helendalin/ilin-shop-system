<?php
include '../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* Customer must login first */
if (!isset($_SESSION['customer_id'])) {
    header("Location: " . BASE_URL . "/auth/customer/login.php?error=Please login first before checkout");
    exit();
}

/* Only allow POST request */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/customer/cart.php");
    exit();
}

$customer_id = intval($_SESSION['customer_id']);
$cart = $_SESSION['cart'] ?? [];

/* Cart must not be empty */
if (empty($cart) || !is_array($cart)) {
    header("Location: " . BASE_URL . "/customer/cart.php");
    exit();
}

/* Checkout session must exist */
if (empty($_SESSION['checkout_shipping']) || empty($_SESSION['checkout_delivery'])) {
    header("Location: " . BASE_URL . "/customer/checkout.php");
    exit();
}

$shipping = $_SESSION['checkout_shipping'];
$delivery = $_SESSION['checkout_delivery'];

/* Shipping information */
$first_name = trim($shipping['first_name'] ?? '');
$last_name = trim($shipping['last_name'] ?? '');
$phone_number = trim($shipping['phone_number'] ?? '');
$email = trim($shipping['email'] ?? '');
$address = trim($shipping['address'] ?? '');

/* Validate shipping information */
if (
    $first_name === '' ||
    $last_name === '' ||
    $phone_number === '' ||
    $email === '' ||
    $address === ''
) {
    header("Location: " . BASE_URL . "/customer/checkout.php?error=Please fill all fields");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: " . BASE_URL . "/customer/checkout.php?error=Invalid email address");
    exit();
}

/* Delivery method */
$allowed_delivery_methods = ['standard', 'express', 'pickup'];
$delivery_method = $delivery['delivery_method'] ?? 'standard';

if (!in_array($delivery_method, $allowed_delivery_methods, true)) {
    $delivery_method = 'standard';
}

$delivery_fee = 20000;

if ($delivery_method === 'express') {
    $delivery_fee = 35000;
} elseif ($delivery_method === 'pickup') {
    $delivery_fee = 0;
}

/* Payment method */
$allowed_payment_methods = ['cod', 'bank_transfer', 'qr_payment'];
$payment_method = $_POST['payment_method'] ?? 'cod';

if (!in_array($payment_method, $allowed_payment_methods, true)) {
    $payment_method = 'cod';
}

$payment_status = 'pending';

if ($payment_method === 'cod') {
    $payment_status = 'cod_pending';
}

$payment_slip = null;
$uploadedSlipPath = null;

/* Upload payment slip when customer chooses bank transfer or QR payment */
if ($payment_method === 'bank_transfer' || $payment_method === 'qr_payment') {
    if (!isset($_FILES['payment_slip']) || $_FILES['payment_slip']['error'] !== UPLOAD_ERR_OK) {
        header("Location: " . BASE_URL . "/customer/checkout_payment.php?error=Please upload payment slip");
        exit();
    }

    $maxFileSize = 5 * 1024 * 1024;
    $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp'];

    $originalName = $_FILES['payment_slip']['name'];
    $tmpName = $_FILES['payment_slip']['tmp_name'];
    $fileSize = intval($_FILES['payment_slip']['size']);
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($fileSize <= 0 || $fileSize > $maxFileSize) {
        header("Location: " . BASE_URL . "/customer/checkout_payment.php?error=Payment slip must be less than 5MB");
        exit();
    }

    if (!in_array($ext, $allowedExt, true)) {
        header("Location: " . BASE_URL . "/customer/checkout_payment.php?error=Invalid slip image type");
        exit();
    }

    if (!is_uploaded_file($tmpName)) {
        header("Location: " . BASE_URL . "/customer/checkout_payment.php?error=Invalid uploaded file");
        exit();
    }

    $imageInfo = @getimagesize($tmpName);
    if ($imageInfo === false) {
        header("Location: " . BASE_URL . "/customer/checkout_payment.php?error=Uploaded file is not a valid image");
        exit();
    }

    $mimeType = $imageInfo['mime'] ?? '';
    if (!in_array($mimeType, $allowedMime, true)) {
        header("Location: " . BASE_URL . "/customer/checkout_payment.php?error=Invalid slip image format");
        exit();
    }

    $uploadDir = __DIR__ . '/../assets/images/payments/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $payment_slip = 'payment_' . date('YmdHis') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $uploadedSlipPath = $uploadDir . $payment_slip;

    if (!move_uploaded_file($tmpName, $uploadedSlipPath)) {
        header("Location: " . BASE_URL . "/customer/checkout_payment.php?error=Upload slip failed");
        exit();
    }
}

/* Clean and combine cart items */
$cartItems = [];

foreach ($cart as $item) {
    $product_id = isset($item['product_id']) ? intval($item['product_id']) : 0;
    $qty = isset($item['qty']) ? intval($item['qty']) : 0;

    if ($product_id <= 0 || $qty <= 0) {
        continue;
    }

    if (!isset($cartItems[$product_id])) {
        $cartItems[$product_id] = 0;
    }

    $cartItems[$product_id] += $qty;
}

if (empty($cartItems)) {
    if ($uploadedSlipPath && file_exists($uploadedSlipPath)) {
        unlink($uploadedSlipPath);
    }

    header("Location: " . BASE_URL . "/customer/cart.php");
    exit();
}

$conn->begin_transaction();

try {
    /* Update customer profile with latest checkout information */
    $updateCustomer = $conn->prepare("
        UPDATE tb_customer
        SET first_name = ?, last_name = ?, phone_number = ?, email = ?, address = ?
        WHERE customer_id = ?
    ");

    $updateCustomer->bind_param(
        "sssssi",
        $first_name,
        $last_name,
        $phone_number,
        $email,
        $address,
        $customer_id
    );

    if (!$updateCustomer->execute()) {
        throw new Exception("Cannot update customer information");
    }

    /* Prepare product checking */
    $productStmt = $conn->prepare("
        SELECT product_id, product_name, price, qty
        FROM tb_product
        WHERE product_id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $product_total = 0;
    $orderItems = [];

    foreach ($cartItems as $product_id => $qty) {
        $productStmt->bind_param("i", $product_id);
        $productStmt->execute();

        $product = $productStmt->get_result()->fetch_assoc();

        if (!$product) {
            throw new Exception("Product not found");
        }

        $current_stock = intval($product['qty']);
        $price = floatval($product['price']);

        if ($current_stock < $qty) {
            throw new Exception("Stock not enough");
        }

        $subtotal = $price * $qty;
        $product_total += $subtotal;

        $orderItems[] = [
            'product_id' => intval($product['product_id']),
            'qty' => $qty,
            'price' => $price,
            'subtotal' => $subtotal
        ];
    }

    $grand_total = $product_total + $delivery_fee;

    $emp_id = 1;
    $status = 'pending';

    /* Create sale order */
    $saleStmt = $conn->prepare("
        INSERT INTO tb_sale
        (
            customer_id,
            emp_id,
            total_amount,
            status,
            delivery_method,
            delivery_fee,
            payment_method,
            payment_status,
            payment_slip
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $saleStmt->bind_param(
        "iidssdsss",
        $customer_id,
        $emp_id,
        $grand_total,
        $status,
        $delivery_method,
        $delivery_fee,
        $payment_method,
        $payment_status,
        $payment_slip
    );

    if (!$saleStmt->execute()) {
        throw new Exception("Cannot create sale order");
    }

    $sale_id = $conn->insert_id;

    /* Insert sale details */
    $detailStmt = $conn->prepare("
        INSERT INTO tb_sale_detail
        (sale_id, product_id, qty, price, subtotal)
        VALUES (?, ?, ?, ?, ?)
    ");

    /* Update product stock */
    $stockStmt = $conn->prepare("
        UPDATE tb_product
        SET qty = qty - ?
        WHERE product_id = ? AND qty >= ?
    ");

    foreach ($orderItems as $orderItem) {
        $product_id = intval($orderItem['product_id']);
        $qty = intval($orderItem['qty']);
        $price = floatval($orderItem['price']);
        $subtotal = floatval($orderItem['subtotal']);

        $detailStmt->bind_param(
            "iiidd",
            $sale_id,
            $product_id,
            $qty,
            $price,
            $subtotal
        );

        if (!$detailStmt->execute()) {
            throw new Exception("Cannot save sale detail");
        }

        $stockStmt->bind_param("iii", $qty, $product_id, $qty);

        if (!$stockStmt->execute()) {
            throw new Exception("Cannot update stock");
        }

        if ($stockStmt->affected_rows === 0) {
            throw new Exception("Stock not enough");
        }
    }

    $conn->commit();

    unset($_SESSION['cart']);
    unset($_SESSION['checkout_shipping']);
    unset($_SESSION['checkout_delivery']);

    header("Location: " . BASE_URL . "/customer/order_success.php?sale_id=" . intval($sale_id));
    exit();

} catch (Exception $e) {
    $conn->rollback();

    if ($uploadedSlipPath && file_exists($uploadedSlipPath)) {
        unlink($uploadedSlipPath);
    }

    header("Location: " . BASE_URL . "/customer/checkout_payment.php?error=Order failed. Please check your cart and try again");
    exit();
}
?>