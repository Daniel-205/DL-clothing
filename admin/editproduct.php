<?php


require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';

session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}



if (!isset($_GET['id'])) {
    echo "No product ID provided.";
    exit();
}

$id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $brand = trim($_POST['brand']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    
    // Handle file upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        // Create upload directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Validate file
        if (!in_array($_FILES['image']['type'], $allowed_types)) {
            $error_message = "Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $error_message = "File size too large. Maximum 5MB allowed.";
        } else {
            // Generate unique filename
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $file_extension;
            $image_path = $upload_dir . $filename;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $error_message = "Failed to upload image.";
            }
        }
    }
    
    // Update database if no upload errors
    if (!isset($error_message)) {
        if ($image_path) {
            // Update with new image
            $stmt = $conn->prepare("UPDATE products SET name=?, brand=?, price=?, description=?, image=? WHERE id=?");
            $stmt->bind_param("ssdssi", $name, $brand, $price, $description, $image_path, $id);
        } else {
            // Update without changing image
            $stmt = $conn->prepare("UPDATE products SET name=?, brand=?, price=?, description=? WHERE id=?");
            $stmt->bind_param("ssdsi", $name, $brand, $price, $description, $id);
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Product updated successfully!";
            $stmt->close();
            $conn->close();
            header("Location: dashboard.php");
            exit();
        } else { 
            $error_message = "Error updating product: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch product data using prepared statement
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
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
        
        <?php if (isset($error_message)): ?>
            <div class="message error"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div>
                <label for="name">Product Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required maxlength="255">
            </div>

            <div>
                <label for="brand">Brand *</label>
                <input type="text" id="size" name="size" value="<?php echo htmlspecialchars($product['size']); ?>" required maxlength="100">
            </div>

            <div>
                <label for="price">Price *</label>
                <input type="number" id="price" step="0.01" min="0" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>

            <!-- <div>
                <label for="description">Description *</label>
                <textarea id="description" name="description" required maxlength="1000"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div> -->

            <div>
                <label for="image">Upload New Image (Optional)</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
                <small style="color: #6c757d; font-size: 0.9em;">
                    Accepted formats: JPEG, PNG, GIF, WebP. Maximum size: 5MB
                </small>
                
                <?php if (!empty($product['image']) && file_exists($product['image'])): ?>
                    <div class="current-image">
                        <p><strong>Current Image:</strong></p>
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Current product image">
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit">Update Product</button>
        </form>
    </div>
</body>
</html>

<?php $conn->close(); ?>