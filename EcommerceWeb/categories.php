<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get all categories with product counts
$query = "SELECT c.*, 
          COUNT(p.id) as product_count 
          FROM categories c
          LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
          WHERE c.status = 'active'
          GROUP BY c.id
          ORDER BY c.name";
$stmt = $db->prepare($query);
$stmt->execute();
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หมวดหมู่สินค้า - E-Store</title>
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
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            margin: 3rem 0;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }
        
        .category-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 60px rgba(79, 70, 229, 0.2);
        }
        
        .category-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
        }
        
        .category-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
            color: var(--primary);
            transition: all 0.3s;
        }
        
        .category-card:hover .category-icon {
            transform: scale(1.1);
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
        }
        
        .category-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }
        
        .category-description {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            font-size: 1rem;
        }
        
        .product-count {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 25px;
            color: var(--primary);
            font-weight: 600;
        }
        
        .product-count i {
            margin-right: 5px;
        }
        
        .empty-state {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 4rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state i {
            font-size: 5rem;
            color: var(--primary-light);
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            border: none;
            padding: 1rem 2rem;
            border-radius: 15px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.4);
        }
        
        .footer {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
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
                        <a class="nav-link" href="index.php">หน้าแรก</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">สินค้าทั้งหมด</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="categories.php">หมวดหมู่</a>
                    </li>
                    <!-- ... rest of navigation ... -->
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-th-large me-3"></i>หมวดหมู่สินค้า</h1>
            <p class="text-muted">เลือกหมวดหมู่ที่คุณสนใจ</p>
        </div>
        
        <?php if(empty($categories)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h3>ยังไม่มีหมวดหมู่สินค้า</h3>
                <p>กรุณารอการอัพเดทจากทางร้าน</p>
                <a href="products.php" class="btn btn-primary mt-3">
                    <i class="fas fa-box me-2"></i>ดูสินค้าทั้งหมด
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach($categories as $category): ?>
                <div class="col-md-4">
                    <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-<?php 
                                echo $category['id'] == 1 ? 'mobile-alt' : 
                                    ($category['id'] == 2 ? 'tshirt' : 
                                    ($category['id'] == 3 ? 'book' : 
                                    ($category['id'] == 4 ? 'home' : 'tag'))); 
                            ?>"></i>
                        </div>
                        <h3 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                        <span class="product-count">
                            <i class="fas fa-box"></i>
                            <?php echo $category['product_count']; ?> สินค้า
                        </span>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <!-- ... footer code ... -->
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>