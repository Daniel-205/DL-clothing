<?php
require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';

session_start();

// Only logged-in admins can access
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    setFlashMessage('error', 'Unauthorized access.');
    header("Location: login.php");
    exit;
}

// Create CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add New Product</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
      padding: 30px;
    }

    .form-container {
      background: #fff;
      padding: 25px;
      max-width: 500px;
      margin: auto;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
      color: #444;
    }

    input[type="text"],
    input[type="number"],
    input[type="file"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      width: 100%;
      background: #4361ee;
      color: #fff;
      padding: 12px;
      border: none;
      border-radius: 5px;
      font-description: 16px;
      cursor: pointer;
    }

    button:hover {
      background: #3344cc;
    }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 15px;
      color: #666;
      text-decoration: none;
    }

    .back-link:hover {
      color: #4361ee;
    }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>Add New Product</h2>
    
    <?php
    require_once '../includes/functions.php'; 
    echo display_flash_message(); 
    ?>
    
    <form action="../admin/adfunc/product-process.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

      <div class="form-group">
        <label for="name">Product Name</label>
        <input type="text" name="name" id="name" required>
      </div>

      <div class="form-group">
        <label for="price">Price (GHS)</label>
        <input type="number" step="0.01" name="price" id="price" required>
      </div>

      <div class="form-group">
        <label for="size">Size</label> 
        <input type="text" name="size" id="size" required> 
      </div>

      <div class="form-group">
        <label for="image">Upload Image (JPG/PNG)</label>
        <input type="file" name="image" id="image" accept=".jpg,.jpeg,.png" required>
      </div>

      <button type="submit" name="submit">Add Product</button>
    </form>

    <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
  </div>
</body>
</html>
