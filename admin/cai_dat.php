<?php
include __DIR__ . '/../config/db.php';

$thongbao = "";

// Xử lý lưu cấu hình
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_shop = $conn->real_escape_string($_POST['ten_shop']);
    $sdt_shop = $conn->real_escape_string($_POST['sdt_shop']);
    $ngan_hang = $conn->real_escape_string($_POST['ngan_hang']);
    $so_tk = $conn->real_escape_string($_POST['so_tk']);
    $chu_tk = $conn->real_escape_string($_POST['chu_tk']);

    // Lưu cấu hình vào bảng settings (hoặc file cấu hình)
    // Ở đây ta dùng cơ chế lưu vào bảng settings trong MySQL
    $conn->query("CREATE TABLE IF NOT EXISTS settings (id INT AUTO_INCREMENT PRIMARY KEY, setting_key VARCHAR(50) UNIQUE, setting_value TEXT)");
    
    $settings = [
        'ten_shop' => $ten_shop,
        'sdt_shop' => $sdt_shop,
        'ngan_hang' => $ngan_hang,
        'so_tk' => $so_tk,
        'chu_tk' => $chu_tk
    ];

    foreach ($settings as $key => $val) {
        $conn->query("INSERT INTO settings (setting_key, setting_value) VALUES ('$key', '$val') ON DUPLICATE KEY UPDATE setting_value = '$val'");
    }

    $thongbao = "Cập nhật thông tin cửa hàng thành công!";
}

// Lấy thông tin cấu hình hiện tại
$settings_data = [];
$res = $conn->query("SELECT * FROM settings");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $settings_data[$r['setting_key']] = $r['setting_value'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Cài Đặt Cửa Hàng - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #4A2E2B; color: #fff; height: 100vh; padding: 20px; position: fixed; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; color: #F3EBE1; margin-bottom: 30px; text-align: center; }
        .sidebar a { display: block; color: #ddd; text-decoration: none; padding: 12px 10px; border-radius: 6px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #C85A32; color: #fff; font-weight: bold; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .card { background: #fff; padding: 30px; border-radius: 12px; max-width: 650px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #4A2E2B; font-size: 14px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .btn-submit { background: #C85A32; color: #fff; border: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 15px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>CRAFT HOUSE</h2>
    <a href="dashboard.php">📊 Tổng Quan</a>
    <a href="quan_ly_san_pham.php">🛍️ Quản Lý Sản Phẩm</a>
    <a href="quan_ly_don_hang.php">📦 Quản Lý Đơn Hàng</a>
    <a href="quan_ly_voucher.php">🎟️ Quản Lý Voucher</a>
    <a href="quan_ly_danh_gia.php">⭐ Quản Lý Đánh Giá</a>
    <a href="cai_dat.php" class="active">⚙️ Cài Đặt Cửa Hàng</a>
</div>

<div class="main-content">
    <div class="card">
        <h2 style="margin-top:0; color:#4A2E2B;">Cấu Hình Cửa Hàng & Thanh Toán VietQR</h2>
        <?php if($thongbao): ?><div class="success"><?= $thongbao ?></div><?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>Tên Cửa Hàng:</label>
                <input type="text" name="ten_shop" value="<?= htmlspecialchars($settings_data['ten_shop'] ?? 'CRAFT HOUSE') ?>" required>
            </div>
            <div class="form-group">
                <label>Số Điện Thoại Liên Hệ:</label>
                <input type="text" name="sdt_shop" value="<?= htmlspecialchars($settings_data['sdt_shop'] ?? '0901234567') ?>" required>
            </div>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 25px 0;">
            <h3 style="color: #4A2E2B; margin-top: 0;">Thông Tin Nhận Tiền Chuyển Khoản (VietQR)</h3>
            <div class="form-group">
                <label>Tên Ngân Hàng (Ví dụ: MBBank, Techcombank, VCB):</label>
                <input type="text" name="ngan_hang" value="<?= htmlspecialchars($settings_data['ngan_hang'] ?? 'MBBank') ?>" required>
            </div>
            <div class="form-group">
                <label>Số Tài Khoản Ngân Hàng:</label>
                <input type="text" name="so_tk" value="<?= htmlspecialchars($settings_data['so_tk'] ?? '1234567890') ?>" required>
            </div>
            <div class="form-group">
                <label>Tên Chủ Tài Khoản (Viết hoa không dấu):</label>
                <input type="text" name="chu_tk" value="<?= htmlspecialchars($settings_data['chu_tk'] ?? 'NGUYEN VAN A') ?>" required>
            </div>
            <button type="submit" class="btn-submit">Lưu Cấu Hình</button>
        </form>
    </div>
</div>

</body>
</html>