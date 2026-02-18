<?php
require_once '../config/database.php';

if(!isAdmin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle product addition with image upload
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    // เริ่ม transaction
    $db->beginTransaction();
    
    try {
        // บันทึกสินค้าก่อน
        $query = "INSERT INTO products (category_id, name, description, price, stock) 
                  VALUES (:category_id, :name, :description, :price, :stock)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->execute();
        
        $product_id = $db->lastInsertId();
        
        // จัดการอัปโหลดรูปหลัก
        $primary_image = null;
        if(isset($_FILES['primary_image']) && $_FILES['primary_image']['error'] == 0) {
            $primary_image = uploadImage($_FILES['primary_image'], $product_id, true);
            if($primary_image) {
                // อัปเดต image_url ในตาราง products
                $update_query = "UPDATE products SET image_url = :image_url WHERE id = :id";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':image_url', $primary_image);
                $update_stmt->bindParam(':id', $product_id);
                $update_stmt->execute();
            }
        }
        
        // จัดการอัปโหลดรูปเพิ่มเติม
        if(isset($_FILES['additional_images'])) {
            $files = $_FILES['additional_images'];
            for($i = 0; $i < count($files['name']); $i++) {
                if($files['error'][$i] == 0) {
                    $file = [
                        'name' => $files['name'][$i],
                        'type' => $files['type'][$i],
                        'tmp_name' => $files['tmp_name'][$i],
                        'error' => $files['error'][$i],
                        'size' => $files['size'][$i]
                    ];
                    $is_primary = ($i == 0 && !$primary_image) ? true : false;
                    $image_name = uploadImage($file, $product_id, $is_primary);
                }
            }
        }
        
        $db->commit();
        $_SESSION['success'] = "เพิ่มสินค้าสำเร็จ";
        
    } catch(Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    
    redirect('products.php');
}

// Handle product update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $id = $_POST['id'];
    $category_id = $_POST['category_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];
    
    $db->beginTransaction();
    
    try {
        $query = "UPDATE products SET category_id = :category_id, name = :name, 
                  description = :description, price = :price, stock = :stock, status = :status 
                  WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':status', $status);
        $stmt->execute();
        
        // จัดการอัปโหลดรูปใหม่ (ถ้ามี)
        if(isset($_FILES['new_primary_image']) && $_FILES['new_primary_image']['error'] == 0) {
            // ลบรูปเก่า (ถ้ามี)
            $old_image_query = "SELECT image_url FROM products WHERE id = :id";
            $old_image_stmt = $db->prepare($old_image_query);
            $old_image_stmt->bindParam(':id', $id);
            $old_image_stmt->execute();
            $old_image = $old_image_stmt->fetch(PDO::FETCH_ASSOC);
            
            if($old_image && $old_image['image_url'] && file_exists("../images/products/" . $old_image['image_url'])) {
                unlink("../images/products/" . $old_image['image_url']);
            }
            
            // อัปโหลดรูปใหม่
            $new_image = uploadImage($_FILES['new_primary_image'], $id, true);
            if($new_image) {
                $update_query = "UPDATE products SET image_url = :image_url WHERE id = :id";
                $update_stmt = $db->prepare($update_query);
                $update_stmt->bindParam(':image_url', $new_image);
                $update_stmt->bindParam(':id', $id);
                $update_stmt->execute();
            }
        }
        
        $db->commit();
        $_SESSION['success'] = "อัปเดตสินค้าสำเร็จ";
        
    } catch(Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
    
    redirect('products.php');
}

