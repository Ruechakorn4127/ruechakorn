<?php
require_once '../config/database.php';

if(!isAdmin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle category addition
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    
    $query = "INSERT INTO categories (name, description) VALUES (:name, :description)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "เพิ่มหมวดหมู่สำเร็จ";
    }
    redirect('categories.php');
}

// Handle category update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];
    $status = $_POST['status'];
    
    $query = "UPDATE categories SET name = :name, description = :description, status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':status', $status);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "อัปเดตหมวดหมู่สำเร็จ";
    }
    redirect('categories.php');
}

// Handle category deletion
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if category has products
    $check_query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':id', $id);
    $check_stmt->execute();
    $count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if($count > 0) {
        $_SESSION['error'] = "ไม่สามารถลบหมวดหมู่ที่มีสินค้าอยู่ได้";
    } else {
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "ลบหมวดหมู่สำเร็จ";
        }
    }
    redirect('categories.php');
}

// Get all categories with product count
$query = "SELECT c.*, 
          (SELECT COUNT(*) FROM products WHERE category_id = c.id) as product_count 
          FROM categories c 
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
    <title>จัดการหมวดหมู่ - Admin E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Same styles as dashboard.php */
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
        
        /* Admin Sidebar - same as dashboard */
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
        
        /* Table */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
        }
        
        .btn-cancel {
            background: #e2e8f0;
            color: var(--secondary);
            border: none;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            font-weight: 500;
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
                    </div>
                    
                    <div class="sidebar-menu">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>แดชบอร์ด</span>
                        </a>
                        <a href="products.php">
                            <i class="fas fa-box"></i>
                            <span>จัดการสินค้า</span>
                        </a>
                        <a href="categories.php" class="active">
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
                        <a href="reports.php">
                            <i class="fas fa-chart-bar"></i>
                            <span>รายงาน</span>
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
                                    <i class="fas fa-list me-2"></i>
                                    จัดการหมวดหมู่
                                </h2>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="dashboard.php">หน้าแรก</a></li>
                                        <li class="breadcrumb-item active">จัดการหมวดหมู่</li>
                                    </ol>
                                </nav>
                            </div>
                            <button class="btn-add" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus me-2"></i>
                                เพิ่มหมวดหมู่
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
                    
                    <!-- Categories Table -->
                    <div class="table-container fade-in">
                        <table id="categoriesTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>ชื่อหมวดหมู่</th>
                                    <th>รายละเอียด</th>
                                    <th>จำนวนสินค้า</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($categories as $category): ?>
                                <tr>
                                    <td>#<?php echo $category['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($category['description']); ?></td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $category['product_count']; ?> รายการ
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $category['status']; ?>">
                                            <i class="fas fa-<?php echo $category['status'] == 'active' ? 'check-circle' : 'times-circle'; ?> me-1"></i>
                                            <?php echo $category['status'] == 'active' ? 'เปิดใช้งาน' : 'ปิดใช้งาน'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-action btn-edit" onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', '<?php echo htmlspecialchars($category['description']); ?>', '<?php echo $category['status']; ?>')" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $category['id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบหมวดหมู่นี้?')"
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
    
    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-plus-circle me-2"></i>
                        เพิ่มหมวดหมู่ใหม่
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-tag text-primary me-2"></i>
                                ชื่อหมวดหมู่ <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="name" required 
                                   placeholder="เช่น อิเล็กทรอนิกส์, เสื้อผ้า">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-align-left text-primary me-2"></i>
                                รายละเอียด
                            </label>
                            <textarea class="form-control" name="description" rows="3" 
                                      placeholder="รายละเอียดของหมวดหมู่..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            ยกเลิก
                        </button>
                        <button type="submit" name="add_category" class="btn-save">
                            <i class="fas fa-save me-2"></i>
                            บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        แก้ไขหมวดหมู่
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-tag text-primary me-2"></i>
                                ชื่อหมวดหมู่ <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="name" id="edit_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-align-left text-primary me-2"></i>
                                รายละเอียด
                            </label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-toggle-on text-primary me-2"></i>
                                สถานะ
                            </label>
                            <select class="form-select" name="status" id="edit_status">
                                <option value="active">เปิดใช้งาน</option>
                                <option value="inactive">ปิดใช้งาน</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            ยกเลิก
                        </button>
                        <button type="submit" name="update_category" class="btn-save">
                            <i class="fas fa-save me-2"></i>
                            บันทึก
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
            $('#categoriesTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                },
                order: [[0, 'asc']]
            });
        });
        
        function editCategory(id, name, description, status) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('edit_status').value = status;
            
            new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
        }
    </script>
</body>
</html>