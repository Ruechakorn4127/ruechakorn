<?php
require_once '../config/database.php';

if(!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if(!isset($_POST['user_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing user ID']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, first_name, last_name, email, phone, status 
          FROM users 
          WHERE id = :user_id AND role = 'customer'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_POST['user_id']);
$stmt->execute();

if($stmt->rowCount() > 0) {
    $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($customer);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Customer not found']);
}
?>