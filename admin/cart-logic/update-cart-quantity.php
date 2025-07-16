<?php
ob_start(); // Start output buffering
ini_set('display_errors', 0);

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

// Get visitor token from cookie
$visitor_token = $_COOKIE['visitor_token'] ?? null;
if (!$visitor_token) {
    set_flash_message('error', 'Visitor session not found.');
    header("Location: ../../public/cart.php");
    exit;
}

// Ensure session cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Input validation
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
$new_quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$product_id) {
    send_json_or_redirect(false, 'Invalid product ID.', $is_ajax);
}

// --- Begin Quantity Update Logic ---
if (isset($_SESSION['cart'][$product_id])) {
    if ($action === 'increase') {
        $_SESSION['cart'][$product_id]['quantity']++;
    } elseif ($action === 'decrease') {
        $_SESSION['cart'][$product_id]['quantity']--;
        if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
            unset($_SESSION['cart'][$product_id]);
        }
    } elseif ($new_quantity !== null && $new_quantity >= 0) {
        if ($new_quantity == 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
        }
    }

    // Sync with database (forever_cart)
    if (isset($_SESSION['cart'][$product_id])) {
        $quantity = $_SESSION['cart'][$product_id]['quantity'];
        $stmt = $mysqli->prepare("INSERT INTO forever_cart (visitor_token, product_id, quantity, last_updated)
                                  VALUES (?, ?, ?, NOW())
                                  ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), last_updated = NOW()");
        $stmt->bind_param("sii", $visitor_token, $product_id, $quantity);
        $stmt->execute();
    } else {
        // Remove from DB if quantity is 0
        $stmt = $mysqli->prepare("DELETE FROM forever_cart WHERE visitor_token = ? AND product_id = ?");
        $stmt->bind_param("si", $visitor_token, $product_id);
        $stmt->execute();
    }

} else {
    send_json_or_redirect(false, 'Product not found in cart.', $is_ajax);
}

// Totals
$subtotal = 0;
foreach ($_SESSION['cart'] as $cart_item) {
    $subtotal += $cart_item['price'] * $cart_item['quantity'];
}
$taxRate = 0.05;
$shippingCost = 15;
$tax = $subtotal * $taxRate;
$grandTotal = $subtotal + $tax + ($subtotal > 0 ? $shippingCost : 0);

$currentQuantity = $_SESSION['cart'][$product_id]['quantity'] ?? 0;
$itemExistsInCart = isset($_SESSION['cart'][$product_id]);

if ($is_ajax) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Cart updated successfully.',
        'cart' => $_SESSION['cart'],
        'totals' => [
            'subtotal' => number_format($subtotal, 2),
            'tax' => number_format($tax, 2),
            'shipping' => number_format(($subtotal > 0 ? $shippingCost : 0), 2),
            'grandTotal' => number_format($grandTotal, 2)
        ],
        'updatedItemId' => $product_id,
        'newItemQuantity' => $currentQuantity,
        'itemRemoved' => !$itemExistsInCart && $new_quantity == 0
    ]);
    exit;
}

header("Location: ../../public/cart.php");
exit;

// Helper function
function send_json_or_redirect($success, $message, $is_ajax) {
    if ($is_ajax) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    } else {
        set_flash_message($success ? 'success' : 'error', $message);
        header("Location: ../../public/cart.php");
        exit;
    }
}