// Handle product deletion
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    $db->beginTransaction();
    
    try {
        // ลบรูปใน product_images ก่อน
        $img_query = "SELECT image_url FROM product_images WHERE product_id = :product_id";
        $img_stmt = $db->prepare($img_query);
        $img_stmt->bindParam(':product_id', $id);
        $img_stmt->execute();
        $images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($images as $image) {
            $file_path = "../images/products/" . $image['image_url'];
            if(file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // ลบรูปหลัก
        $prod_query = "SELECT image_url FROM products WHERE id = :id";
        $prod_stmt = $db->prepare($prod_query);
        $prod_stmt->bindParam(':id', $id);
        $prod_stmt->execute();
        $product = $prod_stmt->fetch(PDO::FETCH_ASSOC);
        
        if($product && $product['image_url'] && file_exists("../images/products/" . $product['image_url'])) {
            unlink("../images/products/" . $product['image_url']);
        }
        
        // ลบสินค้า
        $query = "DELETE FROM products WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        $db->commit();
        $_SESSION['success'] = "ลบสินค้าสำเร็จ";
        
    } catch(Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = "ไม่สามารถลบสินค้าได้";
    }
    
    redirect('products.php');
}

// ฟังก์ชันอัปโหลดรูป
function uploadImage($file, $product_id, $is_primary = false) {
    $target_dir = "../images/products/";
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // ตรวจสอบนามสกุลไฟล์
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if(!in_array($file_extension, $allowed_extensions)) {
        throw new Exception("นามสกุลไฟล์ไม่ถูกต้อง อนุญาตเฉพาะ: " . implode(', ', $allowed_extensions));
    }
    
    // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
    if($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("ไฟล์มีขนาดใหญ่เกินไป (ไม่เกิน 5MB)");
    }
    
    // สร้างชื่อไฟล์ใหม่
    $new_filename = $product_id . '_' . time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // ย้ายไฟล์
    if(move_uploaded_file($file['tmp_name'], $target_file)) {
        // บันทึกในตาราง product_images
        global $db;
        $img_query = "INSERT INTO product_images (product_id, image_url, is_primary) 
                      VALUES (:product_id, :image_url, :is_primary)";
        $img_stmt = $db->prepare($img_query);
        $img_stmt->bindParam(':product_id', $product_id);
        $img_stmt->bindParam(':image_url', $new_filename);
        $img_stmt->bindParam(':is_primary', $is_primary, PDO::PARAM_BOOL);
        $img_stmt->execute();
        
        return $new_filename;
    }
    
    return false;
}

// Get all products with images
$query = "SELECT p.*, c.name as category_name,
          (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image,
          (SELECT COUNT(*) FROM product_images WHERE product_id = p.id) as image_count
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          ORDER BY p.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for dropdown
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
    <title>จัดการสินค้า - Admin E-Store</title>
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
        
        /* Admin Sidebar */
        .admin-sidebar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            min-height: 100vh;
            color: white;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            position: sticky;
            top: 0;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header i {
            font-size: 3rem;
            color: var(--primary-light);
            margin-bottom: 1rem;
        }
        
        .sidebar-header h5 {
            font-weight: 700;
            font-size: 1.2rem;
            margin: 0;
            color: white;
        }
        
        .sidebar-menu {
            padding: 1.5rem 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            gap: 1rem;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a i {
            width: 25px;
            font-size: 1.2rem;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--primary);
        }
        
        .sidebar-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 1rem 1.5rem;
        }
        
        /* Main Content */
        .admin-content {
            padding: 2rem;
        }
        
        .content-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0;
        }
        
        .btn-add {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        /* Table */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #e2e8f0;
        }
        
        .product-image-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--secondary);
            font-size: 1.5rem;
        }
        
        .stock-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .stock-high {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-low {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .btn-action {
            width: 35px;
            height: 35px;
            border-radius: 10px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            margin: 0 2px;
        }
        
        .btn-view {
            background: var(--info);
            color: white;
        }
        
        .btn-view:hover {
            background: #2563eb;
            transform: scale(1.1);
        }
        
        .btn-edit {
            background: var(--warning);
            color: white;
        }
        
        .btn-edit:hover {
            background: #d97706;
            transform: scale(1.1);
        }
        
        .btn-delete {
            background: var(--danger);
            color: white;
        }
        
        .btn-delete:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        
        /* Modal */
        .modal-content {
            border-radius: 20px;
            border: none;
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .modal-footer {
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.6rem 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        
        .image-preview {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            border: 2px dashed #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 0.5rem;
            overflow: hidden;
            background: #f8fafc;
        }
        
        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .image-preview i {
            font-size: 2rem;
            color: var(--secondary);
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        .btn-cancel {
            background: #e2e8f0;
            color: var(--secondary);
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-cancel:hover {
            background: #cbd5e1;
            color: var(--dark);
        }
        
        /* Alert */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* DataTables customization */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 1rem 0;
        }
        
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.4rem 1rem;
            margin-left: 0.5rem;
        }
        
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 8px;
            padding: 0.4rem 0.8rem;
            margin: 0 2px;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            border: none;
            color: white !important;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 p-0">
                <div class="admin-sidebar">
                    <div class="sidebar-header">
                        <i class="fas fa-store"></i>
                        <h5>Admin Panel</h5>
                        <p>เวอร์ชัน 1.0.0</p>
                    </div>
                    
                    <div class="sidebar-menu">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>แดชบอร์ด</span>
                        </a>
                        <a href="products.php" class="active">
                            <i class="fas fa-box"></i>
                            <span>จัดการสินค้า</span>
                        </a>
                        <a href="categories.php">
                            <i class="fas fa-list"></i>
                            <span>จัดการหมวดหมู่</span>
                        </a>
                        <a href="orders.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>จัดการคำสั่งซื้อ</span>
                        </a>
                        <a href="customers.php">
                            <i class="fas fa-users"></i>
                            <span>จัดการลูกค้า</span>
                        </a>
                        
                        <div class="sidebar-divider"></div>
                        
                        <a href="../index.php">
                            <i class="fas fa-home"></i>
                            <span>ดูหน้าร้าน</span>
                        </a>
                        <a href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>ออกจากระบบ</span>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-lg-10 col-md-9">
                <div class="admin-content">
                    <!-- Header -->
                    <div class="content-header fade-in">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="page-title">
                                    <i class="fas fa-box me-2"></i>
                                    จัดการสินค้า
                                </h2>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="dashboard.php">แดชบอร์ด</a></li>
                                        <li class="breadcrumb-item active">จัดการสินค้า</li>
                                    </ol>
                                </nav>
                            </div>
                            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addProductModal">
                                <i class="fas fa-plus me-2"></i>
                                เพิ่มสินค้า
                            </button>
                        </div>
                    </div>
                    
                    <!-- Alerts -->
                    <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php 
                                echo $_SESSION['success'];
                                unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Products Table -->
                    <div class="table-container fade-in">
                        <table id="productsTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>รูป</th>
                                    <th>ชื่อสินค้า</th>
                                    <th>หมวดหมู่</th>
                                    <th>ราคา</th>
                                    <th>คงเหลือ</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if($product['primary_image']): ?>
                                            <img src="../images/products/<?php echo $product['primary_image']; ?>" 
                                                 class="product-image"
                                                 onerror="this.src='../images/products/default.jpg'">
                                        <?php else: ?>
                                            <div class="product-image-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($product['image_count'] > 1): ?>
                                            <br>
                                            <small class="text-muted">
                                                +<?php echo $product['image_count'] - 1; ?> รูป
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <br>
                                        <small class="text-muted">ID: #<?php echo $product['id']; ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">
                                            ฿<?php echo number_format($product['price'], 2); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php 
                                            $stock_class = 'stock-high';
                                            if($product['stock'] <= 5) $stock_class = 'stock-low';
                                            elseif($product['stock'] <= 20) $stock_class = 'stock-medium';
                                        ?>
                                        <span class="stock-badge <?php echo $stock_class; ?>">
                                            <?php echo $product['stock']; ?> ชิ้น
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $product['status']; ?>">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            <?php echo $product['status'] == 'active' ? 'เปิดใช้งาน' : 'ปิดใช้งาน'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../product-detail.php?id=<?php echo $product['id']; ?>" 
                                           class="btn-action btn-view" title="ดูหน้าร้าน" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn-action btn-edit" onclick="editProduct(<?php echo $product['id']; ?>)" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $product['id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้านี้?\\nการลบจะไม่สามารถกู้คืนได้')"
                                           title="ลบ">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        เพิ่มสินค้าใหม่
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-tag text-primary me-2"></i>
                                    ชื่อสินค้า <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-list text-primary me-2"></i>
                                    หมวดหมู่ <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">เลือกหมวดหมู่</option>
                                    <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-align-left text-primary me-2"></i>
                                    รายละเอียดสินค้า
                                </label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign text-primary me-2"></i>
                                    ราคา (บาท) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-cubes text-primary me-2"></i>
                                    จำนวนคงเหลือ <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="stock" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-image text-primary me-2"></i>
                                    รูปหลัก
                                </label>
                                <input type="file" class="form-control" name="primary_image" accept="image/*" 
                                       onchange="previewImage(this, 'mainPreview')">
                                <div class="image-preview" id="mainPreview">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <small class="text-muted">ขนาดไม่เกิน 5MB (jpg, png, gif)</small>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-images text-primary me-2"></i>
                                    รูปเพิ่มเติม (เลือกได้หลายรูป)
                                </label>
                                <input type="file" class="form-control" name="additional_images[]" multiple accept="image/*">
                                <small class="text-muted">สามารถเลือกได้หลายรูป กด Ctrl ค้างไว้เพื่อเลือกหลายไฟล์</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            ยกเลิก
                        </button>
                        <button type="submit" name="add_product" class="btn-save">
                            <i class="fas fa-save me-2"></i>
                            บันทึกสินค้า
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#productsTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                },
                order: [[0, 'desc']],
                pageLength: 25
            });
        });
        
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        function editProduct(id) {
            window.location.href = 'edit-product.php?id=' + id;
        }
    </script>
</body>
</html>