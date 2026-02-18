<?php
require_once '../config/database.php';

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get cart items
$cart_query = "SELECT c.*, p.name, p.price, p.id as product_id 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = :user_id";
$cart_stmt = $db->prepare($cart_query);
$cart_stmt->bindParam(':user_id', $user_id);
$cart_stmt->execute();
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if(empty($cart_items)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit();
}

// Calculate total
$subtotal = 0;
foreach($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$shipping = 50;
$total = $subtotal + $shipping;

// Generate order number
$order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);

// Create shipping address string
$shipping_address = $_POST['address'] . ', ' . $_POST['city'] . ', ' . $_POST['postal_code'] . ', ' . $_POST['country'];

// Insert order
$order_query = "INSERT INTO orders (user_id, order_number, total_amount, shipping_address, 
                shipping_city, shipping_postal, shipping_country, payment_method, order_status) 
                VALUES (:user_id, :order_number, :total_amount, :shipping_address, 
                :shipping_city, :shipping_postal, :shipping_country, :payment_method, 'pending')";
$order_stmt = $db->prepare($order_query);
$order_stmt->bindParam(':user_id', $user_id);
$order_stmt->bindParam(':order_number', $order_number);
$order_stmt->bindParam(':total_amount', $total);
$order_stmt->bindParam(':shipping_address', $_POST['address']);
$order_stmt->bindParam(':shipping_city', $_POST['city']);
$order_stmt->bindParam(':shipping_postal', $_POST['postal_code']);
$order_stmt->bindParam(':shipping_country', $_POST['country']);
$order_stmt->bindParam(':payment_method', $_POST['payment_method']);
$order_stmt->execute();

$order_id = $db->lastInsertId();

// Insert order items
foreach($cart_items as $item) {
    $item_query = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) 
                   VALUES (:order_id, :product_id, :product_name, :quantity, :price, :total)";
    $item_stmt = $db->prepare($item_query);
    $item_stmt->bindParam(':order_id', $order_id);
    $item_stmt->bindParam(':product_id', $item['product_id']);
    $item_stmt->bindParam(':product_name', $item['name']);
    $item_stmt->bindParam(':quantity', $item['quantity']);
    $item_stmt->bindParam(':price', $item['price']);
    $item_total = $item['price'] * $item['quantity'];
    $item_stmt->bindParam(':total', $item_total);
    $item_stmt->execute();
    
    // Update product stock
    $stock_query = "UPDATE products SET stock = stock - :quantity WHERE id = :id";
    $stock_stmt = $db->prepare($stock_query);
    $stock_stmt->bindParam(':quantity', $item['quantity']);
    $stock_stmt->bindParam(':id', $item['product_id']);
    $stock_stmt->execute();
}

// Clear cart
$clear_query = "DELETE FROM cart WHERE user_id = :user_id";
$clear_stmt = $db->prepare($clear_query);
$clear_stmt->bindParam(':user_id', $user_id);
$clear_stmt->execute();

echo json_encode([
    'success' => true,
    'order_id' => $order_id
]);