<?php
session_start();

require_once '../../includes/dbconfig.php';
require_once '../../includes/functions.php';

// Use our standardized helper for all AJAX responses.

// Verify this is a legitimate AJAX request.
if (!is_ajax_request()) {
    send_json_response(false, 'Invalid request type.');
}

// Protect against Cross-Site Request Forgery.
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    send_json_response(false, 'CSRF token validation failed. Please refresh and try again.');
}

// Sanitize and validate the product ID from the POST request.
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) {
    send_json_response(false, 'Invalid product ID specified.');
}

// Get the visitor's unique token.
$visitor_token = get_or_create_visitor_token();
if (!$visitor_token) {
    // This case is unlikely if get_or_create_visitor_token works correctly, but it's good practice to check.
    send_json_response(false, 'Could not identify visitor session.');
}

// --- Core Removal Logic ---

// Remove the item from the session cart if it exists.
if (isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
}

// Also remove the item from the persistent database cart to ensure consistency.
// Use a prepared statement to prevent SQL injection.
$stmt = $mysqli->prepare("DELETE FROM forever_cart WHERE visitor_token = ? AND product_id = ?");
$stmt->bind_param("si", $visitor_token, $product_id);

if (!$stmt->execute()) {
    // Log the actual database error for debugging instead of showing it to the user.
    error_log('Cart DB Deletion Failed: ' . $stmt->error);
    send_json_response(false, 'Could not remove item from cart due to a database error.');
}

// --- Prepare and Send Response ---

// Recalculate the cart totals after removal.
$totals = calculate_cart_totals($_SESSION['cart'] ?? []);

// Manually calculate total items since we can't modify the helper function.
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalItems += $item['quantity'];
}
$totals['totalItems'] = $totalItems;

// The JavaScript expects a `totals` object in the response to update the summary.
$response_data = ['totals' => $totals];

// Send a standardized success response back to the browser.
send_json_response(true, 'Item removed successfully.', $response_data);

?>
