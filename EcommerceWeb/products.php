<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$category_id = isset($_GET['category']) ? $_GET['category'] : null;
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT p.*, c.name as category_name,
          (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.status = 'active'";

$params = [];

if ($category_id) {
    $query .= " AND p.category_id = :category_id";
    $params[':category_id'] = $category_id;
}

if ($search) {
    $query .= " AND (p.name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all categories for filter
$cat_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
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
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .nav-link {
            color: var(--dark) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            position: relative;
            transition: all 0.3s;
        }
        
        .nav-link:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 3px;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            transition: width 0.3s;
            border-radius: 3px;
        }
        
        .nav-link:hover:after,
        .nav-link.active:after {
            width: 80%;
        }
        
        .cart-badge {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -10px;
            font-weight: 600;
        }
        
        /* Products Container */
        .products-wrapper {
            padding: 3rem 0;
        }
        
        /* Sidebar Filters */
        .filter-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 100px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .filter-title {
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filter-title i {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        .category-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .category-list li {
            margin-bottom: 0.8rem;
        }
        
        .category-list a {
            display: block;
            padding: 0.8rem 1.2rem;
            color: var(--secondary);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .category-list a:hover,
        .category-list a.active {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }
        
        .category-list a i {
            width: 25px;
            margin-right: 10px;
        }
        
        .search-info {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        
        .clear-filter {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .clear-filter:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Main Content */
        .main-content {
            background: transparent;
        }
        
        .content-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .content-header h2 {
            font-weight: 700;
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .content-header p {
            color: var(--secondary);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }
        
        /* View Toggle */
        .view-toggle {
            display: inline-flex;
            background: #f1f5f9;
            padding: 0.5rem;
            border-radius: 15px;
            gap: 0.5rem;
        }
        
        .view-toggle .btn {
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            font-weight: 600;
            color: var(--secondary);
            transition: all 0.3s;
        }
        
        .view-toggle .btn:hover {
            background: rgba(79, 70, 229, 0.1);
            color: var(--primary);
        }
        
        .view-toggle .btn.active {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }
        
        /* Product Cards */
        .product-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            height: 100%;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }
        
        .product-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 30px 60px rgba(79, 70, 229, 0.2);
        }
        
        .product-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.9rem;
            z-index: 10;
            box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
        }
        
        .product-image {
            height: 250px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            max-width: 80%;
            max-height: 80%;
            object-fit: contain;
            transition: all 0.5s;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.1);
        }
        
        .product-details {
            padding: 1.5rem;
            background: white;
        }
        
        .product-category {
            color: var(--primary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .product-title {
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .product-title a {
            color: inherit;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .product-title a:hover {
            color: var(--primary);
        }
        
        .product-description {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.6;
        }
        
        .product-price {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .product-price small {
            font-size: 1rem;
            color: var(--secondary);
            font-weight: 400;
        }
        
        .product-stock {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .stock-in {
            background: #dcfce7;
            color: #166534;
        }
        
        .stock-low {
            background: #fed7aa;
            color: #9a3412;
        }
        
        .btn-add-to-cart {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 15px;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
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
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.4);
        }
        
        .btn-add-to-cart i {
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        .btn-add-to-cart:hover i {
            transform: translateX(5px);
        }
        
        /* Table View */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .product-table-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .product-table-name {
            font-weight: 600;
            color: var(--dark);
            text-decoration: none;
            font-size: 1.1rem;
        }
        
        .product-table-name:hover {
            color: var(--primary);
        }
        
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 1rem 0;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            margin-left: 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            border: none;
            color: white !important;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state i {
            font-size: 5rem;
            color: var(--primary-light);
            margin-bottom: 1.5rem;
        }
        
        .empty-state h3 {
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .empty-state p {
            color: var(--secondary);
            margin-bottom: 2rem;
        }
        
        .empty-state .btn {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .empty-state .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.4);
        }
        
        /* Footer */
        .footer {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
        }
        
        .footer h5 {
            font-weight: 700;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        
        .footer h5:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            border-radius: 3px;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer a:hover {
            color: white;
            padding-left: 5px;
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
                        <a class="nav-link active" href="products.php">
                            <i class="fas fa-box"></i> สินค้าทั้งหมด
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

    <!-- Products Section -->
    <div class="products-wrapper">
        <div class="container">
            <div class="row">
                <!-- Sidebar Filters -->
                <div class="col-lg-3">
                    <div class="filter-sidebar">
                        <h5 class="filter-title">
                            <i class="fas fa-filter"></i>
                            หมวดหมู่สินค้า
                        </h5>
                        
                        <ul class="category-list">
                            <li>
                                <a href="products.php" class="<?php echo !$category_id ? 'active' : ''; ?>">
                                    <i class="fas fa-th-large"></i>
                                    สินค้าทั้งหมด
                                </a>
                            </li>
                            <?php foreach($categories as $cat): ?>
                            <li>
                                <a href="products.php?category=<?php echo $cat['id']; ?>" 
                                   class="<?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
                                    <i class="fas fa-<?php 
                                        echo $cat['id'] == 1 ? 'mobile-alt' : 
                                            ($cat['id'] == 2 ? 'tshirt' : 
                                            ($cat['id'] == 3 ? 'book' : 'home')); 
                                    ?>"></i>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <?php if($search): ?>
                        <div class="search-info">
                            <h6 class="fw-bold mb-2">
                                <i class="fas fa-search"></i>
                                ผลการค้นหา
                            </h6>
                            <p class="mb-2">คำค้น: "<?php echo htmlspecialchars($search); ?>"</p>
                            <a href="products.php" class="clear-filter">
                                <i class="fas fa-times-circle"></i>
                                ล้างตัวกรอง
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Products Grid/Table -->
                <div class="col-lg-9">
                    <div class="content-header">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h2>
                                    <i class="fas fa-box-open me-2"></i>
                                    สินค้าทั้งหมด
                                </h2>
                                <p>พบสินค้าทั้งหมด <?php echo count($products); ?> รายการ</p>
                            </div>
                            
                            <!-- View Toggle -->
                            <div class="view-toggle">
                                <button class="btn active" onclick="showGridView()">
                                    <i class="fas fa-th"></i> การ์ด
                                </button>
                                <button class="btn" onclick="showTableView()">
                                    <i class="fas fa-table"></i> ตาราง
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <?php if(empty($products)): ?>
                        <!-- Empty State -->
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h3>ไม่พบสินค้า</h3>
                            <p>ไม่พบสินค้าที่คุณค้นหา ลองค้นหาด้วยคำอื่น</p>
                            <a href="products.php" class="btn">
                                <i class="fas fa-redo me-2"></i>
                                ดูสินค้าทั้งหมด
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Grid View -->
                        <div id="gridView">
                            <div class="row">
                                <?php foreach($products as $product): ?>
                                <div class="col-xl-4 col-md-6">
                                    <div class="product-card">
                                        <?php if($product['stock'] <= 5 && $product['stock'] > 0): ?>
                                            <div class="product-badge">
                                                <i class="fas fa-exclamation-circle"></i>
                                                เหลือน้อย
                                            </div>
                                        <?php elseif($product['stock'] == 0): ?>
                                            <div class="product-badge" style="background: linear-gradient(135deg, var(--secondary) 0%, #475569 100%);">
                                                <i class="fas fa-times-circle"></i>
                                                สินค้าหมด
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="product-image">
                                            <img src="images/products/<?php echo $product['primary_image'] ?? 'default.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>">
                                        </div>
                                        <div class="product-details">
                                            <div class="product-category">
                                                <i class="fas fa-tag me-1"></i>
                                                <?php echo htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่'); ?>
                                            </div>
                                            <h5 class="product-title">
                                                <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </a>
                                            </h5>
                                            <p class="product-description">
                                                <?php echo mb_substr(htmlspecialchars($product['description']), 0, 50) . '...'; ?>
                                            </p>
                                            <div class="product-price">
                                                ฿<?php echo number_format($product['price'], 2); ?>
                                                <small>/ชิ้น</small>
                                            </div>
                                            <div class="mb-3">
                                                <span class="product-stock <?php echo $product['stock'] > 5 ? 'stock-in' : ($product['stock'] > 0 ? 'stock-low' : ''); ?>">
                                                    <i class="fas fa-<?php echo $product['stock'] > 0 ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                                    คงเหลือ: <?php echo $product['stock']; ?> ชิ้น
                                                </span>
                                            </div>
                                            <?php if($product['stock'] > 0): ?>
                                                <button class="btn-add-to-cart" onclick="addToCart(<?php echo $product['id']; ?>)">
                                                    <i class="fas fa-cart-plus"></i>
                                                    เพิ่มลงตะกร้า
                                                </button>
                                            <?php else: ?>
                                                <button class="btn-add-to-cart" style="background: var(--secondary);" disabled>
                                                    <i class="fas fa-ban"></i>
                                                    สินค้าหมด
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Table View -->
                        <div id="tableView" style="display: none;">
                            <div class="table-container">
                                <table id="productsTable" class="table table-striped table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>รูปภาพ</th>
                                            <th>ชื่อสินค้า</th>
                                            <th>หมวดหมู่</th>
                                            <th>ราคา</th>
                                            <th>คงเหลือ</th>
                                            <th>จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($products as $product): ?>
                                        <tr>
                                            <td>
                                                <img src="images/products/<?php echo $product['primary_image'] ?? 'default.jpg'; ?>" 
                                                     class="product-table-image">
                                            </td>
                                            <td>
                                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-table-name">
                                                    <?php echo htmlspecialchars($product['name']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">
                                                    <?php echo htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-primary">฿<?php echo number_format($product['price'], 2); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $product['stock'] > 10 ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger'); ?>">
                                                    <?php echo $product['stock']; ?> ชิ้น
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($product['stock'] > 0): ?>
                                                    <button class="btn btn-sm btn-primary" onclick="addToCart(<?php echo $product['id']; ?>)">
                                                        <i class="fas fa-cart-plus"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-secondary" disabled>
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>เกี่ยวกับเรา</h5>
                    <p>ร้านค้าออนไลน์ที่คุณวางใจ สินค้าคุณภาพ ราคาถูก บริการประทับใจ</p>
                    <div class="mt-3">
                        <a href="#" class="me-3"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-line fa-2x"></i></a>
                        <a href="#" class="me-3"><i class="fab fa-instagram fa-2x"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <h5>เมนู</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="products.php"><i class="fas fa-chevron-right me-2"></i>สินค้าทั้งหมด</a></li>
                        <li class="mb-2"><a href="categories.php"><i class="fas fa-chevron-right me-2"></i>หมวดหมู่สินค้า</a></li>
                        <li class="mb-2"><a href="about.php"><i class="fas fa-chevron-right me-2"></i>เกี่ยวกับเรา</a></li>
                        <li class="mb-2"><a href="contact.php"><i class="fas fa-chevron-right me-2"></i>ติดต่อเรา</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>ติดต่อเรา</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            123 ถนนสุขุมวิท กรุงเทพฯ 10110
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            02-123-4567
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2"></i>
                            support@estore.com
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-clock me-2"></i>
                            จ-ศ 09:00 - 18:00 น.
                        </li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="background: rgba(255,255,255,0.1);">
            <div class="text-center">
                <p class="mb-0">&copy; 2024 E-Store. สงวนลิขสิทธิ์</p>
            </div>
        </div>
    </footer>

    <!-- Notification -->
    <div id="notification" class="notification" style="display: none;"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            updateCartCount();
            
            $('#productsTable').DataTable({
                pageLength: 10,
                responsive: true,
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json",
                    search: "ค้นหา:",
                    lengthMenu: "แสดง _MENU_ รายการ",
                    info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                    paginate: {
                        first: "หน้าแรก",
                        last: "หน้าสุดท้าย",
                        next: "ถัดไป",
                        previous: "ก่อนหน้า"
                    }
                }
            });
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
        
        function showGridView() {
            $('#gridView').show();
            $('#tableView').hide();
            $('.view-toggle .btn').removeClass('active');
            $('.view-toggle .btn:first').addClass('active');
        }
        
        function showTableView() {
            $('#gridView').hide();
            $('#tableView').show();
            $('.view-toggle .btn').removeClass('active');
            $('.view-toggle .btn:last').addClass('active');
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
                        showNotification('เพิ่มสินค้าลงตะกร้าเรียบร้อย');
                        updateCartCount();
                    } else {
                        showNotification('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง', 'error');
                    }
                }
            });
        }
    </script>
</body>
</html>