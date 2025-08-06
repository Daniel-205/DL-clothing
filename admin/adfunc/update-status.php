<?php
// Step 1: Connect to the database
require_once '../../includes/dbconfig.php';

// Step 2: Check if it's a valid POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']); // Sanitize

    // Step 3: Fetch order details (name, phone) for WhatsApp message
    $stmt = $mysqli->prepare("SELECT full_name, phone FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->bind_result($full_name, $phone);

    if ($stmt->fetch()) {
        $stmt->close();

        // Step 4: Update the status
        $update = $mysqli->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
        $update->bind_param("i", $order_id);

        if ($update->execute()) {
            $update->close();

            // Step 5: Redirect to WhatsApp with prefilled message
            $cleanedPhone = preg_replace('/\D/', '', $phone); // Remove non-numeric
            if (substr($cleanedPhone, 0, 1) === "0") {
                $cleanedPhone = "233" . substr($cleanedPhone, 1); // Convert local to int'l format
            }

            $message = urlencode("Hello $full_name. Thank you for shopping with us! Hope to hear from you again.");
            $whatsappURL = "https://wa.me/$cleanedPhone?text=$message";

            header("Location: $whatsappURL");
            exit;
        } else {
            echo "❌ Failed to update order.";
        }
    } else {
        echo "⚠️ Order not found.";
    }
} else {
    echo "⚠️ Invalid request.";
}
