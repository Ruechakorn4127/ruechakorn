<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>แบบฟอร์มสมัครงาน บจก.ฤชากร คอปเปอร์เรชั่น จำกัด</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome สำหรับไอคอน -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .header-title {
            color: #1a237e;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
            padding-bottom: 10px;
            border-bottom: 3px solid #3949ab;
            margin-bottom: 25px;
        }
        
        .form-container {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .company-header {
            background-color: #1a237e;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
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
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
        }
        
        .info-box {
            background-color: #e3f2fd;
            border-left: 5px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container mt-4 mb-5">
        <!-- หัวข้อบริษัท -->
        <div class="company-header">
            <h2><i class="fas fa-building me-2"></i>บริษัท ฤชากร คอปเปอร์เรชั่น จำกัด</h2>
            <p class="mb-0">แบบฟอร์มสมัครงานออนไลน์</p>
        </div>
        
        <!-- ฟอร์มสมัครงาน -->
        <div class="form-container">
            <form method="post" action="">
                <!-- แสดงข้อความสำเร็จ -->
                <?php if(isset($success_message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                
                <!-- ข้อมูลผู้สมัคร -->
                <div class="info-box">
                    <i class="fas fa-info-circle me-2"></i>
                    กรุณากรอกข้อมูลให้ครบถ้วนและถูกต้อง
                </div>
                
                <h4 class="mb-4" style="color: #1a237e;">
                    <i class="fas fa-user-tie me-2"></i>ข้อมูลส่วนตัว
                </h4>
                
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
                    <!-- อีเมล -->
                    <div class="col-md-6 mb-3">
                        <label for="email" class="form-label required-field">
                            <i class="fas fa-envelope me-1"></i>อีเมล
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <!-- วันเกิด -->
                    <div class="col-md-6 mb-3">
                        <label for="birthday" class="form-label">
                            <i class="fas fa-birthday-cake me-1"></i>วัน/เดือน/ปีเกิด
                        </label>
                        <input type="date" class="form-control" id="birthday" name="birthday">
                    </div>
                </div>
                
                <!-- ที่อยู่ -->
                <div class="mb-3">
                    <label for="address" class="form-label required-field">
                        <i class="fas fa-home me-1"></i>ที่อยู่
                    </label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>
                
                <hr class="my-4">
                
                <h4 class="mb-4" style="color: #1a237e;">
                    <i class="fas fa-graduation-cap me-2"></i>ข้อมูลการศึกษา
                </h4>
                
                <div class="row">
                    <!-- ระดับการศึกษา -->
                    <div class="col-md-6 mb-3">
                        <label for="education" class="form-label required-field">
                            <i class="fas fa-university me-1"></i>ระดับการศึกษา
                        </label>
                        <select class="form-select" id="education" name="education" required>
                            <option value="">-- เลือกระดับการศึกษา --</option>
                            <option value="มัธยมปลาย">มัธยมปลาย</option>
                            <option value="ปวช.">ปวช.</option>
                            <option value="ปวส.">ปวส.</option>
                            <option value="ปริญญาตรี">ปริญญาตรี</option>
                            <option value="ปริญญาโท">ปริญญาโท</option>
                            <option value="ปริญญาเอก">ปริญญาเอก</option>
                        </select>
                    </div>
                    
                    <!-- สาขาวิชา -->
                    <div class="col-md-6 mb-3">
                        <label for="major" class="form-label required-field">
                            <i class="fas fa-book me-1"></i>สาขาวิชา
                        </label>
                        <input type="text" class="form-control" id="major" name="major" required>
                    </div>
                </div>
                
                <div class="row">
                    <!-- สถาบันการศึกษา -->
                    <div class="col-md-6 mb-3">
                        <label for="institution" class="form-label required-field">
                            <i class="fas fa-school me-1"></i>สถาบันการศึกษา
                        </label>
                        <input type="text" class="form-control" id="institution" name="institution" required>
                    </div>
                    
                    <!-- ปีที่จบการศึกษา -->
                    <div class="col-md-6 mb-3">
                        <label for="graduation_year" class="form-label required-field">
                            <i class="fas fa-calendar-alt me-1"></i>ปีที่จบการศึกษา
                        </label>
                        <select class="form-select" id="graduation_year" name="graduation_year" required>
                            <option value="">-- เลือกปีที่จบ --</option>
                            <?php for($year = date('Y'); $year >= 1990; $year--): ?>
                            <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <h4 class="mb-4" style="color: #1a237e;">
                    <i class="fas fa-briefcase me-2"></i>ข้อมูลตำแหน่งงาน
                </h4>
                
                <div class="row">
                    <!-- ตำแหน่งที่สมัคร -->
                    <div class="col-md-6 mb-3">
                        <label for="position" class="form-label required-field">
                            <i class="fas fa-user-tag me-1"></i>ตำแหน่งที่สมัคร
                        </label>
                        <select class="form-select" id="position" name="position" required>
                            <option value="">-- เลือกตำแหน่ง --</option>
                            <option value="พนักงานขาย">พนักงานขาย</option>
                            <option value="พนักงานการตลาด">พนักงานการตลาด</option>
                            <option value="พนักงานบัญชี">พนักงานบัญชี</option>
                            <option value="โปรแกรมเมอร์">โปรแกรมเมอร์</option>
                            <option value="วิศวกร">วิศวกร</option>
                            <option value="พนักงานฝ่ายบุคคล">พนักงานฝ่ายบุคคล</option>
                            <option value="อื่นๆ">อื่นๆ</option>
                        </select>
                    </div>
                    
                    <!-- ประสบการณ์ทำงาน -->
                    <div class="col-md-6 mb-3">
                        <label for="experience" class="form-label">
                            <i class="fas fa-chart-line me-1"></i>ประสบการณ์ทำงาน (ปี)
                        </label>
                        <select class="form-select" id="experience" name="experience">
                            <option value="0">ไม่มีประสบการณ์</option>
                            <option value="1">1 ปี</option>
                            <option value="2">2 ปี</option>
                            <option value="3">3 ปี</option>
                            <option value="4">4 ปี</option>
                            <option value="5">5 ปี</option>
                            <option value="6">มากกว่า 5 ปี</option>
                        </select>
                    </div>
                </div>
                
                <!-- เงินเดือนที่คาดหวัง -->
                <div class="mb-3">
                    <label for="salary" class="form-label">
                        <i class="fas fa-money-bill-wave me-1"></i>เงินเดือนที่คาดหวัง (บาท)
                    </label>
                    <select class="form-select" id="salary" name="salary">
                        <option value="">-- เลือกเงินเดือน --</option>
                        <option value="15000-20000">15,000 - 20,000 บาท</option>
                        <option value="20000-25000">20,000 - 25,000 บาท</option>
                        <option value="25000-30000">25,000 - 30,000 บาท</option>
                        <option value="30000-35000">30,000 - 35,000 บาท</option>
                        <option value="35000-40000">35,000 - 40,000 บาท</option>
                        <option value="40000-50000">40,000 - 50,000 บาท</option>
                        <option value="50000">มากกว่า 50,000 บาท</option>
                        <option value="ตามโครงสร้างบริษัท">ตามโครงสร้างบริษัท</option>
                    </select>
                </div>
                
                <!-- ปุ่มต่างๆ -->
                <div class="btn-group-custom">
                    <button type="submit" class="btn btn-success btn-custom" name="submit_application">
                        <i class="fas fa-paper-plane me-2"></i>ส่งใบสมัคร
                    </button>
                    <button type="reset" class="btn btn-warning btn-custom">
                        <i class="fas fa-eraser me-2"></i>ล้างฟอร์ม
                    </button>
                    <button type="button" class="btn btn-info btn-custom" onclick="previewData()">
                        <i class="fas fa-eye me-2"></i>ดูตัวอย่างข้อมูล
                    </button>
                    <button type="button" class="btn btn-secondary btn-custom" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>พิมพ์ฟอร์ม
                    </button>
                </div>
            </form>
        </div>
        
        <?php

        
        // ตรวจสอบว่ามีการส่งฟอร์มหรือไม่
        if(isset($_POST['submit_application'])) {
            // เก็บค่าจากฟอร์ม
            $fullname = $_POST['fullname'];
            $phone = $_POST['phone'];
            $email = $_POST['email'];
            $birthday = $_POST['birthday'];
            $address = $_POST['address'];
            $education = $_POST['education'];
            $major = $_POST['major'];
            $institution = $_POST['institution'];
            $graduation_year = $_POST['graduation_year'];
            $position = $_POST['position'];
            $experience = $_POST['experience'];
            $salary = $_POST['salary'];
            
            $host = "localhost";
            $user = "root";
            $pwd = "";
            $db = "4127db";
            $conn = mysqli_connect($host,$user,$pwd,$db) or die ("เชื่อมต่อฐานข้อมูลไม่ได้");
            mysqli_query($conn,"SET NAMES utf8");

            $sql = "INSERT INTO application (a_id,a_name,a_phone,a_email,a_birthday,a_address,a_education,a_major,a_institution,a_graduation_year,a_position,a_experience,a_salary) VALUES (NULL, '{$fullname}','{$phone}','{$email}','{$birthday}','{$address}','{$education}','{$major}','{$institution}','{$graduation_year}','{$position}','{$experience}','{$salary}');";
            mysqli_query($conn, $sql)or die ("insert ไม่ได้");

            echo "<script>";
            echo "alert('บันทึกเเล้ว');";
            echo "</script>";
        }
        
        // แสดงข้อความสำเร็จจากเซสชัน
        if(isset($_SESSION['success_message'])) {
            $success_message = $_SESSION['success_message'];
            unset($_SESSION['success_message']); // ลบข้อความหลังจากแสดงแล้ว
        }
        ?>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>บริษัท ฤชากร คอปเปอร์เรชั่น จำกัด</strong></p>
            <p>โทรศัพท์: 02-XXX-XXXX | อีเมล: hr@rushakorn-corp.co.th</p>
            <p>© 2024 - แบบฟอร์มสมัครงานออนไลน์</p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- JavaScript สำหรับตรวจสอบฟอร์ม -->
    <script>
        // ตรวจสอบเบอร์โทรศัพท์ (เฉพาะตัวเลข)
        document.getElementById('phone').addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // ตรวจสอบอายุผู้สมัครจากวันเกิด
        document.getElementById('birthday').addEventListener('change', function() {
            const birthday = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthday.getFullYear();
            const monthDiff = today.getMonth() - birthday.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthday.getDate())) {
                age--;
            }
            
            if (age < 18 && this.value !== '') {
                alert('ผู้สมัครต้องมีอายุมากกว่า 18 ปี');
                this.value = '';
            }
        });
        
        // ตรวจสอบรูปแบบอีเมล
        document.getElementById('email').addEventListener('blur', function() {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(this.value) && this.value !== '') {
                alert('กรุณากรอกอีเมลให้ถูกต้อง');
                this.focus();
            }
        });
        
        // ฟังก์ชันดูตัวอย่างข้อมูล
        function previewData() {
            const fullname = document.getElementById('fullname').value;
            const phone = document.getElementById('phone').value;
            const email = document.getElementById('email').value;
            const position = document.getElementById('position').value;
            
            if (!fullname || !phone || !email || !position) {
                alert('กรุณากรอกข้อมูลให้ครบถ้วนก่อน');
                return;
            }
            
            let previewText = "=== ตัวอย่างข้อมูลที่กรอก ===\n\n";
            previewText += "ชื่อ-สกุล: " + fullname + "\n";
            previewText += "เบอร์โทร: " + phone + "\n";
            previewText += "อีเมล: " + email + "\n";
            previewText += "ตำแหน่งที่สมัคร: " + position + "\n\n";
            previewText += "กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนส่งใบสมัคร";
            
            alert(previewText);
        }
        
        // ฟังก์ชันตรวจสอบข้อมูลก่อนส่งฟอร์ม
        document.querySelector('form').addEventListener('submit', function(event) {
            const requiredFields = document.querySelectorAll('[required]');
            let isValid = true;
            let firstInvalidField = null;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                    
                    if (!firstInvalidField) {
                        firstInvalidField = field;
                    }
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                event.preventDefault();
                alert('กรุณากรอกข้อมูลในช่องที่จำเป็นให้ครบถ้วน');
                
                if (firstInvalidField) {
                    firstInvalidField.focus();
                }
            }
        });
    </script>
</body>
</html>