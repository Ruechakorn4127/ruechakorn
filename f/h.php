<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฤชากร นัคราภิบาล ต่อ 66010914127</title>
</head>
<body>
<h1>ฤชากร นัคราภิบาล ต่อ 66010914127 - for</h1>
<form method = "post" action="">
    รหัสนิสิต<input type="number"name="a" autofocus required>
    <button type="submit" name=Submit>Ok</button>
<hr>

<?php
if(isset($_POST['Submit'])){
    $id = $_POST['a'];
    $y = substr($id,0,2);
    echo "<img src='http://202.28.32.211/picture/student/{$y}/{$id}.jpg'width='600'>";
}
?>
</body>
</html>