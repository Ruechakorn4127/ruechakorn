<?php
// สร้างรูปเริ่มต้นขนาด 500x500 pixels
$width = 500;
$height = 500;
$image = imagecreatetruecolor($width, $height);

// สีพื้นหลัง (สีเทาอ่อน)
$bg_color = imagecolorallocate($image, 240, 240, 240);
imagefill($image, 0, 0, $bg_color);

// สีข้อความ (สีเทาเข้ม)
$text_color = imagecolorallocate($image, 150, 150, 150);

// วาดเส้นกากบาท
imageline($image, 0, 0, $width, $height, $text_color);
imageline($image, $width, 0, 0, $height, $text_color);

// วาดกรอบสี่เหลี่ยม
imagerectangle($image, 10, 10, $width-10, $height-10, $text_color);

// เพิ่มข้อความ "No Image"
$text = "No Image";
$font_size = 5; // built-in font
$text_width = imagefontwidth($font_size) * strlen($text);
$text_height = imagefontheight($font_size);
$x = ($width - $text_width) / 2;
$y = ($height - $text_height) / 2;
imagestring($image, $font_size, $x, $y, $text, $text_color);

// บันทึกไฟล์
imagejpeg($image, 'images/products/default.jpg', 90);
imagedestroy($image);

echo "สร้างรูป default.jpg เรียบร้อย!";
?>