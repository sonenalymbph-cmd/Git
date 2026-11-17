<?php
session_start();
include __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ten_khach = $conn->real_escape_string($_POST['ten_khach']);
    $sdt = $conn->real_escape_string($_POST['sdt']);
    $dia_chi = $conn->real_escape_string($_POST['dia_chi']);
    $ghi_chu = $conn->real_escape_string($_POST['ghi_chu']);
    $tong_tien = floatval($_POST['total_price']);
    
    // Tạo mã đơn hàng ngẫu nhiên duy nhất
    $ma_don = 'DH' . rand(1000, 9999);
    $trang_thai = 'Chờ xác nhận';

    $sql = "INSERT INTO don_hang (ma_don, ten_khach, sdt, dia_chi, ghi_chu, tong_tien, trang_thai) 
            VALUES ('$ma_don', '$ten_khach', '$sdt', '$dia_chi', '$ghi_chu', '$tong_tien', '$trang_thai')";

    if ($conn->query($sql) === TRUE) {
        // Xóa giỏ hàng sau khi đặt thành công
        unset($_SESSION['cart']);
        ?>
        <!DOCTYPE html>
        <html lang="vi">
        <head>
            <meta charset="UTF-8">
            <title>Đặt Hàng Thành Công</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; text-align: center; padding-top: 80px; }
                .success-box { background: #fff; max-width: 500px; margin: auto; padding: 40px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
                h2 { color: #155724; }
                a { background: #C85A32; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="success-box">
                <h2>🎉 Đặt Hàng Thành Công!</h2>
                <p>Mã đơn hàng của bạn: <strong>#<?= $ma_don ?></strong></p>
                <p>Cảm ơn bạn đã ủng hộ sản phẩm thủ công của Craft House. Thợ thủ công sẽ sớm liên hệ xác nhận đơn hàng cho bạn!</p>
                <a href="index.php">Quay lại trang chủ</a>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Lỗi hệ thống khi lưu đơn hàng: " . $conn->error;
    }
}
?>