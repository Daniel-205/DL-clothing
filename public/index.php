
<?php include '../includes/dbconfig.php'; 


 include '../includes/header.php';
  ?>


<body class="font-sans bg-gray-50">
    <!-- Main Content (will be replaced by each page's content) -->
    <main id="main-content">
        <!-- Hero Section -->
        <section class="hero bg-indigo-100 py-12">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h1 class="display-4 fw-bold mb-4">Premium Quality T-Shirts</h1>
                        <p class="lead mb-5">Discover our collection of comfortable, stylish t-shirts designed for every occasion.</p>
                        <a href="shop.php" class="btn btn-indigo-600 btn-lg px-4 py-2">Shop Now</a>
                    </div>
                    <div class="col-lg-6">
                        <img src="../uploads/shirt-mockup-concept-with-plain-clothing.jpg" alt="ThreadCraft T-Shirt" class="img-fluid rounded shadow">
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
                        $sql = "SELECT * FROM products LIMIT 4"; // to load only 4 products form the database
                        $result = $mysqli->query($sql);
                        
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo '<div class="col-md-3 mb-4">';
                                echo '    <div class="card h-100 border-0 shadow-sm product-card">';
                                echo '        <div class="product-image-container">';
                                // Assuming 'image' column stores the path relative to a base uploads directory e.g., 'uploads/image.jpg'
                                // If 'image' column contains absolute URLs or needs different handling, adjust accordingly.
                                                echo '<img src="../' . htmlspecialchars($row["image"]) . '" class="card-img-top" alt="' . htmlspecialchars($row["name"]) . '">';
                                echo '        </div>';
                                echo '        <div class="card-body">';
                                echo '            <h5 class="card-title text-truncate">' . htmlspecialchars($row["name"]) . '</h5>';
                                echo '            <p class="card-text text-muted text-truncate">Size: ' . htmlspecialchars($row["size"]) . '</p>';
                                echo '            <a href="shop.php?product_id=' . htmlspecialchars($row["id"]) . '" class="btn btn-sm btn-outline-indigo-600">View Details</a>';
                                echo '        </div>';
                                echo '    </div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="text-center text-gray-600">No featured products available at the moment.</p>';
                        }
                                  
                    ?>
                    
                </div>
                <div class="text-center mt-6">
                    <a href="shop.php" class="btn btn-outline-indigo-600 px-4 py-2">View All Products</a>
                </div>
            </div>
        </section>
               <!-- Features -->
        <section class="py-12 bg-gray-100">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="text-center p-4">
                            <div class="text-indigo-600 text-4xl mb-3">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h3 class="h5 mb-2">Fast Delivery</h3>
                            <p class="text-gray-600"> All Delivery Nation Wide</p>
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
        </section>


        <!-- about us -->


        <section class="py-8"  id="about">
            <div class="container">
                <div class="row align-items-center mb-8">
                    <div class="col-lg-6 mb-5 mb-lg-0">
                        <h1 class="display-4 fw-bold mb-4">Our Story</h1>
                        <!-- <p class="lead mb-4">DeLion Clothing was born out of a passion for quality, comfort,affordable, and minimalist design.</p> -->
                        <p>DELion Clothing is a small but growing business that started in 2015 with a simple idea to make affordable, quality T-shirts available in all colors 
                            for anyone, for any purpose. What began as a side hustle has slowly grown into a trusted name for selling and supplying T-shirts across 
                            Ghana.We may be small now, but we’re building something big — with hard work, honesty, and real service. Whether you’re buying one shirt or ordering for a whole team,
                            we’re here to deliver quality you can wear with pride.
                        </p>
                    </div>
                    <div class="col-lg-6">
                        <img src="" alt="Our Story" class="img-fluid rounded shadow">
                    </div>
                </div>
                
                <div class="row align-items-center mb-8">
                    <div class="col-lg-6 order-lg-2 mb-5 mb-lg-0">
                        <h2 class="mb-4 text-3xl font-bold">Our Mission</h2>
                        <p>To make high-quality, colorful T-shirts accessible to everyone at affordable prices, with styles that fit every mood, every day.We’re here to help you look good, feel confident, and express yourself without breaking the bank.</p>
                    </div>
                    <div class="col-lg-6 order-lg-1">
                        <img src="../uploads/mission.jpg" alt="Our Mission" class="img-fluid rounded shadow">
                    </div>
                </div>
                
                <div class="py-8 my-8 border-top border-bottom">
                    <div class="row justify-content-center text-center">
                        <div class="col-lg-8">
                            <h2 class="mb-4 text-3xl font-bold">Our Values</h2>
                            <p class="lead">Quality, Sustainability, and Transparency</p>
                        </div>
                    </div>
                    
                    <div class="row mt-6">
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-5">
                                    <div class="text-indigo-600 text-4xl mb-4">
                                        <i class="fas fa-leaf"></i>
                                    </div>
                                    <h3 class="h5 mb-3">Sustainability</h3>
                                    <p class="text-gray-600">We use organic materials and eco-friendly packaging to minimize our environmental impact.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-5">
                                    <div class="text-indigo-600 text-4xl mb-4">
                                        <i class="fas fa-medal"></i>
                                    </div>
                                    <h3 class="h5 mb-3">Quality</h3>
                                    <p class="text-gray-600">Every t-shirt is crafted with attention to detail using premium materials for lasting comfort.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-5">
                                    <div class="text-indigo-600 text-4xl mb-4">
                                        <i class="fas fa-globe"></i>
                                    </div>
                                    <h3 class="h5 mb-3">Ethical Production</h3>
                                    <p class="text-gray-600">We partner with factories that provide fair wages and safe working conditions.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- contactt us -->
        <section class="py-8" id="contact">
            <div class="container">
                <div class="row mb-8">
                    <div class="col-lg-6 mb-5 mb-lg-0">
                        <h1 class="display-4 fw-bold mb-4">Get in Touch</h1>
                        <p class="lead mb-4">We'd love to hear from you! Whether you have a question about our products, need help with an order, or just want to say hello, feel free to contact us.</p>
                        
                        <div class="d-flex mb-4">
                            <div class="me-4 text-indigo-600">
                                <i class="fas fa-map-marker-alt fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Our Location</h5>
                                <p class="text-gray-600 mb-0">123 Fashion Street, Los Angeles, CA 90015, USA</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="me-4 text-indigo-600">
                                <i class="fas fa-phone-alt fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Phone Number</h5>
                                <p class="text-gray-600 mb-0">+233 57103 4506</p>
                            </div>
                        </div>
                        
                        <!-- <div class="d-flex mb-4">
                            <div class="me-4 text-indigo-600">
                                <i class="fas fa-envelope fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Email Address</h5>
                                <p class="text-gray-600 mb-0">hello@threadcraft.com</p>
                            </div>
                        </div> -->
                        
                        <div class="d-flex">
                            <div class="me-4 text-indigo-600">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Working Hours</h5>
                                <p class="text-gray-600 mb-0">Monday - Sunday: 23/7</p>
                            </div>
                        </div>
                    </div>

                <!-- Google Map -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        <div class="map-container" style="height: 400px;">
                            <iframe src="https://maps.app.goo.gl/L72NbSMUB15EZFYPA?g_st=com.google.maps.preview.copy" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php include '../includes/footer.php'; ?>
     
    </main>
    
    

   

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./assert/js/product.js"></script>
    <script src="assets/js/cart.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>