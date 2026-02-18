<?php
require_once '../config/database.php';

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'] ?? 1;
$user_id = $_SESSION['user_id'];

// Check if product already in cart
$check_query = "SELECT id, quantity FROM cart WHERE user_id = :user_id AND product_id = :product_id";
$check_stmt = $db->prepare($check_query);
$check_stmt->bindParam(':user_id', $user_id);
$check_stmt->bindParam(':product_id', $product_id);
$check_stmt->execute();

if($check_stmt->rowCount() > 0) {
    // Update quantity
    $cart = $check_stmt->fetch(PDO::FETCH_ASSOC);
    $new_quantity = $cart['quantity'] + $quantity;
    
    $update_query = "UPDATE cart SET quantity = :quantity WHERE id = :id";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':quantity', $new_quantity);
    $update_stmt->bindParam(':id', $cart['id']);
    $update_stmt->execute();
} else {
    // Insert new item
    $insert_query = "INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)";
    $insert_stmt = $db->prepare($insert_query);
    $insert_stmt->bindParam(':user_id', $user_id);
    $insert_stmt->bindParam(':product_id', $product_id);
    $insert_stmt->bindParam(':quantity', $quantity);
    $insert_stmt->execute();
}

echo json_encode(['success' => true]);