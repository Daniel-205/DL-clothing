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

// --- Security Checks ---
// 1. Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash_message('error', 'Invalid request method.');
    header("Location: dashboard.php");
    exit;
}

// 2. Verify CSRF token
if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
    set_flash_message('error', 'Invalid CSRF token.');
    header("Location: dashboard.php");
    exit;
}

// 3. Check if product ID is provided
if (!isset($_POST['id']) || empty($_POST['id'])) {
    set_flash_message('error', 'No product ID provided.');
    header("Location: dashboard.php");
    exit;
}

$product_id = (int)$_POST['id'];

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
        if (!empty($product['image']) && file_exists('../' . $product['image'])) { // Corrected path for file_exists
            // Delete the image file
            if (unlink('../' . $product['image'])) { // Corrected path for unlink
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
if (isset($stmt)) $stmt->close();
if (isset($delete_stmt)) $delete_stmt->close();
$mysqli->close();

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>
