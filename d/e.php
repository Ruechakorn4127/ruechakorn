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
                    <h1><i class="fas fa-file-contract me-2"></i>ใบสมัครงาน</h1>
                    <p class="subtitle">บริษัท ฤชากร จำกัด - สมัครงานกับเราและร่วมเป็นส่วนหนึ่งของทีมเทคโนโลยีชั้นนำ</p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Application Form -->
        <div class="form-container">
            <form method="post" action="f.php">
                <!-- Personal Information Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-user-circle"></i>ข้อมูลส่วนตัว</h3>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="title" class="form-label required-field">คำนำหน้าชื่อ</label>
                            <select class="form-select" id="title" name="title" required>
                                <option value="">-- กรุณาเลือกคำนำหน้า --</option>
                                <option value="นาย">นาย</option>
                                <option value="นางสาว">นางสาว</option>
                                <option value="นาง">นาง</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="fullname" class="form-label required-field">ชื่อ-สกุล</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" placeholder="กรุณากรอกชื่อและนามสกุล" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="birthday" class="form-label required-field">วัน/เดือน/ปีเกิด</label>
                            <input type="date" class="form-control" id="birthday" name="birthday" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label for="education" class="form-label required-field">ระดับการศึกษา</label>
                            <select class="form-select" id="education" name="education" required>
                                <option value="">-- กรุณาเลือกระดับการศึกษา --</option>
                                <option value="มัธยมศึกษา">มัธยมศึกษา</option>
                                <option value="ปวช.">ปวช.</option>
                                <option value="ปวส.">ปวส.</option>
                                <option value="ปริญญาตรี">ปริญญาตรี</option>
                                <option value="ปริญญาโท">ปริญญาโท</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Job Application Section -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-briefcase"></i>ข้อมูลการสมัครงาน</h3>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="position" class="form-label required-field">ตำแหน่งที่ต้องการสมัคร</label>
                            <select class="form-select" id="position" name="position" required>
                                <option value="">-- กรุณาเลือกตำแหน่ง --</option>
                                <option value="โปรแกรมเมอร์">โปรแกรมเมอร์</option>
                                <option value="เจ้าหน้าที่การตลาด">เจ้าหน้าที่การตลาด</option>
                                <option value="ธุรการ">ธุรการ</option>
                                <option value="พนักงานบัญชี">พนักงานบัญชี</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="skill" class="form-label required-field">ความสามารถพิเศษ</label>
                            <textarea class="form-control" id="skill" name="skill" rows="3" placeholder="กรุณาระบุความสามารถพิเศษต่างๆ ของคุณ" required></textarea>
                            <div class="form-text">เช่น ทักษะด้านภาษา, คอมพิวเตอร์, การสื่อสาร ฯลฯ</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="experience" class="form-label required-field">ประสบการณ์ทำงาน</label>
                            <textarea class="form-control" id="experience" name="experience" rows="3" placeholder="กรุณาระบุประสบการณ์ทำงานที่เกี่ยวข้อง" required></textarea>
                            <div class="form-text">ระบุชื่อบริษัท, ตำแหน่ง, ระยะเวลา และหน้าที่ความรับผิดชอบ</div>
                        </div>
                    </div>
                </div>
                
                <!-- Form Buttons -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="btn-group-responsive d-md-flex justify-content-md-end gap-2">
                            <button type="submit" name="Submit" class="btn btn-custom">
                                <i class="fas fa-paper-plane me-2"></i>ส่งใบสมัคร
                            </button>
                            <button type="reset" class="btn btn-secondary-custom">
                                <i class="fas fa-redo me-2"></i>ล้างข้อมูล
                            </button>
                            <button type="button" class="btn btn-print-custom" onclick="window.print();">
                                <i class="fas fa-print me-2"></i>พิมพ์ใบสมัคร
                            </button>
                        </div>
                        <p class="text-muted mt-3"><small>หมายเหตุ: ช่องที่มีเครื่องหมาย * จำเป็นต้องกรอกข้อมูล</small></p>
                    </div>
                </div>
            </form>
        </div>
        <!-- Footer -->
        <div class="footer">
            <p><i class="fas fa-phone me-1"></i> โทรศัพท์: 0630217119 | <i class="fas fa-envelope me-1"></i> อีเมล: 66010914127@mau.ac.th</p>
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