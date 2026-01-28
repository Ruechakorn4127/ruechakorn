<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pop Supermarket - DataTables</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0">ฤชากร นัคราภิบาล - ระบบจัดการข้อมูลสินค้า</h3>
        </div>
        <div class="card-body">
            
            <table id="myTable" class="table table-striped table-hover table-bordered" style="width:100%">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>ชื่อสินค้า</th>
                        <th>ประเภทสินค้า</th>
                        <th>วันที่</th>
                        <th>ประเทศ</th>
                        <th>จำนวนเงิน</th>
                        <th>รูปภาพ</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                include_once("connectdb.php");
                $sql = "SELECT * FROM `popsupermarket`";
                $rs = mysqli_query($conn, $sql);
                while ($data = mysqli_fetch_array($rs)) {
                ?>  
                    <tr>
                        <td align="center"><?php echo $data['p_order_id'];?></td>
                        <td><?php echo $data['p_product_name'];?></td>
                        <td><span class="badge bg-info text-dark"><?php echo $data['p_category'];?></span></td>
                        <td><?php echo $data['p_date'];?></td>
                        <td><?php echo $data['p_country'];?></td>
                        <td align='right' class="fw-bold text-primary">
                            <?php echo number_format($data['p_amount'], 0);?>
                        </td>
                        <td align="center">
                            <img src="image/<?php echo $data['p_product_name'];?>.jpg" 
                                 width="50" class="img-thumbnail" 
                                 onerror="this.src='https://placehold.co/50x50?text=No+Img'">
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    $('#myTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/th.json" // เมนูภาษาไทย
        },
        "pageLength": 10 // แสดงหน้าละ 10 แถว
    });
});
</script>

</body>
</html>