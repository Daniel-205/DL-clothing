<?php
session_start();
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['flash_message'] = "Your cart is empty. Add products before checking out.";
    header("Location: cart.php");
    exit;
}

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

<div class="checkout-container">
    
  <!-- Billing Details -->
  <div class="form-section">
    <h3>Billing Details</h3>
    <form action="controllers/process-checkout.php" method="post">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <div class="form-group">
        <label for="fname">Full Name</label>
        <input type="text" id="fname" name="full_name" required>
      </div>

      <div class="form-group">
        <label for="email">Email *</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="phone">Phone *</label>
        <input type="tel" id="phone" name="phone" required>
      </div>

      <div class="form-group">
        <label for="city">City *</label>
        <input type="text" id="city" name="city" required>
      </div>

      <div class="form-group">
        <label for="address">Delivery Address </label>
        <input type="text" id="address" name="address" required>
      </div>
  </div>

  <!-- Order Summary -->
  <div class="summary-section">
    <h3>Your Order</h3>
    <div class="order-summary">
      <ul class="mb-4">
        <?php foreach ($cart as $item): ?>
          <li class="flex justify-between border-b py-1">
            <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
            <span>GHS <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>

      <div class="space-y-1">
        <p class="font-bold text-lg">Total: GHS <?= number_format($grandTotal, 2) ?></p>
      </div>
    </div>

    <input type="hidden" name="order_total" value="<?= $grandTotal ?>">
    <button type="submit" class="place-order-btn">Place Order</button>
  </div>
  </form> <!-- Close form here -->
</div>

</body>
</html>
