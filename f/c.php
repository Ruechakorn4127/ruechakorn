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
    กรอกคะเเนน<input type="number" min="0" max="100"name="a" autofocus required>
    <button type="submit" name=Submit>Ok</button>
<hr>
<?php

if(isset($_POST['Submit'])){
    $score = $_POST['a'];
    if ($score >= 80) {
        $grade = "A" ;
    } 
    else if ($score >= 70) {
        $grade = "B" ;
    } 
    else if ($score >= 60) {
        $grade = "C" ;
    } 
    else if ($score >= 50) {
        $grade = "D" ;
    } 
    else {
        $grade = "F" ;
    } 
    echo "<strong>คะเเนน $score ได้เกรด $grade";
}
?>
</form>
</body>
</html>