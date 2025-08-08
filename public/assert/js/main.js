document.addEventListener('DOMContentLoaded', function() {
    console.log('Main JS loaded.');

    // --- Global Variables & Elements ---
    const sideCart = document.getElementById('side-cart');
    const closeCartBtn = document.getElementById('close-cart-btn');
    const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').getAttribute('content') : null;

    // --- Utility Functions ---
    function updateCartIcon(totalItems) {
        const cartCount = document.querySelector('.cart-count');
        if (cartCount) {
            cartCount.textContent = totalItems;
            if (totalItems > 0) {
                cartCount.classList.remove('d-none');
            } else {
                cartCount.classList.add('d-none');
            }
        }
    }

    function updateSideCart(cart, totals) {
        const sideCartBody = document.querySelector('.side-cart-body');
        const sideCartSubtotal = document.getElementById('side-cart-subtotal');

        if (!sideCartBody || !sideCartSubtotal) return;

        sideCartBody.innerHTML = ''; // Clear existing items

        if (Object.keys(cart).length === 0) {
            sideCartBody.innerHTML = '<div class="text-center text-muted">Your cart is empty.</div>';
            sideCartSubtotal.textContent = 'GHS 0.00';
        } else {
            for (const itemId in cart) {
                const item = cart[itemId];
                const itemElement = document.createElement('div');
                itemElement.classList.add('side-cart-item');
                itemElement.innerHTML = `
                    <img src="../${item.image}" alt="${item.name}">
                    <div>
                        <strong>${item.name}</strong>
                        <div>${item.quantity} x GHS ${parseFloat(item.price).toFixed(2)}</div>
                    </div>
                `;
                sideCartBody.appendChild(itemElement);
            }
            sideCartSubtotal.textContent = `GHS ${totals.subtotal}`;
        }
    }

    // --- Side Cart Logic ---
    function openSideCart() {
        if (sideCart) {
            sideCart.classList.add('open');
        }
    }

    function closeSideCart() {
        if (sideCart) {
            sideCart.classList.remove('open');
        }
    }

    if (closeCartBtn) {
        closeCartBtn.addEventListener('click', closeSideCart);
    }

    // --- Add to Cart Logic ---
    const addToCartForms = document.querySelectorAll('.add-to-cart-form');
    addToCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }

            fetch('../admin/cart-logic/add-to-cart.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateSideCart(data.data.cart, data.data.totals);
                    updateCartIcon(data.data.totals.totalItems);
                    openSideCart();
                } else {
                    alert(data.message || 'Could not add item to cart.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the item to the cart.');
            });
        });
    });

    // --- Initial Cart Load ---
    function updateCartOnPageLoad() {
        fetch('../admin/cart-logic/get-cart.php', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateCartIcon(data.data.totals.totalItems);
                updateSideCart(data.data.cart, data.data.totals);
            }
        })
        .catch(error => {
            console.error('Error fetching initial cart state:', error);
        });
    }

    // Load cart data as soon as the page is ready
    updateCartOnPageLoad();
});
