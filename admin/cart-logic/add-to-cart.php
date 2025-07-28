<?php
session_start();
require_once '../../includes/dbconfig.php';
require_once '../../includes/functions.php';

// Generate or retrieve visitor token (persistent user identifier)
if (!isset($_COOKIE['visitor_token'])) {
    $visitor_token = bin2hex(random_bytes(16));
    setcookie('visitor_token', $visitor_token, time() + (86400 * 30), "/"); // Expires in 30 days
} else {
    $visitor_token = $_COOKIE['visitor_token'];
}

// Input validation
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

if (!$product_id || !$quantity || $quantity < 1) {
    $_SESSION['flash_message'] = "Invalid product or quantity.";
    header("Location: ../../public/cart.php");
    exit;
}

// Fetch product info
$stmt = $mysqli->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_message'] = "Product not found.";
    header("Location: ../../public/cart.php");
    exit;
}

$product = $result->fetch_assoc();

// Check if item already exists in the persistent cart
$stmt = $mysqli->prepare("SELECT quantity FROM forever_cart WHERE visitor_token = ? AND product_id = ?");
$stmt->bind_param("si", $visitor_token, $product_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    // Update quantity if already in cart
    $new_quantity = $existing['quantity'] + $quantity;
    $stmt = $mysqli->prepare("UPDATE forever_cart SET quantity = ?, last_updated = NOW() WHERE visitor_token = ? AND product_id = ?");
    $stmt->bind_param("isi", $new_quantity, $visitor_token, $product_id);
} else {
    // Insert new cart item
    $stmt = $mysqli->prepare("INSERT INTO forever_cart (visitor_token, product_id, quantity, last_updated) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sii", $visitor_token, $product_id, $quantity);
}
$stmt->execute();

// Update session cart (for display purposes)
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$product_id])) {
    $_SESSION['cart'][$product_id]['quantity'] += $quantity;
} else {
    $_SESSION['cart'][$product_id] = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image'],
        'quantity' => $quantity
    ];
}

$_SESSION['flash_message'] = "Product added to cart!";
header("Location: ../../public/cart.php");
exit;
