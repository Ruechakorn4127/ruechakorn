<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ฤชากร นัคราภิบาล ต่อ 6601091427</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .header-title {
            color: #dc3545;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
            padding-bottom: 10px;
            border-bottom: 2px solid #dc3545;
            margin-bottom: 25px;
        }
        
        .form-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .result-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        
        .color-preview {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border: 2px solid #dee2e6;
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        
        .btn-group-custom {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-custom {
            min-width: 150px;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="container mt-4 mb-5">
        <!-- หัวข้อหลัก -->
        <div class="text-center mb-4">
            <h1 class="header-title">
                <i class="fas fa-user-circle me-2"></i>ฟอร์มรับข้อมูลส่วนบุคคล
            </h1>
            <p class="lead text-muted">ฤชากร นัคราภิบาล ต่อ 6601091427</p>
        </div>
        
        <!-- ฟอร์มรับข้อมูล -->
        <div class="form-container">
            <form method="post" action="">
                <div class="row">
                    <!-- ชื่อ-สกุล -->
                    <div class="col-md-6 mb-3">
                        <label for="fullname" class="form-label required-field">
                            <i class="fas fa-user me-1"></i>ชื่อ-สกุล
                        </label>
                        <input type="text" class="form-control" id="fullname" name="fullname" autofocus required>
                    </div>
                    
                    <!-- เบอร์โทร -->
                    <div class="col-md-6 mb-3">
                        <label for="phone" class="form-label required-field">
                            <i class="fas fa-phone me-1"></i>เบอร์โทร
                        </label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                </div>
                
                <div class="row">
                    <!-- ส่วนสูง -->
                    <div class="col-md-4 mb-3">
                        <label for="height" class="form-label required-field">
                            <i class="fas fa-ruler-vertical me-1"></i>ส่วนสูง
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="height" name="height" max="200" min="100" required>
                            <span class="input-group-text">ซม.</span>
                        </div>
                        <div class="form-text">ระบุส่วนสูงระหว่าง 100-200 ซม.</div>
                    </div>
                    
                    <!-- วันเกิด -->
                    <div class="col-md-4 mb-3">
                        <label for="birthday" class="form-label">
                            <i class="fas fa-birthday-cake me-1"></i>วัน/เดือน/ปีเกิด
                        </label>
                        <input type="date" class="form-control" id="birthday" name="birthday">
                    </div>
                    
                    <!-- สีที่ชอบ -->
                    <div class="col-md-4 mb-3">
                        <label for="color" class="form-label">
                            <i class="fas fa-palette me-1"></i>สีที่ชอบ
                        </label>
                        <div class="input-group">
                            <span class="color-preview" id="colorPreview"></span>
                            <input type="color" class="form-control form-control-color" id="color" name="color" value="#0d6efd">
                        </div>
                    </div>
                </div>
                
                <!-- ที่อยู่ -->
                <div class="mb-3">
                    <label for="address" class="form-label">
                        <i class="fas fa-home me-1"></i>ที่อยู่
                    </label>
                    <textarea class="form-control" id="address" name="address" rows="3"></textarea>
                </div>
                
                <!-- สาขาวิชา -->
                <div class="mb-4">
                    <label for="major" class="form-label">
                        <i class="fas fa-graduation-cap me-1"></i>สาขาวิชา
                    </label>
                    <select class="form-select" id="major" name="major">
                        <option value="การบัญชี">การบัญชี</option>
                        <option value="การตลาด">การตลาด</option>
                        <option value="การจัดการ">การจัดการ</option>
                        <option value="คอมพิวเตอร์ธุรกิจ">คอมพิวเตอร์ธุรกิจ</option>
                    </select>
                </div>
                
                <!-- ปุ่มต่างๆ -->
                <div class="btn-group-custom">
                    <button type="submit" class="btn btn-success btn-custom" name="Submit">
                        <i class="fas fa-user-plus me-2"></i>สมัครสมาชิก
                    </button>
                    <button type="reset" class="btn btn-warning btn-custom" name="Reset">
                        <i class="fas fa-eraser me-2"></i>ล้างเนื้อหา
                    </button>
                    <button type="button" class="btn btn-primary btn-custom" onclick="window.location='https://reg.msu.ac.th';">
                        <i class="fas fa-external-link-alt me-2"></i>GO TO reg.msu
                    </button>
                    <button type="button" class="btn btn-info btn-custom" ondblclick="alert('See you later!')">
                        <i class="fas fa-eye me-2"></i>See
                    </button>
                    <button type="button" class="btn btn-secondary btn-custom" onclick="window.print();">
                        <i class="fas fa-print me-2"></i>ปริ้น
                    </button>
                </div>
            </form>
        </div>
        
        <!-- แสดงผลข้อมูล -->
        <?php
        if(isset($_POST['Submit'])){
            $fullname = $_POST['fullname'];
            $phone = $_POST['phone'];
            $height = $_POST['height'];
            $address = $_POST['address'];
            $birthday = $_POST['birthday'];
            $color = $_POST['color'];
            $major = $_POST['major'];
            
            $host = "localhost";
            $user = "root";
            $pwd = "";
            $db = "4127db";
            $conn = mysqli_connect($host,$user,$pwd,$db) or die ("เชื่อมต่อฐานข้อมูลไม่ได้");
            mysqli_query($conn,"SET NAMES utf8");

            $sql = "INSERT INTO register (r_id,r_name,r_phone,r_height,r_adress,r_birthday,r_color,r_major) VALUES (NULL, '{$fullname}','{$phone}','{$height}','{$address}','{$birthday}','{$color}','{$major}');";
            mysqli_query($conn, $sql)or die ("insert ไม่ได้");

            echo "<script>";
            echo "alert('บันทึกเเล้ว');";
            echo "</script>";
        }
        ?>
        <?php ?>

        <!-- Footer -->
        <div class="footer">
            <p>แบบฟอร์มนี้พัฒนาโดย ฤชากร นัคราภิบาล ต่อ 6601091427</p>
            <p>© 2023 - สร้างด้วย HTML, PHP และ Bootstrap 5.3</p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript สำหรับแสดงตัวอย่างสี -->
    <script>
        // อัพเดทสีตัวอย่างเมื่อเลือกสี
        document.getElementById('color').addEventListener('input', function() {
            document.getElementById('colorPreview').style.backgroundColor = this.value;
        });
        
        // ตั้งค่าสีเริ่มต้น
        document.addEventListener('DOMContentLoaded', function() {
            const colorInput = document.getElementById('color');
            document.getElementById('colorPreview').style.backgroundColor = colorInput.value;
        });
    </script>
</body>
</html>