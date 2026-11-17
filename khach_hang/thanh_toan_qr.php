<?php
session_start();
include __DIR__ . '/../config/db.php';

// Lấy mã đơn hàng hoặc tổng tiền từ URL hoặc Session vừa đặt xong
$ma_don = isset($_GET['ma_don']) ? $_GET['ma_don'] : 'DH1000';
$tong_tien = isset($_GET['tong_tien']) ? floatval($_GET['tong_tien']) : 250000;

// Lấy thông tin tài khoản ngân hàng từ bảng settings trong CSDL đã cấu hình ở trang Admin
$settings_data = [];
$res = $conn->query("SELECT * FROM settings");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $settings_data[$r['setting_key']] = $r['setting_value'];
    }
}

// Gán thông tin mặc định nếu chưa cài đặt
$ngan_hang = $settings_data['ngan_hang'] ?? 'MB'; // Mã ngân hàng VietQR (Ví dụ: MB, VCB, TCB...)
$so_tk = $settings_data['so_tk'] ?? '1234567890';
$chu_tk = $settings_data['chu_tk'] ?? 'NGUYEN VAN A';

// Link API VietQR chính thức tự động tạo mã QR chứa số tiền và nội dung
$vietqr_url = "https://img.vietqr.io/image/$ngan_hang-$so_tk-compact2.png?amount=$tong_tien&addInfo=$ma_don&accountName=" . urlencode($chu_tk);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán Chuyển Khoản QR - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; padding: 0; color: #4A2E2B; }
        header { background: #4A2E2B; color: #fff; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 24px; }
        header a { color: #F3EBE1; text-decoration: none; font-weight: 600; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); text-align: center; }
        .qr-box { background: #f9f9f9; padding: 20px; border-radius: 10px; display: inline-block; border: 1px solid #eee; margin: 20px 0; }
        .qr-box img { width: 250px; height: 250px; object-fit: contain; }
        .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #f0f0f0; font-size: 15px; text-align: left; }
        .info-row span:last-child { font-weight: bold; color: #4A2E2B; }
        .btn-done { background: #C85A32; color: #fff; border: none; padding: 12px 30px; border-radius: 6px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; margin-top: 25px; font-size: 16px; }
        .btn-done:hover { background: #b04c28; }
    </style>
</head>
<body>

<header>
    <h1>CRAFT HOUSE 🌿</h1>
    <div>
        <a href="index.php">Về Trang Chủ</a>
    </div>
</header>

<div class="container">
    <h2 style="margin-top:0; color:#4A2E2B;">Quét Mã QR Để Thanh Toán</h2>
    <p style="color: #666;">Vui lòng sử dụng ứng dụng Ngân hàng hoặc ví điện tử bất kỳ để quét mã QR bên dưới.</p>

    <div class="qr-box">
        <!-- Hiển thị hình ảnh mã QR được tạo tự động qua API VietQR -->
        <img src="<?= $vietqr_url ?>" alt="Mã VietQR thanh toán">
    </div>

    <div style="background: #fff8dc; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: left; border-left: 4px solid #C85A32;">
        <div class="info-row" style="border: none; padding: 5px 0;">
            <span>Mã đơn hàng:</span>
            <span style="color: #C85A32;"><?= htmlspecialchars($ma_don) ?></span>
        </div>
        <div class="info-row" style="border: none; padding: 5px 0;">
            <span>Số tiền cần chuyển:</span>
            <span style="color: #C85A32; font-size: 1.2em;"><?= number_format($tong_tien, 0, ',', '.') ?> đ</span>
        </div>
        <div class="info-row" style="border: none; padding: 5px 0;">
            <span>Nội dung chuyển khoản:</span>
            <span><?= htmlspecialchars($ma_don) ?></span>
        </div>
    </div>

    <div style="text-align: left;">
        <div class="info-row">
            <span>Ngân hàng:</span>
            <span><?= htmlspecialchars($ngan_hang) ?></span>
        </div>
        <div class="info-row">
            <span>Số tài khoản:</span>
            <span><?= htmlspecialchars($so_tk) ?></span>
        </div>
        <div class="info-row">
            <span>Chủ tài khoản:</span>
            <span><?= htmlspecialchars($chu_tk) ?></span>
        </div>
    </div>

    <a href="index.php" class="btn-done">Hoàn Tất & Về Trang Chủ</a>
</div>

</body>
</html>