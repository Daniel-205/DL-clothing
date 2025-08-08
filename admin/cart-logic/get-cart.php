<?php
// get-cart.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// The logic from calculate_cart_totals() is now inside this file.
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
$totalItems = 0;

if (is_array($cart)) {
    foreach ($cart as $item) {
        if (isset($item['price']) && isset($item['quantity'])) {
            $subtotal += $item['price'] * $item['quantity'];
            $totalItems += $item['quantity'];
        }
    }
}

$tax_rate = 0.00; // 0% tax
$shipping = $subtotal > 0 ? 15.00 : 0.00;
$tax = $subtotal * $tax_rate;
$grand_total = $subtotal + $tax + $shipping;

$totals = [
    'subtotal' => number_format($subtotal, 2, '.', ''),
    'tax' => number_format($tax, 2, '.', ''),
    'shipping' => number_format($shipping, 2, '.', ''),
    'grandTotal' => number_format($grand_total, 2, '.', ''),
    'totalItems' => $totalItems
];

$response = [
    'success' => true,
    'data' => [
        'cart' => $cart,
        'totals' => $totals
    ]
];

echo json_encode($response);
?>
