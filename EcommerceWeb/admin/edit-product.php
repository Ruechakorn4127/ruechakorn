<?php
require_once '../config/database.php';

if(!isAdmin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

if(!isset($_GET['id'])) {
    redirect('products.php');
}

$product_id = $_GET['id'];

// Get product details
$query = "SELECT p.*, c.name as category_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          WHERE p.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $product_id);
$stmt->execute();

if($stmt->rowCount() == 0) {
    redirect('products.php');
}

$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Get product images
$img_query = "SELECT * FROM product_images WHERE product_id = :product_id ORDER BY is_primary DESC, id ASC";
$img_stmt = $db->prepare($img_query);
$img_stmt->bindParam(':product_id', $product_id);
$img_stmt->execute();
$product_images = $img_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for dropdown
$cat_query = "SELECT * FROM categories WHERE status = 'active' ORDER BY name";
$cat_stmt = $db->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle product update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Update product details
    if(isset($_POST['update_product'])) {
        $category_id = $_POST['category_id'];
        $name = $_POST['name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $status = $_POST['status'];
        
        $db->beginTransaction();
        
        try {
            $update_query = "UPDATE products SET 
                            category_id = :category_id, 
                            name = :name, 
                            description = :description, 
                            price = :price, 
                            stock = :stock, 
                            status = :status 
                            WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $product_id);
            $update_stmt->bindParam(':category_id', $category_id);
            $update_stmt->bindParam(':name', $name);
            $update_stmt->bindParam(':description', $description);
            $update_stmt->bindParam(':price', $price);
            $update_stmt->bindParam(':stock', $stock);
            $update_stmt->bindParam(':status', $status);
            $update_stmt->execute();
            
            // Handle new primary image upload
            if(isset($_FILES['new_primary_image']) && $_FILES['new_primary_image']['error'] == 0) {
                $new_image = uploadImage($_FILES['new_primary_image'], $product_id, true);
                if($new_image) {
                    // อัปเดต image_url ใน products
                    $update_prod_query = "UPDATE products SET image_url = :image_url WHERE id = :id";
                    $update_prod_stmt = $db->prepare($update_prod_query);
                    $update_prod_stmt->bindParam(':image_url', $new_image);
                    $update_prod_stmt->bindParam(':id', $product_id);
                    $update_prod_stmt->execute();
                }
            }
            
            // Handle additional images upload
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
                        uploadImage($file, $product_id, false);
                    }
                }
            }
            
            $db->commit();
            $_SESSION['success'] = "อัปเดตสินค้าสำเร็จ";
            redirect("edit-product.php?id=$product_id");
            
        } catch(Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
        }
    }
    
    // Delete image
    if(isset($_POST['delete_image'])) {
        $image_id = $_POST['image_id'];
        $image_url = $_POST['image_url'];
        
        // ลบไฟล์
        $file_path = "../images/products/" . $image_url;
        if(file_exists($file_path)) {
            unlink($file_path);
        }
        
        // ลบจากฐานข้อมูล
        $delete_query = "DELETE FROM product_images WHERE id = :id";
        $delete_stmt = $db->prepare($delete_query);
        $delete_stmt->bindParam(':id', $image_id);
        $delete_stmt->execute();
        
        // ถ้าเป็นรูปหลัก ให้ลบ image_url ใน products ด้วย
        if($_POST['is_primary'] == 1) {
            $update_query = "UPDATE products SET image_url = NULL WHERE id = :id";
            $update_stmt = $db->prepare($update_query);
            $update_stmt->bindParam(':id', $product_id);
            $update_stmt->execute();
        }
        
        $_SESSION['success'] = "ลบรูปภาพสำเร็จ";
        redirect("edit-product.php?id=$product_id");
    }
    
    // Set as primary image
    if(isset($_POST['set_primary'])) {
        $image_id = $_POST['image_id'];
        $image_url = $_POST['image_url'];
        
        // รีเซ็ต is_primary ทั้งหมด
        $reset_query = "UPDATE product_images SET is_primary = 0 WHERE product_id = :product_id";
        $reset_stmt = $db->prepare($reset_query);
        $reset_stmt->bindParam(':product_id', $product_id);
        $reset_stmt->execute();
        
        // ตั้งค่ารูปใหม่เป็น primary
        $set_query = "UPDATE product_images SET is_primary = 1 WHERE id = :id";
        $set_stmt = $db->prepare($set_query);
        $set_stmt->bindParam(':id', $image_id);
        $set_stmt->execute();
        
        // อัปเดต image_url ใน products
        $update_query = "UPDATE products SET image_url = :image_url WHERE id = :id";
        $update_stmt = $db->prepare($update_query);
        $update_stmt->bindParam(':image_url', $image_url);
        $update_stmt->bindParam(':id', $product_id);
        $update_stmt->execute();
        
        $_SESSION['success'] = "ตั้งเป็นรูปหลักเรียบร้อย";
        redirect("edit-product.php?id=$product_id");
    }
}

