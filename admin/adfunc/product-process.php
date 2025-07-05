<?php

require_once '../../includes/dbconfig.php';
require_once '../../includes/functions.php'; 


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

//  1. Protect: Only admin can access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    setFlashMessage('error', 'Access denied.');
    header("Location: login.php");
    exit;
}

//  2. Validate method and CSRF token
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request.');
}
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token mismatch.');
}

//  3. Sanitize input
$name = trim($_POST['name'] ?? '');
$brand = trim($_POST['brand'] ?? '');
$price = floatval($_POST['price'] ?? 0);
$description = trim($_POST['description'] ?? '');

if (!$name || !$brand || !$price || !$description) {
    setFlashMessage('error', 'Please fill in all required fields.');
    header("Location: ../addproduct.php");
    exit;
}

//  4. Image Validation
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    setFlashMessage('error', 'Image upload failed.');
    header("Location: addproduct.php");
    exit;
}

$allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$fileType = mime_content_type($_FILES['image']['tmp_name']);

if (!in_array($fileType, $allowedTypes)) {
    setFlashMessage('error', 'Invalid file type.');
    header("Location: addproduct.php");
    exit;
}

if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
    setFlashMessage('error', 'Image too large (max 2MB).');
    header("Location: addproduct.php");
    exit;
}

//  5. Image Uploading
$uploadDir = 'uploads/products/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
$filename = uniqid('product_', true) . '.' . $ext;
$uploadPath = $uploadDir . $filename;

if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
    setFlashMessage('error', 'Could not move uploaded file.');
    header("Location: addproduct.php");
    exit;
}


if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
    setFlashMessage('error', 'Failed to move uploaded file.');
    header("Location: addproduct.php");
    exit;
}

//  Resize the image after saving (optional thumbnail filename)
$thumbnailPath = $uploadDir . 'thumb_' . $filename;

resizeImage($uploadPath, $thumbnailPath, 500, 80); // Resize to 500px width


//  6. Save product in database
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("INSERT INTO products (name, brand, price, description, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdss", $name, $brand, $price, $description, $uploadPath);

    if ($stmt->execute()) {
        setFlashMessage('success', 'Product added successfully.');
        header("Location: dashboard.php");
        exit;
    } else {
        setFlashMessage('error', 'Database error: ' . $stmt->error);
        header("Location: addproduct.php");
        exit;
    }

    $stmt->close();
} catch (Exception $e) {
    setFlashMessage('error', 'Something went wrong: ' . $e->getMessage());
    header("Location: addproduct.php");
    exit;
}
