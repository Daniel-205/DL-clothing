

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Include your database class or connection
require_once 'includes/database.php'; 

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request.');
}

// Basic validation
$name = trim($_POST['name'] ?? '');
$brand = trim($_POST['brand'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');

if (!$name || !$brand || !$price || !$description) {
    die('Please fill in all required fields.');
}

// Handle file upload
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    die('Error uploading image.');
}

// Validate image
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$fileType = mime_content_type($_FILES['image']['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    die('Only JPG, PNG, WEBP, and GIF images are allowed.');
}

// Limit file size to 2MB
if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
    die('Image file too large (max 2MB).');
}

// Generate safe filename
$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid('product_', true) . '.' . $ext;
$uploadDir = 'uploads/products/';
$uploadPath = $uploadDir . $filename;

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Move file
if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
    die('Failed to save uploaded image.');
}

// Insert into database
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("INSERT INTO products (name, brand, price, description, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $name, $brand, $price, $description, $uploadPath);

    if ($stmt->execute()) {
        echo "Product added successfully.";
        // redirect:
        header('Location: admin-viewproduct.php');
         exit;
    } else {
        echo "Database error: " . $stmt->error;
    }

    $stmt->close();
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
