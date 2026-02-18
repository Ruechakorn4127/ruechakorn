<?php
require_once '../config/database.php';

if(!isAdmin()) {
    redirect('../login.php');
}

$database = new Database();
$db = $database->getConnection();

if(!isset($_GET['id'])) {
    redirect('orders.php');
}

$order_id = $_GET['id'];

// Get order details
$query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone, u.address, u.city, u.postal_code 
          FROM orders o
          JOIN users u ON o.user_id = u.id
          WHERE o.id = :order_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':order_id', $order_id);
$stmt->execute();

if($stmt->rowCount() == 0) {
    redirect('orders.php');
}

$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Get order items
$items_query = "SELECT * FROM order_items WHERE order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->bindParam(':order_id', $order_id);
$items_stmt->execute();
$items = $items_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
foreach($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 50;
$tax = $subtotal * 0.07;
$total = $subtotal + $shipping + $tax;

// Handle order status update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['order_status'];
    
    $update_query = "UPDATE orders SET order_status = :status WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':status', $new_status);
    $update_stmt->bindParam(':id', $order_id);
    
    if($update_stmt->execute()) {
        $_SESSION['success'] = "อัปเดตสถานะคำสั่งซื้อสำเร็จ";
        redirect("order-detail.php?id=$order_id");
    }
}

