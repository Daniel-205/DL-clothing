<?php
session_start();

// Ensure cart exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Validate product_id
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) {
    // Product ID is invalid or missing, redirect to cart
    header("Location: cart.php");
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
        $_SESSION['flash_message'] = "Invalid quantity update.";
    }
} else {
    $_SESSION['flash_message'] = "Product not found in cart for quantity update.";
}

// Redirect back to the cart page
header("Location: cart.php");
exit;
?>
