<?php
session_start();
require_once '../../includes/dbconfig.php';
require_once '../../includes/functions.php';

// This script now handles AJAX requests from product pages.

// --- Validation and Security ---
if (!is_ajax_request()) {
    send_json_response(false, 'Invalid request type.');
}

// CSRF token should be sent with the add to cart request.
// We'll assume the new JS will send it.
if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    send_json_response(false, 'CSRF token validation failed.');
}

$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$product_id || !$quantity || $quantity < 1) {
    send_json_response(false, 'Invalid product or quantity.');
}

// --- Fetch Product and Visitor Info ---
$visitor_token = get_or_create_visitor_token();

$stmt = $mysqli->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    send_json_response(false, 'Product not found.');
}
$product = $result->fetch_assoc();

// --- Core Cart Logic (Session and DB) ---

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Update session cart
if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    $new_quantity = $_SESSION['cart'][$product_id]['quantity'];
} else {
    $_SESSION['cart'][$product_id] = [
        'id'       => $product['id'],
        'name'     => $product['name'],
        'price'    => $product['price'],
        'image'    => $product['image'],
        'quantity' => $quantity
    ];
    $new_quantity = $quantity;
}

// Update persistent cart in DB
$stmt = $mysqli->prepare("
    INSERT INTO forever_cart (visitor_token, product_id, quantity, last_updated) 
    VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity), last_updated = NOW()
");
// Note: Using 'quantity + VALUES(quantity)' is a neat trick to increment on duplicate.
// However, to be consistent with our session logic, we'll just set the new total quantity.
$stmt_update = $mysqli->prepare("
    INSERT INTO forever_cart (visitor_token, product_id, quantity, last_updated)
    VALUES (?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE quantity = ?, last_updated = NOW()
");
$stmt_update->bind_param("siii", $visitor_token, $product_id, $new_quantity, $new_quantity);
$stmt_update->execute();


// --- Prepare and Send Response ---
$totals = calculate_cart_totals($_SESSION['cart']);

// Manually calculate total items
$totalItems = 0;
foreach ($_SESSION['cart'] as $item) {
    $totalItems += $item['quantity'];
}
$totals['totalItems'] = $totalItems;

$response_data = [
    'addedItem' => $_SESSION['cart'][$product_id],
    'totals' => $totals,
    'cart' => $_SESSION['cart'] // Send the whole cart back
];

send_json_response(true, 'Product added to cart!', $response_data);

?>
