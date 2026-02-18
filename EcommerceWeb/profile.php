<?php
require_once 'config/database.php';

if(!isLoggedIn()) {
    redirect('login.php');
}

$database = new Database();
$db = $database->getConnection();

// Get user data
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get order history with items count
$order_query = "SELECT o.*, 
                (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
                FROM orders o 
                WHERE o.user_id = :user_id 
                ORDER BY o.created_at DESC";
$order_stmt = $db->prepare($order_query);
$order_stmt->bindParam(':user_id', $_SESSION['user_id']);
$order_stmt->execute();
$orders = $order_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total spent
$total_spent_query = "SELECT SUM(total_amount) as total FROM orders WHERE user_id = :user_id AND payment_status = 'completed'";
$total_spent_stmt = $db->prepare($total_spent_query);
$total_spent_stmt->bindParam(':user_id', $_SESSION['user_id']);
$total_spent_stmt->execute();
$total_spent = $total_spent_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Handle profile update
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    
    $update_query = "UPDATE users SET first_name = :first_name, last_name = :last_name, 
                     phone = :phone, address = :address, city = :city, 
                     postal_code = :postal_code, country = :country 
                     WHERE id = :user_id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':first_name', $first_name);
    $update_stmt->bindParam(':last_name', $last_name);
    $update_stmt->bindParam(':phone', $phone);
    $update_stmt->bindParam(':address', $address);
    $update_stmt->bindParam(':city', $city);
    $update_stmt->bindParam(':postal_code', $postal_code);
    $update_stmt->bindParam(':country', $country);
    $update_stmt->bindParam(':user_id', $_SESSION['user_id']);
    
    if($update_stmt->execute()) {
        $_SESSION['success'] = "อัปเดตโปรไฟล์สำเร็จ";
        redirect('profile.php');
    }
}

