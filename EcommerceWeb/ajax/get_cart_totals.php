<?php
require_once '../config/database.php';

if(!isLoggedIn()) {
    echo json_encode(['subtotal' => 0, 'total' => 0]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT SUM(p.price * c.quantity) as subtotal 
          FROM cart c 
          JOIN products p ON c.product_id = p.id 
          WHERE c.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$subtotal = $result['subtotal'] ?? 0;
$shipping = 50;
$total = $subtotal + $shipping;

echo json_encode([
    'subtotal' => $subtotal,
    'total' => $total
]);