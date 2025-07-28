<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once '../includes/dbconfig.php';  // your DB connection
require_once '../includes/functions.php'; // for set_flash_message, etc.

// Step 1: Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../checkout.php");
    exit;
}

// Optional: CSRF protection (skip if not using)
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    set_flash_message('error', 'Invalid request. Please refresh and try again.');
    header("Location: ../checkout.php");
    exit;
}

// Step 2: Sanitize inputs
$full_name = trim($_POST['full_name'] ?? '');
$email     = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
$phone     = trim($_POST['phone'] ?? '');
$address   = trim($_POST['address'] ?? '');

if (!$full_name || !$email || !$phone || !$address) {
    set_flash_message('error', 'Please fill in all required fields.');
    header("Location: ../checkout.php");
    exit;
}

// Step 3: Get cart and visitor
$visitor_token = $_COOKIE['visitor_token'] ?? session_id();
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    set_flash_message('error', 'Your cart is empty.');
    header("Location: ../checkout.php");
    exit;
}

// Step 4: Calculate total
$order_total = 0;
foreach ($cart as $product_id => $item) {
    $order_total += $item['price'] * $item['quantity'];
}

// Step 5: Generate order code
$order_code = strtoupper(uniqid('ORD'));

// Step 6: Check if tables exist and create them if they don't
$mysqli->query("
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_token` varchar(64) NOT NULL,
  `order_code` varchar(32) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `order_total` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `visitor_token` (`visitor_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

$mysqli->query("
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

// Insert into orders table
$stmt = $mysqli->prepare("
    INSERT INTO orders 
    (visitor_token, order_code, full_name, email, phone, address, order_total, status, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
");

$stmt->bind_param(
    "ssssssd",
    $visitor_token,
    $order_code,
    $full_name,
    $email,
    $phone,
    $address,
    $order_total
);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;

    // Step 7: Insert into order_items
    $insert_item = $mysqli->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price) 
        VALUES (?, ?, ?, ?)
    ");

    foreach ($cart as $product_id => $item) {
        $insert_item->bind_param("iiid", $order_id, $product_id, $item['quantity'], $item['price']);
        $insert_item->execute();
    }

    // Step 8: Prepare WhatsApp message
    $message = " *New Order Received!*\n"
        . "*Name:* $full_name\n"
        // . "*Email:* $email\n"
        . "*Phone:* $phone\n"
        . "*Address:* $address\n"
        // . "*Order Code:* $order_code\n"
        . "*Order Total:* GHS " . number_format($order_total, 2) . "\n\n"
        . "*Items:*\n"
        . "*Thanks for your purchase! Your order will be delivered shortly. Stay tuned! *\n";


    foreach ($cart as $item) {
        $message .= "- {$item['name']} x {$item['quantity']} = GHS " 
            . number_format($item['price'] * $item['quantity'], 2) . "\n";
    }

    // Step 9: Clear cart
    unset($_SESSION['cart']);
    setcookie('visitor_token', $visitor_token, time() + (86400 * 30), "/");

    // Step 10: Redirect to WhatsApp
    $whatsappNumber = "233544125283"; // replace with your number
    $encodedMessage = urlencode($message);
    $whatsappUrl = "https://wa.me/$whatsappNumber?text=$encodedMessage";

    header("Location: $whatsappUrl");
    exit;

} else {
    // On DB error
    set_flash_message('error', 'Something went wrong. Please try again.');
    header("Location: ../checkout.php");
    exit;
}
