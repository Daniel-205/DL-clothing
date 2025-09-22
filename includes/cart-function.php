<?php
/**
 * Load persistent cart from database into session
 * @param mysqli $mysqli
 */
function load_persistent_cart_into_session($mysqli) {
    // Ensure we have a visitor token
    $visitor_token = get_or_create_visitor_token();

    // Only load from DB if the session cart hasn't been loaded in this request yet
    if (isset($_SESSION['cart_loaded_from_db']) && $_SESSION['cart_loaded_from_db'] === true) {
        return;
    }

    // Load the user's persistent cart from the database.
    $stmt = $mysqli->prepare("
        SELECT fc.product_id, fc.quantity, p.name, p.price, p.image 
        FROM forever_cart fc
        JOIN products p ON fc.product_id = p.id
        WHERE fc.visitor_token = ?
    ");
    $stmt->bind_param("s", $visitor_token);
    $stmt->execute();
    $result = $stmt->get_result();

    $session_cart = [];
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        $session_cart[$product_id] = [
            'id'       => $product_id,
            'name'     => $row['name'],
            'price'    => $row['price'],
            'image'    => $row['image'],
            'quantity' => $row['quantity']
        ];
    }
    
    $_SESSION['cart'] = $session_cart;
    $_SESSION['cart_loaded_from_db'] = true; // Mark that we've loaded from DB
}
?>
