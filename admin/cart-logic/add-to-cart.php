<?php
session_start();

require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';


if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Initialize cart session if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}


// Input validation
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

// Fallback
if (!$product_id || !$quantity || $quantity < 1) {
    $_SESSION['flash_message'] = "Invalid product or quantity.";
    header("Location: cart.php");
    exit;
}

// Fetch product from DB
$stmt = $conn->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['flash_message'] = "Product not found.";
    header("Location: cart.php");
    exit;
}

$product = $result->fetch_assoc();

// Initialize cart if not already
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// If already in cart, update quantity
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

$_SESSION['flash_message'] = " Product added to cart!";
header("Location: cart.php");
exit;
?>
