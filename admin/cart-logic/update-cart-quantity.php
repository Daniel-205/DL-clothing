<?php
// Check if the request is AJAX
ob_start(); // Start output buffering
ini_set('display_errors', 0); // Avoid leaking PHP warnings into AJAX response

session_start();
require_once '../../includes/dbconfig.php';
require_once '../../includes/functions.php'; 
// require_once '../../includes/session-cart.php';

$is_ajax = is_ajax_request(); // Check once

// CSRF Protection
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    if ($is_ajax) {
        ob_clean(); // clean buffer
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'CSRF token validation failed.']);
        exit;
    } else {
        set_flash_message('error', 'CSRF token validation failed. Please try again.');
        header("Location: ../../public/cart.php"); 
        exit;
    }
}

}

// Ensure cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Validate product_id
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) {
    set_flash_message('error', 'Invalid product ID.');
    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
        exit;
    }
    header("Location: ../../public/cart.php");
    exit;
}

// Validate action (increase, decrease) or new quantity
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
$new_quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (isset($_SESSION['cart'][$product_id])) {
    if ($action === 'increase') {
        $_SESSION['cart'][$product_id]['quantity']++;
    } elseif ($action === 'decrease') {
        $_SESSION['cart'][$product_id]['quantity']--;
        if ($_SESSION['cart'][$product_id]['quantity'] <= 0) {
            unset($_SESSION['cart'][$product_id]); // Remove item if quantity is 0 or less
        }
    } elseif ($new_quantity !== null && $new_quantity >= 0) {
        // This part allows setting a specific quantity directly, e.g., from an input field
        if ($new_quantity == 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
        }
    } else {
        // Invalid action or quantity, do nothing or set a flash message
        set_flash_message('error', "Invalid quantity update.");
    }
} else {
    set_flash_message('error', "Product not found in cart for quantity update.");
    if ($is_ajax) { // Use the $is_ajax variable
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Product not found in cart.']);
        exit;
    }
}

// If we reach here and it's an AJAX request, it implies an update occurred or an item was removed.
if ($is_ajax) { // Use the $is_ajax variable
    // Recalculate totals for the response
    $subtotal = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $cart_item) { // Renamed to avoid conflict with $item outside if needed
            $subtotal += $cart_item['price'] * $cart_item['quantity'];
        }
    }
    $taxRate = 0.05;
    $shippingCost = 15; // Fixed shipping cost
    $tax = $subtotal * $taxRate;
    $grandTotal = $subtotal + $tax + ($subtotal > 0 ? $shippingCost : 0);

    $flash = get_flash_message(); // Get and clear any flash message that might have been set
    $message = $flash['message'] ?? 'Cart updated successfully.';
    $success = !($flash && $flash['type'] === 'error'); // Success is true if no error flash message was set

    // If an item quantity became 0 and it was removed, $new_quantity might be 0
    // and the item won't be in $_SESSION['cart'][$product_id]
    $itemExistsInCart = isset($_SESSION['cart'][$product_id]);
    $currentQuantity = $itemExistsInCart ? $_SESSION['cart'][$product_id]['quantity'] : 0;

    ob_clean();// Clear the output buffer
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'cart' => $_SESSION['cart'] ?? [], // Send current state of cart
        'totals' => [
            'subtotal' => number_format($subtotal, 2),
            'tax' => number_format($tax, 2),
            'shipping' => number_format(($subtotal > 0 ? $shippingCost : 0), 2),
            'grandTotal' => number_format($grandTotal, 2)
        ],
        'updatedItemId' => $product_id,
        'newItemQuantity' => $currentQuantity, // This will be 0 if item is removed
        'itemRemoved' => !$itemExistsInCart && $new_quantity == 0 // Flag if item was removed due to quantity set to 0
    ]);
    exit;
}

// Redirect back to the cart page for non-AJAX requests
header("Location: ../../public/cart.php");
exit;
?>
