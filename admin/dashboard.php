<?php


require_once '../includes/dbconfig.php';
require_once '../includes/functions.php';


require_login('admin', 'admin-login.php');

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $mysqli->query($sql);
?>






<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Products - Admin</title>


  <style>

    /* admin-style.css */
    :root {
        --primary-color: #4361ee;
        --primary-dark: #3a56c8;
        --secondary-color: #f8f9fa;
        --text-color: #2b2d42;
        --light-gray: #e9ecef;
        --medium-gray: #adb5bd;
        --dark-gray: #495057;
        --danger-color: #ef233c;
        --success-color: #4cc9f0;
        --border-radius: 6px;
        --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fb;
        color: var(--text-color);
        line-height: 1.6;
        padding: 20px;
    }

    .dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 30px;
    }

    h1 {
        color: var(--text-color);
        margin-bottom: 25px;
        font-weight: 600;
        border-bottom: 2px solid var(--light-gray);
        padding-bottom: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .product-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        font-size: 14px;
    }

    .product-table th {
        background-color: var(--primary-color);
        color: white;
        text-align: left;
        padding: 12px 15px;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .product-table td {
        padding: 12px 15px;
        border-bottom: 1px solid var(--light-gray);
        vertical-align: middle;
    }

    .product-table tr:hover {
        background-color: rgba(67, 97, 238, 0.05);
    }

    .product-table tr:last-child td {
        border-bottom: none;
    }

    .thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid var(--light-gray);
        transition: var(--transition);
    }

    .thumbnail:hover {
        transform: scale(1.5);
        box-shadow: var(--box-shadow);
    }

    .btn-edit, .btn-back {
        display: inline-block;
        padding: 8px 16px;
        border-radius: var(--border-radius);
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: var(--transition);
        margin-bottom: 20px;
    }

    .btn-edit {
        background-color: var(--primary-color);
        color: white;
        border: 1px solid var(--primary-color);
    }

    .btn-edit:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }

    .btn-back {
        background-color: var(--dark-gray);
        color: white;
        margin-left: 10px;
    }

    .btn-back:hover {
        background-color: var(--text-color);
    }

    a[onclick] {
        color: var(--danger-color);
        text-decoration: none;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: var(--border-radius);
        transition: var(--transition);
    }

    a[onclick]:hover {
        background-color: rgba(239, 35, 60, 0.1);
        text-decoration: underline;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 15px;
        }
        
        .product-table {
            display: block;
            overflow-x: auto;
        }
        
        .btn-edit, .btn-back {
            display: block;
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .btn-back {
            margin-left: 0;
        }
    }

    /* Animation for empty state */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .product-table tbody tr {
        animation: fadeIn 0.3s ease forwards;
    }

    .product-table tbody tr:nth-child(even) {
        background-color: rgba(233, 236, 239, 0.3);
    }

    /* Tooltip for delete confirmation */
    [onclick]:after {
        content: attr(title);
        position: absolute;
        background: var(--text-color);
        color: white;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 0.8rem;
        white-space: nowrap;
        visibility: hidden;
        opacity: 0;
        transition: var(--transition);
        transform: translateY(10px);
    }

    [onclick]:hover:after {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    } 
  </style>

</head>
<body>
  <div class="dashboard-container">
    <h1>Manage Products</h1>
    <a href="addproduct.php" class="btn-edit">Add New product</a>
    <a href="logout.php" class="btn-back">logout</a>
    <table class="product-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Size</th>
          <th>Price (GHS)</th>
          <th>Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['id']); ?></td>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['size']); ?></td>
              <td><?php echo htmlspecialchars($row['price']); ?></td>
              <td><img src="<?php echo htmlspecialchars($row['image']); ?>" alt="" class="thumbnail"></td>
              <td>
                <a href="admin-editproduct.php?id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                <a href="delete.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>

              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6">No products found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>

<?php $mysqli->close(); ?>
