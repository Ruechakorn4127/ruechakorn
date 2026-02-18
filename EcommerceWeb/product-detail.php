<?php
require_once 'config/database.php';

if(!isset($_GET['id'])) {
    redirect('products.php');
}

$database = new Database();
$db = $database->getConnection();

// Get product details
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_GET['id']);
$stmt->execute();
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$product) {
    redirect('products.php');
}

// Get product images
$img_query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_primary DESC";
$img_stmt = $db->prepare($img_query);
$img_stmt->bindParam(':product_id', $_GET['id']);
$img_stmt->execute();
$images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get related products
$rel_query = "SELECT p.*, 
              (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
              FROM products p 
              WHERE p.category_id = :category_id AND p.id != :product_id AND p.status = 'active'
              LIMIT 4";
$rel_stmt = $db->prepare($rel_query);
$rel_stmt->bindParam(':category_id', $product['category_id']);
$rel_stmt->bindParam(':product_id', $product['id']);
$rel_stmt->execute();
$related_products = $rel_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Prompt', sans-serif;
        }
        
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #818cf8;
            --secondary: #64748b;
            --success: #22c55e;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #0f172a;
            --light: #f8fafc;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            padding: 1rem 0;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* Breadcrumb */
        .breadcrumb-wrapper {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1rem 2rem;
            margin: 2rem 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .breadcrumb {
            margin-bottom: 0;
            background: transparent;
        }
        
        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .breadcrumb-item.active {
            color: var(--secondary);
        }
        
        /* Product Container */
        .product-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Gallery */
        .gallery-container {
            position: sticky;
            top: 100px;
        }
        
        .main-image-container {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
            cursor: zoom-in;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .main-image {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
            transition: transform 0.3s;
        }
        
        .main-image-container:hover .main-image {
            transform: scale(1.1);
        }
        
        .zoom-lens {
            position: absolute;
            border: 3px solid var(--primary);
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: none;
            pointer-events: none;
        }
        
        .thumbnail-container {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            padding: 0.5rem;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 12px;
            cursor: pointer;
            object-fit: cover;
            border: 3px solid transparent;
            transition: all 0.3s;
            background: #f8fafc;
            padding: 0.5rem;
        }
        
        .thumbnail:hover,
        .thumbnail.active {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }
        
        .badge-stock {
            position: absolute;
            top: 1rem;
            left: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            z-index: 10;
        }
        
        .badge-instock {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
        }
        
        .badge-lowstock {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .badge-outstock {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        /* Product Info */
        .product-category {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 25px;
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .product-category i {
            margin-right: 0.5rem;
        }
        
        .product-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .product-rating {
            margin-bottom: 1.5rem;
        }
        
        .stars {
            color: #fbbf24;
            font-size: 1.2rem;
        }
        
        .rating-count {
            color: var(--secondary);
            margin-left: 0.5rem;
        }
        
        .product-price {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
        }
        
        .product-price small {
            font-size: 1rem;
            color: var(--secondary);
            font-weight: 400;
        }
        
        .stock-info {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .stock-status {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .stock-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .stock-icon.instock {
            background: #22c55e;
            color: white;
        }
        
        .stock-icon.lowstock {
            background: #f59e0b;
            color: white;
        }
        
        .stock-text {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .stock-text.instock {
            color: #16a34a;
        }
        
        .stock-text.lowstock {
            color: #d97706;
        }
        
        .stock-bar {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .stock-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, #764ba2 100%);
            border-radius: 4px;
            transition: width 0.3s;
        }
        
        .quantity-selector {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 1rem;
            margin: 1.5rem 0;
        }
        
        .quantity-label {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .quantity-btn {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            border: none;
            background: white;
            color: var(--primary);
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .quantity-btn:hover:not(:disabled) {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }
        
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-input {
            width: 80px;
            height: 50px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            text-align: center;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .quantity-input:focus {
            border-color: var(--primary);
            outline: none;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin: 2rem 0;
        }
        
        .btn-add-to-cart {
            flex: 2;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1.2rem;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .btn-add-to-cart:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .btn-add-to-cart:hover:before {
            width: 300px;
            height: 300px;
        }
        
        .btn-add-to-cart:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.4);
        }
        
        .btn-buy-now {
            flex: 1;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            border: none;
            padding: 1.2rem;
            border-radius: 15px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-buy-now:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.4);
        }
        
        .btn-wishlist {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            border: 2px solid #e2e8f0;
            background: white;
            color: var(--danger);
            font-size: 1.3rem;
            transition: all 0.3s;
        }
        
        .btn-wishlist:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
            transform: scale(1.1);
        }
        
        /* Product Tabs */
        .product-tabs {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin: 3rem 0;
        }
        
        .nav-tabs {
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: var(--secondary);
            font-weight: 600;
            padding: 1rem 2rem;
            position: relative;
        }
        
        .nav-tabs .nav-link:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: transparent;
        }
        
        .nav-tabs .nav-link.active:after {
            transform: scaleX(1);
        }
        
        .tab-content {
            padding: 1rem;
        }
        
        /* Related Products */
        .related-products {
            margin: 4rem 0;
        }
        
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 2rem;
            position: relative;
            padding-bottom: 1rem;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            border-radius: 2px;
        }
        
        .related-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        
        .related-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.2);
        }
        
        .related-image {
            height: 200px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        
        .related-image img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
        }
        
        .related-info {
            padding: 1.5rem;
            text-align: center;
        }
        
        .related-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .related-price {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.2rem;
        }
        
        /* Footer */
        .footer {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
        }
        
        /* Notification */
        .notification {
            position: fixed;
            top: 100px;
            right: 30px;
            z-index: 9999;
            background: white;
            border-radius: 15px;
            padding: 1rem 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border-left: 5px solid var(--success);
            animation: slideIn 0.5s ease;
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
        
        /* Zoom effect */
        .zoom-container {
            position: relative;
            overflow: hidden;
        }
        
        .zoom-result {
            position: absolute;
            top: 0;
            left: 100%;
            width: 400px;
            height: 400px;
            margin-left: 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            display: none;
            overflow: hidden;
            z-index: 1000;
        }
        
        .zoom-result img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
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
                            <i class="fas fa-home"></i> หน้าแรก
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-box"></i> สินค้า
                        </a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item position-relative">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i> ตะกร้า
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
                                    <i class="fas fa-cog"></i> จัดการ
                                </a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt"></i> เข้าสู่ระบบ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">
                                <i class="fas fa-user-plus"></i> สมัครสมาชิก
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-wrapper">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
                    <li class="breadcrumb-item"><a href="products.php"><i class="fas fa-box"></i> สินค้า</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
                </ol>
            </nav>
        </div>
        
        <!-- Product Detail -->
        <div class="product-container">
            <div class="row g-5">
                <!-- Product Images -->
                <div class="col-lg-6">
                    <div class="gallery-container">
                        <div class="main-image-container" id="mainImageContainer">
                            <?php 
                            $stockClass = '';
                            $stockText = '';
                            if($product['stock'] > 10) {
                                $stockClass = 'badge-instock';
                                $stockText = 'ในสต็อก';
                            } elseif($product['stock'] > 0) {
                                $stockClass = 'badge-lowstock';
                                $stockText = 'เหลือน้อย';
                            } else {
                                $stockClass = 'badge-outstock';
                                $stockText = 'สินค้าหมด';
                            }
                            ?>
                            <span class="badge-stock <?php echo $stockClass; ?>">
                                <i class="fas fa-<?php echo $product['stock'] > 0 ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                <?php echo $stockText; ?>
                            </span>
                            <img src="images/products/<?php echo $images[0]['image_url'] ?? 'default.jpg'; ?>" 
                                 class="main-image" id="mainImage" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        </div>
                        
                        <div class="thumbnail-container">
                            <?php if(empty($images)): ?>
                                <img src="images/products/default.jpg" class="thumbnail active">
                            <?php else: ?>
                                <?php foreach($images as $index => $image): ?>
                                    <img src="images/products/<?php echo $image['image_url']; ?>" 
                                         class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>"
                                         onclick="changeMainImage(this.src, this)">
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="col-lg-6">
                    <span class="product-category">
                        <i class="fas fa-tag"></i>
                        <?php echo htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่'); ?>
                    </span>
                    
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <div class="product-rating">
                        <span class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </span>
                        <span class="rating-count">(124 รีวิว)</span>
                    </div>
                    
                    <div class="product-price">
                        ฿<?php echo number_format($product['price'], 2); ?>
                        <small>รวมภาษี</small>
                    </div>
                    
                    <div class="stock-info">
                        <div class="stock-status">
                            <div class="stock-icon <?php echo $product['stock'] > 10 ? 'instock' : ($product['stock'] > 0 ? 'lowstock' : ''); ?>">
                                <i class="fas fa-<?php echo $product['stock'] > 0 ? 'check' : 'times'; ?>"></i>
                            </div>
                            <div>
                                <div class="stock-text <?php echo $product['stock'] > 10 ? 'instock' : ($product['stock'] > 0 ? 'lowstock' : ''); ?>">
                                    <?php echo $product['stock'] > 10 ? 'สินค้าพร้อมส่ง' : ($product['stock'] > 0 ? 'สินค้าใกล้หมด' : 'สินค้าหมดชั่วคราว'); ?>
                                </div>
                                <small class="text-muted">
                                    คงเหลือ <?php echo $product['stock']; ?> ชิ้น
                                </small>
                            </div>
                        </div>
                        <div class="stock-bar">
                            <div class="stock-bar-fill" style="width: <?php echo min(($product['stock'] / 100) * 100, 100); ?>%"></div>
                        </div>
                    </div>
                    
                    <?php if($product['stock'] > 0): ?>
                    <div class="quantity-selector">
                        <div class="quantity-label">
                            <i class="fas fa-sort-amount-up me-2"></i>
                            เลือกจำนวนที่ต้องการ
                        </div>
                        <div class="quantity-control">
                            <button class="quantity-btn" onclick="decrementQuantity()" id="decrementBtn">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantity" class="quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>" readonly>
                            <button class="quantity-btn" onclick="incrementQuantity()" id="incrementBtn">
                                <i class="fas fa-plus"></i>
                            </button>
                            <span class="ms-3 text-muted">
                                คงเหลือ <?php echo $product['stock']; ?> ชิ้น
                            </span>
                        </div>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn-add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                            <i class="fas fa-cart-plus"></i>
                            เพิ่มลงตะกร้า
                        </button>
                        <button class="btn-buy-now" onclick="buyNow(<?php echo $product['id']; ?>)">
                            <i class="fas fa-bolt"></i>
                            ซื้อทันที
                        </button>
                        <button class="btn-wishlist" onclick="addToWishlist(<?php echo $product['id']; ?>)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Product Meta -->
                    <div class="mt-4 pt-4 border-top">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-truck text-primary fs-4 me-3"></i>
                                    <div>
                                        <strong>จัดส่งฟรี</strong><br>
                                        <small class="text-muted">เมื่อสั่งซื้อครบ 500 บาท</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-undo-alt text-primary fs-4 me-3"></i>
                                    <div>
                                        <strong>คืนสินค้าได้</strong><br>
                                        <small class="text-muted">ภายใน 7 วัน</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-shield-alt text-primary fs-4 me-3"></i>
                                    <div>
                                        <strong>การรับประกัน</strong><br>
                                        <small class="text-muted">สินค้ามีประกัน 1 ปี</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-credit-card text-primary fs-4 me-3"></i>
                                    <div>
                                        <strong>ผ่อนชำระได้</strong><br>
                                        <small class="text-muted">0% นานสูงสุด 10 เดือน</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Tabs -->
            <div class="product-tabs mt-5">
                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
                            <i class="fas fa-info-circle me-2"></i>รายละเอียดสินค้า
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button" role="tab">
                            <i class="fas fa-list me-2"></i>ข้อมูลจำเพาะ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">
                            <i class="fas fa-star me-2"></i>รีวิว (124)
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="productTabsContent">
                    <div class="tab-pane fade show active" id="description" role="tabpanel">
                        <div class="product-description">
                            <h5 class="mb-3">รายละเอียดสินค้า</h5>
                            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>                            
                        </div>
                    </div>
                    
                    <div class="tab-pane fade" id="specs" role="tabpanel">
                        <h5 class="mb-3">ข้อมูลจำเพาะ</h5>
                        <table class="table table-striped">
                            <tr>
                                <th style="width: 200px;">แบรนด์</th>
                                <td>E-Store</td>
                            </tr>
                            <tr>
                                <th>รุ่น</th>
                                <td><?php echo htmlspecialchars($product['name']); ?></td>
                            </tr>
                            <tr>
                                <th>หมวดหมู่</th>
                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่'); ?></td>
                            </tr>
                            <tr>
                                <th>สถานะ</th>
                                <td><?php echo $product['stock'] > 0 ? 'พร้อมส่ง' : 'สินค้าหมด'; ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="text-center py-4">
                            <i class="fas fa-star text-warning fa-3x mb-3"></i>
                            <h5>คะแนนสินค้า 4.5/5</h5>
                            <p class="text-muted">จาก 124 รีวิว</p>
                            <button class="btn btn-outline-primary mt-3">
                                <i class="fas fa-pen me-2"></i>เขียนรีวิว
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <?php if(!empty($related_products)): ?>
        <div class="related-products">
            <h3 class="section-title">สินค้าใกล้เคียง</h3>
            <div class="row g-4">
                <?php foreach($related_products as $related): ?>
                <div class="col-md-3">
                    <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="related-card">
                        <div class="related-image">
                            <img src="images/products/<?php echo $related['primary_image'] ?? 'default.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>">
                        </div>
                        <div class="related-info">
                            <div class="related-name"><?php echo htmlspecialchars($related['name']); ?></div>
                            <div class="related-price">฿<?php echo number_format($related['price'], 2); ?></div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>เกี่ยวกับเรา</h5>
                    <p>ร้านค้าออนไลน์ที่คุณวางใจ สินค้าคุณภาพ ราคาถูก บริการประทับใจ</p>
                </div>
                <div class="col-md-4">
                    <h5>เมนู</h5>
                    <ul class="list-unstyled">
                        <li><a href="products.php">สินค้าทั้งหมด</a></li>
                        <li><a href="categories.php">หมวดหมู่สินค้า</a></li>
                        <li><a href="about.php">เกี่ยวกับเรา</a></li>
                        <li><a href="contact.php">ติดต่อเรา</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>ติดต่อเรา</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-phone me-2"></i> 02-123-4567</li>
                        <li><i class="fas fa-envelope me-2"></i> support@estore.com</li>
                        <li><i class="fas fa-clock me-2"></i> จ-ศ 09:00 - 18:00 น.</li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <!-- Notification -->
    <div id="notification" class="notification" style="display: none;"></div>

    <!-- Zoom Result -->
    <div class="zoom-result" id="zoomResult">
        <img src="" id="zoomImage" alt="Zoom">
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentQuantity = 1;
        const maxStock = <?php echo $product['stock']; ?>;
        
        function changeMainImage(src, element) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.classList.remove('active');
            });
            element.classList.add('active');
        }
        
        function incrementQuantity() {
            if (currentQuantity < maxStock) {
                currentQuantity++;
                updateQuantityDisplay();
            }
        }
        
        function decrementQuantity() {
            if (currentQuantity > 1) {
                currentQuantity--;
                updateQuantityDisplay();
            }
        }
        
        function updateQuantityDisplay() {
            document.getElementById('quantity').value = currentQuantity;
            document.getElementById('decrementBtn').disabled = currentQuantity <= 1;
            document.getElementById('incrementBtn').disabled = currentQuantity >= maxStock;
        }
        
        function showNotification(message, type = 'success') {
            const bgColor = type === 'success' ? '#22c55e' : '#ef4444';
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
            
            const $notification = $('#notification');
            $notification.css({
                'background': 'white',
                'color': '#0f172a',
                'border-left': '5px solid ' + bgColor
            }).html(`
                <div class="d-flex align-items-center">
                    <i class="fas fa-${icon}" style="color: ${bgColor}; font-size: 1.5rem; margin-right: 1rem;"></i>
                    <div>
                        <strong>${message}</strong>
                    </div>
                </div>
            `).fadeIn();
            
            setTimeout(function() {
                $notification.fadeOut();
            }, 3000);
        }
        
        function addToCart(productId) {
            <?php if (!isLoggedIn()): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            
            const quantity = document.getElementById('quantity').value;
            
            $.ajax({
                url: 'ajax/add_to_cart.php',
                method: 'POST',
                data: { product_id: productId, quantity: quantity },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        showNotification('เพิ่มสินค้าลงตะกร้าเรียบร้อย');
                        updateCartCount();
                    } else {
                        showNotification('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง', 'error');
                    }
                }
            });
        }
        
        function buyNow(productId) {
            <?php if (!isLoggedIn()): ?>
                window.location.href = 'login.php';
                return;
            <?php endif; ?>
            
            const quantity = document.getElementById('quantity').value;
            
            $.ajax({
                url: 'ajax/add_to_cart.php',
                method: 'POST',
                data: { product_id: productId, quantity: quantity },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        window.location.href = 'checkout.php';
                    } else {
                        showNotification('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง', 'error');
                    }
                }
            });
        }
        
        function addToWishlist(productId) {
            showNotification('เพิ่มสินค้าในรายการโปรดแล้ว');
        }
        
        // Zoom functionality
        const container = document.getElementById('mainImageContainer');
        const mainImage = document.getElementById('mainImage');
        const zoomResult = document.getElementById('zoomResult');
        const zoomImage = document.getElementById('zoomImage');
        
        container.addEventListener('mousemove', function(e) {
            const rect = container.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const xPercent = (x / rect.width) * 100;
            const yPercent = (y / rect.height) * 100;
            
            zoomImage.style.transform = `translate(-${xPercent}%, -${yPercent}%) scale(2)`;
        });
        
        container.addEventListener('mouseenter', function() {
            zoomResult.style.display = 'block';
            zoomImage.src = mainImage.src;
        });
        
        container.addEventListener('mouseleave', function() {
            zoomResult.style.display = 'none';
        });
    </script>
</body>
</html>