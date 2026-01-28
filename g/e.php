<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart.js with PHP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="container py-4">
    <h1 class="mb-4">ฤชากร นัคราภิบาล</h1>

    <div class="row">
        <div class="col-md-5">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>ประเทศ</th>
                        <th>ยอดขาย</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                include_once("connectdb.php");
                $sql = "SELECT `p_country`, SUM(`p_amount`) AS total FROM `popsupermarket` GROUP BY `p_country`";
                $rs = mysqli_query($conn, $sql);
                
                $countries = [];
                $totals = [];

                while ($data = mysqli_fetch_array($rs)){
                    $countries[] = $data['p_country'];
                    $totals[] = $data['total'];
                ?>
                <tr>
                    <td><?php echo $data['p_country'];?></td>
                    <td align="right"><?php echo number_format($data['total'], 0);?></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

        <div class="col-md-7">
            <div class="row">
                <div class="col-12 mb-4">
                    <canvas id="barChart" style="height:250px"></canvas>
                </div>
                <div class="col-12">
                    <div style="width: 300px; margin: auto;">
                        <canvas id="pieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // เตรียมข้อมูลจาก PHP ใส่ตัวแปร JS
    const labels = <?php echo json_encode($countries); ?>;
    const dataValues = <?php echo json_encode($totals); ?>;
    const colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

    // กราฟแท่ง (Bar Chart)
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'ยอดขายรวมตามประเทศ',
                data: dataValues,
                backgroundColor: colors
            }]
        },
        options: { responsive: true }
    });

    // กราฟวงกลม (Pie Chart)
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: dataValues,
                backgroundColor: colors
            }]
        },
        options: { responsive: true }
    });
    </script>
</body>
</html>