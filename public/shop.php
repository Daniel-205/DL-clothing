<?php
include '../includes/dbconfig.php'; 

 include '../includes/header.php'; 

// Fetch products
$query = "SELECT * FROM products LIMIT 8";
$result = $mysqli->query($query);
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | DL Clothing</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
</head>
<body>
    <!-- Navigation would be included here -->
    
    <div class="container py-5">
        <!-- Search Header -->
        <div class="row mb-5">
            <div class="col-lg-6 mx-auto text-center">
                <h1 class="display-5 mb-4">Discover Our T-Shirts</h1>
                <form class="d-flex">
                    <input type="text" name="search" 
                           class="form-control search-box py-3 px-4" 
                           placeholder="Search by style, color...">
                    <button class="btn btn-primary search-btn px-4" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </form>
            </div>
        </div>

        <!-- Product Grid -->
        <div class="row g-4">
            <?php foreach ($products as $product): ?>
                <div class="col-xl-3 col-lg-4 col-md-6">
                    <div class="product-card h-100">
                        <div class="position-relative">
                            <!-- Product Image -->
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" class="product-img w-100">
                                 
                            <!-- Badge (for sales/featured) -->
                            <?php if($product['is_featured']): ?>
                                <span class="position-absolute top-0 start-0 bg-danger text-white px-3 py-1 m-2 rounded-pill">
                                    HOT
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-body p-4">
                            <!-- Product Title -->
                            <h5 class="card-title mb-2">
                                <?= htmlspecialchars($product['name']) ?>
                            </h5>
                            
                            <!-- Price -->
                            <p class="price-tag mb-3">
                                $<?= number_format($product['price'], 2) ?>
                            </p>
                            
                            <!-- Add to Cart Button -->
                            <button class="btn btn-custom text-white w-100">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap Icons (for cart/search icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>