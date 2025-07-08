<?php


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if cart_token exists in cookie
if (!isset($_COOKIE['cart_token'])) {
    // Generate a secure token
    $cart_token = bin2hex(random_bytes(32)); // 64-character token

    // Set cookie for 30 days (secure, HTTP-only)
    setcookie('cart_token', $cart_token, time() + (30 * 24 * 60 * 60), "/", "", false, true);
    
    // Store in session too (optional)
    $_SESSION['cart_token'] = $cart_token;
} else {
    $cart_token = $_COOKIE['cart_token'];
    $_SESSION['cart_token'] = $cart_token;
}

