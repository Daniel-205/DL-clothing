<?php

session_start();
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';

// Generate or retrieve visitor token
if (!isset($_COOKIE['visitor_token'])) {
    $visitor_token = bin2hex(random_bytes(16));
    setcookie('visitor_token', $visitor_token, time() + (86400 * 30), "/"); // 30 days
} else {
    $visitor_token = $_COOKIE['visitor_token'];
}

// Load persistent cart from database
$stmt = $mysqli->prepare("
    SELECT fc.product_id, fc.quantity, p.name, p.price, p.image 
    FROM forever_cart fc
    JOIN products p ON fc.product_id = p.id
    WHERE fc.visitor_token = ?
");
$stmt->bind_param("s", $visitor_token);
$stmt->execute();
$result = $stmt->get_result();

// Update session cart
$_SESSION['cart'] = [];

while ($row = $result->fetch_assoc()) {
    $product_id = $row['product_id'];
    $_SESSION['cart'][$product_id] = [
        'id' => $product_id,
        'name' => $row['name'],
        'price' => $row['price'],
        'image' => $row['image'],
        'quantity' => $row['quantity']
    ];
}


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
// $shippingCost = 0; // Example fixed shipping
// $taxRate = 0.05;    // Example 5% tax rate

// Calculate subtotal
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// $tax = $subtotal * $taxRate;
// $currentShipping = ($subtotal > 0 ? $shippingCost : 0);

// Calculate grand total
$grandTotal = $subtotal;

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
                            <div class="payment-icon-placeholder bg-light rounded p-2" style="width: 60px; height: 40px;"></div>
                            <div class="payment-icon-placeholder bg-light rounded p-2" style="width: 60px; height: 40px;"></div>
                            <div class="payment-icon-placeholder bg-light rounded p-2" style="width: 60px; height: 40px;"></div>
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
    const removeCartUrl = '../admin/cart-logic/remove-from-cart.php';

    document.addEventListener('DOMContentLoaded', function () {
        const cartItemsContainer = document.querySelector('.cart-items');

        if (cartItemsContainer) {
            cartItemsContainer.addEventListener('click', function (event) {
                const target = event.target.closest('button');
                if (!target) return;

                const productId = target.dataset.productId;
                const tableRow = target.closest('tr');

                if (target.classList.contains('btn-quantity-increase')) {
                    updateCartQuantity(productId, 'increase', tableRow);
                } else if (target.classList.contains('btn-quantity-decrease')) {
                    updateCartQuantity(productId, 'decrease', tableRow);
                } else if (target.classList.contains('btn-remove-item')) {
                    const productName = target.dataset.productName;
                    if (confirm(`Are you sure you want to remove "${productName}" from your cart?`)) {
                        removeCartItem(productId, tableRow);
                    }
                }
            });
        }

        function updateCartQuantity(productId, action, tableRow) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('action', action);
            formData.append('csrf_token', csrfToken);

            fetch(updateCartUrl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                displayCartMessage(data.message, data.success ? 'success' : 'error');

                if (data.success) {
                    if (data.itemRemoved || (data.newItemQuantity !== undefined && data.newItemQuantity <= 0)) {
                        if (tableRow) tableRow.remove();
                        checkCartEmpty();
                    } else if (tableRow && data.updatedItemId == productId) {
                        const quantitySpan = tableRow.querySelector('.item-quantity');
                        const totalSpan = tableRow.querySelector('.item-total');
                        const priceText = tableRow.querySelector('.item-price').textContent;
                        const itemPrice = parseFloat(priceText.replace("GHS ", ""));

                        if (quantitySpan) quantitySpan.textContent = data.newItemQuantity;
                        if (totalSpan && !isNaN(itemPrice)) {
                            totalSpan.textContent = 'GHS ' + (itemPrice * data.newItemQuantity).toFixed(2);
                        }
                    }

                    updateOrderSummary(data.totals);
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
                displayCartMessage('Failed to update cart. Please try again.', 'error');
            });
        }

        function removeCartItem(productId, tableRow) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('csrf_token', csrfToken);

            fetch(removeCartUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(raw => {
                console.log("Remove response:", raw);
                let data;
                try {
                    data = JSON.parse(raw);
                } catch (e) {
                    displayCartMessage("Unexpected server response. Please refresh.", 'error');
                    return;
                }

                displayCartMessage(data.message, data.success ? 'success' : 'error');

                if (data.success) {
                    if (tableRow) tableRow.remove();
                    checkCartEmpty();
                    updateOrderSummary(data.totals);
                }
            })
            .catch(error => {
                console.error('Error removing item:', error);
                displayCartMessage('Failed to remove item. Please try again.', 'error');
            });
        }

        function updateOrderSummary(totals) {
            if (totals) {
                const subtotalEl = document.querySelector('.cart-subtotal');
                const totalEl = document.querySelector('.cart-total');

                if (subtotalEl) subtotalEl.textContent = 'GHS ' + totals.subtotal;
                if (totalEl) totalEl.textContent = 'GHS ' + totals.grandTotal;
            }
        }

        function checkCartEmpty() {
            const cartItemsContainer = document.querySelector('.cart-items');
            if (cartItemsContainer.querySelectorAll('tr:not(.cart-empty)').length === 0) {
                cartItemsContainer.innerHTML = `<tr class="cart-empty"><td colspan="5" class="text-center py-5">Your cart is empty</td></tr>`;
            }
        }

        function displayCartMessage(message, type = 'info') {
            const messageArea = document.getElementById('cart-message-area');
            if (!messageArea) return;

            const alertClass = type === 'success' ? 'alert-success' : (type === 'error' ? 'alert-danger' : 'alert-info');
            messageArea.innerHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;

            setTimeout(() => {
                const alert = messageArea.querySelector('.alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        }

        // Animations and style injections
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        });

        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

        const styleElement = document.createElement('style');
        styleElement.textContent = `
            .btn-quantity-increase.clicked,
            .btn-quantity-decrease.clicked {
                background-color: #1C1C1C !important;
                color: white !important;
                transform: scale(0.95);
            }

            .btn-quantity-increase,
            .btn-quantity-decrease {
                transition: all 0.2s ease;
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
            }

            .btn-quantity-increase:hover,
            .btn-quantity-decrease:hover {
                background-color: #1C1C1C;
                color: white;
                border-color: #1C1C1C;
            }

            @media (max-width: 767.98px) {
                .table td {
                    display: block;
                    text-align: left;
                    padding: 0.75rem 1rem;
                    border-top: none;
                    position: relative;
                }

                .table td:before {
                    content: attr(data-label);
                    font-weight: 600;
                    margin-bottom: 0.5rem;
                    display: block;
                    font-size: 0.75rem;
                    text-transform: uppercase;
                    color: #1C1C1C;
                }

                .table tr {
                    border-bottom: 1px solid #dee2e6;
                    display: block;
                    margin-bottom: 1rem;
                    padding-bottom: 1rem;
                }

                .quantity-controls {
                    justify-content: flex-start;
                }

                .card {
                    margin-bottom: 1rem;
                }
            }

            .fade-in {
                opacity: 0;
                transform: translateY(20px);
                transition: opacity 0.5s ease, transform 0.5s ease;
            }

            .hover-lift {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .hover-lift:hover {
                transform: translateY(-3px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
        `;
        document.head.appendChild(styleElement);

        document.querySelectorAll('.btn-quantity-increase, .btn-quantity-decrease').forEach(btn => {
            btn.addEventListener('click', function () {
                this.classList.add('clicked');
                setTimeout(() => this.classList.remove('clicked'), 150);
            });
        });

        setTimeout(() => {
            document.querySelectorAll('.fade-in').forEach(el => {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            });
        }, 100);
    });
</script>
