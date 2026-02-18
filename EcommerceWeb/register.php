<?php
require_once 'config/database.php';

if(isLoggedIn()) {
    redirect('index.php');
}

$database = new Database();
$db = $database->getConnection();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    
    // Validation
    $errors = [];
    
    if($password != $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if(strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    // Check if username exists
    $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':username', $username);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->execute();
    
    if($check_stmt->rowCount() > 0) {
        $errors[] = "Username or email already exists";
    }
    
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $query = "INSERT INTO users (username, email, password, first_name, last_name, phone, role) 
                  VALUES (:username, :email, :password, :first_name, :last_name, :phone, 'customer')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':phone', $phone);
        
        if($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please login.";
            redirect('login.php');
        } else {
            $errors[] = "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - E-Store</title>
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
        
        .register-container {
            max-width: 700px;
            margin: 0 auto;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header i {
            font-size: 4rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .register-header h3 {
            font-weight: 700;
            font-size: 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-top: 1rem;
        }
        
        .register-header p {
            color: var(--secondary);
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
        
        .btn-register {
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
        
        .btn-register:before {
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
        
        .btn-register:hover:before {
            width: 300px;
            height: 300px;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 30px rgba(79, 70, 229, 0.3);
        }
        
        .btn-register i {
            margin-right: 0.5rem;
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
        
        .login-link {
            text-align: center;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e2e8f0;
        }
        
        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .login-link a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
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
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <i class="fas fa-store"></i>
                    <h3>Create Account</h3>
                    <p>Join our community today!</p>
                </div>
                
                <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php 
                            echo $_SESSION['success'];
                            unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="registerForm">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user text-primary me-2"></i>First Name *
                            </label>
                            <input type="text" class="form-control" name="first_name" required 
                                   value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                                   placeholder="Enter your first name">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-user text-primary me-2"></i>Last Name *
                            </label>
                            <input type="text" class="form-control" name="last_name" required
                                   value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                                   placeholder="Enter your last name">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-at text-primary me-2"></i>Username *
                            </label>
                            <input type="text" class="form-control" name="username" required
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   placeholder="Choose a username">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-envelope text-primary me-2"></i>Email *
                            </label>
                            <input type="email" class="form-control" name="email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                   placeholder="Enter your email">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">
                                <i class="fas fa-phone text-primary me-2"></i>Phone
                            </label>
                            <input type="text" class="form-control" name="phone"
                                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                   placeholder="Enter your phone number">
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-lock text-primary me-2"></i>Password *
                            </label>
                            <input type="password" class="form-control" name="password" id="password" required
                                   placeholder="Minimum 6 characters">
                            <div class="password-strength mt-2">
                                <div class="password-strength-bar" id="passwordStrength"></div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-lock text-primary me-2"></i>Confirm Password *
                            </label>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" required
                                   placeholder="Re-enter your password">
                            <small class="text-muted" id="passwordMatch"></small>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary">Terms of Service</a> and 
                                    <a href="#" class="text-primary">Privacy Policy</a> *
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn-register" id="submitBtn">
                                <i class="fas fa-user-plus"></i>
                                Create Account
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="login-link">
                    <span class="text-muted">Already have an account?</span>
                    <a href="login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login here
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
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
        
        // Password match checker
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirm = this.value;
            const matchMsg = document.getElementById('passwordMatch');
            
            if(password === confirm) {
                matchMsg.innerHTML = '<i class="fas fa-check-circle text-success"></i> Passwords match';
                matchMsg.className = 'text-success';
            } else {
                matchMsg.innerHTML = '<i class="fas fa-times-circle text-danger"></i> Passwords do not match';
                matchMsg.className = 'text-danger';
            }
        });
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const terms = document.getElementById('terms').checked;
            
            if(password !== confirm) {
                e.preventDefault();
                alert('Passwords do not match!');
                return;
            }
            
            if(!terms) {
                e.preventDefault();
                alert('Please accept the Terms of Service and Privacy Policy');
                return;
            }
        });
    </script>
</body>
</html>