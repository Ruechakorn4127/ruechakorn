<?php
require_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    
    // ตรวจสอบข้อมูล
    if($password != $confirm_password) {
        $error = "รหัสผ่านไม่ตรงกัน";
    } elseif(strlen($password) < 6) {
        $error = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
    } else {
        // ตรวจสอบว่ามีผู้ใช้ซ้ำหรือไม่
        $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':username', $username);
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();
        
        if($check_stmt->rowCount() > 0) {
            $error = "มีชื่อผู้ใช้หรืออีเมลนี้ในระบบแล้ว";
        } else {
            // สร้างผู้ดูแลระบบใหม่
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (username, email, password, first_name, last_name, role) 
                      VALUES (:username, :email, :password, :first_name, :last_name, 'admin')";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            
            if($stmt->execute()) {
                $message = "สร้างผู้ดูแลระบบสำเร็จ!";
            } else {
                $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
            }
        }
    }
}

// ดึงรายชื่อผู้ดูแลระบบทั้งหมด
$admin_query = "SELECT id, username, email, first_name, last_name, created_at FROM users WHERE role = 'admin'";
$admin_stmt = $db->prepare($admin_query);
$admin_stmt->execute();
$admins = $admin_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สร้างผู้ดูแลระบบ - E-Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Prompt', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 50px 0;
        }
        
        .container {
            max-width: 800px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 2.5rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }
        
        h2 {
            color: white;
            font-weight: 700;
            margin-bottom: 2rem;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--dark);
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 0.8rem 1.2rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            padding: 1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 30px rgba(79, 70, 229, 0.3);
        }
        
        .alert {
            border-radius: 15px;
            padding: 1rem;
        }
        
        .table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .table th {
            background: #f8fafc;
            font-weight: 600;
        }
        
        .login-link {
            text-align: center;
            margin-top: 2rem;
        }
        
        .login-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>
            <i class="fas fa-user-shield me-3"></i>
            สร้างผู้ดูแลระบบ
        </h2>
        
        <?php if($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST">
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-user text-primary me-2"></i>ชื่อ
                        </label>
                        <input type="text" class="form-control" name="first_name" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-user text-primary me-2"></i>นามสกุล
                        </label>
                        <input type="text" class="form-control" name="last_name" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-at text-primary me-2"></i>Username
                        </label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-envelope text-primary me-2"></i>อีเมล
                        </label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-lock text-primary me-2"></i>รหัสผ่าน
                        </label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-lock text-primary me-2"></i>ยืนยันรหัสผ่าน
                        </label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    
                    <div class="col-12">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-user-plus me-2"></i>
                            สร้างผู้ดูแลระบบ
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if(!empty($admins)): ?>
        <div class="card mt-4">
            <h5 class="mb-4">
                <i class="fas fa-list me-2"></i>
                รายชื่อผู้ดูแลระบบ
            </h5>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ชื่อ</th>
                            <th>Username</th>
                            <th>อีเมล</th>
                            <th>วันที่สร้าง</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($admins as $admin): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                            <td><?php echo htmlspecialchars($admin['email']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($admin['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="login-link">
            <a href="../login.php">
                <i class="fas fa-arrow-left me-2"></i>
                กลับไปหน้าเข้าสู่ระบบ
            </a>
        </div>
    </div>
</body>
</html>