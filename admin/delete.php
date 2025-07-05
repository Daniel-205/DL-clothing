<?php
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    set_flash_message('error', 'Access denied. Please log in.');
    header("Location: admin-login.php");
    exit;
}

// Check if product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    set_flash_message('error', 'No product ID provided.');
    header("Location: dashboard.php");
    exit;
}

$product_id = (int)$_GET['id'];

try {
    // First, get the product details to retrieve the image path
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        set_flash_message('error', 'Product not found.');
        header("Location: dashboard.php");
        exit;
    }
    
    $product = $result->fetch_assoc();
    
    // Delete the product from database
    $delete_stmt = $mysqli->prepare("DELETE FROM products WHERE id = ?");
    $delete_stmt->bind_param("i", $product_id);
    
    if ($delete_stmt->execute()) {
        // If database deletion successful, try to delete the image file
        if (!empty($product['image']) && file_exists($product['image'])) {
            // Delete the image file
            if (unlink($product['image'])) {
                $message = 'Product and image deleted successfully.';
            } else {
                $message = 'Product deleted successfully, but could not delete image file.';
            }
        } else {
            $message = 'Product deleted successfully.';
        }
        
        // Log the activity
        log_activity('product_deleted', "Product deleted: {$product['name']} (ID: {$product_id})", 'admin');
        
        set_flash_message('success', $message);
    } else {
        set_flash_message('error', 'Failed to delete product. Please try again.');
    }
    
} catch (Exception $e) {
    error_log("Delete product error: " . $e->getMessage());
    set_flash_message('error', 'An error occurred while deleting the product.');
}

// Close database connection
$mysqli->close();

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>