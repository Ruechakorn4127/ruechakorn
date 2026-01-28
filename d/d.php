<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>66010914127 ฤชากร นัคราภิบาล (ต่อ)</title>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .font {
            color: rgb(255, 0, 0);
            text-shadow: black 1px 1px 2px;
        }
        body {
            background: linear-gradient(120deg, #e0f7fa, #ffffff);
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
    </style>
</head>

<body class="py-5">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card p-4">
                <h2 class="text-center font mb-4">ฟอร์มรับข้อมูล<br>66010914127 ฤชากร นัคราภิบาล (ต่อ) - ChatGPT</h2>

                <form method="post" action="">

                    <div class="mb-3">
                        <label class="form-label">ชื่อ-สกุล *</label>
                        <input type="text" class="form-control" name="fullname" autofocus required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">เบอร์โทร *</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ส่วนสูง (ซม.) *</label>
                        <input type="number" class="form-control" name="height" max="200" min="100" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ที่อยู่</label>
                        <textarea class="form-control" name="address" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">วัน/เดือน/ปีเกิด</label>
                        <input type="date" class="form-control" name="birthday">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">สีที่ชอบ</label>
                        <input type="color" class="form-control form-control-color" name="color">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">สาขาวิชา</label>
                        <select class="form-select" name="major">
                            <option value="การบัญชี">การบัญชี</option>
                            <option value="การตลาด">การตลาด</option>
                            <option value="การจัดการ">การจัดการ</option>
                            <option value="คอมพิเตอร์ธุรกิจ">คอมพิเตอร์ธุรกิจ</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" name="Submit" class="btn btn-success">สมัครสมาชิก</button>
                        <button type="reset" class="btn btn-warning">ล้างเนื้อหา</button>
                        <button type="button" class="btn btn-primary" onclick="window.location='https://reg.msu.ac.th';">GO TO reg.msu</button>
                        <button type="button" class="btn btn-danger" ondblclick="alert('see ya')">see</button>
                        <button type="button" class="btn btn-secondary" onclick="window.print();">ปริ้น</button>
                    </div>

                </form>
            </div>

            <!-- แสดงผล PHP -->
            <?php
            if(isset($_POST['Submit'])){
                $fullname = $_POST['fullname'];
                $phone = $_POST['phone'];
                $height = $_POST['height'];
                $address = $_POST['address'];
                $birthday = $_POST['birthday'];
                $color = $_POST['color'];
                $major = $_POST['major'];
            ?>

            <div class="card mt-4 p-4">
                <h4 class="text-center mb-3">ข้อมูลที่คุณกรอก</h4>
                <p><strong>ชื่อ-สกุล:</strong> <?= $fullname ?></p>
                <p><strong>เบอร์โทร:</strong> <?= $phone ?></p>
                <p><strong>ส่วนสูง:</strong> <?= $height ?> ซม.</p>
                <p><strong>ที่อยู่:</strong> <?= $address ?></p>
                <p><strong>วันเกิด:</strong> <?= $birthday ?></p>
                <p><strong>สาขาวิชา:</strong> <?= $major ?></p>
                <p><strong>สีที่ชอบ:</strong></p>
                <div style="background-color:<?= $color ?>; width:100%; height:40px; border-radius:10px;"></div>
            </div>

            <?php } ?>

        </div>
    </div>
</div>

</body>
</html>
