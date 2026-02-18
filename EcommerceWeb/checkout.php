<?php
require_once 'config/database.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user data
$user_query = "SELECT * FROM users WHERE id = :user_id";
$user_stmt = $db->prepare($user_query);
$user_stmt->bindParam(':user_id', $_SESSION['user_id']);
$user_stmt->execute();
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.stock,
               (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
               FROM cart c
               JOIN products p ON c.product_id = p.id
               WHERE c.user_id = :user_id";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->bindParam(':user_id', $_SESSION['user_id']);
$cart_stmt->execute();
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if(empty($cart_items)) {
    redirect('cart.php');
}

// Calculate total
$subtotal = 0;
foreach($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 50;
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระสินค้า - E-Store</title>
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
        
        /* Main Content */
        .checkout-wrapper {
            padding: 3rem 0;
        }
        
        .page-title {
            color: white;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 2rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .checkout-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 2.5rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        
        /* Form Styles */
        .form-label {
            font-weight: 500;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 0.8rem 1.2rem;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }
        
        .form-control.is-valid {
            border-color: var(--success);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2322c55e' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.2rem;
        }
        
        .form-control.is-invalid {
            border-color: var(--danger);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ef4444'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23ef4444' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.2rem;
        }
        
        textarea.form-control {
            min-height: 100px;
        }
        
        /* Payment Methods */
        .payment-methods {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .payment-method {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 1.2rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .payment-method:hover {
            border-color: var(--primary);
            background: #f8fafc;
            transform: translateY(-2px);
        }
        
        .payment-method.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, #f8fafc 0%, #e8f0fe 100%);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.1);
        }
        
        .payment-method input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: var(--primary);
        }
        
        .payment-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .payment-info {
            flex: 1;
        }
        
        .payment-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.2rem;
        }
        
        .payment-desc {
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        /* Order Summary */
        .order-summary {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 20px;
            padding: 2rem;
            position: sticky;
            top: 100px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .order-items {
            max-height: 300px;
            overflow-y: auto;
            padding-right: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .order-items::-webkit-scrollbar {
            width: 5px;
        }
        
        .order-items::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        
        .order-items::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        
        .order-item {
            display: flex;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            background: white;
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
            margin-bottom: 0.2rem;
        }
        
        .-item-meta {
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        .item-price {
            font-weight: 600;
            color: var(--primary);
            text-align: right;
            min-width: 100px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }
        
        .summary-row.total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            border-top: 2px solid #e2e8f0;
            margin-top: 1rem;
            padding-top: 1rem;
        }
        
        .btn-place-order {
            background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1.2rem;
            font-weight: 600;
            font-size: 1.2rem;
            width: 100%;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            margin: 1.5rem 0 1rem;
        }
        
        .btn-place-order:before {
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
        
        .btn-place-order:hover:before {
            width: 300px;
            height: 300px;
        }
        
        .btn-place-order:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 30px rgba(34, 197, 94, 0.3);
        }
        
        .btn-place-order i {
            margin-right: 0.5rem;
        }
        
        .btn-back {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 0.8rem;
            color: var(--secondary);
            text-decoration: none;
            display: block;
            text-align: center;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        /* Security Badge */
        .security-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin-top: 1.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
        }
        
        .security-badge i {
            font-size: 1.5rem;
            color: var(--success);
        }
        
        .security-badge span {
            font-size: 0.9rem;
            color: var(--secondary);
        }
        
        /* Loading Overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(5px);
        }
        
        .loading-spinner {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
        }
        
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Footer */
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
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> ตะกร้า
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p class="mb-0">กำลังดำเนินการ...</p>
        </div>
    </div>

    <div class="checkout-wrapper">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-credit-card me-3"></i>
                ชำระสินค้า
            </h1>
            
            <div class="row g-4">
                <!-- Left Column - Forms -->
                <div class="col-lg-8">
                    <div class="checkout-container">
                        <!-- Shipping Information -->
                        <div class="mb-5">
                            <h5 class="section-title">
                                <i class="fas fa-truck"></i>
                                ข้อมูลการจัดส่ง
                            </h5>
                            
                            <form id="checkoutForm" class="row g-4">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-user text-primary me-2"></i>ชื่อ *
                                    </label>
                                    <input type="text" class="form-control" id="first_name" 
                                           value="<?php echo htmlspecialchars($user['first_name']); ?>" 
                                           placeholder="กรอกชื่อ" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-user text-primary me-2"></i>นามสกุล *
                                    </label>
                                    <input type="text" class="form-control" id="last_name" 
                                           value="<?php echo htmlspecialchars($user['last_name']); ?>" 
                                           placeholder="กรอกนามสกุล" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-envelope text-primary me-2"></i>อีเมล *
                                    </label>
                                    <input type="email" class="form-control" id="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" 
                                           placeholder="example@email.com" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-phone text-primary me-2"></i>เบอร์โทรศัพท์ *
                                    </label>
                                    <input type="text" class="form-control" id="phone" 
                                           value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                           placeholder="081-234-5678" required>
                                </div>
                                
                                <div class="col-12">
                                    <label class="form-label">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>ที่อยู่ *
                                    </label>
                                    <textarea class="form-control" id="address" rows="3" 
                                              placeholder="บ้านเลขที่ ถนน ตำบล/แขวง" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-city text-primary me-2"></i>จังหวัด *
                                    </label>
                                    <input type="text" class="form-control" id="city" 
                                           value="<?php echo htmlspecialchars($user['city']); ?>" 
                                           placeholder="กรุงเทพฯ" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-mail-bulk text-primary me-2"></i>รหัสไปรษณีย์ *
                                    </label>
                                    <input type="text" class="form-control" id="postal_code" 
                                           value="<?php echo htmlspecialchars($user['postal_code']); ?>" 
                                           placeholder="10110" required>
                                </div>
                                
                                <div class="col-md-4">
                                    <label class="form-label">
                                        <i class="fas fa-globe text-primary me-2"></i>ประเทศ *
                                    </label>
                                    <input type="text" class="form-control" id="country" 
                                           value="<?php echo htmlspecialchars($user['country'] ?: 'Thailand'); ?>" 
                                           placeholder="Thailand" required>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Payment Method -->
                        <div>
                            <h5 class="section-title">
                                <i class="fas fa-credit-card"></i>
                                วิธีการชำระเงิน
                            </h5>
                            
                            <div class="payment-methods">
                                <div class="payment-method selected" onclick="selectPayment('credit_card')">
                                    <input type="radio" name="payment_method" value="credit_card" checked>
                                    <div class="payment-icon">
                                        <i class="fas fa-credit-card"></i>
                                    </div>
                                    <div class="payment-info">
                                        <div class="payment-name">บัตรเครดิต / เดบิต</div>
                                        <div class="payment-desc">ชำระด้วยบัตร VISA, Mastercard, JCB</div>
                                    </div>
                                </div>
                                
                                <div class="payment-method" onclick="selectPayment('bank_transfer')">
                                    <input type="radio" name="payment_method" value="bank_transfer">
                                    <div class="payment-icon">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="payment-info">
                                        <div class="payment-name">โอนผ่านธนาคาร</div>
                                        <div class="payment-desc">ชำระผ่านบัญชีธนาคารไทยพาณิชย์ กสิกร กรุงเทพ</div>
                                    </div>
                                </div>
                                
                                <div class="payment-method" onclick="selectPayment('cod')">
                                    <input type="radio" name="payment_method" value="cod">
                                    <div class="payment-icon">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                    <div class="payment-info">
                                        <div class="payment-name">เก็บเงินปลายทาง</div>
                                        <div class="payment-desc">ชำระเงินเมื่อได้รับสินค้า (เพิ่ม 30 บาท)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Save Information -->
                        <div class="mt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="saveInfo" checked>
                                <label class="form-check-label" for="saveInfo">
                                    บันทึกข้อมูลการจัดส่งสำหรับครั้งต่อไป
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary">
                        <h5 class="section-title">
                            <i class="fas fa-shopping-bag"></i>
                            สรุปรายการสั่งซื้อ
                        </h5>
                        
                        <div class="order-items">
                            <?php foreach($cart_items as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="images/products/<?php echo $item['image'] ?? 'default.jpg'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>">
                                </div>
                                <div class="item-details">
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-meta">จำนวน: <?php echo $item['quantity']; ?> ชิ้น</div>
                                </div>
                                <div class="item-price">
                                    ฿<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-row">
                            <span>ยอดรวมสินค้า:</span>
                            <span>฿<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>ค่าจัดส่ง:</span>
                            <span>฿<?php echo number_format($shipping, 2); ?></span>
                        </div>
                        
                        <?php if(isset($_POST['payment_method']) && $_POST['payment_method'] == 'cod'): ?>
                        <div class="summary-row">
                            <span>ค่าธรรมเนียม COD:</span>
                            <span>฿30.00</span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="summary-row total">
                            <span>ยอดรวมทั้งสิ้น:</span>
                            <span>฿<?php echo number_format($total, 2); ?></span>
                        </div>
                        
                        <button class="btn-place-order" onclick="placeOrder()">
                            <i class="fas fa-lock"></i>
                            ยืนยันการสั่งซื้อ
                        </button>
                        
                        <a href="cart.php" class="btn-back">
                            <i class="fas fa-arrow-left me-2"></i>
                            กลับไปหน้าตะกร้า
                        </a>
                        
                        <div class="security-badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>การชำระเงินปลอดภัยด้วยระบบ SSL</span>
                        </div>
                    </div>
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
                        <li><a href="products.php" class="text-white-50">สินค้าทั้งหมด</a></li>
                        <li><a href="categories.php" class="text-white-50">หมวดหมู่สินค้า</a></li>
                        <li><a href="about.php" class="text-white-50">เกี่ยวกับเรา</a></li>
                        <li><a href="contact.php" class="text-white-50">ติดต่อเรา</a></li>
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
        function selectPayment(method) {
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            
            event.currentTarget.classList.add('selected');
            document.querySelector(`input[value="${method}"]`).checked = true;
        }
        
        function validateForm() {
            const fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'postal_code', 'country'];
            let isValid = true;
            
            fields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                }
            });
            
            // Validate email format
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.add('is-invalid');
                isValid = false;
            }
            
            // Validate phone (basic)
            const phone = document.getElementById('phone');
            const phoneRegex = /^[0-9]{9,10}$/;
            if (!phoneRegex.test(phone.value.replace(/-/g, ''))) {
                phone.classList.add('is-invalid');
                isValid = false;
            }
            
            return isValid;
        }
        
        function placeOrder() {
            if (!validateForm()) {
                alert('กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง');
                return;
            }
            
            // Show loading
            document.getElementById('loadingOverlay').style.display = 'flex';
            
            const orderData = {
                first_name: document.getElementById('first_name').value,
                last_name: document.getElementById('last_name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                address: document.getElementById('address').value,
                city: document.getElementById('city').value,
                postal_code: document.getElementById('postal_code').value,
                country: document.getElementById('country').value,
                payment_method: document.querySelector('input[name="payment_method"]:checked').value
            };
            
            $.ajax({
                url: 'ajax/place_order.php',
                method: 'POST',
                data: orderData,
                success: function(response) {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    const result = JSON.parse(response);
                    if (result.success) {
                        window.location.href = 'order-success.php?order_id=' + result.order_id;
                    } else {
                        alert('เกิดข้อผิดพลาด: ' + result.message);
                    }
                },
                error: function() {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    alert('เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองอีกครั้ง');
                }
            });
        }
        
        // Real-time validation
        document.querySelectorAll('#checkoutForm input, #checkoutForm textarea').forEach(input => {
            input.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                    this.classList.add('is-valid');
                } else {
                    this.classList.remove('is-valid');
                    this.classList.add('is-invalid');
                }
            });
        });
    </script>
</body>
</html>