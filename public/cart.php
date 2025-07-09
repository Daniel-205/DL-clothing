<?php
session_start();
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php'; // For CSRF token


// Generate CSRF token if not already set by functions.php
if (empty($_SESSION['csrf_token'])) {
    generate_csrf_token(); // Ensure it's generated for the cart page
}
$csrf_token = $_SESSION['csrf_token'];


// Handle item removal from cart
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $removeId = intval($_GET['remove']);
    unset($_SESSION['cart'][$removeId]);
    // Consider CSRF protection for removal as well if it's sensitive
    header("Location: cart.php");
    exit;
}

// Setup initial totals
$cartItems = $_SESSION['cart'] ?? [];
$subtotal = 0;
// Shipping and Tax Rates - these might come from config or db in a real app
$shippingCost = 15; // Example fixed shipping
$taxRate = 0.05;    // Example 5% tax rate

// Calculate subtotal
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * $taxRate;
$currentShipping = ($subtotal > 0 ? $shippingCost : 0);
$grandTotal = $subtotal + $tax + $currentShipping;

include '../includes/header.php';
?>

<section class="py-8">
    <div class="container">
        <h1 class="mb-6 text-3xl font-bold">Your Shopping Cart</h1>
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
                                            <tr data-product-id="<?php echo $productId; ?>">
                                                <td>
                                                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" width="50" height="50" style="object-fit:cover; border-radius:5px; margin-right:10px;">
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </td>
                                                <td class="item-price">GHS <?php echo number_format($item['price'], 2); ?></td>
                                                <td style="min-width: 120px;">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-quantity-decrease" data-product-id="<?php echo $productId; ?>">-</button>
                                                    <span class="item-quantity" style="padding: 0 10px;"><?php echo $item['quantity']; ?></span>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-quantity-increase" data-product-id="<?php echo $productId; ?>">+</button>
                                                </td>
                                                <td class="item-total">GHS <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                                <td>
                                                    <a href="cart.php?remove=<?php echo $productId; ?>" class="text-danger btn-remove-item" data-product-id="<?php echo $productId; ?>" onclick="return confirm('Remove this item?');">Remove</a>
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
                            <span class="text-gray-600">Delivery</span>
                            <span class="cart-shipping">GHS <?php echo number_format($currentShipping, 2); ?></span>
                        </div>
                         <div class="d-flex justify-content-between mb-2">
                            <span class="text-gray-600">Tax (<?php echo ($taxRate * 100); ?>%)</span>
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
    const csrfToken = '<?php echo $csrf_token; ?>';
    const updateCartUrl = '../admin/cart-logic/update-cart-quantity.php';

    document.addEventListener('DOMContentLoaded', function () {
        const cartItemsContainer = document.querySelector('.cart-items');

        cartItemsContainer.addEventListener('click', function(event) {
            const target = event.target;
            let action = null;

            if (target.classList.contains('btn-quantity-increase')) {
                action = 'increase';
            } else if (target.classList.contains('btn-quantity-decrease')) {
                action = 'decrease';
            }

            if (action) {
                event.preventDefault();
                const productId = target.dataset.productId;
                updateCartQuantity(productId, action, target.closest('tr'));
            }
        });

        function updateCartQuantity(productId, action, tableRow) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', action);
            formData.append('csrf_token', csrfToken);

            fetch(updateCartUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                displayCartMessage(data.message, data.success ? 'success' : 'error');

                if (data.success) {
                    if (data.itemRemoved || (data.newItemQuantity !== undefined && data.newItemQuantity <= 0)) {
                        if (tableRow) {
                            tableRow.remove();
                        }
                        // Check if cart is now empty
                        if (document.querySelectorAll('.cart-items tr').length === 0 || (document.querySelector('.cart-items tr.cart-empty') && document.querySelectorAll('.cart-items tr:not(.cart-empty)').length === 0) ) {
                             if (!document.querySelector('.cart-items tr.cart-empty')) {
                                const emptyRow = `<tr class="cart-empty"><td colspan="5" class="text-center py-5">Your cart is empty</td></tr>`;
                                cartItemsContainer.innerHTML = emptyRow;
                             }
                        }
                    } else if (tableRow && data.updatedItemId == productId) {
                        const quantitySpan = tableRow.querySelector('.item-quantity');
                        const totalSpan = tableRow.querySelector('.item-total');
                        // Assuming item price can be found or is fixed per item for client-side total update.
                        // For simplicity, we'll rely on the server's cart data for totals if possible,
                        // but for item total, we need the item's price.
                        // Let's get it from the DOM if possible, or better, from the response.
                        // The current response doesn't send individual item price.
                        // For now, let's parse it from the price column.
                        const priceText = tableRow.querySelector('.item-price').textContent;
                        const itemPrice = parseFloat(priceText.replace(/[^0-9.-]+/g,""));


                        if (quantitySpan) quantitySpan.textContent = data.newItemQuantity;
                        if (totalSpan && !isNaN(itemPrice)) {
                             totalSpan.textContent = 'GHS ' + (itemPrice * data.newItemQuantity).toFixed(2);
                        }
                    }
                    // Update order summary
                    if (data.totals) {
                        document.querySelector('.cart-subtotal').textContent = 'GHS ' + data.totals.subtotal;
                        document.querySelector('.cart-tax').textContent = 'GHS ' + data.totals.tax;
                        document.querySelector('.cart-shipping').textContent = 'GHS ' + data.totals.shipping;
                        document.querySelector('.cart-total').textContent = 'GHS ' + data.totals.grandTotal;
                    }
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
                displayCartMessage('Failed to update cart. Please try again.', 'error');
            });
        }

        function displayCartMessage(message, type = 'info') {
            const messageArea = document.getElementById('cart-message-area');
            if (!messageArea) return;

            const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
            messageArea.innerHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
        }
    });

    function confirmRemove(itemName) { // Keep existing remove confirmation
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

    .catch(async error => {
        const raw = await error.response?.text?.() ?? '';
        console.error(' Failed to parse JSON:', raw);
    });


    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });
</script>