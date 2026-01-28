<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<h1>ฤชากร นัคราภิบาล</h1>

<table border="1">
<tr>
    <th>เดือน</th>
    <th>ยอดขาย</th>
</tr>  
<?php
include_once("connectdb.php");
$sql = "SELECT 
MONTH(p_date) AS Month, 
SUM(p_amount) AS Total_Sales
FROM popsupermarket
GROUP BY MONTH(p_date)
ORDER BY Month";
$rs = mysqli_query($conn,$sql);
while ($data = mysqli_fetch_array($rs)){
?>  

<tr>
    <td><?php echo $data['Month'];?></td>
    <td aligh="right"><?php echo number_format($data['Total_Sales'],0);?></td>
</tr>

<?php } ?>
</table>   
</body>
</html>