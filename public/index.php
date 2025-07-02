
<?php include './includes/dbconfig.php'; ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> LION T-Shirts</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind CSS -->
    <link href="./assert/css/tailwind.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="./assert/css/main.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="font-sans bg-gray-50">
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3">
        <div class="container">
            <a class="navbar-brand fw-bold text-2xl text-indigo-600" href="index.html">LION</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="./hero.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="shop.html">Shop</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.html">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.html">Contact</a></li>
                    <li class="nav-item ms-2">
                        <!-- <a class="nav-link position-relative" href="cart.html"> -->
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content (will be replaced by each page's content) -->
    <main id="main-content">
        <!-- Hero Section -->
        <section class="hero bg-indigo-100 py-12">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h1 class="display-4 fw-bold mb-4">Premium Quality T-Shirts</h1>
                        <p class="lead mb-5">Discover our collection of comfortable, stylish t-shirts designed for every occasion.</p>
                        <a href="shop.html" class="btn btn-indigo-600 btn-lg px-4 py-2">Shop Now</a>
                    </div>
                    <div class="col-lg-6">
                        <img src="./uploads/shirt-mockup-concept-with-plain-clothing.jpg" alt="ThreadCraft T-Shirt" class="img-fluid rounded shadow">
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="py-12 bg-white">
            <div class="container">
                <h2 class="text-center mb-8 text-3xl font-bold">Featured Products</h2>
                <div class="row featured-products">
                    <!-- Products will be loaded from database  -->
                    <?php  
                        $sql = "SELECT * FROM products";
                        
                       
                       
            //              if ($result->num_rows > 0) {
            //     while($row = $result->fetch_assoc()) {
            //         echo '<div class="product-card">';
            //         echo '<div class="product-image-container">';
            //         echo '<img src="' . htmlspecialchars($row["image"]) . '" alt="' . htmlspecialchars($row["name"]) . '">';
            //         echo '</div>';
            //         echo '<div class="product-info">';
            //         echo '<h3>' . htmlspecialchars($row["name"]) . '</h3>';
            //         echo '<p class="product-brand"><strong>Brand:</strong> ' . htmlspecialchars($row["brand"]) . '</p>';
            //         echo '<a href="product.php?id=' . htmlspecialchars($row["id"]) . '" class="btn-view" target="_blank">View Details</a>';
            //         // echo '<a href="product.php?id=' . $row["id"] . '" class="btn-view">View Details</a>';
            //         echo '</div>';
            //         echo '</div>';
            //     }
            // }

                                    
                                    



                                    



                                  
                    ?>
                    
                      

                    
                </div>
                <div class="text-center mt-6">
                    <a href="shop.html" class="btn btn-outline-indigo-600 px-4 py-2">View All Products</a>
                </div>
            </div>
        </section> 



         
       
        <!-- Features -->
        <!-- <section class="py-12 bg-gray-100">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center p-4">
                            <div class="text-indigo-600 text-4xl mb-3">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h3 class="h5 mb-2">Free Shipping</h3>
                            <p class="text-gray-600">Free shipping on all orders over $50</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-4">
                            <div class="text-indigo-600 text-4xl mb-3">
                                <i class="fas fa-undo"></i>
                            </div>
                            <h3 class="h5 mb-2">Easy Returns</h3>
                            <p class="text-gray-600">30-day return policy for all items</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-4">
                            <div class="text-indigo-600 text-4xl mb-3">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h3 class="h5 mb-2">Secure Payment</h3>
                            <p class="text-gray-600">100% secure payment processing</p>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->

        <!-- Testimonials -->
        <!-- <section class="py-12 bg-white">
            <div class="container">
                <h2 class="text-center mb-8 text-3xl font-bold">What Our Customers Say</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3 text-yellow-400">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="mb-4">"The quality of these t-shirts is amazing! Super comfortable and the designs are unique."</p>
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/user1.jpg" alt="User" class="rounded-circle me-3" width="50">
                                    <div>
                                        <h6 class="mb-0">Sarah Johnson</h6>
                                        <small class="text-gray-600">Verified Buyer</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3 text-yellow-400">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                                <p class="mb-4">"Fast shipping and great customer service. Will definitely order again!"</p>
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/user2.jpg" alt="User" class="rounded-circle me-3" width="50">
                                    <div>
                                        <h6 class="mb-0">Michael Chen</h6>
                                        <small class="text-gray-600">Verified Buyer</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body p-4">
                                <div class="mb-3 text-yellow-400">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                                <p class="mb-4">"Love the minimalist designs. Perfect fit and very comfortable material."</p>
                                <div class="d-flex align-items-center">
                                    <img src="assets/images/user3.jpg" alt="User" class="rounded-circle me-3" width="50">
                                    <div>
                                        <h6 class="mb-0">David Wilson</h6>
                                        <small class="text-gray-600">Verified Buyer</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->
        
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="text-xl font-bold mb-4">ThreadCraft</h5>
                    <p class="text-gray-400">Premium quality t-shirts with unique designs for every occasion.</p>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="font-bold mb-4">Shop</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="shop.html" class="text-gray-400 hover:text-white">All Products</a></li>
                        <li class="mb-2"><a href="#" class="text-gray-400 hover:text-white">New Arrivals</a></li>
                        <li class="mb-2"><a href="#" class="text-gray-400 hover:text-white">Best Sellers</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="font-bold mb-4">Company</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="about.html" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li class="mb-2"><a href="#" class="text-gray-400 hover:text-white">Careers</a></li>
                        <li class="mb-2"><a href="#" class="text-gray-400 hover:text-white">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h6 class="font-bold mb-4">Newsletter</h6>
                    <p class="text-gray-400 mb-4">Subscribe to get updates on new products and discounts.</p>
                    <form class="d-flex">
                        <input type="email" class="form-control rounded-0" placeholder="Your email">
                        <button class="btn btn-indigo-600 text-white rounded-0">Subscribe</button>
                    </form>
                </div>
            </div>
            <hr class="my-6 border-gray-700">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-gray-400">&copy; 2023 ThreadCraft. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-gray-400 hover:text-white mx-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white mx-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white mx-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white mx-2"><i class="fab fa-pinterest"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./assert/js/product.js"></script>
    <script src="assets/js/cart.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>