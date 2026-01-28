<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยอดขายรายเดือน - ฤชากร นัคราภิบาล</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        header {
            background: linear-gradient(135deg, #1e5799 0%, #207cca 100%);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .subtitle {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 300;
        }
        
        .content {
            padding: 30px;
        }
        
        .section-title {
            font-size: 22px;
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ecf0f1;
        }
        
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        @media (max-width: 768px) {
            .charts-container {
                grid-template-columns: 1fr;
            }
        }
        
        .chart-wrapper {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }
        
        .chart-title {
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: center;
        }
        
        canvas {
            width: 100% !important;
            height: 300px !important;
        }
        
        .table-container {
            overflow-x: auto;
            margin-top: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th {
            background-color: #2c3e50;
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        td {
            padding: 14px 15px;
            border-bottom: 1px solid #e1e1e1;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #f0f7ff;
        }
        
        .amount-cell {
            text-align: right;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .month-cell {
            font-weight: 500;
        }
        
        footer {
            text-align: center;
            padding: 20px;
            margin-top: 30px;
            color: #7f8c8d;
            font-size: 14px;
            border-top: 1px solid #ecf0f1;
        }
        
        .highlight {
            color: #1e5799;
            font-weight: 600;
        }
        
        .total-row {
            background-color: #e8f4fc !important;
            font-weight: bold;
        }
        
        .total-row td {
            border-top: 2px solid #1e5799;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>ฤชากร นัคราภิบาล</h1>
            <div class="subtitle">รายงานยอดขายรายเดือน - Pop Supermarket</div>
        </header>
        
        <div class="content">
            <h2 class="section-title">ภาพรวมยอดขายรายเดือน</h2>
            
            <div class="charts-container">
                <div class="chart-wrapper">
                    <h3 class="chart-title">กราฟแท่งแสดงยอดขายรายเดือน</h3>
                    <canvas id="barChart"></canvas>
                </div>
                
                <div class="chart-wrapper">
                    <h3 class="chart-title">กราฟโดนัทแสดงสัดส่วนยอดขายรายเดือน</h3>
                    <canvas id="donutChart"></canvas>
                </div>
            </div>
            
            <h2 class="section-title">ตารางสรุปยอดขายรายเดือน</h2>
            <div class="table-container">
                <table id="salesTable">
                    <thead>
                        <tr>
                            <th>เดือน</th>
                            <th>ยอดขาย</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- ข้อมูลจาก PHP จะถูกแทรกที่นี่ -->
                        <?php
                        include_once("connectdb.php");
                        
                        $sql = "SELECT
                        MONTH(p_date) AS Month,
                        SUM(p_amount) AS Total_Sales
                        FROM popsupermarket
                        GROUP BY MONTH(p_date)
                        ORDER BY Month";
                        
                        $rs = mysqli_query($conn, $sql);
                        $totalAllSales = 0;
                        $monthlyData = [];
                        
                        while ($data = mysqli_fetch_array($rs)) {
                            $month = $data['Month'];
                            $totalSales = $data['Total_Sales'];
                            $totalAllSales += $totalSales;
                            $monthlyData[] = [
                                'month' => $month,
                                'sales' => $totalSales
                            ];
                            ?>  
                            <tr>
                                <td class="month-cell">เดือน <?php echo $month; ?></td>
                                <td class="amount-cell"><?php echo number_format($totalSales, 0); ?></td>
                            </tr>
                        <?php } ?>
                        
                        <!-- แถวรวมทั้งหมด -->
                        <tr class="total-row">
                            <td>ยอดขายรวมทั้งปี</td>
                            <td class="amount-cell"><?php echo number_format($totalAllSales, 0); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <footer>
            <p>ระบบรายงานยอดขาย &copy; 2023 | อัปเดตล่าสุด: <?php echo date('d/m/Y'); ?></p>
        </footer>
    </div>

    <script>
        // ข้อมูลจาก PHP จะถูกแปลงเป็น JavaScript array
        const monthlyData = <?php echo json_encode($monthlyData); ?>;
        const totalAllSales = <?php echo $totalAllSales; ?>;
        
        // เตรียมข้อมูลสำหรับกราฟ
        const months = [];
        const salesData = [];
        const backgroundColors = [];
        const borderColors = [];
        
        // สร้างสีสำหรับแต่ละเดือน
        const colorPalette = [
            'rgba(54, 162, 235, 0.7)',
            'rgba(255, 99, 132, 0.7)',
            'rgba(75, 192, 192, 0.7)',
            'rgba(255, 206, 86, 0.7)',
            'rgba(153, 102, 255, 0.7)',
            'rgba(255, 159, 64, 0.7)',
            'rgba(201, 203, 207, 0.7)',
            'rgba(50, 168, 82, 0.7)',
            'rgba(220, 53, 69, 0.7)',
            'rgba(23, 162, 184, 0.7)',
            'rgba(102, 16, 242, 0.7)',
            'rgba(253, 126, 20, 0.7)'
        ];
        
        const borderColorPalette = [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(201, 203, 207, 1)',
            'rgba(50, 168, 82, 1)',
            'rgba(220, 53, 69, 1)',
            'rgba(23, 162, 184, 1)',
            'rgba(102, 16, 242, 1)',
            'rgba(253, 126, 20, 1)'
        ];
        
        // จัดเตรียมข้อมูล
        monthlyData.forEach((item, index) => {
            months.push(`เดือน ${item.month}`);
            salesData.push(item.sales);
            backgroundColors.push(colorPalette[index % colorPalette.length]);
            borderColors.push(borderColorPalette[index % borderColorPalette.length]);
        });
        
        // กราฟแท่ง (Bar Chart)
        const barCtx = document.getElementById('barChart').getContext('2d');
        const barChart = new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'ยอดขาย',
                    data: salesData,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += new Intl.NumberFormat('th-TH').format(context.parsed.y);
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('th-TH').format(value);
                            }
                        },
                        title: {
                            display: true,
                            text: 'ยอดขาย'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'เดือน'
                        }
                    }
                }
            }
        });
        
        // กราฟโดนัท (Donut Chart)
        const donutCtx = document.getElementById('donutChart').getContext('2d');
        const donutChart = new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: months,
                datasets: [{
                    label: 'ยอดขาย',
                    data: salesData,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1,
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 15,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    // แสดงทั้งยอดขายและเปอร์เซ็นต์
                                    const value = context.parsed;
                                    const percentage = Math.round((value / totalAllSales) * 100);
                                    label += new Intl.NumberFormat('th-TH').format(value) + 
                                            ` (${percentage}%)`;
                                }
                                return label;
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    </script>
</body>
</html>