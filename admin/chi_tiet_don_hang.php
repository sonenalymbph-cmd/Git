<?php
include __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = $conn->query("SELECT * FROM don_hang WHERE id = $id");
$order = $result->fetch_assoc();

if (!$order) {
    die("Không tìm thấy đơn hàng!");
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Đơn Hàng #<?= htmlspecialchars($order['ma_don']) ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; padding: 40px; margin: 0; }
        .invoice-card { background: #fff; padding: 40px; border-radius: 12px; max-width: 700px; margin: auto; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px dashed #ddd; padding-bottom: 20px; margin-bottom: 25px; }
        .info-group { margin-bottom: 12px; font-size: 15px; }
        .note-box { background: #fff8dc; padding: 12px; border-left: 4px solid #C85A32; margin: 20px 0; border-radius: 0 6px 6px 0; }
        .total { text-align: right; font-size: 1.3em; font-weight: bold; color: #C85A32; margin-top: 25px; border-top: 1px solid #eee; padding-top: 15px; }
        .btn-print { background: #4A2E2B; color: #fff; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-back { text-decoration: none; color: #666; font-weight: 600; line-height: 35px; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none; }
            .invoice-card { box-shadow: none; padding: 0; }
        }
    </style>
</head>

<body>

<div class="invoice-card">
    <div class="header">
        <h2 style="margin:0; color:#4A2E2B;">CRAFT HOUSE - ĐỒ HANDMADE</h2>
        <p style="margin:5px 0; color:#666;">Phiếu đóng gói & Hóa đơn khách hàng</p>
        <h3 style="margin-top:10px; color:#C85A32;">Mã đơn: #<?= htmlspecialchars($order['ma_don']) ?></h3>
    </div>

    <div class="info-group"><strong>Khách hàng:</strong> <?= htmlspecialchars($order['ten_khach']) ?></div>
    <div class="info-group"><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['sdt']) ?></div>
    <div class="info-group"><strong>Địa chỉ giao:</strong> <?= htmlspecialchars($order['dia_chi']) ?></div>
    <div class="info-group"><strong>Thời gian đặt:</strong> <?= htmlspecialchars($order['ngay_dat']) ?></div>

    <?php if(!empty($order['ghi_chu'])): ?>
    <div class="note-box">
        <strong>✍️ Yêu cầu tùy chỉnh riêng của khách:</strong><br>
        <?= htmlspecialchars($order['ghi_chu']) ?>
    </div>
    <?php endif; ?>

    <div class="total">Tổng thanh toán: <?= number_format($order['tong_tien'], 0, ',', '.') ?> đ</div>

    <div class="no-print" style="margin-top: 35px; display: flex; justify-content: space-between; align-items: center;">
        <a href="quan_ly_don_hang.php" class="btn-back">← Quay lại danh sách đơn hàng</a>
        <button onclick="window.print()" class="btn-print">🖨️ In Hóa Đơn</button>
    </div>
</div>

</body>
</html>