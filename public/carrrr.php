
<?php
session_start();

// Handle item removal from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $removeId = intval($_GET['remove']);
    unset($_SESSION['cart'][$removeId]);
    header("Location: cart.php");
    exit;
}

// Setup initial totals
$cartItems = $_SESSION['cart'] ?? [];
$subtotal = 0;
$shipping = 15; // fixed
$taxRate = 0.05;

// Calculate subtotal
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * $taxRate;
$grandTotal = $subtotal + $tax + ($subtotal > 0 ? $shipping : 0);

include '../includes/header.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Modern Design</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-400: #9ca3af;
            --gray-500: #6b7280;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .container {
            max-width: 1200px;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .page-title {
            color: var(--gray-800);
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .cart-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .cart-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .table {
            margin: 0;
            background: transparent;
        }

        .table thead th {
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            border: none;
            color: var(--gray-700);
            font-weight: 600;
            padding: 1.5rem 1rem;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            padding: 1.5rem 1rem;
            border-color: var(--gray-200);
            vertical-align: middle;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.05);
        }

        .product-name {
            font-weight: 600;
            color: var(--gray-800);
            font-size: 1.1rem;
        }

        .price {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .quantity-badge {
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .remove-btn {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remove-btn:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--gray-500);
        }

        .empty-cart i {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding: 0 1rem;
        }

        .btn-continue {
            background: linear-gradient(135deg, var(--gray-600), var(--gray-700));
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-continue:hover {
            background: linear-gradient(135deg, var(--gray-700), var(--gray-800));
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .summary-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 2rem;
            position: sticky;
            top: 2rem;
        }

        .summary-title {
            color: var(--gray-800);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            color: var(--gray-600);
            font-weight: 500;
        }

        .summary-value {
            font-weight: 600;
            color: var(--gray-800);
        }

        .total-row {
            background: linear-gradient(135deg, var(--gray-50), var(--gray-100));
            margin: 1rem -2rem;
            padding: 1rem 2rem;
            border-radius: 12px;
        }

        .total-row .summary-label,
        .total-row .summary-value {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--gray-800);
        }

        .terms-checkbox {
            margin: 1.5rem 0;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .checkout-btn {
            background: linear-gradient(135deg, var(--primary-color), #8b5cf6);
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
            border: none;
            width: 100%;
            box-shadow: var(--shadow-md);
        }

        .checkout-btn:hover {
            background: linear-gradient(135deg, var(--primary-hover), #7c3aed);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .checkout-btn:disabled {
            background: var(--gray-400);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .payment-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1.5rem;
            margin-top: 1.5rem;
        }

        .payment-title {
            color: var(--gray-800);
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-methods {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .payment-method {
            width: 50px;
            height: 35px;
            object-fit: contain;
            background: white;
            padding: 0.25rem;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.3s ease;
        }

        .payment-method:hover {
            transform: translateY(-2px);
        }

        .security-badge {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }
            
            .product-info {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .product-image {
                width: 60px;
                height: 60px;
            }
            
            .table-responsive {
                font-size: 0.9rem;
            }
            
            .action-buttons {
                flex-direction: column;
                gap: 1rem;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header fade-in">
            <h1 class="page-title">
                <i class="fas fa-shopping-cart me-3"></i>
                Your Shopping Cart
            </h1>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-4">
                <div class="cart-card fade-in">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($cartItems) > 0): ?>
                                        <?php foreach ($cartItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" width="50" height="50" style="object-fit:cover; border-radius:5px; margin-right:10px;">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </td>
                                                <td>GHS <?php echo number_format($item['price'], 2); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td>GHS <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                <td>
                                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" class="text-danger" onclick="return confirm('Remove this item?');">Remove</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="cart-empty">
                                            <td colspan="5" class="text-center py-5">Your cart is empty</td>
                                        </tr>
                                    <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="shop.php" class="btn btn-outline-indigo-600">Continue Shopping</a>
                    <!-- Future: Update cart quantities -->
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="cart-subtotal">GHS <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600">Shipping</span>
                            <span class="cart-shipping">GHS <?php echo $subtotal > 0 ? number_format($shipping, 2) : '0.00'; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600">Tax (5%)</span>
                            <span class="cart-tax">GHS <?php echo number_format($tax, 2); ?></span>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total</span>
                            <span class="fw-bold cart-total">GHS <?php echo number_format($grandTotal, 2); ?></span>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" checked>
                            <label class="form-check-label" for="agreeTerms">I agree to the <a href="#">terms and conditions</a></label>
                        </div>

                        <a href="checkout.php" class="btn btn-indigo-600 w-100 py-3 checkout-btn">Proceed to Checkout</a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Secure Payment</h5>
                        <div class="payment-methods d-flex gap-2">
                            <img src="assets/images/visa.png" width="40" alt="">
                            <img src="assets/images/mastercard.png" width="40" alt="">
                            <img src="assets/images/amex.png" width="40" alt="">
                            <img src="assets/images/paypal.png" width="40" alt="">
                            <img src="assets/images/apple-pay.png" width="40" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function proceedToCheckout() {
            const checkbox = document.getElementById('agreeTerms');
            if (!checkbox.checked) {
                alert('Please agree to the terms and conditions before proceeding.');
                return;
            }
            
            // Add loading state
            const btn = document.querySelector('.checkout-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            btn.disabled = true;
            
            // Simulate checkout process
            setTimeout(() => {
                alert('Redirecting to checkout...');
                // window.location.href = 'checkout.php';
                btn.innerHTML = originalText;
                btn.disabled = false;
            }, 2000);
        }

        // Add smooth animations on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>