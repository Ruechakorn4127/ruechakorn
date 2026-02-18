<?php
require_once 'config/database.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get cart items
$query = "SELECT c.*, p.name, p.price, p.stock,
          (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image
          FROM cart c
          JOIN products p ON c.product_id = p.id
          WHERE c.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>ตะกร้าสินค้า - E-Store</title>
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
        
        .nav-link {
            color: var(--dark) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            position: relative;
        }
        
        .cart-badge {
            background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 0.7rem;
            position: absolute;
            top: -5px;
            right: -10px;
        }
        
        /* Page Title */
        .page-title {
            color: white;
            font-weight: 700;
            font-size: 2.5rem;
            margin: 2rem 0;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Cart Container */
        .cart-wrapper {
            padding-bottom: 3rem;
        }
        
        .cart-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 2rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Cart Items */
        .cart-items {
            max-height: 500px;
            overflow-y: auto;
            padding-right: 0.5rem;
        }
        
        .cart-items::-webkit-scrollbar {
            width: 5px;
        }
        
        .cart-items::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        
        .cart-items::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 10px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            position: relative;
        }
        
        .cart-item:hover {
            transform: translateX(5px);
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.15);
        }
        
        .cart-item-image {
            width: 100px;
            height: 100px;
            border-radius: 15px;
            object-fit: cover;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 0.5rem;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .cart-item-title:hover {
            color: var(--primary);
        }
        
        .cart-item-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        .cart-item-stock {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: #f1f5f9;
            border-radius: 20px;
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .stock-in {
            color: var(--success);
        }
        
        .stock-low {
            color: var(--warning);
        }
        
        /* Quantity Control */
        .quantity-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: #f1f5f9;
            border-radius: 50px;
            padding: 0.3rem;
        }
        
        .quantity-btn {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            border: none;
            background: white;
            color: var(--primary);
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .quantity-btn:hover:not(:disabled) {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
        }
        
        .quantity-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .quantity-input {
            width: 50px;
            text-align: center;
            border: none;
            background: transparent;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .quantity-input:focus {
            outline: none;
        }
        
        /* Item Total */
        .item-total {
            text-align: right;
            min-width: 120px;
        }
        
        .item-total-label {
            font-size: 0.8rem;
            color: var(--secondary);
            margin-bottom: 0.2rem;
        }
        
        .item-total-value {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark);
        }
        
        /* Remove Button */
        .btn-remove {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #fee2e2;
            background: white;
            color: var(--danger);
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .btn-remove:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
            transform: scale(1.1);
        }
        
        /* Cart Summary */
        .cart-summary {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 20px;
            padding: 2rem;
            position: sticky;
            top: 100px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .summary-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .summary-title i {
            width: 35px;
            height: 35px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        
        .summary-row:last-child {
            border-bottom: none;
        }
        
        .summary-total {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin: 1.5rem 0;
            padding: 1rem 0;
            border-top: 2px solid #e2e8f0;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1rem;
            font-weight: 600;
            font-size: 1.1rem;
            width: 100%;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .btn-checkout:before {
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
        
        .btn-checkout:hover:before {
            width: 300px;
            height: 300px;
        }
        
        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 30px rgba(34, 197, 94, 0.3);
        }
        
        .btn-continue {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 0.8rem;
            color: var(--secondary);
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        
        .btn-continue:hover {
            border-color: var(--primary);
            color: var(--primary);
        }
        
        .btn-clear {
            background: white;
            border: 2px solid #fee2e2;
            border-radius: 15px;
            padding: 0.8rem;
            color: var(--danger);
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        
        .btn-clear:hover {
            background: var(--danger);
            color: white;
            border-color: var(--danger);
        }
        
        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-cart i {
            font-size: 6rem;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }
        
        .empty-cart h3 {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
        }
        
        .empty-cart p {
            color: var(--secondary);
            margin-bottom: 2rem;
        }
        
        .btn-shop {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-shop:hover {
            transform: scale(1.05);
            box-shadow: 0 20px 30px rgba(79, 70, 229, 0.3);
        }
        
        /* Shipping Progress */
        .shipping-progress {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .progress-bar-custom {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            margin: 1rem 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary) 0%, #764ba2 100%);
            border-radius: 4px;
            width: <?php echo min(($subtotal / 500) * 100, 100); ?>%;
            transition: width 0.3s;
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
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> ตะกร้า
                            <span class="cart-badge" id="cartCount"><?php echo count($cart_items); ?></span>
                        </a>
                    </li>
                    <?php if(isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user"></i> บัญชีของฉัน
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="cart-wrapper">
        <div class="container">
            <h1 class="page-title">
                <i class="fas fa-shopping-cart me-3"></i>
                ตะกร้าสินค้า
            </h1>
            
            <?php if(!empty($cart_items)): ?>
                <!-- Shipping Progress -->
                <div class="shipping-progress">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-truck text-primary me-2"></i>
                            <strong>จัดส่งฟรีเมื่อสั่งซื้อครบ ฿500</strong>
                        </div>
                        <span>ยอดปัจจุบัน ฿<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="progress-bar-custom">
                        <div class="progress-fill"></div>
                    </div>
                    <?php if($subtotal < 500): ?>
                        <small class="text-muted">
							เหลืออีก ฿<?php echo number_format(500 - $subtotal, 2); ?> เพื่อรับสิทธิ์จัดส่งฟรี
                        </small>
                    <?php else: ?>
                        <small class="text-success">
                            <i class="fas fa-check-circle"></i> คุณได้รับสิทธิ์จัดส่งฟรีแล้ว!
                        </small>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-container">
                        <?php if(empty($cart_items)): ?>
                            <div class="empty-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <h3>ตะกร้าสินค้าของคุณว่างเปล่า</h3>
                                <p>เริ่มช้อปปิ้งกับเราได้เลย! สินค้าคุณภาพมากมายรอคุณอยู่</p>
                                <a href="products.php" class="btn-shop">
                                    <i class="fas fa-store me-2"></i>
                                    เริ่มช้อปปิ้ง
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="cart-items">
                                <?php foreach($cart_items as $item): ?>
                                <div class="cart-item" id="cart-item-<?php echo $item['id']; ?>">
                                    <img src="images/products/<?php echo $item['image'] ?? 'default.jpg'; ?>" 
                                         class="cart-item-image" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    
                                    <div class="cart-item-details">
                                        <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" class="cart-item-title">
                                            <?php echo htmlspecialchars($item['name']); ?>
                                        </a>
                                        <div class="cart-item-price">
                                            ฿<?php echo number_format($item['price'], 2); ?>
                                        </div>
                                        <div class="cart-item-stock">
                                            <i class="fas fa-<?php echo $item['stock'] > 5 ? 'check-circle text-success' : 'exclamation-circle text-warning'; ?> me-1"></i>
                                            คงเหลือ <?php echo $item['stock']; ?> ชิ้น
                                        </div>
                                    </div>
                                    
                                    <div class="quantity-wrapper">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'decrease')" 
                                                <?php echo $item['quantity'] <= 1 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" class="quantity-input" id="quantity-<?php echo $item['id']; ?>" 
                                               value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>"
                                               onchange="updateQuantity(<?php echo $item['id']; ?>, 'set')" readonly>
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['id']; ?>, 'increase')"
                                                <?php echo $item['quantity'] >= $item['stock'] ? 'disabled' : ''; ?>>
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="item-total">
                                        <div class="item-total-label">รวม</div>
                                        <div class="item-total-value" id="item-total-<?php echo $item['id']; ?>">
                                            ฿<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                                        </div>
                                    </div>
                                    
                                    <button class="btn-remove" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="mt-4 d-flex justify-content-between align-items-center">
                                <a href="products.php" class="text-primary">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    เลือกสินค้าเพิ่มเติม
                                </a>
                                <button class="btn-clear" onclick="clearCart()">
                                    <i class="fas fa-trash-alt me-2"></i>
                                    ล้างตะกร้าทั้งหมด
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <?php if(!empty($cart_items)): ?>
                <div class="col-lg-4">
                    <div class="cart-summary">
                        <h5 class="summary-title">
                            <i class="fas fa-receipt"></i>
                            สรุปคำสั่งซื้อ
                        </h5>
                        
                        <div class="summary-row">
                            <span>ยอดรวมสินค้า:</span>
                            <span id="subtotal">฿<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>ค่าจัดส่ง:</span>
                            <span>฿<?php echo number_format($shipping, 2); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>ภาษี (7%):</span>
                            <span>฿<?php echo number_format($subtotal * 0.07, 2); ?></span>
                        </div>
                        
                        <div class="summary-total d-flex justify-content-between">
                            <span>ยอดสุทธิ:</span>
                            <span id="total">฿<?php echo number_format($total + ($subtotal * 0.07), 2); ?></span>
                        </div>
                        
                        <button class="btn-checkout" onclick="proceedToCheckout()">
                            <i class="fas fa-lock me-2"></i>
                            ดำเนินการชำระเงิน
                        </button>
                        
                        <a href="products.php" class="btn-continue">
                            <i class="fas fa-shopping-bag me-2"></i>
                            เลือกซื้อสินค้าเพิ่ม
                        </a>
                        
                        <!-- Secure Payment Badge -->
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt text-success me-1"></i>
                                การชำระเงินปลอดภัยด้วย SSL
                            </small>
                        </div>
                        
                        <!-- Payment Methods -->
                        <div class="mt-3 text-center">
                            <i class="fab fa-cc-visa fa-2x mx-1 text-primary"></i>
                            <i class="fab fa-cc-mastercard fa-2x mx-1 text-danger"></i>
                            <i class="fab fa-cc-paypal fa-2x mx-1 text-info"></i>
                            <i class="fas fa-money-bill-wave fa-2x mx-1 text-success"></i>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
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
        function updateQuantity(cartId, action) {
            const quantityInput = document.getElementById(`quantity-${cartId}`);
            let newQuantity = parseInt(quantityInput.value);
            const maxStock = parseInt(quantityInput.max);
            
            if (action === 'increase' && newQuantity < maxStock) {
                newQuantity++;
            } else if (action === 'decrease' && newQuantity > 1) {
                newQuantity--;
            } else if (action === 'set') {
                newQuantity = Math.min(Math.max(parseInt(quantityInput.value) || 1, 1), maxStock);
            }
            
            quantityInput.value = newQuantity;
            
            // Update button states
            const decreaseBtn = quantityInput.parentElement.querySelector('.quantity-btn:first-child');
            const increaseBtn = quantityInput.parentElement.querySelector('.quantity-btn:last-child');
            
            decreaseBtn.disabled = newQuantity <= 1;
            increaseBtn.disabled = newQuantity >= maxStock;
            
            $.ajax({
                url: 'ajax/update_cart.php',
                method: 'POST',
                data: { cart_id: cartId, quantity: newQuantity },
                success: function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        updateCartTotals();
                        updateCartCount();
                        document.getElementById(`item-total-${cartId}`).innerHTML = 
                            '฿' + result.item_total.toFixed(2);
                    }
                }
            });
        }
        
        function removeFromCart(cartId) {
            if(confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้านี้ออกจากตะกร้า?')) {
                $.ajax({
                    url: 'ajax/remove_from_cart.php',
                    method: 'POST',
                    data: { cart_id: cartId },
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            const item = document.getElementById(`cart-item-${cartId}`);
                            item.style.animation = 'slideOut 0.3s ease';
                            setTimeout(() => {
                                item.remove();
                                updateCartTotals();
                                updateCartCount();
                                
                                if(result.cart_empty) {
                                    location.reload();
                                }
                            }, 300);
                        }
                    }
                });
            }
        }
        
        function clearCart() {
            if(confirm('คุณแน่ใจหรือไม่ที่จะลบสินค้าทั้งหมดออกจากตะกร้า?')) {
                $.ajax({
                    url: 'ajax/clear_cart.php',
                    method: 'POST',
                    success: function(response) {
                        const result = JSON.parse(response);
                        if (result.success) {
                            location.reload();
                        }
                    }
                });
            }
        }
        
        function updateCartTotals() {
            $.ajax({
                url: 'ajax/get_cart_totals.php',
                method: 'GET',
                success: function(response) {
                    const result = JSON.parse(response);
                    document.getElementById('subtotal').innerHTML = '฿' + result.subtotal.toFixed(2);
                    
                    // Calculate tax and total
                    const tax = result.subtotal * 0.07;
                    const shipping = 50;
                    const total = result.subtotal + shipping + tax;
                    
                    document.getElementById('total').innerHTML = '฿' + total.toFixed(2);
                }
            });
        }
        
        function updateCartCount() {
            $.ajax({
                url: 'ajax/get_cart_count.php',
                method: 'GET',
                success: function(response) {
                    document.getElementById('cartCount').textContent = response;
                }
            });
        }
        
        function proceedToCheckout() {
            window.location.href = 'checkout.php';
        }
        
        // Add slide out animation
        const style = document.createElement('style');
        style.innerHTML = `
            @keyframes slideOut {
                to {
                    opacity: 0;
                    transform: translateX(100%);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>