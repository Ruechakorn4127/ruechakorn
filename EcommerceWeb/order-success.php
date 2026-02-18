<?php
require_once 'config/database.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

if(!isset($_GET['order_id'])) {
    redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT o.*, u.first_name, u.last_name, u.email 
          FROM orders o
          JOIN users u ON o.user_id = u.id
          WHERE o.id = :order_id AND o.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':order_id', $_GET['order_id']);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) {
    redirect('index.php');
}

// Get order items count
$items_query = "SELECT COUNT(*) as count, SUM(quantity) as total_items 
                FROM order_items WHERE order_id = :order_id";
$items_stmt = $db->prepare($items_query);
$items_stmt->bindParam(':order_id', $_GET['order_id']);
$items_stmt->execute();
$items_info = $items_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งซื้อสำเร็จ - E-Store</title>
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
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --light: #f8fafc;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        
        .success-wrapper {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .success-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .success-card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary) 0%, #764ba2 50%, var(--success) 100%);
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, var(--success) 0%, #16a34a 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3.5rem;
            margin: 0 auto 2rem;
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.3);
            animation: scaleIn 0.5s ease;
        }
        
        @keyframes scaleIn {
            from {
                transform: scale(0);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        
        .success-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.5rem;
            animation: fadeInUp 0.5s ease 0.2s both;
        }
        
        .success-subtitle {
            color: var(--dark);
            font-size: 1.2rem;
            margin-bottom: 2rem;
            animation: fadeInUp 0.5s ease 0.3s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .order-number {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 50px;
            padding: 1rem 2rem;
            display: inline-block;
            margin-bottom: 2rem;
            border: 1px solid rgba(79, 70, 229, 0.2);
            animation: fadeInUp 0.5s ease 0.4s both;
        }
        
        .order-number-label {
            color: var(--secondary);
            font-size: 0.9rem;
            margin-right: 1rem;
        }
        
        .order-number-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: 2px;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
            margin: 2rem 0;
            padding: 2rem 0;
            border-top: 2px dashed #e2e8f0;
            border-bottom: 2px dashed #e2e8f0;
            animation: fadeInUp 0.5s ease 0.5s both;
        }
        
        .info-item {
            text-align: center;
        }
        
        .info-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin: 0 auto 1rem;
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
        
        .email-alert {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-radius: 15px;
            padding: 1.2rem;
            margin: 2rem 0;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: fadeInUp 0.5s ease 0.6s both;
        }
        
        .email-alert i {
            font-size: 2rem;
            color: #2563eb;
        }
        
        .email-alert-content {
            flex: 1;
            text-align: left;
        }
        
        .email-alert-title {
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 0.2rem;
        }
        
        .email-alert-text {
            color: #3b82f6;
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
            animation: fadeInUp 0.5s ease 0.7s both;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px rgba(79, 70, 229, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-3px);
        }
        
        .print-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .btn-print {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 0.8rem 1.5rem;
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
        
        /* Confetti Animation */
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--primary);
            opacity: 0;
            animation: confetti 3s ease-out forwards;
        }
        
        @keyframes confetti {
            0% {
                transform: translateY(0) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100vh) rotate(720deg);
                opacity: 0;
            }
        }
        
        @media (max-width: 768px) {
            .success-card {
                padding: 2rem;
            }
            
            .order-info {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .success-title {
                font-size: 2rem;
            }
            
            .order-number-value {
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Confetti Animation -->
    <div id="confetti-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 9999;"></div>

    <div class="container">
        <div class="success-wrapper">
            <div class="success-card">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                
                <h1 class="success-title">สั่งซื้อสำเร็จ!</h1>
                <p class="success-subtitle">ขอบคุณที่ไว้วางใจเลือกซื้อสินค้ากับเรา</p>
                
                <div class="order-number">
                    <span class="order-number-label">เลขที่คำสั่งซื้อ</span>
                    <span class="order-number-value">#<?php echo $order['order_number']; ?></span>
                </div>
                
                <div class="order-info">
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="info-label">จำนวนสินค้า</div>
                        <div class="info-value"><?php echo $items_info['total_items']; ?> ชิ้น</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="info-label">ยอดชำระเงิน</div>
                        <div class="info-value">฿<?php echo number_format($order['total_amount'], 2); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="info-label">วันที่สั่งซื้อ</div>
                        <div class="info-value"><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></div>
                    </div>
                </div>
                
                <div class="email-alert">
                    <i class="fas fa-envelope-open-text"></i>
                    <div class="email-alert-content">
                        <div class="email-alert-title">ยืนยันทางอีเมล</div>
                        <div class="email-alert-text">ระบบได้ส่งรายละเอียดคำสั่งซื้อไปยัง <strong><?php echo $order['email']; ?></strong></div>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn-primary">
                        <i class="fas fa-eye"></i>
                        ดูรายละเอียดคำสั่งซื้อ
                    </a>
                    <a href="products.php" class="btn-outline">
                        <i class="fas fa-shopping-bag"></i>
                        สั่งซื้อเพิ่มเติม
                    </a>
                </div>
                
                <div class="print-section">
                    <button onclick="window.print()" class="btn-print">
                        <i class="fas fa-print"></i>
                        พิมพ์ใบสั่งซื้อ
                    </button>
                </div>
            </div>
            
            <!-- Recommended Products -->
            <div class="text-center mt-5">
                <p class="text-white mb-3">หรือเลือกชมสินค้าแนะนำ</p>
                <a href="products.php" class="text-white">
                    <i class="fas fa-arrow-right me-2"></i>
                    ดูสินค้าแนะนำ
                </a>
            </div>
        </div>
    </div>

    <script>
        // Create confetti effect
        function createConfetti() {
            const container = document.getElementById('confetti-container');
            const colors = ['#4f46e5', '#764ba2', '#22c55e', '#f59e0b', '#ef4444'];
            
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.animationDelay = Math.random() * 2 + 's';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.width = Math.random() * 10 + 5 + 'px';
                    confetti.style.height = confetti.style.width;
                    container.appendChild(confetti);
                    
                    setTimeout(() => {
                        confetti.remove();
                    }, 3000);
                }, i * 100);
            }
        }
        
        // Run confetti on page load
        window.onload = createConfetti;
    </script>
</body>
</html>