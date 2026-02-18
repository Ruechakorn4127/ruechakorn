<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$new_password = '123'; // เปลี่ยนเป็นรหัสที่ต้องการ
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$query = "UPDATE users SET password = :password WHERE username = 'admin'";
$stmt = $db->prepare($query);
$stmt->bindParam(':password', $hashed_password);

if($stmt->execute()) {
    echo "เปลี่ยนรหัสผ่าน admin สำเร็จ!\n";
    echo "รหัสผ่านใหม่: " . $new_password . "\n";
} else {
    echo "เกิดข้อผิดพลาด\n";
}
?>