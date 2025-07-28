<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/dbconfig.php';

// Create orders table
$create_orders = "
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `visitor_token` varchar(64) NOT NULL,
  `order_code` varchar(32) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `order_total` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `visitor_token` (`visitor_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Create order_items table
$create_order_items = "
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// Execute queries
$result1 = $mysqli->query($create_orders);
$result2 = $mysqli->query($create_order_items);

if ($result1 && $result2) {
    echo "Tables created successfully!";
} else {
    echo "Error creating tables: " . $mysqli->error;
}

// Ensure foreign keys
$add_foreign_key = "
ALTER TABLE `order_items` 
ADD CONSTRAINT `fk_order_items_order` 
FOREIGN KEY (`order_id`) 
REFERENCES `orders` (`id`) 
ON DELETE CASCADE;
";

// This might fail if the constraint already exists, so we'll handle the error
try {
    $mysqli->query($add_foreign_key);
    echo "<br>Foreign key constraints added successfully!";
} catch (Exception $e) {
    echo "<br>Note: Foreign key might already exist, or there was an error: " . $e->getMessage();
}

echo "<br>Setup complete. <a href='../public/cart.php'>Return to cart</a>";
?>