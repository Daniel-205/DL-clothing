<?php
session_start();
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';
// The cart loading logic MUST come before any check that might cause a redirect.
require_once '../includes/cart-function.php';

// Load the persistent cart into the session first.
if (isset($mysqli)) {
    load_persistent_cart_into_session($mysqli);
}

// Now, with the session populated, check if the cart is empty.
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    // Use the redirect_with_message function for consistency
    redirect_with_message('cart.php', 'Your cart is empty. Add products before checking out.', 'info');
}

// The cart is not empty, so we can proceed to display the page.
// The header will be included now, which is safe because we are no longer redirecting.
require_once '../includes/header.php';

$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$grandTotal = $subtotal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #FFFFFF;
      color: #1C1C1C;
    }

    .checkout-header {
      background-color: #1C1C1C;
      color: white;
      padding: 1rem;
      text-align: center;
    }

    .checkout-steps {
      display: flex;
      justify-content: center;
      margin: 20px 0;
    }

    .step {
      padding: 10px 20px;
      border-bottom: 3px solid #ccc;
      margin: 0 10px;
      font-weight: bold;
    }

    .step.active {
      border-color: #1C1C1C;
    }

    .checkout-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 40px;
      padding: 20px;
    }

    .form-section, .summary-section {
      flex: 1 1 400px;
      max-width: 600px;
      background-color: #f8f8f8;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      font-weight: bold;
      display: block;
      margin-bottom: 5px;
    }

    input, select, textarea {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 14px;
    }

    .order-summary {
      border-top: 1px solid #ccc;
      padding-top: 10px;
      margin-top: 10px;
    }

    .place-order-btn {
      background-color: #1C1C1C;
      color: white;
      padding: 15px;
      width: 100%;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      margin-top: 20px;
    }

    .place-order-btn:hover {
      background-color: #333;
    }

    @media screen and (max-width: 768px) {
      .checkout-container {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>

<div class="container py-5">
  <div class="row">
    <div class="col-lg-7">
      <div class="card mb-4">
        <div class="card-header">
          <h3 class="card-title">Billing Details</h3>
        </div>
        <div class="card-body">
          <form action="../controllers/process-checkout.php" method="post" id="checkout-form">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="fname" class="form-label">Full Name</label>
                <input type="text" id="fname" name="full_name" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email *</label>
                <input type="email" id="email" name="email" class="form-control" required>
              </div>
            </div>
            <div class="mb-3">
              <label for="phone" class="form-label">Phone *</label>
              <input type="tel" id="phone" name="phone" class="form-control" required>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="city" class="form-label">City *</label>
                <input type="text" id="city" name="city" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="address" class="form-label">Delivery Address</label>
                <input type="text" id="address" name="address" class="form-control" required>
              </div>
            </div>
          </form>
        </div>
      </div>
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Payment Method</h3>
        </div>
        <div class="card-body">
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" form="checkout-form" checked>
            <label class="form-check-label" for="cod">
              Cash on Delivery
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="payment_method" id="momo" value="momo" form="checkout-form">
            <label class="form-check-label" for="momo">
              Mobile Money
            </label>
          </div>
        </div>
      </div>
    </div>
    <div class="col-lg-5">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Your Order</h3>
        </div>
        <div class="card-body">
          <div class="order-summary">
            <ul class="list-group list-group-flush">
              <?php foreach ($cart as $item): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <div class="d-flex align-items-center">
                    <img src="../<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-summary-img">
                    <div>
                      <p class="mb-0"><?= htmlspecialchars($item['name']) ?></p>
                      <small class="text-muted">Quantity: <?= $item['quantity'] ?></small>
                    </div>
                  </div>
                  <span>GHS <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <div class="d-flex justify-content-between font-weight-bold mt-3">
              <p class="mb-0">Total:</p>
              <p class="mb-0">GHS <?= number_format($grandTotal, 2) ?></p>
            </div>
          </div>
        </div>
        <div class="card-footer">
          <input type="hidden" name="order_total" value="<?= $grandTotal ?>" form="checkout-form">
          <button type="submit" class="btn btn-primary w-100" form="checkout-form">Place Order</button>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>

<?php   require_once '../includes/footer.php'; ?>
