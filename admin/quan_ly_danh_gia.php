<?php
include __DIR__ . '/../config/db.php';

// Xử lý xóa đánh giá nếu có yêu cầu
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM danh_gia WHERE id = $del_id");
    header("Location: quan_ly_danh_gia.php");
    exit();
}

// Lấy danh sách đánh giá từ CSDL
$sql = "SELECT * FROM danh_gia ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Đánh Giá - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #4A2E2B; color: #fff; height: 100vh; padding: 20px; position: fixed; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; color: #F3EBE1; margin-bottom: 30px; text-align: center; }
        .sidebar a { display: block; color: #ddd; text-decoration: none; padding: 12px 10px; border-radius: 6px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #C85A32; color: #fff; font-weight: bold; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #f0f0f0; text-align: left; font-size: 14px; }
        th { background: #F3EBE1; color: #4A2E2B; }
        .stars { color: #f39c12; font-weight: bold; }
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
        <h2 style="margin-top:0; color:#4A2E2B;">Đánh Giá & Phản Hồi Từ Khách Hàng</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách Hàng</th>
                    <th>Sản Phẩm</th>
                    <th>Số Sao</th>
                    <th>Nội Dung Nhận Xét</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td><strong><?= htmlspecialchars($row['ten_khach']) ?></strong></td>
                        <td><?= htmlspecialchars($row['ten_san_pham']) ?></td>
                        <td><span class="stars"><?= str_repeat('★', intval($row['so_sao'])) ?></span></td>
                        <td><?= htmlspecialchars($row['noi_dung']) ?></td>
                        <td>
                            <a href="quan_ly_danh_gia.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')" style="color: #dc3545; text-decoration:none; font-weight:600;">Xóa</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; color:#777;">Chưa có đánh giá nào từ khách hàng.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>