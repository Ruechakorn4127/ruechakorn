<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ใบสมัครงาน - บริษัท ฤชากร จำกัด</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #FF3535FF;
            --secondary-color: #6c757d;
            --accent-color: #E40000FF;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Tahoma', 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #441361FF 100%);
            color: white;
            padding: 2rem 0;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 2.5rem;
        }
        
        .header h1 {
            font-weight: 700;
            text-shadow: 1px 2px 3px rgba(0, 0, 0, 0.2);
            margin-bottom: 0.5rem;
        }
        
        .header .subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        
        .form-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-of-type {
            border-bottom: none;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #eaeaea;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--accent-color);
        }
        
        .required-field::after {
            content: " *";
            color: #dc3545;
        }
        
        .btn-custom {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            background-color: #0a58ca;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary-custom {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-secondary-custom:hover {
            background-color: #5a6268;
        }
        
        .btn-print-custom {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-print-custom:hover {
            background-color: #e55a2b;
        }
        
        .result-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-top: 2rem;
            border-left: 5px solid var(--primary-color);
        }
        
        .result-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px dashed #eee;
        }
        
        .info-label {
            font-weight: 600;
            color: #555;
            min-width: 180px;
        }
        
        .company-logo {
            font-size: 2.5rem;
            color: white;
            background-color: var(--accent-color);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .footer {
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
            padding: 1.5rem 0;
            margin-top: 2rem;
            border-top: 1px solid #eee;
        }
        
        @media (max-width: 768px) {
            .form-container {
                padding: 1.5rem;
            }
            
            .header h1 {
                font-size: 1.8rem;
            }
            
            .btn-group-responsive {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-group-responsive button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <!-- Header Section -->
    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <div class="company-logo">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                </div>
                <div class="col-md-10">
                    <h1><i class="fas fa-file-contract me-2"></i>ข้อมูลผู้สมัครงาน</h1>
                    <p class="subtitle">บริษัท ฤชากร จำกัด - สมัครงานกับเราและร่วมเป็นส่วนหนึ่งของทีมเทคโนโลยีชั้นนำ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Application Form -->
        <div class="form-container">
            <!-- Results Section -->
<?php
        if(isset($_POST['Submit'])){
            $position = $_POST['position'];
            $title = $_POST['title'];
            $fullname = $_POST['fullname'];
            $birthday = $_POST['birthday'];
            $education = $_POST['education'];
            $skill = $_POST['skill'];
            $experience = $_POST['experience'];
            
            // Format date to Thai style
            $birthday_formatted = date("d/m/Y", strtotime($birthday));
        ?>
        <div class="result-container">
            <h3 class="result-title"><i class="fas fa-user-check me-2"></i>ข้อมูลผู้สมัครงาน</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item d-flex">
                        <div class="info-label">ตำแหน่งที่สมัคร:</div>
                        <div class="info-value fw-bold text-primary"><?php echo $position; ?></div>
                    </div>
                    
                    <div class="info-item d-flex">
                        <div class="info-label">ชื่อ-สกุล:</div>
                        <div class="info-value"><?php echo $title . $fullname; ?></div>
                    </div>
                    
                    <div class="info-item d-flex">
                        <div class="info-label">วันเกิด:</div>
                        <div class="info-value"><?php echo $birthday_formatted; ?></div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-item d-flex">
                        <div class="info-label">ระดับการศึกษา:</div>
                        <div class="info-value"><?php echo $education; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="info-item">
                        <div class="info-label mb-2">ความสามารถพิเศษ:</div>
                        <div class="info-value p-3 bg-light rounded"><?php echo nl2br($skill); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label mb-2">ประสบการณ์ทำงาน:</div>
                        <div class="info-value p-3 bg-light rounded"><?php echo nl2br($experience); ?></div>
                    </div>
                    
                    <div class="alert alert-success mt-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i><strong>ส่งใบสมัครสำเร็จ!</strong> เราได้รับข้อมูลการสมัครงานของคุณแล้ว ทีมงานจะติดต่อกลับภายใน 3-5 วันทำการ
                    </div>
                </div>
            </div>
        </div>
    <?php } ?>
        </div>
        <!-- Footer -->
        <div class="footer">
            <p><i class="fas fa-phone me-1"></i> โทรศัพท์: 0630217119| <i class="fas fa-envelope me-1"></i> อีเมล: 66010914127@mau.ac.th</p>
            <p class="mt-3">© 2023 บริษัท ฤชากร จำกัด. สงวนลิขสิทธิ์.</p>
        </div>
    </div>

    <!-- Bootstrap JS with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Set today as default for birthday field for easier testing
        document.addEventListener('DOMContentLoaded', function() {
            // Set min date to 18 years ago and max to today
            const today = new Date();
            const maxDate = today.toISOString().split('T')[0];
            
            // Calculate date 18 years ago
            const minDate = new Date();
            minDate.setFullYear(today.getFullYear() - 60);
            const minDateStr = minDate.toISOString().split('T')[0];
            
            const birthdayField = document.getElementById('birthday');
            if (birthdayField) {
                birthdayField.setAttribute('max', maxDate);
                birthdayField.setAttribute('min', minDateStr);
                
                // Set default to 25 years ago for easier testing
                const defaultDate = new Date();
                defaultDate.setFullYear(today.getFullYear() - 25);
                const defaultDateStr = defaultDate.toISOString().split('T')[0];
                birthdayField.value = defaultDateStr;
            }
        });
    </script>
</body>
</html>