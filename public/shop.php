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
                            <img src="../<?= htmlspecialchars($product['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>">
                        </div>
                        
                        <div class="card-body p-4">
                            <!-- Product Title -->
                            <h5 class="card-title mb-2">
                                <?= htmlspecialchars($product['name']) ?>
                            </h5>
                            
                            <!-- Price -->
                            <p class="price-tag mb-3">
                                GH₵<?= number_format($product['price'], 2) ?>
                            </p>
                            
                            <!-- Add to Cart Button -->
                            <form action="../admin/cart-logic/add-to-cart.php" method="POST" class="mt-2">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn btn-sm btn-dark">Add to Cart</button>
                            </form>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>