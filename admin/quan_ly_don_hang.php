<?php
include __DIR__ . '/../config/db.php';

$sql = "SELECT * FROM don_hang ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Đơn Hàng - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #4A2E2B; color: #fff; height: 100vh; padding: 20px; position: fixed; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; color: #F3EBE1; margin-bottom: 30px; text-align: center; }
        .sidebar a { display: block; color: #ddd; text-decoration: none; padding: 12px 10px; border-radius: 6px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #C85A32; color: #fff; font-weight: bold; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #f0f0f0; text-align: left; }
        th { background: #4A2E2B; color: #fff; font-weight: 600; }
        .note-box { background: #fff8dc; padding: 8px 10px; border-left: 3px solid #C85A32; font-size: 13px; margin-top: 4px; border-radius: 0 4px 4px 0; }
        .btn-detail { color: #C85A32; text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="sidebar-brand">
            <h1>CRAFT HOUSE</h1>
            <p>Handmade & Soap</p>
        </div>
        <a href="dashboard.php" class="active">📊 Tổng Quan</a>
        <a href="quan_ly_san_pham.php">🛍️ Quản Lý Sản Phẩm</a>
        <a href="quan_ly_don_hang.php">📦 Quản Lý Đơn Hàng</a>
        <a href="quan_ly_voucher.php">🎟️ Quản Lý Voucher</a>
        <a href="quan_ly_danh_gia.php">⭐ Quản Lý Đánh Giá</a>
        <a href="cai_dat.php">⚙️ Cài Đặt Cửa Hàng</a>
        <a href="bao_cao_doanh_thu.php">📈 Báo Cáo Doanh Thu</a>
    </div>
    <a href="../auth/logout.php" style="color: #FFB3B3; margin-top: 30px;">🚪 Đăng Xuất</a>
</div>

<div class="main-content">
    <div class="card">
        <h2 style="margin-top:0; color:#4A2E2B;">Danh Sách Đơn Hàng Đặt Làm</h2>
        <table>
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Số Điện Thoại</th>
                    <th>Yêu Cầu Tùy Chỉnh (Ghi chú)</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= htmlspecialchars($row['ma_don']) ?></strong></td>
                        <td><?= htmlspecialchars($row['ten_khach']) ?></td>
                        <td><?= htmlspecialchars($row['sdt']) ?></td>
                        <td>
                            <?php if(!empty($row['ghi_chu'])): ?>
                                <div class="note-box">✍️ <?= htmlspecialchars($row['ghi_chu']) ?></div>
                            <?php else: ?>
                                <span style="color:#888; font-style:italic;">Không có ghi chú</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:#C85A32; font-weight:bold;"><?= number_format($row['tong_tien'], 0, ',', '.') ?> đ</td>
                        <td><b><?= htmlspecialchars($row['trang_thai']) ?></b></td>
                        <td>
                            <a href="chi_tiet_don_hang.php?id=<?= $row['id'] ?>" class="btn-detail">Xem chi tiết</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; color:#777;">Chưa có đơn hàng nào được tạo.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>