// Handle password change
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if($new_password != $confirm_password) {
        $error = "รหัสผ่านใหม่ไม่ตรงกัน";
    } elseif(strlen($new_password) < 6) {
        $error = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } else {
        // Verify current password
        $pass_query = "SELECT password FROM users WHERE id = :user_id";
        $pass_stmt = $db->prepare($pass_query);
        $pass_stmt->bindParam(':user_id', $_SESSION['user_id']);
        $pass_stmt->execute();
        $user_data = $pass_stmt->fetch(PDO::FETCH_ASSOC);
        
        if(password_verify($current_password, $user_data['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_pass_query = "UPDATE users SET password = :password WHERE id = :user_id";
            $update_pass_stmt = $db->prepare($update_pass_query);
            $update_pass_stmt->bindParam(':password', $hashed_password);
            $update_pass_stmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if($update_pass_stmt->execute()) {
                $_SESSION['success'] = "เปลี่ยนรหัสผ่านสำเร็จ";
                redirect('profile.php');
            }
        } else {
            $error = "รหัสผ่านปัจจุบันไม่ถูกต้อง";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรไฟล์ของฉัน - E-Store</title>
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
        
        /* Page Title */
        .page-title {
            color: white;
            font-weight: 700;
            font-size: 2.5rem;
            margin: 2rem 0;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        /* Profile Sidebar */
        .profile-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 2rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: sticky;
            top: 100px;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.3);
        }
        
        .profile-avatar i {
            font-size: 5rem;
            color: white;
        }
        
        .avatar-upload {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            text-align: center;
            padding: 0.5rem;
            cursor: pointer;
            transform: translateY(100%);
            transition: all 0.3s;
        }
        
        .profile-avatar:hover .avatar-upload {
            transform: translateY(0);
        }
        
        .profile-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .profile-email {
            color: var(--secondary);
            margin-bottom: 1.5rem;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .profile-stats {
            display: flex;
            justify-content: space-around;
            padding: 1.5rem 0;
            border-top: 2px solid #e2e8f0;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
            line-height: 1.2;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .profile-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .profile-menu li {
            margin-bottom: 0.5rem;
        }
        
        .profile-menu a {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            border-radius: 15px;
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s;
            gap: 1rem;
        }
        
        .profile-menu a i {
            width: 25px;
            font-size: 1.2rem;
        }
        
        .profile-menu a:hover,
        .profile-menu a.active {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
            transform: translateX(5px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
        }
        
        .profile-menu a.logout {
            color: var(--danger);
            margin-top: 1rem;
        }
        
        .profile-menu a.logout:hover {
            background: var(--danger);
            color: white;
        }
        
        /* Profile Content */
        .profile-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 2.5rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .section-title i {
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
        
        .form-control[readonly] {
            background: #f1f5f9;
            cursor: not-allowed;
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
            margin-top: 1rem;
        }
        
        .btn-save:before {
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
        
        .btn-save:hover:before {
            width: 300px;
            height: 300px;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 30px rgba(79, 70, 229, 0.3);
        }
        
        .btn-save i {
            margin-right: 0.5rem;
        }
        
        /* Order Cards */
        .order-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            border: 1px solid transparent;
        }
        
        .order-card:hover {
            transform: translateX(5px);
            border-color: var(--primary);
            box-shadow: 0 10px 30px rgba(79, 70, 229, 0.1);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 2px dashed #e2e8f0;
        }
        
        .order-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary);
        }
        
        .order-date {
            color: var(--secondary);
            font-size: 0.9rem;
        }
        
        .order-date i {
            margin-right: 0.3rem;
        }
        
        .order-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-info {
            display: flex;
            gap: 2rem;
        }
        
        .order-info-item {
            text-align: center;
        }
        
        .order-info-label {
            font-size: 0.8rem;
            color: var(--secondary);
        }
        
        .order-info-value {
            font-weight: 600;
            color: var(--dark);
        }
        
        .order-status {
            display: inline-block;
            padding: 0.5rem 1.2rem;
            border-radius: 25px;
            font-size: 0.9rem;
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
        
        .btn-view {
            background: white;
            border: 2px solid var(--primary);
            border-radius: 25px;
            padding: 0.5rem 1.5rem;
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-view:hover {
            background: var(--primary);
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-state i {
            font-size: 5rem;
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
        }
        
        .empty-state h5 {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
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
        
        /* Password Strength */
        .password-strength {
            margin-top: 0.5rem;
            height: 5px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: all 0.3s;
        }
        
        .strength-weak {
            background: var(--danger);
        }
        
        .strength-medium {
            background: var(--warning);
        }
        
        .strength-strong {
            background: var(--success);
        }
        
        /* Alert */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
        }
        
        /* Footer */
        .footer {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: 4rem;
        }
        
        .footer a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer a:hover {
            color: white;
            padding-left: 5px;
        }
        
        /* Animation */
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
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i> ตะกร้า
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="page-title">
            <i class="fas fa-user-circle me-3"></i>
            โปรไฟล์ของฉัน
        </h1>
        
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
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                        <div class="avatar-upload">
                            <i class="fas fa-camera"></i> เปลี่ยนรูป
                        </div>
                    </div>
                    
                    <div class="profile-name">
                        <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                    </div>
                    <div class="profile-email">
                        <i class="fas fa-envelope me-2"></i>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo count($orders); ?></div>
                            <div class="stat-label">คำสั่งซื้อ</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">฿<?php echo number_format($total_spent, 0); ?></div>
                            <div class="stat-label">ยอดใช้จ่าย</div>
                        </div>
                    </div>
                    
                    <ul class="profile-menu">
                        <li>
                            <a href="#profile" class="active" onclick="showSection('profile', event)">
                                <i class="fas fa-user"></i>
                                ข้อมูลส่วนตัว
                            </a>
                        </li>
                        <li>
                            <a href="#orders" onclick="showSection('orders', event)">
                                <i class="fas fa-shopping-bag"></i>
                                ประวัติการสั่งซื้อ
                            </a>
                        </li>
                        <li>
                            <a href="#password" onclick="showSection('password', event)">
                                <i class="fas fa-key"></i>
                                เปลี่ยนรหัสผ่าน
                            </a>
                        </li>
                        <li>
                            <a href="logout.php" class="logout">
                                <i class="fas fa-sign-out-alt"></i>
                                ออกจากระบบ
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Content -->
            <div class="col-lg-8">
                <!-- Profile Information Section -->
                <div id="profile-section" class="profile-content fade-in">
                    <h5 class="section-title">
                        <i class="fas fa-user-edit"></i>
                        ข้อมูลส่วนตัว
                    </h5>
                    
                    <form method="POST">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user text-primary me-2"></i>ชื่อ
                                </label>
                                <input type="text" class="form-control" name="first_name" 
                                       value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-user text-primary me-2"></i>นามสกุล
                                </label>
                                <input type="text" class="form-control" name="last_name" 
                                       value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-envelope text-primary me-2"></i>อีเมล
                                </label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <small class="text-muted">ไม่สามารถเปลี่ยนแปลงอีเมลได้</small>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    <i class="fas fa-phone text-primary me-2"></i>เบอร์โทรศัพท์
                                </label>
                                <input type="text" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone']); ?>" 
                                       placeholder="081-234-5678">
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt text-primary me-2"></i>ที่อยู่
                                </label>
                                <textarea class="form-control" name="address" rows="3" 
                                          placeholder="บ้านเลขที่ ถนน ตำบล/แขวง"><?php echo htmlspecialchars($user['address']); ?></textarea>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-city text-primary me-2"></i>จังหวัด
                                </label>
                                <input type="text" class="form-control" name="city" 
                                       value="<?php echo htmlspecialchars($user['city']); ?>" 
                                       placeholder="กรุงเทพฯ">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-mail-bulk text-primary me-2"></i>รหัสไปรษณีย์
                                </label>
                                <input type="text" class="form-control" name="postal_code" 
                                       value="<?php echo htmlspecialchars($user['postal_code']); ?>" 
                                       placeholder="10110">
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">
                                    <i class="fas fa-globe text-primary me-2"></i>ประเทศ
                                </label>
                                <input type="text" class="form-control" name="country" 
                                       value="<?php echo htmlspecialchars($user['country'] ?: 'Thailand'); ?>">
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" name="update_profile" class="btn-save">
                                    <i class="fas fa-save"></i>
                                    บันทึกข้อมูล
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Order History Section -->
                <div id="orders-section" class="profile-content" style="display: none;">
                    <h5 class="section-title">
                        <i class="fas fa-history"></i>
                        ประวัติการสั่งซื้อ
                    </h5>
                    
                    <?php if(empty($orders)): ?>
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <h5>ยังไม่มีประวัติการสั่งซื้อ</h5>
                            <p>เริ่มช้อปปิ้งกับเราเพื่อสะสมประวัติการสั่งซื้อ!</p>
                            <a href="products.php" class="btn-shop">
                                <i class="fas fa-store me-2"></i>
                                เริ่มช้อปปิ้ง
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach($orders as $order): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div>
                                    <span class="order-number">#<?php echo $order['order_number']; ?></span>
                                </div>
                                <div class="order-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <div class="order-info">
                                    <div class="order-info-item">
                                        <div class="order-info-label">จำนวนสินค้า</div>
                                        <div class="order-info-value"><?php echo $order['item_count']; ?> ชิ้น</div>
                                    </div>
                                    <div class="order-info-item">
                                        <div class="order-info-label">ยอดชำระ</div>
                                        <div class="order-info-value">฿<?php echo number_format($order['total_amount'], 2); ?></div>
                                    </div>
                                    <div class="order-info-item">
                                        <div class="order-info-label">การชำระเงิน</div>
                                        <div class="order-info-value">
                                            <span class="badge bg-<?php echo $order['payment_status'] == 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo $order['payment_status'] == 'completed' ? 'ชำระแล้ว' : 'รอชำระ'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center gap-3">
                                    <span class="order-status status-<?php echo $order['order_status']; ?>">
                                        <?php 
                                            $status_th = [
                                                'pending' => 'รอตรวจสอบ',
                                                'processing' => 'กำลังดำเนินการ',
                                                'shipped' => 'จัดส่งแล้ว',
                                                'delivered' => 'ได้รับสินค้าแล้ว',
                                                'cancelled' => 'ยกเลิก'
                                            ];
                                            echo $status_th[$order['order_status']] ?? $order['order_status'];
                                        ?>
                                    </span>
                                    <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn-view">
                                        <i class="fas fa-eye me-1"></i>
                                        ดูรายละเอียด
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Change Password Section -->
                <div id="password-section" class="profile-content" style="display: none;">
                    <h5 class="section-title">
                        <i class="fas fa-lock"></i>
                        เปลี่ยนรหัสผ่าน
                    </h5>
                    
                    <form method="POST" id="passwordForm">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-lock text-primary me-2"></i>รหัสผ่านปัจจุบัน
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-2 border-end-0">
                                        <i class="fas fa-lock text-primary"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" name="current_password" 
                                           id="current_password" required placeholder="กรอกรหัสผ่านปัจจุบัน">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-key text-primary me-2"></i>รหัสผ่านใหม่
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-2 border-end-0">
                                        <i class="fas fa-key text-primary"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" name="new_password" 
                                           id="new_password" required placeholder="อย่างน้อย 6 ตัวอักษร">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="password-strength mt-2">
                                    <div class="password-strength-bar" id="passwordStrength"></div>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">
                                    <i class="fas fa-check-circle text-primary me-2"></i>ยืนยันรหัสผ่านใหม่
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-2 border-end-0">
                                        <i class="fas fa-check-circle text-primary"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0" name="confirm_password" 
                                           id="confirm_password" required placeholder="กรอกรหัสผ่านอีกครั้ง">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="text-muted" id="passwordMatch"></small>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" name="change_password" class="btn-save" id="submitBtn">
                                    <i class="fas fa-key"></i>
                                    เปลี่ยนรหัสผ่าน
                                </button>
                            </div>
                        </div>
                    </form>
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
        function showSection(section, event) {
            event.preventDefault();
            
            // Hide all sections with animation
            document.querySelectorAll('.profile-content').forEach(el => {
                el.style.opacity = '0';
                setTimeout(() => {
                    el.style.display = 'none';
                }, 200);
            });
            
            // Show selected section
            setTimeout(() => {
                const selectedSection = document.getElementById(section + '-section');
                selectedSection.style.display = 'block';
                setTimeout(() => {
                    selectedSection.style.opacity = '1';
                }, 50);
            }, 200);
            
            // Update active menu
            document.querySelectorAll('.profile-menu a').forEach(el => {
                el.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
        }
        
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const button = input.nextElementSibling;
            const icon = button.querySelector('i');
            
            if(input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
        
        // Password strength checker
        const newPassword = document.getElementById('new_password');
        if(newPassword) {
            newPassword.addEventListener('input', function() {
                const password = this.value;
                const strengthBar = document.getElementById('passwordStrength');
                let strength = 0;
                
                if(password.length >= 6) strength += 25;
                if(password.match(/[a-z]+/)) strength += 25;
                if(password.match(/[A-Z]+/)) strength += 25;
                if(password.match(/[0-9]+/)) strength += 25;
                
                strengthBar.style.width = strength + '%';
                
                if(strength <= 25) {
                    strengthBar.className = 'password-strength-bar strength-weak';
                } else if(strength <= 50) {
                    strengthBar.className = 'password-strength-bar strength-medium';
                } else {
                    strengthBar.className = 'password-strength-bar strength-strong';
                }
            });
        }
        
        // Password match checker
        const confirmPassword = document.getElementById('confirm_password');
        if(confirmPassword) {
            confirmPassword.addEventListener('input', function() {
                const password = document.getElementById('new_password').value;
                const confirm = this.value;
                const matchMsg = document.getElementById('passwordMatch');
                
                if(password === confirm) {
                    matchMsg.innerHTML = '<i class="fas fa-check-circle text-success"></i> รหัสผ่านตรงกัน';
                    matchMsg.className = 'text-success';
                } else {
                    matchMsg.innerHTML = '<i class="fas fa-times-circle text-danger"></i> รหัสผ่านไม่ตรงกัน';
                    matchMsg.className = 'text-danger';
                }
            });
        }
        
        // Form validation
        const passwordForm = document.getElementById('passwordForm');
        if(passwordForm) {
            passwordForm.addEventListener('submit', function(e) {
                const newPass = document.getElementById('new_password').value;
                const confirmPass = document.getElementById('confirm_password').value;
                
                if(newPass !== confirmPass) {
                    e.preventDefault();
                    alert('รหัสผ่านใหม่ไม่ตรงกัน กรุณาตรวจสอบอีกครั้ง');
                }
                
                if(newPass.length < 6) {
                    e.preventDefault();
                    alert('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                }
            });
        }
    </script>
</body>
</html>