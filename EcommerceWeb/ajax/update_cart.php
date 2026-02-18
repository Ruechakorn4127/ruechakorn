<?php
require_once '../config/database.php';

if(!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$cart_id = $_POST['cart_id'];
$quantity = $_POST['quantity'];
$user_id = $_SESSION['user_id'];

// Verify cart belongs to user
$check_query = "SELECT c.*, p.price FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = :cart_id AND c.user_id = :user_id";
$check_stmt = $db->prepare($check_query);
$check_stmt->bindParam(':cart_id', $cart_id);
$check_stmt->bindParam(':user_id', $user_id);
$check_stmt->execute();

if($check_stmt->rowCount() > 0) {
    $item = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    $update_query = "UPDATE cart SET quantity = :quantity WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':quantity', $quantity);
    $update_stmt->bindParam(':id', $cart_id);
    $update_stmt->execute();
    
    $item_total = $item['price'] * $quantity;
    
    echo json_encode([
        'success' => true,
        'item_total' => $item_total
    ]);
} else {
    echo json_encode(['success' => false]);
}