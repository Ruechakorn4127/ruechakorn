<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>66010914127 ฤชากร นัคราภิบาล (ต่อ)</title>
<style>
    .font {
        color: rgb(255, 0, 0);
        text-shadow: black 1px 1px 2px;
    }
</style>
</head>

<body>

<h1 class="font">66010914127 ฤชากร นัคราภิบาล (ต่อ)</h1>

<form method="post" action="">

    ชื่อ-สกุล <input type="text" name="fullname" autofocus required>* <br>
    เบอร์โทร <input type="text" name="phone" required>* <br>
    ส่วนสูง <input type="number" name="height" max="200" min="100" required>ซม.* <br>
    ที่อยู่ <br> <textarea name="address" cols="40" rows="4"></textarea> <br>
    วัน/เดือน/ปีเกิด <input type="date" name="birthday"> <br>
    สีทีชอบ <input type="color" name="color"> <br>
    สาขาวิชา 
    <select name="major">
        <option value="การบัญชี">การบัญชี</option>
        <option value="การตลาด">การตลาด</option>
        <option value="การจัดการ">การจัดการ</option>
        <option value="คอมพิเตอร์ธุรกิจ">คอมพิเตอร์ธุรกิจ</option>
    </select>
    <br>

    <!--<input type="submit" name="Submit" value="สมัครสมาชิก"> <br>-->
    <button type="submit" name="Submit">สมัครสมาชิก</button>
    <button type="reset" name="Reset">ล้างเนื้อหา</button>
    <button type="button" name="Button" onclick="window.location='https://reg.msu.ac.th';">GO TO reg.msu</button>
    <button type="button" onmouseover="alert('see ya')">see</button>
    <button type="button" onclick="window.print();">ปริ้น</button>

</form>
<hr>

<?php
if(isset($_POST['Submit'])){
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $height = $_POST['height'];
    $address = $_POST['address'];
    $birthday = $_POST['birthday'];
    $color = $_POST['color'];
    $major = $_POST['major'];

    echo "ชื่อ-สกุล : ".$_POST['fullname']."<br>";
    echo "เบอร์โทร : ".$_POST['phone']."<br>";
    echo "ส่วนสูง : ".$_POST['height']." ซม.<br>";
    echo "ที่อยู่ : ".$_POST['address']."<br>";
    echo "วัน/เดือน/ปีเกิด : ".$_POST['birthday']."<br>";
    echo "สีทีชอบ :<div style='background-color:{$color}; width:300px'>".$color."</div> <br>";
    echo "สาขาวิชา : ".$_POST['major']."<br>";
}

?>

</body>
</html>