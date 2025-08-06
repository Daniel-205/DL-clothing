document.addEventListener('DOMContentLoaded', function () {
    // Instead of getting variables from PHP, we'll get them from data attributes
    // on a DOM element. We'll assume a 'cart-scripts' element for this.
    const scriptContainer = document.getElementById('cart-script-data');
    if (!scriptContainer) {
        console.error('Cart script data not found. Please ensure the data attributes are set in your HTML.');
        return;
    }

    const csrfToken = scriptContainer.dataset.csrfToken;
    const updateCartUrl = scriptContainer.dataset.updateCartUrl;
    const removeCartUrl = scriptContainer.dataset.removeCartUrl;

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
        const buttons = tableRow.querySelectorAll('.btn-quantity-increase, .btn-quantity-decrease');
        buttons.forEach(btn => btn.disabled = true);

        fetch(updateCartUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                product_id: productId,
                action: action,
                csrf_token: csrfToken
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Update failed');
            }

            const { newQuantity, itemRemoved, totals } = data.data;
            
            if (itemRemoved) {
                tableRow.remove();
                checkCartEmpty();
            } else {
                const quantitySpan = tableRow.querySelector('.item-quantity');
                const totalSpan = tableRow.querySelector('.item-total');
                const priceText = tableRow.querySelector('.item-price').textContent;
                const itemPrice = parseFloat(priceText.replace(/[^0-9.-]+/g, ""));
                
                if (quantitySpan) quantitySpan.textContent = newQuantity;
                if (totalSpan) {
                    totalSpan.textContent = 'GHS ' + (itemPrice * newQuantity).toFixed(2);
                }
            }
            updateOrderSummary(totals);
        })
        .catch(error => {
            console.error('Error:', error);
            displayCartMessage(error.message || 'Failed to update cart', 'error');
        })
        .finally(() => {
            buttons.forEach(btn => btn.disabled = false);
        });
    }

    function removeCartItem(productId, tableRow) {
        fetch(removeCartUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                product_id: productId,
                csrf_token: csrfToken
            })
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                displayCartMessage(data.message, 'error');
            }

            if (data.success) {
                if (tableRow) tableRow.remove();
                checkCartEmpty();
                updateOrderSummary(data.data.totals);
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
            // Ensure totals are formatted correctly
            const formattedSubtotal = parseFloat(totals.subtotal).toFixed(2);
            const formattedGrandTotal = parseFloat(totals.grandTotal).toFixed(2);

            if (subtotalEl) subtotalEl.textContent = 'GHS ' + formattedSubtotal;
            if (totalEl) totalEl.textContent = 'GHS ' + formattedGrandTotal;
        }
    }

    function checkCartEmpty() {
        const cartItemsContainer = document.querySelector('.cart-items');
        if (cartItemsContainer.querySelectorAll('tr:not(.cart-empty)').length === 0) {
            cartItemsContainer.innerHTML = `<tr class="cart-empty"><td colspan="5" class="text-center py-5">Your cart is empty. <a href="shop.php">Start Shopping</a></td></tr>`;
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
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    // --- UI ENHANCEMENTS ---
    // Fade-in animations for elements
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    });
    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

    // Dynamic styles for responsive tables and buttons
    const styleElement = document.createElement('style');
    styleElement.textContent = `
        .fade-in { opacity: 0; transform: translateY(20px); transition: opacity 0.5s ease, transform 0.5s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
        .hover-lift { transition: transform 0.3s ease, box-shadow 0.3s ease; }
        .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    `;
    document.head.appendChild(styleElement);
});
