<?php
session_start();

require_once '../../includes/dbconfig.php';
require_once '../../includes/functions.php';

// Validate input
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) {
    set_flash_message('error', 'Invalid product ID.');
    header("Location: ../../public/cart.php");
    exit;
}

// Get visitor token from cookie
$visitor_token = $_COOKIE['visitor_token'] ?? null;
if (!$visitor_token) {
    set_flash_message('error', 'Session expired. Please refresh and try again.');
    header("Location: ../../public/cart.php");
    exit;
}

// Remove from session cart
if (isset($_SESSION['cart'][$product_id])) {
    unset($_SESSION['cart'][$product_id]);
}

// Remove from persistent cart in DB
$stmt = $mysqli->prepare("DELETE FROM forever_cart WHERE visitor_token = ? AND product_id = ?");
$stmt->bind_param("si", $visitor_token, $product_id);
$stmt->execute();

set_flash_message('success', 'Item removed from cart.');
header("Location: ../../public/cart.php");
exit;
