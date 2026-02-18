<?php
require_once 'config/database.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

if(!isset($_GET['id'])) {
    redirect('profile.php');
}

$database = new Database();
$db = $database->getConnection();

// Get order details
$query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone 
          FROM orders o
          JOIN users u ON o.user_id = u.id
          WHERE o.id = :order_id AND o.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':order_id', $_GET['id']);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    redirect('profile.php');
}

// Get order items with product images
$items_query = "SELECT oi.*, 
                (SELECT image_url FROM product_images WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1) as product_image
                FROM order_items oi
                WHERE oi.order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->bindParam(':order_id', $_GET['id']);
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดคำสั่งซื้อ - E-Store</title>
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
        }
        
        /* Page Title */
        .page-title {
            color: white;
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 2rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Order Status */
        .status-timeline {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .timeline-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 2rem 0;
        }
        
        .timeline-steps:before {
            content: '';
            position: absolute;
            top: 25px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e2e8f0;
            z-index: 1;
        }
        
        .timeline-step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        
        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.2rem;
            color: var(--secondary);
            transition: all 0.3s;
        }
        
        .step-icon.completed {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }
        
        .step-icon.active {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        .step-label {
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--dark);
        }
        
        .step-date {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        /* Order Details */
        .detail-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
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
        
        .order-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .order-date {
            color: var(--secondary);
            margin-bottom: 1rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .info-item {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 1.2rem;
        }
        
        .info-label {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        /* Order Items */
        .order-items {
            margin-top: 1.5rem;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        
        .order-item:hover {
            background: #f8fafc;
        }
        
        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 15px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem;
        }
        
        .item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.3rem;
        }
        
        .item-meta {
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .item-price {
            text-align: right;
            min-width: 150px;
        }
        
        .item-price .price {
            font-weight: 700;
            color: var(--primary);
            font-size: 1.1rem;
        }
        
        .item-price .quantity {
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        /* Summary Table */
        .summary-table {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        
        .summary-row.total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            border-bottom: none;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--primary);
        }
        
        /* Shipping Address */
        .address-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 1.5rem;
        }
        
        .address-line {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.8rem;
            color: var(--dark);
        }
        
        .address-line i {
            width: 25px;
            color: var(--primary);
        }
        
        /* Payment Info */
        .payment-info {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 1.5rem;
        }
        
        .payment-method {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .payment-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .payment-details {
            flex: 1;
        }
        
        .payment-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.2rem;
        }
        
        .payment-status {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .status-completed {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-failed {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            flex: 1;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            flex: 1;
            transition: all 0.3s;
        }
        
        .btn-danger:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(220, 38, 38, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 10px;
            padding: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Footer */
        .footer {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
        }
        
        @media (max-width: 768px) {
            .timeline-steps {
                flex-direction: column;
                gap: 1rem;
            }
            
            .timeline-steps:before {
                display: none;
            }
            
            .timeline-step {
                display: flex;
                align-items: center;
                gap: 1rem;
                text-align: left;
            }
            
            .step-icon {
                margin: 0;
            }
            
            .order-item {
                flex-direction: column;
                text-align: center;
            }
            
            .item-price {
                text-align: center;
                min-width: auto;
            }
            
            .action-buttons {
                flex-direction: column;
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
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user"></i> โปรไฟล์
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb-wrapper fade-in">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> หน้าแรก</a></li>
                    <li class="breadcrumb-item"><a href="profile.php"><i class="fas fa-user"></i> โปรไฟล์</a></li>
                    <li class="breadcrumb-item active">รายละเอียดคำสั่งซื้อ</li>
                </ol>
            </nav>
        </div>
        
        <h1 class="page-title fade-in">
            <i class="fas fa-file-invoice me-3"></i>
            รายละเอียดคำสั่งซื้อ
        </h1>
        
        <!-- Order Status Timeline -->
        <div class="status-timeline fade-in">
            <h5 class="card-title">
                <i class="fas fa-clock"></i>
                สถานะคำสั่งซื้อ
            </h5>
            
            <?php
            $status_order = ['pending', 'processing', 'shipped', 'delivered'];
            $current_status = $order['order_status'];
            $current_index = array_search($current_status, $status_order);
            
            $status_th = [
                'pending' => 'รอดำเนินการ',
                'processing' => 'กำลังดำเนินการ',
                'shipped' => 'จัดส่งแล้ว',
                'delivered' => 'ได้รับสินค้า',
                'cancelled' => 'ยกเลิก'
            ];
            
            $status_icon = [
                'pending' => 'fa-clock',
                'processing' => 'fa-spinner',
                'shipped' => 'fa-truck',
                'delivered' => 'fa-check-circle',
                'cancelled' => 'fa-times-circle'
            ];
            ?>
            
            <?php if($current_status == 'cancelled'): ?>
                <div class="text-center py-4">
                    <div class="step-icon cancelled" style="background: var(--danger); border-color: var(--danger); color: white; width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 1rem;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <h4 style="color: var(--danger);">คำสั่งซื้อถูกยกเลิก</h4>
                    <p class="text-muted">หากมีข้อสงสัยกรุณาติดต่อฝ่ายบริการลูกค้า</p>
                </div>
            <?php else: ?>
                <div class="timeline-steps">
                    <?php foreach($status_order as $index => $status): ?>
                        <div class="timeline-step">
                            <div class="step-icon <?php 
                                if($index < $current_index) echo 'completed';
                                elseif($index == $current_index) echo 'active';
                            ?>">
                                <i class="fas <?php echo $status_icon[$status]; ?>"></i>
                            </div>
                            <div class="step-label"><?php echo $status_th[$status]; ?></div>
                            <?php if($index == $current_index): ?>
                                <div class="step-date">กำลังดำเนินการ</div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="row g-4">
            <!-- Order Items -->
            <div class="col-lg-8">
                <div class="detail-card fade-in">
                    <h5 class="card-title">
                        <i class="fas fa-box"></i>
                        รายการสินค้า
                    </h5>
                    
                    <div class="order-number">#<?php echo $order['order_number']; ?></div>
                    <div class="order-date">
                        <i class="far fa-calendar-alt me-2"></i>
                        <?php echo date('d F Y H:i', strtotime($order['created_at'])); ?> น.
                    </div>
                    
                    <div class="order-items">
                        <?php foreach($items as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="images/products/<?php echo $item['product_image'] ?? 'default.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            </div>
                            <div class="item-details">
                                <div class="item-name"><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div class="item-meta">รหัสสินค้า: #<?php echo $item['product_id']; ?></div>
                            </div>
                            <div class="item-price">
                                <div class="price">฿<?php echo number_format($item['price'], 2); ?></div>
                                <div class="quantity">จำนวน: <?php echo $item['quantity']; ?> ชิ้น</div>
                                <div class="text-primary fw-bold mt-1">รวม ฿<?php echo number_format($item['total'], 2); ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="summary-table">
                        <div class="summary-row">
                            <span>ยอดรวมสินค้า:</span>
                            <span>฿<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>ค่าจัดส่ง:</span>
                            <span>฿<?php echo number_format($shipping, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span>ภาษีมูลค่าเพิ่ม (7%):</span>
                            <span>฿<?php echo number_format($tax, 2); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>ยอดสุทธิ:</span>
                            <span>฿<?php echo number_format($total, 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Information -->
            <div class="col-lg-4">
                <!-- Shipping Information -->
                <div class="detail-card fade-in">
                    <h5 class="card-title">
                        <i class="fas fa-truck"></i>
                        ข้อมูลการจัดส่ง
                    </h5>
                    
                    <div class="address-card">
                        <div class="address-line">
                            <i class="fas fa-user"></i>
                            <span><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></span>
                        </div>
                        <div class="address-line">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo $order['shipping_address']; ?></span>
                        </div>
                        <div class="address-line">
                            <i class="fas fa-city"></i>
                            <span><?php echo $order['shipping_city'] . ', ' . $order['shipping_postal']; ?></span>
                        </div>
                        <div class="address-line">
                            <i class="fas fa-globe"></i>
                            <span><?php echo $order['shipping_country']; ?></span>
                        </div>
                        <div class="address-line">
                            <i class="fas fa-phone"></i>
                            <span><?php echo $order['phone']; ?></span>
                        </div>
                        <div class="address-line">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo $order['email']; ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Information -->
                <div class="detail-card fade-in">
                    <h5 class="card-title">
                        <i class="fas fa-credit-card"></i>
                        ข้อมูลการชำระเงิน
                    </h5>
                    
                    <div class="payment-info">
                        <div class="payment-method">
                            <div class="payment-icon">
                                <i class="fas fa-<?php 
                                    echo $order['payment_method'] == 'credit_card' ? 'credit-card' : 
                                        ($order['payment_method'] == 'bank_transfer' ? 'university' : 'money-bill-wave'); 
                                ?>"></i>
                            </div>
                            <div class="payment-details">
                                <div class="payment-name">
                                    <?php 
                                        $method_th = [
                                            'credit_card' => 'บัตรเครดิต/เดบิต',
                                            'bank_transfer' => 'โอนเงินผ่านธนาคาร',
                                            'cod' => 'เก็บเงินปลายทาง'
                                        ];
                                        echo $method_th[$order['payment_method']] ?? $order['payment_method'];
                                    ?>
                                </div>
                                <div class="payment-status status-<?php echo $order['payment_status']; ?>">
                                    <?php 
                                        $status_payment = [
                                            'pending' => 'รอการชำระเงิน',
                                            'completed' => 'ชำระเงินแล้ว',
                                            'failed' => 'ชำระเงินไม่สำเร็จ'
                                        ];
                                        echo $status_payment[$order['payment_status']] ?? $order['payment_status'];
                                    ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-grid">
                            <div class="info-item">
                                <div class="info-label">วันที่สั่งซื้อ</div>
                                <div class="info-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">ยอดชำระ</div>
                                <div class="info-value">฿<?php echo number_format($order['total_amount'], 2); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="detail-card fade-in">
                    <div class="action-buttons">
                        <a href="products.php" class="btn-outline">
                            <i class="fas fa-shopping-bag"></i>
                            สั่งซื้อเพิ่ม
                        </a>
                        <?php if($order['order_status'] == 'pending'): ?>
                            <button class="btn-danger" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                <i class="fas fa-times-circle"></i>
                                ยกเลิกคำสั่งซื้อ
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php if($order['order_status'] == 'pending'): ?>
                        <div class="alert alert-warning mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <small>คุณสามารถยกเลิกคำสั่งซื้อได้ก่อนที่ร้านจะดำเนินการจัดส่ง</small>
                        </div>
                    <?php endif; ?>
                    
                    <button onclick="window.print()" class="btn-outline w-100 mt-3">
                        <i class="fas fa-print"></i>
                        พิมพ์ใบเสร็จ
                    </button>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function cancelOrder(orderId) {
            if(confirm('คุณแน่ใจหรือไม่ที่จะยกเลิกคำสั่งซื้อนี้?')) {
                window.location.href = 'cancel-order.php?id=' + orderId;
            }
        }
    </script>
</body>
</html>