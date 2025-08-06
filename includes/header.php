<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


require_once '../includes/functions.php';

// Calculate total items in cart
$totalCartItems = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        if (isset($item['quantity'])) {
            $totalCartItems += $item['quantity'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
    <title>LION T-Shirts</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <link href="./assert/css/tailwind.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="./assert/css/main.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Side Cart Styles */
        .side-cart {
            position: fixed;
            top: 0;
            left: -350px; /* Start off-screen */
            width: 350px;
            height: 100%;
            background-color: #fff;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            transition: left 0.3s ease-in-out;
            z-index: 1050;
            display: flex;
            flex-direction: column;
        }
        .side-cart.open {
            left: 0;
        }
        .side-cart-header {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .side-cart-body {
            flex-grow: 1;
            overflow-y: auto;
            padding: 1rem;
        }
        .side-cart-footer {
            padding: 1rem;
            border-top: 1px solid #e9ecef;
        }
        .side-cart-item {
            display: flex;
            margin-bottom: 1rem;
        }
        .side-cart-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
        <div class="container">
            <p class="fw-bold text-2xl text-indigo-600">LION</p>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.php">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#contact">Contact</a></li>
                    <li class="nav-item ms-2">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo $totalCartItems; ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Side Cart -->
    <div id="side-cart" class="side-cart">
        <div class="side-cart-header">
            <h5 class="mb-0">Your Cart</h5>
            <button type="button" class="btn-close" id="close-cart-btn" aria-label="Close"></button>
        </div>
        <div class="side-cart-body">
            <!-- Cart items will be dynamically inserted here -->
            <div class="text-center text-muted">Your cart is empty.</div>
        </div>
        <div class="side-cart-footer">
            <div class="d-flex justify-content-between mb-3">
                <strong>Subtotal:</strong>
                <span id="side-cart-subtotal">GHS 0.00</span>
            </div>
            <a href="cart.php" class="btn btn-primary w-100 mb-2">View Cart</a>
            <a href="checkout.php" class="btn btn-success w-100">Checkout</a>
        </div>
    </div>

<script src="./assert/js/main.js"></script>

