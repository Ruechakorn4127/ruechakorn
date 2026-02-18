<?php
require_once '../config/database.php';

if(!isLoggedIn()) {
    echo '0';
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo $result['total'] ?? '0';