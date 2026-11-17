<?php
session_start();
include __DIR__ . '/../config/db.php';

// Kiểm tra xem khách đã đăng nhập chưa
if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit();
}

$username = $_SESSION['user'];

// Lấy thông tin đơn hàng của khách hàng này (dựa vào tên tài khoản hoặc tên khách)
$sql = "SELECT * FROM don_hang WHERE ten_khach = '$username' ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch Sử Mua Hàng - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; padding: 0; color: #4A2E2B; }
        header { background: #4A2E2B; color: #fff; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 24px; }
        header a { color: #F3EBE1; text-decoration: none; font-weight: 600; margin-left: 20px; }
        .container { max-width: 1000px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #f0f0f0; text-align: left; font-size: 14px; }
        th { background: #F3EBE1; color: #4A2E2B; }
        .badge-status { background: #fff3cd; color: #856404; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; }
        .note-box { background: #fff8dc; padding: 6px 10px; border-left: 3px solid #C85A32; font-size: 13px; margin-top: 5px; }
    </style>
</head>
<body>

<header>
    <h1>CRAFT HOUSE 🌿</h1>
    <div>
        <a href="index.php">Trang Chủ</a>
        <a href="giohang.php">🛒 Giỏ Hàng</a>
        <a href="../auth/logout.php" style="color: #ff9999;">Đăng Xuất</a>
    </div>
</header>

<div class="container">
    <h2 style="margin-top:0; color:#4A2E2B;">Lịch Sử Đơn Hàng Của Bạn</h2>
    <p style="color: #666;">Xin chào <b><?= htmlspecialchars($username) ?></b>, dưới đây là trạng thái các món đồ thủ công bạn đã đặt làm tại cửa hàng.</p>

    <table>
        <thead>
            <tr>
                <th>Mã Đơn</th>
                <th>Địa Chỉ Nhận</th>
                <th>Yêu Cầu Tùy Chỉnh</th>
                <th>Tổng Tiền</th>
                <th>Trạng Thái</th>
                <th>Thời Gian</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= htmlspecialchars($row['ma_don']) ?></strong></td>
                    <td><?= htmlspecialchars($row['dia_chi']) ?></td>
                    <td>
                        <?php if(!empty($row['ghi_chu'])): ?>
                            <div class="note-box">✍️ <?= htmlspecialchars($row['ghi_chu']) ?></div>
                        <?php else: ?>
                            <span style="color:#888; font-style:italic;">Không có</span>
                        <?php endif; ?>
                    </td>
                    <td style="color:#C85A32; font-weight:bold;"><?= number_format($row['tong_tien'], 0, ',', '.') ?> đ</td>
                    <td><span class="badge-status"><?= htmlspecialchars($row['trang_thai']) ?></span></td>
                    <td><?= htmlspecialchars($row['ngay_dat']) ?></td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align:center; color:#777; padding: 30px;">Bạn chưa có đơn hàng nào.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>