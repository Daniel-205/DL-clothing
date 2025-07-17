<?php
session_start();
require_once '../includes/functions.php';
require_once '../includes/header.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $_SESSION['flash_message'] = "Your cart is empty. Add products before checking out.";
    header("Location: cart.php");
    exit;
}





// Fetch cart data (from session or DB)
$cart = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
// $taxRate = 0.05;
// $shippingCost = 15;
// $tax = $subtotal * $taxRate;
$grandTotal = $subtotal 

?>

<div class="max-w-4xl mx-auto p-6 bg-white rounded-xl shadow">
    <h2 class="text-2xl font-bold mb-4">Checkout</h2>

    <form action="checkout-process.php" method="POST" class="space-y-4">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

        <div>
            <label for="name" class="block font-semibold">Full Name</label>
            <input type="text" name="name" id="name" required class="w-full border px-3 py-2 rounded">
        </div>

        <div>
            <label for="email" class="block font-semibold">Email</label>
            <input type="email" name="email" id="email" required class="w-full border px-3 py-2 rounded">
        </div>

        <div>
            <label for="address" class="block font-semibold">Shipping Address</label>
            <textarea name="address" id="address" required class="w-full border px-3 py-2 rounded"></textarea>
        </div>

        <div class="border-t pt-4">
            <h3 class="text-lg font-semibold mb-2">Cart Summary</h3>
            <ul class="mb-4">
                <?php foreach ($cart as $item): ?>
                    <li class="flex justify-between border-b py-1">
                        <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                        <span>GHS <?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="space-y-1">
                <!-- <p>Subtotal: GHS <= number_format($subtotal, 2) ?></p>
                <p>Tax (5%): GHS <= number_format($tax, 2) ?></p>
                <p>Shipping: GHS <= number_format(($subtotal > 0 ? $shippingCost : 0), 2) ?></p> -->
                <p class="font-bold text-lg">Total: GHS <?= number_format($grandTotal, 2) ?></p>
            </div>
        </div>

        <button type="submit" class="bg-black text-white px-5 py-2 rounded hover:bg-gray-800">Place Order</button>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>
