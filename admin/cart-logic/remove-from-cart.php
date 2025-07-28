<?php
ob_start();
session_start();

require_once '../../includes/dbconfig.php';
require_once '../../includes/functions.php';

$is_ajax = is_ajax_request();

// CSRF Protection
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    if ($is_ajax) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
        exit;
    } else {
        set_flash_message('error', 'CSRF token validation failed. Please try again.');
        header("Location: ../../public/cart.php"); 
        exit;
    }
}

// Validate input
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) {
    if ($is_ajax) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
        exit;
    } else {
        set_flash_message('error', 'Invalid product ID.');
        header("Location: ../../public/cart.php");
        exit;
    }
}

// Get visitor token from cookie
$visitor_token = $_COOKIE['visitor_token'] ?? null;
if (!$visitor_token) {
    if ($is_ajax) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Session expired. Please refresh and try again.']);
        exit;
    } else {
        set_flash_message('error', 'Session expired. Please refresh and try again.');
        header("Location: ../../public/cart.php");
        exit;
    }
}

// Remove from session cart
if (isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
}

// Remove from persistent cart in DB
$stmt = $mysqli->prepare("DELETE FROM forever_cart WHERE visitor_token = ? AND product_id = ?");
$stmt->bind_param("si", $visitor_token, $product_id);
$stmt->execute();

if ($is_ajax) {
    // Recalculate totals
    $subtotal = 0;
    foreach ($_SESSION['cart'] as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    $taxRate = 0.05;
    $shippingCost = 15;
    $tax = $subtotal * $taxRate;
    $grandTotal = $subtotal + $tax + ($subtotal > 0 ? $shippingCost : 0);

    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Item removed from cart.',
        'totals' => [
            'subtotal' => number_format($subtotal, 2),
            'tax' => number_format($tax, 2),
            'shipping' => number_format(($subtotal > 0 ? $shippingCost : 0), 2),
            'grandTotal' => number_format($grandTotal, 2)
        ]
    ]);
    exit;
}

set_flash_message('success', 'Item removed from cart.');
header("Location: ../../public/cart.php");
exit;
