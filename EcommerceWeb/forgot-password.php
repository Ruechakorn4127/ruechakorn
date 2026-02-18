<?php
require_once 'config/database.php';

if(isLoggedIn()) {
    redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

$step = 1; // 1: กรอกข้อมูล, 2: เปลี่ยนรหัสผ่าน
$username = '';
$email = '';
$phone = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['verify_identity'])) {
        // ขั้นตอนที่ 1: ตรวจสอบข้อมูลผู้ใช้
        $username = $_POST['username'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        
        $query = "SELECT * FROM users WHERE username = :username AND email = :email AND phone = :phone";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_username'] = $user['username'];
            $step = 2;
        } else {
            $error = "ไม่พบข้อมูลผู้ใช้ กรุณาตรวจสอบอีกครั้ง";
        }
    }
    elseif(isset($_POST['reset_password'])) {
        // ขั้นตอนที่ 2: เปลี่ยนรหัสผ่าน
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $user_id = $_SESSION['reset_user_id'];
        
        if($password != $confirm_password) {
            $error = "รหัสผ่านไม่ตรงกัน";
        }
        elseif(strlen($password) < 6) {
            $error = "รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร";
        }
        else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':id', $user_id);
            
            if($stmt->execute()) {
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_username']);
                $_SESSION['success'] = "เปลี่ยนรหัสผ่านสำเร็จ! กรุณาเข้าสู่ระบบ";
                redirect('login.php');
            } else {
                $error = "เกิดข้อผิดพลาด กรุณาลองใหม่";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - E-Store</title>
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
            display: flex;
            align-items: center;
            padding: 20px 0;
        }
        
        .forgot-container {
            max-width: 500px;
            margin: 0 auto;
        }
        
        .forgot-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .forgot-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .forgot-header i {
            font-size: 4rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .forgot-header h3 {
            font-weight: 700;
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-top: 1rem;
        }
        
        .forgot-header p {
            color: var(--secondary);
        }
        
        .step-indicator {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 3rem;
            gap: 1rem;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        
        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--secondary);
            transition: all 0.3s;
        }
        
        .step.active .step-circle {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            border-color: var(--primary);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
        }
        
        .step.completed .step-circle {
            background: var(--success);
            border-color: var(--success);
            color: white;
        }
        
        .step-line {
            width: 80px;
            height: 3px;
            background: #e2e8f0;
            margin: 0 1rem;
        }
        
        .step-label {
            font-size: 0.9rem;
            color: var(--secondary);
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }
        
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
        
        .input-group-text {
            border: 2px solid #e2e8f0;
            border-radius: 15px 0 0 15px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
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
            margin-top: 1rem;
            cursor: pointer;
        }
        
        .btn-primary:before {
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
        
        .btn-primary:hover:before {
            width: 300px;
            height: 300px;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 30px rgba(79, 70, 229, 0.3);
        }
        
        .btn-primary i {
            margin-right: 0.5rem;
        }
        
        .btn-outline-primary {
            background: transparent;
            border: 2px solid var(--primary);
            color: var(--primary);
            border-radius: 15px;
            padding: 0.8rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-outline-primary:hover {
            background: var(--primary);
            color: white;
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .alert-success {
            background: #dcfce7;
            color: #16a34a;
        }
        
        .info-box {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-box i {
            font-size: 1.5rem;
            color: var(--primary);
            margin-right: 1rem;
        }
        
        .info-box p {
            margin-bottom: 0;
            color: var(--dark);
        }
        
        .back-link {
            text-align: center;
            margin-top: 2rem;
        }
        
        .back-link a {
            color: var(--secondary);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-link a:hover {
            color: var(--primary);
        }
        
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
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-container">
            <div class="forgot-card">
                <div class="forgot-header">
                    <i class="fas fa-key"></i>
                    <h3>ลืมรหัสผ่าน?</h3>
                    <p>ไม่ต้องกังวล เราช่วยคุณได้</p>
                </div>
                
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step <?php echo $step >= 1 ? 'active' : ''; ?> <?php echo $step > 1 ? 'completed' : ''; ?>">
                        <div class="step-circle">
                            <?php if($step > 1): ?>
                                <i class="fas fa-check"></i>
                            <?php else: ?>
                                1
                            <?php endif; ?>
                        </div>
                        <div class="step-label">ยืนยันตัวตน</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step <?php echo $step >= 2 ? 'active' : ''; ?>">
                        <div class="step-circle">2</div>
                        <div class="step-label">ตั้งรหัสผ่านใหม่</div>
                    </div>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($step == 1): ?>
                    <!-- ขั้นตอนที่ 1: กรอกข้อมูลยืนยันตัวตน -->
                    <div class="info-box">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt"></i>
                            <div>
                                <strong>กรอกข้อมูลเพื่อยืนยันตัวตน</strong>
                                <p class="text-muted small mt-1">กรอกข้อมูลให้ตรงกับที่เคยลงทะเบียนไว้</p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-user text-primary me-2"></i>Username *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user text-primary"></i>
                                </span>
                                <input type="text" class="form-control" name="username" required 
                                       placeholder="กรอกชื่อผู้ใช้"
                                       value="<?php echo htmlspecialchars($username); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-envelope text-primary me-2"></i>อีเมล *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-envelope text-primary"></i>
                                </span>
                                <input type="email" class="form-control" name="email" required 
                                       placeholder="กรอกอีเมลที่ลงทะเบียน"
                                       value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-phone text-primary me-2"></i>เบอร์โทรศัพท์ *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-phone text-primary"></i>
                                </span>
                                <input type="text" class="form-control" name="phone" required 
                                       placeholder="กรอกเบอร์โทรศัพท์"
                                       value="<?php echo htmlspecialchars($phone); ?>">
                            </div>
                        </div>
                        
                        <button type="submit" name="verify_identity" class="btn-primary">
                            <i class="fas fa-check-circle"></i>
                            ตรวจสอบและดำเนินการต่อ
                        </button>
                    </form>
                    
                <?php elseif($step == 2): ?>
                    <!-- ขั้นตอนที่ 2: ตั้งรหัสผ่านใหม่ -->
                    <div class="info-box">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle text-success"></i>
                            <div>
                                <strong>ยืนยันตัวตนสำเร็จ!</strong>
                                <p class="text-muted small mt-1">สวัสดีคุณ <?php echo htmlspecialchars($_SESSION['reset_username']); ?> กรุณาตั้งรหัสผ่านใหม่</p>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" id="resetForm">
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-lock text-primary me-2"></i>รหัสผ่านใหม่ *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" class="form-control" name="password" id="password" required 
                                       placeholder="อย่างน้อย 6 ตัวอักษร">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="password-strength-bar" id="passwordStrength"></div>
                            </div>
                            <small class="text-muted">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</small>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-lock text-primary me-2"></i>ยืนยันรหัสผ่านใหม่ *
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required 
                                       placeholder="กรอกรหัสผ่านอีกครั้ง">
                            </div>
                            <small class="text-muted" id="passwordMatch"></small>
                        </div>
                        
                        <button type="submit" name="reset_password" class="btn-primary" id="submitBtn">
                            <i class="fas fa-save"></i>
                            บันทึกรหัสผ่านใหม่
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="back-link">
                    <a href="login.php">
                        <i class="fas fa-arrow-left me-2"></i>
                        กลับไปหน้าเข้าสู่ระบบ
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        if(togglePassword) {
            togglePassword.addEventListener('click', function() {
                const password = document.getElementById('password');
                const icon = this.querySelector('i');
                
                if(password.type === 'password') {
                    password.type = 'text';
                    icon.className = 'fas fa-eye-slash';
                } else {
                    password.type = 'password';
                    icon.className = 'fas fa-eye';
                }
            });
        }
        
        // Password strength checker
        const passwordInput = document.getElementById('password');
        if(passwordInput) {
            passwordInput.addEventListener('input', function() {
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
        const confirmInput = document.getElementById('confirm_password');
        if(confirmInput) {
            confirmInput.addEventListener('input', function() {
                const password = document.getElementById('password').value;
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
        const resetForm = document.getElementById('resetForm');
        if(resetForm) {
            resetForm.addEventListener('submit', function(e) {
                const password = document.getElementById('password').value;
                const confirm = document.getElementById('confirm_password').value;
                
                if(password !== confirm) {
                    e.preventDefault();
                    alert('รหัสผ่านไม่ตรงกัน กรุณาตรวจสอบอีกครั้ง');
                }
                
                if(password.length < 6) {
                    e.preventDefault();
                    alert('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
                }
            });
        }
    </script>
</body>
</html>