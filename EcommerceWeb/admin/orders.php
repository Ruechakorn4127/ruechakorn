<?php
require_once '../config/database.php';

if(!isAdmin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Handle order status update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    $query = "UPDATE orders SET order_status = :status WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':id', $order_id);
    $stmt->execute();
    
    $_SESSION['success'] = "อัปเดตสถานะคำสั่งซื้อสำเร็จ";
    redirect('orders.php');
}

// Get all orders with filters
$where = [];
$params = [];

// Filter by status
if(isset($_GET['status']) && $_GET['status'] != '') {
    $where[] = "o.order_status = :status";
    $params[':status'] = $_GET['status'];
}

// Filter by date
if(isset($_GET['date_from']) && $_GET['date_from'] != '') {
    $where[] = "DATE(o.created_at) >= :date_from";
    $params[':date_from'] = $_GET['date_from'];
}

if(isset($_GET['date_to']) && $_GET['date_to'] != '') {
    $where[] = "DATE(o.created_at) <= :date_to";
    $params[':date_to'] = $_GET['date_to'];
}

// Search
if(isset($_GET['search']) && $_GET['search'] != '') {
    $where[] = "(o.order_number LIKE :search OR u.first_name LIKE :search OR u.last_name LIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

$query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone,
          (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
          FROM orders o
          JOIN users u ON o.user_id = u.id
          $where_clause
          ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_query = "SELECT 
                COUNT(*) as total_orders,
                SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN order_status = 'processing' THEN 1 ELSE 0 END) as processing_count,
                SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_count,
                SUM(CASE WHEN payment_status = 'completed' THEN total_amount ELSE 0 END) as total_revenue
                FROM orders";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการคำสั่งซื้อ - Admin E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Same styles as categories.php plus additional */
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
        
        /* Sidebar - same as before */
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
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.2;
        }
        
        .stat-label {
            color: var(--secondary);
            font-size: 0.9rem;
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
        }
        
        .filter-group input,
        .filter-group select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.5rem 1rem;
            width: 100%;
        }
        
        .btn-filter {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 0.5rem 1.5rem;
            height: 42px;
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
        }
        
        /* Table */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .order-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-processing {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-shipped {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .payment-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .payment-completed {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .payment-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .payment-failed {
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
        
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
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
                        <a href="orders.php" class="active">
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
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    จัดการคำสั่งซื้อ
                                </h2>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="dashboard.php">หน้าแรก</a></li>
                                        <li class="breadcrumb-item active">จัดการคำสั่งซื้อ</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics -->
                    <div class="stats-grid fade-in">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                            <div class="stat-label">คำสั่งซื้อทั้งหมด</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--warning);">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['pending_count']; ?></div>
                            <div class="stat-label">รอดำเนินการ</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--info);">
                                <i class="fas fa-spinner"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['processing_count']; ?></div>
                            <div class="stat-label">กำลังดำเนินการ</div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon" style="background: var(--success);">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['delivered_count']; ?></div>
                            <div class="stat-label">สำเร็จ</div>
                        </div>
                    </div>
                    
                    <!-- Filter Section -->
                    <div class="filter-section fade-in">
                        <h6 class="filter-title">
                            <i class="fas fa-filter me-2"></i>
                            ค้นหาและกรองคำสั่งซื้อ
                        </h6>
                        
                        <form method="GET" class="filter-form">
                            <div class="filter-group">
                                <label>สถานะ</label>
                                <select name="status">
                                    <option value="">ทั้งหมด</option>
                                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                    <option value="processing" <?php echo (isset($_GET['status']) && $_GET['status'] == 'processing') ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                    <option value="shipped" <?php echo (isset($_GET['status']) && $_GET['status'] == 'shipped') ? 'selected' : ''; ?>>จัดส่งแล้ว</option>
                                    <option value="delivered" <?php echo (isset($_GET['status']) && $_GET['status'] == 'delivered') ? 'selected' : ''; ?>>ได้รับสินค้า</option>
                                    <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'cancelled') ? 'selected' : ''; ?>>ยกเลิก</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label>วันที่เริ่มต้น</label>
                                <input type="date" name="date_from" value="<?php echo $_GET['date_from'] ?? ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label>วันที่สิ้นสุด</label>
                                <input type="date" name="date_to" value="<?php echo $_GET['date_to'] ?? ''; ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label>ค้นหา</label>
                                <input type="text" name="search" placeholder="เลขที่ออเดอร์, ชื่อลูกค้า" 
                                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            
                            <div class="filter-group d-flex gap-2">
                                <button type="submit" class="btn-filter">
                                    <i class="fas fa-search me-1"></i>
                                    ค้นหา
                                </button>
                                <a href="orders.php" class="btn-reset">
                                    <i class="fas fa-redo-alt"></i>
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
                    
                    <!-- Orders Table -->
                    <div class="table-container fade-in">
                        <table id="ordersTable" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>เลขที่ออเดอร์</th>
                                    <th>ลูกค้า</th>
                                    <th>วันที่</th>
                                    <th>สินค้า</th>
                                    <th>ยอดรวม</th>
                                    <th>การชำระเงิน</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo $order['order_number']; ?></strong>
                                    </td>
                                    <td>
                                        <strong><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo $order['email']; ?></small>
                                        <br>
                                        <small class="text-muted"><?php echo $order['phone']; ?></small>
                                    </td>
                                    <td>
                                        <i class="far fa-calendar-alt me-1 text-muted"></i>
                                        <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo date('H:i', strtotime($order['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $order['item_count']; ?> รายการ
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">
                                            ฿<?php echo number_format($order['total_amount'], 2); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="payment-badge payment-<?php echo $order['payment_status']; ?>">
                                            <?php 
                                                $payment_th = [
                                                    'pending' => 'รอชำระ',
                                                    'completed' => 'ชำระแล้ว',
                                                    'failed' => 'ล้มเหลว'
                                                ];
                                                echo $payment_th[$order['payment_status']] ?? $order['payment_status'];
                                            ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?php 
                                                $method_th = [
                                                    'credit_card' => 'บัตรเครดิต',
                                                    'bank_transfer' => 'โอนเงิน',
                                                    'cod' => 'เก็บปลายทาง'
                                                ];
                                                echo $method_th[$order['payment_method']] ?? $order['payment_method'];
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="order-status status-<?php echo $order['order_status']; ?>">
                                            <?php 
                                                $status_th = [
                                                    'pending' => 'รอดำเนินการ',
                                                    'processing' => 'กำลังดำเนินการ',
                                                    'shipped' => 'จัดส่งแล้ว',
                                                    'delivered' => 'ได้รับสินค้า',
                                                    'cancelled' => 'ยกเลิก'
                                                ];
                                                echo $status_th[$order['order_status']] ?? $order['order_status'];
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order-detail.php?id=<?php echo $order['id']; ?>" 
                                           class="btn-action btn-view" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn-action btn-edit" onclick="updateStatus(<?php echo $order['id']; ?>)" title="เปลี่ยนสถานะ">
                                            <i class="fas fa-edit"></i>
                                        </button>
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
    
    <!-- Update Status Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        เปลี่ยนสถานะคำสั่งซื้อ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="order_id" id="order_id">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-tag text-primary me-2"></i>
                                เลือกสถานะ
                            </label>
                            <select class="form-select" name="status" required>
                                <option value="pending">รอดำเนินการ</option>
                                <option value="processing">กำลังดำเนินการ</option>
                                <option value="shipped">จัดส่งแล้ว</option>
                                <option value="delivered">ได้รับสินค้าแล้ว</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>
                            ยกเลิก
                        </button>
                        <button type="submit" name="update_status" class="btn-save">
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
            $('#ordersTable').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.11.5/i18n/th.json"
                },
                order: [[2, 'desc']],
                pageLength: 25
            });
        });
        
        function updateStatus(orderId) {
            document.getElementById('order_id').value = orderId;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }
    </script>
</body>
</html>