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
    header("Location: ../login.php"); // Corrected path
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
// $brand = trim($_POST['brand'] ?? ''); // brand is removed, description is used
$price = floatval($_POST['price'] ?? 0);
$size = trim($_POST['size'] ?? ''); // This now comes from the field previously named 'size'

// Ensure all fields, including the new description, are validated
if (!$name || !$price || !$size) { // Removed brand from check
    setFlashMessage('error', 'Please fill in all required fields (name, price, description).');
    header("Location: ../addproduct.php");
    exit;
}

//  4. Image Validation & Uploading using upload_file() function
// Filesystem path for upload, relative to this script (admin/adfunc/product-process.php)
$filesystemUploadDir = '../../uploads/products/'; 
// Path to be stored in database, relative to web root
$dbStoragePathPrefix = 'uploads/products/';

if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
    setFlashMessage('error', 'No image uploaded.'); // Or treat as optional depending on requirements
    header("Location: ../addproduct.php");
    exit;
}
if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    setFlashMessage('error', 'Image upload failed with error code: ' . $_FILES['image']['error']);
    header("Location: ../addproduct.php");
    exit;
}

// Define allowed types and max size for upload_file function
$allowed_mime_types_for_check = ['image/jpeg', 'image/png', 'image/webp', 'image/gif']; // upload_file uses extensions
$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$max_size_bytes = 2 * 1024 * 1024; // 2MB

// We need to ensure the directory exists before calling upload_file, 
// as upload_file itself creates the *final* directory in its path if not existing,
// but here $filesystemUploadDir is a path *segment*.
// However, upload_file also contains: if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
// So, this explicit check might be redundant if upload_file handles it robustly for paths like '../../uploads/products/'
if (!is_dir($filesystemUploadDir)) {
    if (!mkdir($filesystemUploadDir, 0755, true)) {
        setFlashMessage('error', 'Failed to create base upload directory.');
        header("Location: ../addproduct.php");
        exit;
    }
}

$uploadResult = upload_file(
    $_FILES['image'],
    $filesystemUploadDir,
    $allowed_extensions,
    $max_size_bytes
);

if (!$uploadResult['success']) {
    setFlashMessage('error', 'Image upload error: ' . $uploadResult['message']);
    header("Location: ../addproduct.php");
    exit;
}

$filesystemUploadPath = $uploadResult['filename']; // This is the full filesystem path, e.g., ../../uploads/products/newname.jpg
$filename = basename($filesystemUploadPath); // Extract just the filename part
$dbUploadPath = $dbStoragePathPrefix . $filename; // Path to store in DB, e.g., uploads/products/newname.jpg

//  Resize the image after saving
$thumbnailFilesystemPath = $filesystemUploadDir . 'thumb_' . $filename;
resizeImage($filesystemUploadPath, $thumbnailFilesystemPath, 500, 80); 
// Note: The thumbnail is generated but not explicitly used or saved to DB. This is unchanged.

//  6. Save product in database
try {
    // Use the global $mysqli connection from dbconfig.php
    // Ensure dbconfig.php is required once at the top, which it is.
    global $mysqli; 
    $mysqli  = $mysqli;

    if ($mysqli ->connect_error) {
        setFlashMessage('error', 'Database connection failed: ' . $mysqli ->connect_error);
        header("Location: ../addproduct.php"); // Corrected path
        exit;
    }

    // The 'brand' column is being replaced by 'description'.
    // The form now sends 'description' (from the field previously named 'size').
    // The $size variable already holds this.
    // The $brand variable was removed.
    // The table structure is assumed to be (name, price, description, image).
    // If the table still has a 'brand' column and expects it, this needs to be reconciled.
    // For now, proceeding with the assumption that 'description' replaces 'brand' in the DB as well.
    $stmt = $mysqli ->prepare("INSERT INTO products (name, price, size, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdss", $name, $price, $size, $dbUploadPath); // Use $dbUploadPath

    if ($stmt->execute()) {
        setFlashMessage('success', 'Product added successfully.');
        header("Location: ../dashboard.php"); // Corrected path
        exit;
    } else {
        setFlashMessage('error', 'Database error: ' . $stmt->error);
        header("Location: ../addproduct.php"); // Corrected path
        exit;
    }

    $stmt->close();
    // $mysqli ->close(); // Avoid closing global connection here if other scripts might use it after include.
} catch (Exception $e) {
    setFlashMessage('error', 'Something went wrong: ' . $e->getMessage());
    header("Location: ../addproduct.php"); // Corrected path
    exit;
}
