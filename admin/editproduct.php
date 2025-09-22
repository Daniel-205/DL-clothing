<?php


require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

// Create CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_GET['id'])) {
    echo "No product ID provided.";
    exit();
}

$id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = "CSRF token mismatch. Action aborted.";
        // Optionally, unset the token to force regeneration on next load
        // unset($_SESSION['csrf_token']); 
    } else {
        // CSRF token is valid, proceed with form processing
        $name = trim($_POST['name']);
        // $brand = trim($_POST['brand']); // brand is removed, using size
        $price = floatval($_POST['price']);
    $size = trim($_POST['size']); // This now comes from the field named 'size'
    
    // Handle file upload
    $new_image_db_path = null; // This will hold the path to be stored in DB, e.g., "uploads/products/newimage.jpg"
    
    // Check if a new image was actually uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Filesystem path for upload, relative to this script (admin/editproduct.php)
        $filesystem_upload_dir = '../uploads/products/'; 
        // Path prefix for DB storage, relative to web root
        $db_storage_path_prefix = 'uploads/products/';

        // Define allowed types (extensions for upload_file) and max size
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $max_size_bytes = 5 * 1024 * 1024; // 5MB

        // Ensure base upload directory exists (upload_file also does this, but good for clarity)
        if (!is_dir($filesystem_upload_dir)) {
            if (!mkdir($filesystem_upload_dir, 0755, true) && !is_dir($filesystem_upload_dir)) {
                $error_message = "Failed to create upload directory.";
            }
        }

        if (!isset($error_message)) {
            $uploadResult = upload_file(
                $_FILES['image'],
                $filesystem_upload_dir,
                $allowed_extensions,
                $max_size_bytes
            );

            if ($uploadResult['success']) {
                $filesystem_image_path = $uploadResult['filename']; // Full filesystem path
                $filename_only = basename($filesystem_image_path);

                // Sanitize the image by resizing it
                $sanitized_filesystem_path = $filesystem_upload_dir . 'product_' . $filename_only;
                resizeImage($filesystem_image_path, $sanitized_filesystem_path, 500, 80);

                // Delete the original uploaded file
                if (file_exists($filesystem_image_path)) {
                    unlink($filesystem_image_path);
                }

                // Set the DB path to the new sanitized image
                $new_image_db_path = $db_storage_path_prefix . 'product_' . $filename_only;

                // TODO: Consider deleting the old image if a new one is uploaded successfully.
                // This would require fetching the old image path from $product['image']
                // and then calling delete_file('../' . $product['image']).
                // Be careful with this, only delete after successful DB update.
            } else {
                $error_message = 'Image upload error: ' . $uploadResult['message'];
            }
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // An error occurred with the upload, other than no file being selected
        $error_message = 'Image upload failed with error code: ' . $_FILES['image']['error'];
    }
    
    // Update database if no upload errors (from either CSRF or file upload)
    if (!isset($error_message)) {
        if ($new_image_db_path) {
            // Update with new image
            // Assuming 'brand' column is now 'size'. The $size variable holds the new value.
            $stmt = $mysqli ->prepare("UPDATE products SET name=?, price=?, size=?, image=? WHERE id=?");
            $stmt->bind_param("sdssi", $name, $price, $size, $new_image_db_path, $id); // s d s s i
        } else {
            // Update without changing image
            $stmt = $mysqli ->prepare("UPDATE products SET name=?, price=?, size=? WHERE id=?");
            $stmt->bind_param("sdsi", $name, $price, $size, $id); // s d s i
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Product updated successfully!";
            $stmt->close();
            $mysqli ->close();
            header("Location: dashboard.php");
            exit();
        } else { 
            $error_message = "Error updating product: " . $mysqli ->error;
        }
        $stmt->close();
        }
        // End of CSRF token valid block
    }
}

// Fetch product data using prepared statement
$stmt = $mysqli ->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    echo "Product not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="admin-style.css">
    <style>
        /* Form Container */
        .form-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            color: #2b2d42;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
            font-weight: 600;
        }

        /* Form Elements */
        form {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 0.95rem;
        }

        input[type="text"],
        input[type="number"],
        input[type="file"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
            box-sizing: border-box;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="file"]:focus,
        textarea:focus {
            border-color: #4361ee;
            outline: none;
            background-color: white;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        /* Button Styles */
        button[type="submit"] {
            background-color: #4361ee;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background-color: #3a56c8;
            transform: translateY(-2px);
        }

        button[type="submit"]:active {
            transform: translateY(0);
        }

        /* Current Image Preview */
        .current-image {
            margin-top: 10px;
        }

        .current-image img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .current-image img:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
                margin: 20px;
            }
            
            form {
                gap: 15px;
            }
            
            input[type="text"],
            input[type="number"],
            input[type="file"],
            textarea {
                padding: 10px 12px;
            }
        }

        /* Form Validation */
        input:invalid,
        textarea:invalid {
            border-color: #ef233c;
        }

        input:valid,
        textarea:valid {
            border-color: #4cc9f0;
        }

        /* Success/Error Messages */
        .message {
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .message.error {
            background-color: #fee;
            color: #ef233c;
            border-left: 4px solid #ef233c;
        }

        .message.success {
            background-color: #efe;
            color: #2ecc71;
            border-left: 4px solid #2ecc71;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4361ee;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <a href="dashboard.php" class="back-link">← Back to Products</a>
        
        <h2>Edit Product</h2>

        <?php 
        // Display flash messages (from session)
        // functions.php is already required at the top of the file
        echo display_flash_message(); 
        ?>
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div>
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required maxlength="255">
            </div>

            <div>
                <label for="size">Size</label> <!-- Changed label -->
                <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($product['size'] ?? ''); ?>" required maxlength="100"> <!-- Value from product['size'] -->
            </div>

            <div>
                <label for="price">Price *</label>
                <input type="number" id="price" step="0.01" min="0" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>

           

            <div>
                <label for="image">Upload New Image (Optional)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <small style="color: #6c757d; font-size: 0.9em;">
                    Accepted formats: JPEG, PNG, GIF, WebP. Maximum size: 5MB
                </small>
                
                <?php 
                // Check if product image exists and construct the correct path
                // $product['image'] is expected to store something like 'uploads/products/image.jpg'
                $current_image_path_for_display = '';
                if (!empty($product['image'])) {
                    // Path relative to web root (stored in DB)
                    $image_path_from_db = $product['image']; 
                    // Filesystem path to check existence, relative to this script's location
                    $image_filesystem_path = '../' . $image_path_from_db; 
                    
                    if (file_exists($image_filesystem_path)) {
                        // Path for src attribute, relative to this script's location
                        $current_image_path_for_display = '../' . htmlspecialchars($image_path_from_db);
                    }
                }
                ?>
                <?php if ($current_image_path_for_display): ?>
                    <div class="current-image">
                        <p><strong>Current Image:</strong></p>
                        <img src="<?php echo $current_image_path_for_display; ?>" alt="Current product image">
                    </div>
                <?php elseif (!empty($product['image'])): ?>
                    <div class="current-image">
                        <p><strong>Current Image:</strong> (File not found at <?php echo htmlspecialchars('../' . $product['image']); ?>)</p>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit">Update Product</button>
        </form>
    </div>
</body>
</html>

<?php $mysqli ->close(); ?>