// ฟังก์ชันอัปโหลดรูป
function uploadImage($file, $product_id, $is_primary = false) {
    global $db;
    
    $target_dir = "../images/products/";
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // ตรวจสอบนามสกุลไฟล์
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if(!in_array($file_extension, $allowed_extensions)) {
        throw new Exception("นามสกุลไฟล์ไม่ถูกต้อง");
    }
    
    // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
    if($file['size'] > 5 * 1024 * 1024) {
        throw new Exception("ไฟล์มีขนาดใหญ่เกินไป");
    }
    
    // สร้างชื่อไฟล์ใหม่
    $new_filename = $product_id . '_' . time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // ย้ายไฟล์
    if(move_uploaded_file($file['tmp_name'], $target_file)) {
        // บันทึกในตาราง product_images
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขสินค้า #<?php echo $product['id']; ?> - Admin</title>
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
            padding: 2rem 0;
        }
        
        .container {
            max-width: 1200px;
        }
        
        /* Header */
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .page-title {
            font-weight: 700;
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 0;
        }
        
        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
        }
        
        /* Main Card */
        .main-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .section-title i {
            color: var(--primary);
            font-size: 1.5rem;
        }
        
        /* Form */
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
        
        .form-control[readonly] {
            background: #f1f5f9;
            cursor: not-allowed;
        }
        
        /* Image Gallery */
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .image-item {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            border: 2px solid #e2e8f0;
            transition: all 0.3s;
            position: relative;
        }
        
        .image-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.1);
            border-color: var(--primary);
        }
        
        .image-item.primary {
            border-color: var(--success);
            background: #f0fdf4;
        }
        
        .image-preview {
            width: 100%;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        
        .image-actions {
            display: flex;
            gap: 0.3rem;
            justify-content: center;
        }
        
        .btn-image {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            border: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            cursor: pointer;
            font-size: 0.9rem;
        }
        
        .btn-primary-image {
            background: var(--success);
            color: white;
        }
        
        .btn-primary-image:hover {
            background: #16a34a;
            transform: scale(1.1);
        }
        
        .btn-delete-image {
            background: var(--danger);
            color: white;
        }
        
        .btn-delete-image:hover {
            background: #dc2626;
            transform: scale(1.1);
        }
        
        .primary-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: var(--success);
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
        }
        
        /* Upload Area */
        .upload-area {
            border: 2px dashed #e2e8f0;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            background: #f8fafc;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .upload-area:hover {
            border-color: var(--primary);
            background: #f1f5f9;
        }
        
        .upload-area i {
            font-size: 3rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        
        .upload-area.highlight {
            border-color: var(--primary);
            background: #e0e7ff;
        }
        
        /* Buttons */
        .btn-save {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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
            padding: 1rem 2rem;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-cancel:hover {
            background: #cbd5e1;
            color: var(--dark);
        }
        
        .btn-view {
            background: white;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-view:hover {
            background: var(--primary);
            color: white;
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
        
        /* Info Box */
        .info-box {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        
        .info-label {
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--dark);
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
    <div class="container">
        <!-- Header -->
        <div class="page-header fade-in">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">
                        <i class="fas fa-edit me-2"></i>
                        แก้ไขสินค้า #<?php echo $product['id']; ?>
                    </h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">แดชบอร์ด</a></li>
                            <li class="breadcrumb-item"><a href="products.php">จัดการสินค้า</a></li>
                            <li class="breadcrumb-item active">แก้ไขสินค้า</li>
                        </ol>
                    </nav>
                </div>
                <a href="products.php" class="btn-cancel">
                    <i class="fas fa-arrow-left me-2"></i>
                    กลับ
                </a>
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
        
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="main-card fade-in">
                        <h5 class="section-title">
                            <i class="fas fa-info-circle"></i>
                            ข้อมูลพื้นฐาน
                        </h5>
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-tag text-primary me-2"></i>
                                    ชื่อสินค้า <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-list text-primary me-2"></i>
                                    หมวดหมู่ <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">เลือกหมวดหมู่</option>
                                    <?php foreach($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"
                                        <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
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
                                <textarea class="form-control" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-dollar-sign text-primary me-2"></i>
                                    ราคา (บาท) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="price" step="0.01" 
                                       value="<?php echo $product['price']; ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-cubes text-primary me-2"></i>
                                    จำนวนคงเหลือ <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" name="stock" 
                                       value="<?php echo $product['stock']; ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-toggle-on text-primary me-2"></i>
                                    สถานะ
                                </label>
                                <select class="form-select" name="status">
                                    <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>เปิดใช้งาน</option>
                                    <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>ปิดใช้งาน</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upload New Images -->
                    <div class="main-card fade-in">
                        <h5 class="section-title">
                            <i class="fas fa-cloud-upload-alt"></i>
                            อัปโหลดรูปเพิ่มเติม
                        </h5>
                        
                        <div class="upload-area" id="dropArea">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <h6>ลากไฟล์มาวางหรือคลิกเพื่อเลือก</h6>
                            <p class="text-muted small">รองรับไฟล์ JPG, PNG, GIF, WEBP ขนาดไม่เกิน 5MB</p>
                            <input type="file" class="d-none" id="fileInput" name="additional_images[]" multiple accept="image/*">
                        </div>
                        
                        <div class="mt-4">
                            <label class="form-label">
                                <i class="fas fa-image text-primary me-2"></i>
                                อัปโหลดรูปหลักใหม่ (แทนที่รูปเก่า)
                            </label>
                            <input type="file" class="form-control" name="new_primary_image" accept="image/*">
                            <small class="text-muted">เลือกรูปเพื่อแทนที่รูปหลักปัจจุบัน</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Product Images -->
                    <div class="main-card fade-in">
                        <h5 class="section-title">
                            <i class="fas fa-images"></i>
                            รูปภาพสินค้า (<?php echo count($product_images); ?> รูป)
                        </h5>
                        
                        <?php if(empty($product_images)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                <p>ยังไม่มีรูปภาพ</p>
                            </div>
                        <?php else: ?>
                            <div class="image-gallery">
                                <?php foreach($product_images as $image): ?>
                                <div class="image-item <?php echo $image['is_primary'] ? 'primary' : ''; ?>">
                                    <?php if($image['is_primary']): ?>
                                        <div class="primary-badge">
                                            <i class="fas fa-star"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <img src="../images/products/<?php echo $image['image_url']; ?>" 
                                         class="image-preview" alt="Product image">
                                    
                                    <div class="image-actions">
                                        <?php if(!$image['is_primary']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                            <input type="hidden" name="image_url" value="<?php echo $image['image_url']; ?>">
                                            <button type="submit" name="set_primary" class="btn-image btn-primary-image" title="ตั้งเป็นรูปหลัก">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        
                                        <form method="POST" class="d-inline" onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบรูปนี้?');">
                                            <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                            <input type="hidden" name="image_url" value="<?php echo $image['image_url']; ?>">
                                            <input type="hidden" name="is_primary" value="<?php echo $image['is_primary']; ?>">
                                            <button type="submit" name="delete_image" class="btn-image btn-delete-image" title="ลบ">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Product Info -->
                    <div class="main-card fade-in">
                        <h5 class="section-title">
                            <i class="fas fa-chart-bar"></i>
                            ข้อมูลสินค้า
                        </h5>
                        
                        <div class="info-box mb-3">
                            <div class="info-label">รหัสสินค้า</div>
                            <div class="info-value">#<?php echo $product['id']; ?></div>
                        </div>
                        
                        <div class="info-box mb-3">
                            <div class="info-label">วันที่เพิ่ม</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($product['created_at'])); ?></div>
                        </div>
                        
                        <div class="info-box mb-3">
                            <div class="info-label">แก้ไขล่าสุด</div>
                            <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($product['updated_at'])); ?></div>
                        </div>
                        
                        <div class="info-box">
                            <div class="info-label">หมวดหมู่</div>
                            <div class="info-value"><?php echo htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่'); ?></div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="main-card fade-in">
                        <div class="d-grid gap-3">
                            <button type="submit" name="update_product" class="btn-save">
                                <i class="fas fa-save me-2"></i>
                                บันทึกการแก้ไข
                            </button>
                            
                            <a href="../product-detail.php?id=<?php echo $product['id']; ?>" class="btn-view" target="_blank">
                                <i class="fas fa-external-link-alt me-2"></i>
                                ดูหน้าร้าน
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <script>
        // Drag and drop upload
        const dropArea = document.getElementById('dropArea');
        const fileInput = document.getElementById('fileInput');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropArea.classList.add('highlight');
        }
        
        function unhighlight() {
            dropArea.classList.remove('highlight');
        }
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
        }
        
        dropArea.addEventListener('click', () => {
            fileInput.click();
        });
        
        // Preview image before upload
        fileInput.addEventListener('change', function() {
            if(this.files.length > 0) {
                let message = `เลือกไฟล์แล้ว ${this.files.length} รูป:\n`;
                for(let i = 0; i < this.files.length; i++) {
                    message += `- ${this.files[i].name} (${(this.files[i].size / 1024).toFixed(2)} KB)\n`;
                }
                alert(message);
            }
        });
    </script>
</body>
</html>