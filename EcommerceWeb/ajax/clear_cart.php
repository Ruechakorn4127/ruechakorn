<?php
require_once '../config/database.php';

if(!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

$query = "DELETE FROM cart WHERE user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

echo json_encode(['success' => true]);