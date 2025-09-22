<?php include '../includes/dbconfig.php'; 

 include '../includes/header.php';
  ?>


<body class="font-sans bg-gray-50">
    <!-- Main Content (will be replaced by each page's content) -->
    <main id="main-content">
        <!-- Hero Section -->
        <section class="hero">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-up">
                        <h1 class="display-4 fw-bold mb-4">Premium Quality T-Shirts</h1>
                        <p class="lead mb-5">Discover our collection of comfortable, stylish t-shirts designed for every occasion.</p>
                        <a href="shop.php" class="btn btn-indigo-600 btn-lg px-4 py-2">Shop Now</a>
                    </div>
                    <div class="col-lg-6" data-aos="fade-left">
                        <img src="../uploads/shirt-mockup-concept-with-plain-clothing.jpg" alt="ThreadCraft T-Shirt" class="img-fluid rounded shadow">
                    </div>
                </div>
            </div>
        </section>

        <!-- Featured Products -->
        <section class="section bg-white">
            <div class="container">
                <h2 class="text-center mb-8 text-3xl font-bold" data-aos="fade-up">Featured Products</h2>
                <div class="row featured-products">
                    <!-- Products will be loaded from database  -->
                    <?php                        
                        $sql = "SELECT * FROM products LIMIT 4"; // to load only 4 products form the database
                        $result = $mysqli->query($sql);
                        
                        if ($result && $result->num_rows > 0) {
                            $delay = 0;
                            while($row = $result->fetch_assoc()) {
                                echo '<div class="col-md-3 mb-4" data-aos="fade-up" data-aos-delay="' . $delay . '">';
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
                                $delay += 100;
                            }
                        } else {
                            echo '<p class="text-center text-gray-600">No featured products available at the moment.</p>';
                        }
                                  
                    ?>
                    
                </div>
                <div class="text-center mt-6" data-aos="fade-up">
                    <a href="shop.php" class="btn btn-outline-indigo-600 px-4 py-2">View All Products</a>
                </div>
            </div>
        </section>
               <!-- Features -->
        <section class="section bg-gray-100">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="0">
                        <div class="text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h3 class="h5 mb-2">Fast Delivery</h3>
                            <p class="text-gray-600"> All Delivery Nation Wide</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                        <div class="text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-undo"></i>
                            </div>
                            <h3 class="h5 mb-2">Easy Returns</h3>
                            <p class="text-gray-600">30-day return policy for all items</p>
                        </div>
                    </div>
                    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                        <div class="text-center p-4">
                            <div class="feature-icon">
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


        <!-- contactt us -->
        <section class="section" id="contact">
            <div class="container">
                <div class="row mb-8">
                    <div class="col-lg-6 mb-5 mb-lg-0" data-aos="fade-right">
                        <h1 class="display-4 fw-bold mb-4">Get in Touch</h1>
                        <p class="lead mb-4">We'd love to hear from you! Whether you have a question about our products, need help with an order, or just want to say hello, feel free to contact us.</p>
                        
                        <div class="d-flex mb-4">
                            <div class="me-4 feature-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Our Location</h5>
                                <p class="text-gray-600 mb-0">123 Fashion Street, Los Angeles, CA 90015, USA</p>
                            </div>
                        </div>
                        
                        <div class="d-flex mb-4">
                            <div class="me-4 feature-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">Phone Number</h5>
                                <p class="text-gray-600 mb-0">+233 57103 4506</p>
                            </div>
                        </div>
                             
                        <div class="d-flex">
                            <div class="me-4 feature-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>

                                <h5 class="mb-1">Working Hours</h5>
                                <p class="text-gray-600 mb-0">Monday - Sunday: 23/7</p>
                            </div>
                        </div>
                    </div>

             
                    <!-- Google Map -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card border-0 shadow-lg">
                                <div class="card-body p-0">
                                    <div class="map-container" style="height: 400px;">
                                        <iframe src="https://www.google.com/maps/embed/v1/place?key=API_KEY&q=Space+Needle,Seattle+WA" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                                    </div>
                                </div>
                            </div>
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
    <script>
        // Add to cart function (dummy for now)
        function addToCart(productId) {
            // In real app, this would add to session/cart
            alert('Added product #' + productId + ' to cart!');
            // You can connect this to your cart.js later
        }

        // Scroll-triggered animations (optional enhancement)
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.querySelector(this.getAttribute('href')).scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });

            // Parallax effect on hero (optional)
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const hero = document.querySelector('.hero');
                if (hero) {
                    hero.style.backgroundPositionY = -(scrolled * 0.3) + 'px';
                }
            });
        });

        // AOS Init (if not already done in main.js)
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            offset: 100
        });
    </script>
</body>
</html>