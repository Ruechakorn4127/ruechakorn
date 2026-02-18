<?php
require_once '../config/database.php';

if(!isAdmin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle customer status update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE users SET status = :status WHERE id = :id AND role = 'customer'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $user_id);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "อัปเดตสถานะลูกค้าสำเร็จ";
    }
    redirect('customers.php');
}

// Handle customer deletion
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Check if customer has orders
    $check_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = :user_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':user_id', $id);
    $check_stmt->execute();
    $count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    if($count > 0) {
        $_SESSION['error'] = "ไม่สามารถลบลูกค้าที่มีประวัติการสั่งซื้อได้";
    } else {
        $query = "DELETE FROM users WHERE id = :id AND role = 'customer'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "ลบลูกค้าสำเร็จ";
        }
    }
    redirect('customers.php');
}

// Get all customers with statistics
$query = "SELECT u.*, 
          COUNT(DISTINCT o.id) as order_count,
          COALESCE(SUM(o.total_amount), 0) as total_spent,
          MAX(o.created_at) as last_order_date
          FROM users u
          LEFT JOIN orders o ON u.id = o.user_id
          WHERE u.role = 'customer'
          GROUP BY u.id
          ORDER BY u.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_customers,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_customers,
                SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_customers,
                COUNT(DISTINCT o.user_id) as customers_with_orders,
                COALESCE(SUM(o.total_amount), 0) as total_revenue
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'completed'
                WHERE u.role = 'customer'";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการลูกค้า - Admin E-Store</title>
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
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary) 0%, #764ba2 100%);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
        }
        
        .stat-label {
            color: var(--secondary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-sub {
            font-size: 0.8rem;
            color: var(--secondary);
            margin-top: 0.5rem;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .filter-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }
        
        .filter-group label {
            font-size: 0.9rem;
            color: var(--secondary);
            margin-bottom: 0.3rem;
            display: block;
        }
        
        .filter-group input,
        .filter-group select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            width: 100%;
            transition: all 0.3s;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn-filter {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            height: 42px;
            transition: all 0.3s;
        }
        
        .btn-filter:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
        }
        
        .btn-reset {
            background: #e2e8f0;
            color: var(--secondary);
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            height: 42px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .btn-reset:hover {
            background: #cbd5e1;
            color: var(--dark);
        }
        
        /* Table */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .customer-avatar {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .customer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .customer-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.2rem;
        }
        
        .customer-email {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 25px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .status-active i {
            color: #16a34a;
        }
        
        .status-inactive {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .status-inactive i {
            color: #dc2626;
        }
        
        .customer-badge {
            display: inline-block;
            padding: 0.2rem 0.8rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            margin: 2px;
        }
        
        .badge-vip {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            color: white;
        }
        
        .badge-regular {
            background: #e2e8f0;
            color: var(--secondary);
        }
        
        .badge-new {
            background: #dbeafe;
            color: #2563eb;
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
            cursor: pointer;
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
        
        .customer-detail-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 600;
            margin: 0 auto 1rem;
        }
        
        .detail-row {
            display: flex;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .detail-label {
            width: 120px;
            color: var(--secondary);
            font-weight: 500;
        }
        
        .detail-value {
            flex: 1;
            color: var(--dark);
            font-weight: 500;
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
            box-shadow: 0 5px 15px rgba(79, 70, 229, 0.3);
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
        
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                grid-template-columns: 1fr;
            }
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
        
        @keyframes slideIn {
            from {
                transform: translateX(-20px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .slide-in {
            animation: slideIn 0.3s ease forwards;
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
                        <a href="categories.php">
                            <i class="fas fa-list"></i>
                            <span>จัดการหมวดหมู่</span>
                        </a>
                        <a href="orders.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span>จัดการคำสั่งซื้อ</span>
                        </a>
                        <a href="customers.php" class="active">
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
                                    <i class="fas fa-users me-2"></i>
                                    จัดการลูกค้า
                                </h2>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="dashboard.php">หน้าแรก</a></li>
                                        <li class="breadcrumb-item active">จัดการลูกค้า</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <?php echo date('d F Y'); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics Cards -->
                    <div class="stats-grid fade-in">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['total_customers']; ?></div>
                            <div class="stat-label">ลูกค้าทั้งหมด</div>
                            <div class="stat-sub">
                                <i class="fas fa-user-check text-success me-1"></i>
                                ใช้งาน <?php echo $stats['active_customers']; ?> ราย
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--success);">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['customers_with_orders']; ?></div>
                            <div class="stat-label">ลูกค้าที่สั่งซื้อ</div>
                            <div class="stat-sub">
                                คิดเป็น <?php echo $stats['total_customers'] > 0 ? round(($stats['customers_with_orders'] / $stats['total_customers']) * 100) : 0; ?>%
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--warning);">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-value">฿<?php echo number_format($stats['total_revenue'], 0); ?></div>
                            <div class="stat-label">ยอดใช้จ่ายรวม</div>
                            <div class="stat-sub">
                                เฉลี่ย ฿<?php echo $stats['customers_with_orders'] > 0 ? number_format($stats['total_revenue'] / $stats['customers_with_orders'], 0) : 0; ?> ต่อคน
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--info);">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['inactive_customers']; ?></div>
                            <div class="stat-label">ไม่ได้ใช้งาน</div>
                            <div class="stat-sub">
                                <i class="fas fa-exclamation-circle text-warning me-1"></i>
                                รอการติดต่อ
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filter Section -->
                    <div class="filter-section fade-in">
                        <h6 class="filter-title">
                            <i class="fas fa-filter text-primary me-2"></i>
                            ค้นหาและกรองลูกค้า
                        </h6>
                        
                        <form method="GET" class="filter-form">
                            <div class="filter-group">
                                <label>สถานะ</label>
                                <select name="status">
                                    <option value="">ทั้งหมด</option>
                                    <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>ใช้งาน</option>
                                    <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>ไม่ได้ใช้งาน</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label>มีคำสั่งซื้อ</label>
                                <select name="has_orders">
                                    <option value="">ทั้งหมด</option>
                                    <option value="yes" <?php echo (isset($_GET['has_orders']) && $_GET['has_orders'] == 'yes') ? 'selected' : ''; ?>>มีคำสั่งซื้อ</option>
                                    <option value="no" <?php echo (isset($_GET['has_orders']) && $_GET['has_orders'] == 'no') ? 'selected' : ''; ?>>ไม่มีคำสั่งซื้อ</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label>ยอดใช้จ่ายขั้นต่ำ</label>
                                <input type="number" name="min_spent" placeholder="บาท" value="<?php echo $_GET['min_spent'] ?? ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label>ค้นหา</label>
                                <input type="text" name="search" placeholder="ชื่อ, อีเมล, เบอร์โทร" 
                                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            
                            <div class="filter-group d-flex gap-2">
                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-search me-1"></i>
                                    ค้นหา
                                </button>
                                <a href="customers.php" class="btn-reset">
                                    <i class="fas fa-redo-alt me-1"></i>
                                    รีเซ็ต
                                </a>
                            </div>
                        </form>
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
                    
                    <!-- Customers Table -->
                    <div class="table-container fade-in">
                        <table id="customersTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ลูกค้า</th>
                                    <th>ติดต่อ</th>
                                    <th>วันที่สมัคร</th>
                                    <th>คำสั่งซื้อ</th>
                                    <th>ยอดใช้จ่าย</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($customers as $customer): 
                                    $initial = strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1));
                                    $total_spent = floatval($customer['total_spent']);
                                ?>
                                <tr>
                                    <td>
                                        <div class="customer-info">
                                            <div class="customer-avatar">
                                                <?php echo $initial ?: 'U'; ?>
                                            </div>
                                            <div>
                                                <div class="customer-name">
                                                    <?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?>
                                                </div>
                                                <div class="customer-email">
                                                    @<?php echo htmlspecialchars($customer['username']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="fas fa-envelope text-muted me-1 small"></i> <?php echo htmlspecialchars($customer['email']); ?></div>
                                        <div><i class="fas fa-phone text-muted me-1 small"></i> <?php echo htmlspecialchars($customer['phone'] ?: '-'); ?></div>
                                    </td>
                                    <td>
                                        <i class="far fa-calendar-alt text-muted me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($customer['created_at'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo date('H:i', strtotime($customer['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill px-3 py-2">
                                            <?php echo $customer['order_count']; ?> ครั้ง
                                        </span>
                                        <?php if($customer['last_order_date']): ?>
                                            <br>
                                            <small class="text-muted">
                                                ล่าสุด <?php echo date('d/m/Y', strtotime($customer['last_order_date'])); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-primary">
                                            ฿<?php echo number_format($total_spent, 2); ?>
                                        </strong>
                                        <?php 
                                            if($total_spent >= 10000) {
                                                echo '<div><span class="customer-badge badge-vip"><i class="fas fa-crown me-1"></i>VIP</span></div>';
                                            } elseif($total_spent >= 5000) {
                                                echo '<div><span class="customer-badge" style="background: #dbeafe; color: #2563eb;"><i class="fas fa-star me-1"></i>ประจำ</span></div>';
                                            } elseif(strtotime($customer['created_at']) > strtotime('-30 days')) {
                                                echo '<div><span class="customer-badge badge-new"><i class="fas fa-seedling me-1"></i>ใหม่</span></div>';
                                            }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $customer['status']; ?>">
                                            <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                            <?php echo $customer['status'] == 'active' ? 'ใช้งาน' : 'ปิดใช้งาน'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-action btn-view" onclick="viewCustomer(<?php echo $customer['id']; ?>)" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-action btn-edit" onclick="editCustomer(<?php echo $customer['id']; ?>)" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $customer['id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบลูกค้านี้?\\nการลบจะไม่สามารถกู้คืนได้')"
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
    
    <!-- View Customer Modal -->
    <div class="modal fade" id="viewCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle me-2"></i>
                        รายละเอียดลูกค้า
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="customerDetailContent">
                    <!-- Content will be loaded via AJAX -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">กำลังโหลด...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>
                        ปิด
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit me-2"></i>
                        แก้ไขข้อมูลลูกค้า
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="update-customer.php">
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" name="first_name" id="edit_first_name" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" name="last_name" id="edit_last_name" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">อีเมล</label>
                                <input type="email" class="form-control" name="email" id="edit_email" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" name="phone" id="edit_phone">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">สถานะ</label>
                                <select class="form-select" name="status" id="edit_status">
                                    <option value="active">ใช้งาน</option>
                                    <option value="inactive">ปิดใช้งาน</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            ยกเลิก
                        </button>
                        <button type="submit" name="update_customer" class="btn-save">
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
            $('#customersTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                },
                order: [[2, 'desc']],
                pageLength: 25,
                columnDefs: [
                    { orderable: false, targets: [6] }
                ]
            });
        });
        
        function viewCustomer(id) {
            const modal = new bootstrap.Modal(document.getElementById('viewCustomerModal'));
            
            // Load customer details via AJAX
            $.ajax({
                url: 'get-customer-detail.php',
                method: 'POST',
                data: { user_id: id },
                success: function(response) {
                    document.getElementById('customerDetailContent').innerHTML = response;
                },
                error: function() {
                    document.getElementById('customerDetailContent').innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            เกิดข้อผิดพลาดในการโหลดข้อมูล
                        </div>
                    `;
                }
            });
            
            modal.show();
        }
        
        function editCustomer(id) {
            // Load customer data via AJAX
            $.ajax({
                url: 'get-customer-data.php',
                method: 'POST',
                data: { user_id: id },
                dataType: 'json',
                success: function(data) {
                    document.getElementById('edit_user_id').value = data.id;
                    document.getElementById('edit_first_name').value = data.first_name;
                    document.getElementById('edit_last_name').value = data.last_name;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_phone').value = data.phone || '';
                    document.getElementById('edit_status').value = data.status;
                    
                    new bootstrap.Modal(document.getElementById('editCustomerModal')).show();
                },
                error: function() {
                    alert('ไม่สามารถโหลดข้อมูลลูกค้าได้');
                }
            });
        }
    </script>
</body>
</html>