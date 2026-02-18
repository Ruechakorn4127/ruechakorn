<?php
require_once '../config/database.php';

if(!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$cart_id = $_POST['cart_id'];
$user_id = $_SESSION['user_id'];

$query = "DELETE FROM cart WHERE id = :id AND user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $cart_id);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

// Check if cart is empty
$check_query = "SELECT COUNT(*) as count FROM cart WHERE user_id = :user_id";
$check_stmt = $db->prepare($check_query);
$check_stmt->bindParam(':user_id', $user_id);
$check_stmt->execute();
$count = $check_stmt->fetch(PDO::FETCH_ASSOC)['count'];

echo json_encode([
    'success' => true,
    'cart_empty' => $count == 0
]);