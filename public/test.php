<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP is working!<br>";

// Test database connection
include '../includes/dbconfig.php';

if ($mysqli->connect_error) {
    echo "Database connection failed: " . $mysqli->connect_error;
} else {
    echo "Database connection successful!<br>";
    
    // Test query
    $result = $mysqli->query("SELECT COUNT(*) as count FROM products");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Products in database: " . $row['count'];
    }
}
?>

