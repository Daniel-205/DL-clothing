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

<section class="py-8">
    <div class="container">
        <h1 class="mb-6 text-3xl font-bold">Your Shopping Cart</h1>
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
                                        <?php foreach ($cartItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" width="50" height="50" style="object-fit:cover; border-radius:5px; margin-right:10px;">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </td>
                                                <td>GHS <?php echo number_format($item['price'], 2); ?></td>
                                                <td style="min-width: 120px;">
                                                    <form action="admin\cart-logic\update-cart-quantity.php" method="POST" style="display: inline-flex; align-items: center; gap: 5px;">
                                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                        <input type="hidden" name="action" value="decrease">
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm">-</button>
                                                    </form>
                                                    <span style="padding: 0 5px;"><?php echo $item['quantity']; ?></span>
                                                    <form action="update-cart-quantity.php" method="POST" style="display: inline-flex; align-items: center; gap: 5px;">
                                                        <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                                        <input type="hidden" name="action" value="increase">
                                                        <button type="submit" class="btn btn-outline-secondary btn-sm">+</button>
                                                    </form>
                                                </td>
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
</section>

<?php include '../includes/footer.php'; ?>
<script>
    function confirmRemove(itemName) {
        return confirm(`Are you sure you want to remove "${itemName}" from your cart?`);
    }

    function validateCheckout() {
        const checkbox = document.getElementById('agreeTerms');
        if (!checkbox.checked) {
            alert('Please agree to the terms and conditions before proceeding.');
            return false;
        }
        return true;
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