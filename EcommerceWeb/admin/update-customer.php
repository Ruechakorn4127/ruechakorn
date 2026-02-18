<?php
require_once '../config/database.php';

if(!isAdmin()) {
    redirect('../login.php');
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_customer'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $status = $_POST['status'];
    
    // Check if email already exists for other users
    $check_query = "SELECT id FROM users WHERE email = :email AND id != :user_id";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->bindParam(':email', $email);
    $check_stmt->bindParam(':user_id', $user_id);
    $check_stmt->execute();
    
    if($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = "อีเมลนี้มีผู้ใช้แล้ว";
        redirect('customers.php');
    }
    
    $query = "UPDATE users 
              SET first_name = :first_name, 
                  last_name = :last_name, 
                  email = :email, 
                  phone = :phone, 
                  status = :status 
              WHERE id = :id AND role = 'customer'";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $user_id);
    $stmt->bindParam(':first_name', $first_name);
    $stmt->bindParam(':last_name', $last_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':status', $status);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "อัปเดตข้อมูลลูกค้าสำเร็จ";
    } else {
        $_SESSION['error'] = "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
    }
    
    redirect('customers.php');
} else {
    redirect('customers.php');
}
?>