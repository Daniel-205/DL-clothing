<?php
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';

session_start();

// ✅ 1. Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    setFlashMessage('error', 'Unauthorized access.');
    header("Location: login.php");
    exit;
}

// ✅ 2. Validate product ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    setFlashMessage('error', 'Invalid product ID.');
    header("Location: dashboard.php");
    exit;
}

$product_id = intval($_GET['id']);

// ✅ 3. Fetch image name
$imageStmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$imageStmt->bind_param("i", $product_id);
$imageStmt->execute();
$result = $imageStmt->get_result();

if ($row = $result->fetch_assoc()) {
    $imageName = $row['image'];
    $imagePath = realpath('../uploads/' . $imageName);

    // ✅ 4. Delete image file safely
    if ($imagePath && strpos($imagePath, realpath('../uploads')) === 0 && file_exists($imagePath)) {
        unlink($imagePath);
    }
}
$imageStmt->close();

// ✅ 5. Delete product from DB
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    setFlashMessage('success', 'Product deleted successfully.');
} else {
    setFlashMessage('error', 'Failed to delete product.');
}

$stmt->close();
$conn->close();

// ✅ 6. Redirect to dashboard
header("Location: dashboard.php");
exit;
