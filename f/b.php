<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ฤชากร นัคราภิบาล ต่อ 66010914127</title>
</head>
<body>
    <h1>ฤชากร นัคราภิบาล ต่อ 66010914127</h1>
<form method = "post" action="">
    <input type="number" name="a" autofocus required>
    <button type="submit" name=Submit>Ok</button>
<hr>
<?php

if(isset($_POST['Submit'])){
    $gender = $_POST['a'];
    if ($gender == 1){
        echo "ชาย";
    }
    elseif ($gender == 2){
        echo "หญิง";
    }
    elseif ($gender == 3){
        echo "เพศทางเลือก";
    }
    else{
        echo "อื่นๆ";
    }
}
?>
</form>
</body>
</html>