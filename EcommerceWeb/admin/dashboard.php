<?php
require_once '../config/database.php';

if(!isAdmin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get statistics
$stats = [];

// Total products
$prod_query = "SELECT COUNT(*) as total FROM products";
$prod_stmt = $db->query($prod_query);
$stats['products'] = $prod_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total orders
$order_query = "SELECT COUNT(*) as total FROM orders";
$order_stmt = $db->query($order_query);
$stats['orders'] = $order_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total customers
$cust_query = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$cust_stmt = $db->query($cust_query);
$stats['customers'] = $cust_stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total revenue
$rev_query = "SELECT SUM(total_amount) as total FROM orders WHERE payment_status = 'completed'";
$rev_stmt = $db->query($rev_query);
$stats['revenue'] = $rev_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Recent orders
$recent_query = "SELECT o.*, u.first_name, u.last_name 
                 FROM orders o
                 JOIN users u ON o.user_id = u.id
                 ORDER BY o.created_at DESC
                 LIMIT 10";
$recent_stmt = $db->prepare($recent_query);
$recent_stmt->execute();
$recent_orders = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get monthly sales data for chart
$monthly_query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as month, 
                  COUNT(*) as order_count, 
                  SUM(total_amount) as total_sales 
                  FROM orders 
                  WHERE payment_status = 'completed'
                  GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                  ORDER BY month DESC
                  LIMIT 6";
$monthly_stmt = $db->prepare($monthly_query);
$monthly_stmt->execute();
$monthly_data = $monthly_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin E-Store</title>
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
        
        .sidebar-header p {
            font-size: 0.8rem;
            opacity: 0.7;
            margin: 0;
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
        
        .sidebar-menu a.active {
            background: linear-gradient(90deg, rgba(79, 70, 229, 0.2) 0%, transparent 100%);
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
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .content-header h2 {
            font-weight: 700;
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
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(79, 70, 229, 0.2);
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
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.2rem;
        }
        
        .stat-label {
            color: var(--secondary);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .stat-trend {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .trend-up {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .trend-down {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* Charts */
        .chart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }
        
        .chart-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .chart-title i {
            color: var(--primary);
        }
        
        /* Tables */
        .table-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .table-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            border-bottom: 2px solid var(--gray-200);
            color: var(--secondary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        
        .table tbody tr {
            transition: all 0.3s;
        }
        
        .table tbody tr:hover {
            background: var(--gray-100);
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
        
        /* Responsive */
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-sidebar {
                min-height: auto;
            }
        }
        
        /* Animations */
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
                        <a href="dashboard.php" class="active">
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
                                <h2><i class="fas fa-tachometer-alt me-2"></i>แดชบอร์ด</h2>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">หน้าแรก</a></li>
                                        <li class="breadcrumb-item active">แดชบอร์ด</li>
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
                                <i class="fas fa-box"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['products']; ?></div>
                            <div class="stat-label">สินค้าทั้งหมด</div>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up me-1"></i>+12%
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['orders']; ?></div>
                            <div class="stat-label">คำสั่งซื้อ</div>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up me-1"></i>+8%
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value"><?php echo $stats['customers']; ?></div>
                            <div class="stat-label">ลูกค้า</div>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up me-1"></i>+15%
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-value">฿<?php echo number_format($stats['revenue'], 0); ?></div>
                            <div class="stat-label">รายได้</div>
                            <div class="stat-trend trend-up">
                                <i class="fas fa-arrow-up me-1"></i>+23%
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart -->
                    <div class="chart-container fade-in">
                        <h5 class="chart-title">
                            <i class="fas fa-chart-line"></i>
                            ยอดขาย 6 เดือนล่าสุด
                        </h5>
                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                    
                    <!-- Recent Orders -->
                    <div class="table-container fade-in">
                        <h5 class="table-title">
                            <i class="fas fa-clock"></i>
                            คำสั่งซื้อล่าสุด
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>เลขที่คำสั่งซื้อ</th>
                                        <th>ลูกค้า</th>
                                        <th>วันที่</th>
                                        <th>ยอดรวม</th>
                                        <th>สถานะ</th>
                                        <th>จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $order['order_number']; ?></strong>
                                        </td>
                                        <td>
                                            <?php echo $order['first_name'] . ' ' . $order['last_name']; ?>
                                        </td>
                                        <td>
                                            <i class="far fa-calendar-alt me-1 text-muted"></i>
                                            <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                        </td>
                                        <td>
                                            <strong class="text-primary">
                                                ฿<?php echo number_format($order['total_amount'], 2); ?>
                                            </strong>
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
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php 
                    $months = array_reverse(array_column($monthly_data, 'month'));
                    echo json_encode(array_map(function($m) {
                        return date('M Y', strtotime($m . '-01'));
                    }, $months));
                ?>,
                datasets: [{
                    label: 'ยอดขาย (บาท)',
                    data: <?php echo json_encode(array_reverse(array_column($monthly_data, 'total_sales'))); ?>,
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '฿' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>