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
    กรอกเลข<input type="number" min="2"name="a" autofocus required>
    <button type="submit" name=Submit>Ok</button>
<hr>

<?php
if(isset($_POST['Submit'])){
    $m=$_POST['a'];
    for($i=1;$i<=12;$i++){
        $sum = $m * $i;
        echo "{$m} x {$i} = {$sum}<br>";
    }
}
?>
</body>
</html>