// Handle payment status update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_payment'])) {
    $payment_status = $_POST['payment_status'];
    
    $update_query = "UPDATE orders SET payment_status = :status WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':status', $payment_status);
    $update_stmt->bindParam(':id', $order_id);
    
    if($update_stmt->execute()) {
        $_SESSION['success'] = "อัปเดตสถานะการชำระเงินสำเร็จ";
        redirect("order-detail.php?id=$order_id");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำสั่งซื้อ #<?php echo $order['order_number']; ?> - Admin</title>
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
        
        /* Order Details */
        .detail-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .card-title {
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
        
        .card-title i {
            color: var(--primary);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
        }
        
        .order-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .order-date {
            color: var(--secondary);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5rem 1.2rem;
            border-radius: 25px;
            font-size: 1rem;
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
            background: #dcfce7;
            color: #16a34a;
        }
        
        .status-cancelled {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .payment-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
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
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-box {
            background: #f8fafc;
            border-radius: 15px;
            padding: 1.2rem;
        }
        
        .info-label {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .address-box {
            background: #f8fafc;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .address-line {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
        }
        
        .address-line i {
            width: 25px;
            color: var(--primary);
        }
        
        .items-table {
            width: 100%;
            margin-bottom: 2rem;
        }
        
        .items-table th {
            background: #f8fafc;
            padding: 1rem;
            font-weight: 600;
        }
        
        .items-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .total-row {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
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
        
        .btn-print {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.6rem 1.5rem;
            color: var(--secondary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-print:hover {
            border-color: var(--primary);
            color: var(--primary);
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
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .order-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
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
                        <p>เวอร์ชัน 1.0.0</p>
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
                                <h1 class="page-title">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    รายละเอียดคำสั่งซื้อ
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="dashboard.php">แดชบอร์ด</a></li>
                                        <li class="breadcrumb-item"><a href="orders.php">จัดการคำสั่งซื้อ</a></li>
                                        <li class="breadcrumb-item active">#<?php echo $order['order_number']; ?></li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="orders.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    กลับ
                                </a>
                                <button onclick="window.print()" class="btn-print">
                                    <i class="fas fa-print"></i>
                                    พิมพ์
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Alert Messages -->
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
                    
                    <!-- Order Header -->
                    <div class="order-header fade-in">
                        <div>
                            <span class="order-number">#<?php echo $order['order_number']; ?></span>
                        </div>
                        <div>
                            <span class="status-badge status-<?php echo $order['order_status']; ?> me-2">
                                <?php 
                                    $status_th = [
                                        'pending' => 'รอดำเนินการ',
                                        'processing' => 'กำลังดำเนินการ',
                                        'shipped' => 'จัดส่งแล้ว',
                                        'delivered' => 'ได้รับสินค้าแล้ว',
                                        'cancelled' => 'ยกเลิก'
                                    ];
                                    echo $status_th[$order['order_status']] ?? $order['order_status'];
                                ?>
                            </span>
                            <span class="payment-badge payment-<?php echo $order['payment_status']; ?>">
                                <?php 
                                    $payment_th = [
                                        'pending' => 'รอชำระเงิน',
                                        'completed' => 'ชำระเงินแล้ว',
                                        'failed' => 'ชำระเงินล้มเหลว'
                                    ];
                                    echo $payment_th[$order['payment_status']] ?? $order['payment_status'];
                                ?>
                            </span>
                        </div>
                        <div class="order-date">
                            <i class="far fa-calendar-alt me-2"></i>
                            <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?> น.
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Order Items -->
                            <div class="detail-card fade-in">
                                <h5 class="card-title">
                                    <i class="fas fa-box"></i>
                                    รายการสินค้า
                                </h5>
                                
                                <table class="items-table">
                                    <thead>
                                        <tr>
                                            <th>สินค้า</th>
                                            <th>ราคาต่อหน่วย</th>
                                            <th>จำนวน</th>
                                            <th>รวม</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($items as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td>฿<?php echo number_format($item['price'], 2); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>฿<?php echo number_format($item['total'], 2); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                
                                <div class="text-end">
                                    <div>ยอดรวมสินค้า: ฿<?php echo number_format($subtotal, 2); ?></div>
                                    <div>ค่าจัดส่ง: ฿<?php echo number_format($shipping, 2); ?></div>
                                    <div>ภาษีมูลค่าเพิ่ม (7%): ฿<?php echo number_format($tax, 2); ?></div>
                                    <div class="total-row mt-2">ยอดสุทธิ: ฿<?php echo number_format($total, 2); ?></div>
                                </div>
                            </div>
                            
                            <!-- Update Status -->
                            <div class="detail-card fade-in">
                                <h5 class="card-title">
                                    <i class="fas fa-edit"></i>
                                    อัปเดตสถานะ
                                </h5>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <form method="POST" class="mb-3">
                                            <label class="form-label">สถานะคำสั่งซื้อ</label>
                                            <div class="d-flex gap-2">
                                                <select class="form-select" name="order_status">
                                                    <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>รอดำเนินการ</option>
                                                    <option value="processing" <?php echo $order['order_status'] == 'processing' ? 'selected' : ''; ?>>กำลังดำเนินการ</option>
                                                    <option value="shipped" <?php echo $order['order_status'] == 'shipped' ? 'selected' : ''; ?>>จัดส่งแล้ว</option>
                                                    <option value="delivered" <?php echo $order['order_status'] == 'delivered' ? 'selected' : ''; ?>>ได้รับสินค้าแล้ว</option>
                                                    <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>ยกเลิก</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn-save">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <form method="POST">
                                            <label class="form-label">สถานะการชำระเงิน</label>
                                            <div class="d-flex gap-2">
                                                <select class="form-select" name="payment_status">
                                                    <option value="pending" <?php echo $order['payment_status'] == 'pending' ? 'selected' : ''; ?>>รอชำระเงิน</option>
                                                    <option value="completed" <?php echo $order['payment_status'] == 'completed' ? 'selected' : ''; ?>>ชำระเงินแล้ว</option>
                                                    <option value="failed" <?php echo $order['payment_status'] == 'failed' ? 'selected' : ''; ?>>ชำระเงินล้มเหลว</option>
                                                </select>
                                                <button type="submit" name="update_payment" class="btn-save">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <!-- Customer Information -->
                            <div class="detail-card fade-in">
                                <h5 class="card-title">
                                    <i class="fas fa-user"></i>
                                    ข้อมูลลูกค้า
                                </h5>
                                
                                <div class="info-box mb-3">
                                    <div class="info-label">ชื่อ-นามสกุล</div>
                                    <div class="info-value"><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></div>
                                </div>
                                
                                <div class="info-box mb-3">
                                    <div class="info-label">อีเมล</div>
                                    <div class="info-value"><?php echo $order['email']; ?></div>
                                </div>
                                
                                <div class="info-box mb-3">
                                    <div class="info-label">เบอร์โทรศัพท์</div>
                                    <div class="info-value"><?php echo $order['phone'] ?: '-'; ?></div>
                                </div>
                            </div>
                            
                            <!-- Shipping Address -->
                            <div class="detail-card fade-in">
                                <h5 class="card-title">
                                    <i class="fas fa-map-marker-alt"></i>
                                    ที่อยู่จัดส่ง
                                </h5>
                                
                                <div class="address-box">
                                    <div class="address-line">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></span>
                                    </div>
                                    <div class="address-line">
                                        <i class="fas fa-map-pin"></i>
                                        <span><?php echo $order['shipping_address']; ?></span>
                                    </div>
                                    <div class="address-line">
                                        <i class="fas fa-city"></i>
                                        <span><?php echo $order['shipping_city']; ?></span>
                                    </div>
                                    <div class="address-line">
                                        <i class="fas fa-mail-bulk"></i>
                                        <span><?php echo $order['shipping_postal']; ?></span>
                                    </div>
                                    <div class="address-line">
                                        <i class="fas fa-globe"></i>
                                        <span><?php echo $order['shipping_country']; ?></span>
                                    </div>
                                    <div class="address-line">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo $order['phone'] ?: '-'; ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Method -->
                            <div class="detail-card fade-in">
                                <h5 class="card-title">
                                    <i class="fas fa-credit-card"></i>
                                    วิธีการชำระเงิน
                                </h5>
                                
                                <div class="info-box">
                                    <div class="info-label">วิธีชำระเงิน</div>
                                    <div class="info-value">
                                        <?php 
                                            $method_th = [
                                                'credit_card' => '💳 บัตรเครดิต/เดบิต',
                                                'bank_transfer' => '🏦 โอนผ่านธนาคาร',
                                                'cod' => '💰 เก็บเงินปลายทาง'
                                            ];
                                            echo $method_th[$order['payment_method']] ?? $order['payment_method'];
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>