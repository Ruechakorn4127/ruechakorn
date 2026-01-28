<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h1>ฤชากร นัคราภิบาล</h1>

<form method="post" action="">
    คำค้น <input type="text" name="a" autofocus>
    <button type="submit" name="Submit">ok</button>
</form>

<table border="1">
<tr>
    <th>Order ID</th>
    <th>ชื่อสินค้า</th>
    <th>ประเภทสินค้า</th>
    <th>วันที่</th>
    <th>ประเทศ</th>
    <th>จำนวนเงิน</th>
    <th>รูปภาพ</th>
</tr>  

<?php
include_once("connectdb.php");
@$kw = $_POST['a']; 
$sql = "SELECT * FROM `popsupermarket`
WHERE p_country LIKE '%{$kw}%' OR p_product_name LIKE '%{$kw}%'";

$rs = mysqli_query($conn, $sql);
$total = 0;
while ($data = mysqli_fetch_array($rs)){
    $total += $data['p_amount'];
?>  

<tr>
    <td><?php echo $data['p_order_id'];?></td>
    <td><?php echo $data['p_product_name'];?></td>
    <td><?php echo $data['p_category'];?></td>
    <td><?php echo $data['p_date'];?></td>
    <td><?php echo $data['p_country'];?></td>
    <td align='right'><?php echo number_format($data['p_amount'], 0);?></td>
    <td><img src="image/<?php echo $data['p_product_name'];?>.jpg" width="55"></td>
</tr>

<?php 
} 
?>

<tr>
    <td colspan="5" align="right"><strong>รวมเงินทั้งสิ้น</strong></td>
    <td align="right"><strong><?php echo number_format($total, 0);?></strong></td>
    <td>&nbsp;</td>
</tr>

</table>   
</body>
</html>