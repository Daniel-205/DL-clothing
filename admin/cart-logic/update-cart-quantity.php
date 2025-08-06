<?php
session_start();

// This file is the single source of truth for AJAX updates to the cart quantity.
// It handles increasing, decreasing, and removing items from the cart.

require_once '../../includes/dbconfig.php';
require_once '../../includes/functions.php';

// We don't need secure_session_start() as session_start() is sufficient here
// and the function is not defined anywhere.

// Verify this is a legitimate AJAX request.
if (!is_ajax_request()) {
    // Respond with an error and exit if it's not an AJAX request.
    send_json_response(false, 'Invalid request type.');
}

// Protect against Cross-Site Request Forgery.
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    send_json_response(false, 'CSRF token validation failed. Please refresh and try again.');
}

// Get a unique token for the visitor's device to manage their cart.
$visitor_token = get_or_create_visitor_token();

// Sanitize and validate the product ID and action from the POST request.
// Use filter_input for security, as it's more robust than custom functions.
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_FULL_SPECIAL_CHARS); // A safer filter

// Ensure the parameters are valid before proceeding.
if (!$product_id || !in_array($action, ['increase', 'decrease'])) {
    send_json_response(false, 'Invalid product or action specified.');
}

// Initialize the cart in the session if it doesn't already exist.
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if the product is actually in the cart before trying to update it.
if (!isset($_SESSION['cart'][$product_id])) {
    send_json_response(false, 'The product you are trying to update is not in your cart.');
}

// --- Core Quantity Update Logic ---

$current_quantity = $_SESSION['cart'][$product_id]['quantity'];
$new_quantity = $current_quantity;
$item_removed = false;

if ($action === 'increase') {
    // Increment the quantity. Add checks for stock limits here if needed.
    $new_quantity++;
} else { // action === 'decrease'
    // Decrement the quantity.
    $new_quantity--;
}

// If quantity drops to zero or below, remove the item from the cart.
if ($new_quantity <= 0) {
    unset($_SESSION['cart'][$product_id]);
    $item_removed = true;
} else {
    // Otherwise, update the quantity in the session.
    $_SESSION['cart'][$product_id]['quantity'] = $new_quantity;
}

// --- Database Synchronization ---

// Use a prepared statement to prevent SQL injection.
if ($item_removed) {
    // If the item was removed from the cart, delete it from the persistent database cart.
    $stmt = $mysqli->prepare("DELETE FROM forever_cart WHERE visitor_token = ? AND product_id = ?");
    $stmt->bind_param("si", $visitor_token, $product_id);
} else {
    // If the quantity was updated, insert or update the record in the database.
    // ON DUPLICATE KEY UPDATE handles both cases efficiently.
    $stmt = $mysqli->prepare("
        INSERT INTO forever_cart (visitor_token, product_id, quantity, last_updated)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE quantity = VALUES(quantity), last_updated = NOW()
    ");
    $stmt->bind_param("sii", $visitor_token, $product_id, $new_quantity);
}

// Execute the database query and handle potential errors.
if (!$stmt->execute()) {
    // Log the actual database error for debugging instead of showing it to the user.
    error_log('Cart DB Update Failed: ' . $stmt->error);
    send_json_response(false, 'Could not update your cart due to a database error.');
}

// --- Prepare and Send Response ---

// Calculate the new totals for the entire cart.
$totals = calculate_cart_totals($_SESSION['cart']);

// Manually calculate total items since we can't modify the helper function.
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalItems += $item['quantity'];
}
$totals['totalItems'] = $totalItems;


// Prepare the data payload for the JSON response.
$response_data = [
    'newQuantity' => $item_removed ? 0 : $new_quantity,
    'itemRemoved' => $item_removed,
    'totals' => $totals
];

// Send a standardized success response back to the browser.
send_json_response(true, 'Cart updated successfully!', $response_data);

?>
