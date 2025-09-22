<?php

// --- SETUP AND INITIALIZATION ---
session_start();
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';

// --- VISITOR AND CART IDENTIFICATION ---
// Ensure a unique token is set for the visitor to persist their cart.
$visitor_token = get_or_create_visitor_token();

// --- DATA FETCHING AND PROCESSING ---
// The cart is now loaded into the session via header.php,
// so this block is no longer needed here.

// --- CSRF PROTECTION ---
// Generate a CSRF token to protect against cross-site request forgery attacks.
$csrf_token = generate_csrf_token();

// --- HANDLE DIRECT URL ACTIONS (e.g., item removal from a link) ---
// This block is for non-JS scenarios or direct link actions.
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $product_to_remove = intval($_GET['remove']);
    
    // Also remove from the persistent cart in the database.
    $stmt = $mysqli->prepare("DELETE FROM forever_cart WHERE visitor_token = ? AND product_id = ?");
    $stmt->bind_param("si", $visitor_token, $product_to_remove);
    $stmt->execute();
    
    // Remove from the session cart.
    unset($_SESSION['cart'][$product_to_remove]);
    
    // Redirect back to the cart to prevent re-submission on refresh.
    header("Location: cart.php");
    exit;
}

// --- CALCULATIONS FOR DISPLAY ---
// Prepare variables for rendering in the HTML.
$cartItems = $_SESSION['cart'] ?? [];
$totals = calculate_cart_totals($cartItems); // Using the function from functions.php is cleaner
$subtotal = $totals['subtotal'];
$grandTotal = $totals['grandTotal'];


// --- PAGE RENDERING ---
// Include the site header.
include '../includes/header.php';
?>

<section class="py-8 fade-in">
    <div class="container">
        <h1 class="mb-6 text-3xl font-bold text-dark-theme">Your Shopping Cart</h1>
        <div id="cart-message-area" class="mb-3"></div> <!-- For displaying messages -->
        <div class="row">
            <div class="col-lg-8 mb-5 mb-lg-0">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="cart-items">
                                    <?php if (count($cartItems) > 0): ?>
                                        <?php foreach ($cartItems as $productId => $item): ?>
                                            <tr data-product-id="<?php echo $productId; ?>" class="fade-in">
                                                <td data-label="Product" class="d-flex align-items-center">
                                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" width="50" height="50" 
                                                         style="object-fit:cover; border-radius:5px; margin-right:10px;"
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>" class="product-thumbnail">
                                                    <span class="product-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                                </td>
                                                <td data-label="Price" class="item-price">GHS <?php echo number_format($item['price'], 2); ?></td>
                                                <td data-label="Quantity" class="quantity-cell" style="min-width: 120px;">
                                                    <div class="quantity-controls d-flex align-items-center">
                                                        <button type="button" 
                                                                class="btn btn-quantity-decrease" 
                                                                data-product-id="<?php echo $productId; ?>"
                                                                aria-label="Decrease quantity">
                                                            <span>-</span>
                                                        </button>
                                                        <span class="item-quantity mx-2"><?php echo $item['quantity']; ?></span>
                                                        <button type="button" 
                                                                class="btn btn-quantity-increase" 
                                                                data-product-id="<?php echo $productId; ?>"
                                                                aria-label="Increase quantity">
                                                            <span>+</span>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td data-label="Total" class="item-total">GHS <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                <td data-label="Actions">
                                                    <button type="button" 
                                                            class="text-danger btn-remove-item" 
                                                            data-product-id="<?php echo $productId; ?>"
                                                            data-product-name="<?php echo htmlspecialchars(addslashes($item['name'])); ?>">
                                                        <span class="d-none d-sm-inline">Remove</span>
                                                        <span class="d-sm-none">✕</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="cart-empty">
                                            <td colspan="5" class="text-center py-5">
                                                <div class="empty-cart-message fade-in">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="mb-3 mx-auto text-gray-400">
                                                        <circle cx="8" cy="21" r="1"></circle>
                                                        <circle cx="19" cy="21" r="1"></circle>
                                                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path>
                                                    </svg>
                                                    <p class="text-gray-500 mb-4">Your cart is empty</p>
                                                    <a href="shop.php" class="btn btn-indigo-600 px-4">Start Shopping</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <a href="shop.php" class="btn btn-outline-indigo-600 hover-lift">
                        <span>Continue Shopping</span>
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px; z-index: 100;">
                    <div class="card-body">
                        <h5 class="card-title mb-4 text-dark-theme">Order Summary</h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="cart-subtotal">GHS <?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <!-- <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600">Delivery</span>
                            <span class="cart-shipping">GHS ?php echo number_format($currentShipping, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600">Tax (<php echo ($taxRate * 100); ?>%)</span>
                            <span class="cart-tax">GHS ?php echo number_format($tax, 2); ?></span>
                        </div> -->

                        <hr class="my-4">

                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold text-dark-theme">Total</span>
                            <span class="fw-bold cart-total">GHS <?php echo number_format($grandTotal, 2); ?></span>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="agreeTerms" checked>
                            <label class="form-check-label" for="agreeTerms">
                                I agree to the <a href="#" class="text-dark-theme hover:underline">terms and conditions</a>
                            </label>
                        </div>

                        <a href="checkout.php" 
                           class="btn btn-indigo-600 w-100 py-3 checkout-btn hover-lift"
                           onclick="return validateCheckout();">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3 text-dark-theme">Secure Payment</h5>
                        <p class="text-muted mb-3 small">We use secure encryption for all transactions</p>
                        <div class="payment-methods d-flex gap-2 flex-wrap justify-content-center">
                            <!-- Payment method icons will be loaded here -->
                            <img src=""  class="payment-icon-placeholder bg-light rounded p-2" style="width: 60px; height: 40px;">
                            <img src="./public/assert/image/telecelCash.png"  class="payment-icon-placeholder bg-light rounded p-2" style="width: 60px; height: 40px;">
                            <img src="./public/assert/image/tigo-cash-airtel-money.jpg"  class="payment-icon-placeholder bg-light rounded p-2" style="width: 60px; height: 40px;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
// --- SCRIPT DATA ---
// Pass PHP variables to the external JavaScript file.
// This is a secure way to make server-side data available to the client-side script.
?>
<span id="cart-script-data" 
      data-csrf-token="<?php echo htmlspecialchars($csrf_token); ?>"
      data-update-cart-url="../admin/cart-logic/update-cart-quantity.php"
      data-remove-cart-url="../admin/cart-logic/remove-from-cart.php">
</span>

<?php 
include '../includes/footer.php'; 
?>

<script src="../public/assert/js/cart.js"></script>
