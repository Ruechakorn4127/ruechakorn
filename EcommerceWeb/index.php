<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Fetch featured products
$query = "SELECT p.*, c.name as category_name, 
          (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active' 
          ORDER BY p.created_at DESC 
          LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch categories
$cat_query = "SELECT * FROM categories WHERE status = 'active' LIMIT 4";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #858796;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
        }
        
        body {
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fc;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            color: white;
            font-weight: 800;
            font-size: 1.5rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }
        
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 50px;
        }
        
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .search-box {
            max-width: 500px;
            margin: 30px auto;
        }
        
        .search-box .input-group {
            background: white;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .search-box input {
            border: none;
            padding: 15px 25px;
            font-size: 1rem;
        }
        
        .search-box button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            background: #224abe;
        }
        
        .category-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
            color: inherit;
        }
        
        .category-icon {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .product-image {
            height: 250px;
            background: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: cover;
        }
        
        .product-details {
            padding: 20px;
        }
        
        .product-category {
            color: var(--secondary-color);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .product-title {
            font-weight: 700;
            margin: 10px 0;
            font-size: 1.2rem;
        }
        
        .product-price {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin: 15px 0;
        }
        
        .btn-add-to-cart {
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-add-to-cart:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary-color) 0%, #224abe 100%);
            border-radius: 2px;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            padding: 50px 0 20px;
            margin-top: 50px;
        }
        
        .cart-badge {
            background: var(--danger-color);
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -5px;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-store"></i> E-Store
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php">
                            <i class="fas fa-list"></i> Categories
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i> Cart
                                <span class="cart-badge" id="cartCount">0</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                            </a>
                        </li>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">
                                    <i class="fas fa-cog"></i> Admin
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="hero-title">Welcome to E-Store</h1>
            <p class="hero-subtitle">Discover amazing products at the best prices</p>
            
            <!-- Search Box -->
            <div class="search-box">
                <form action="products.php" method="GET">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Search products...">
                        <button class="btn" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <div class="container">
        <h2 class="section-title">Shop by Category</h2>
        <div class="row">
            <?php foreach($categories as $category): ?>
            <div class="col-md-3">
                <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                    <div class="category-icon">
                        <i class="fas fa-<?php 
                            echo $category['id'] == 1 ? 'mobile-alt' : 
                                ($category['id'] == 2 ? 'tshirt' : 
                                ($category['id'] == 3 ? 'book' : 'home')); 
                        ?>"></i>
                    </div>
                    <h4><?php echo htmlspecialchars($category['name']); ?></h4>
                    <p><?php echo htmlspecialchars($category['description']); ?></p>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Featured Products -->
    <div class="container mt-5">
        <h2 class="section-title">Featured Products</h2>
        <div class="row">
            <?php foreach($products as $product): ?>
            <div class="col-lg-3 col-md-4 col-sm-6">
                <div class="product-card">
                    <div class="product-image">
                        <img src="images/products/<?php echo $product['primary_image'] ?? 'default.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </div>
                    <div class="product-details">
                        <div class="product-category">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                        </div>
                        <h5 class="product-title">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h5>
                        <div class="product-price">
                            ฿<?php echo number_format($product['price'], 2); ?>
                        </div>
                        <button class="btn-add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>About Us</h5>
                    <p>Your trusted online store for quality products at competitive prices.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="products.php" style="color: white; text-decoration: none;">Products</a></li>
                        <li><a href="categories.php" style="color: white; text-decoration: none;">Categories</a></li>
                        <li><a href="contact.php" style="color: white; text-decoration: none;">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Info</h5>
                    <p><i class="fas fa-envelope"></i> support@estore.com</p>
                    <p><i class="fas fa-phone"></i> +66 123 456 789</p>
                </div>
            </div>
            <hr style="background: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p>&copy; 2024 E-Store. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Notification Area -->
    <div id="notification" class="notification" style="display: none;"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Update cart count on load
        $(document).ready(function() {
            updateCartCount();
        });
        
        function updateCartCount() {
            $.ajax({
                url: 'ajax/get_cart_count.php',
                method: 'GET',
                success: function(response) {
                    $('#cartCount').text(response);
                }
            });
        }
        
        function showNotification(message, type = 'success') {
            const bgColor = type === 'success' ? '#28a745' : '#dc3545';
            const $notification = $('#notification');
            $notification.css({
                'background': bgColor,
                'color': 'white',
                'padding': '15px 25px',
                'border-radius': '5px',
                'box-shadow': '0 5px 15px rgba(0,0,0,0.2)'
            }).html(message).fadeIn();
            
            setTimeout(function() {
                $notification.fadeOut();
            }, 3000);
        }
        
        function addToCart(productId) {
            <?php if (!isLoggedIn()): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            
            $.ajax({
                url: 'ajax/add_to_cart.php',
                method: 'POST',
                data: { product_id: productId, quantity: 1 },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        showNotification('Product added to cart successfully!');
                        updateCartCount();
                    } else {
                        showNotification('Error adding product to cart', 'error');
                    }
                }
            });
        }
    </script>
</body>
</html>