<?php
include __DIR__ . '/../config/db.php';

$thongbao = "";

// Xử lý thêm voucher mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code']));
    $type = $_POST['type']; // 'percent' hoặc 'fixed'
    $value = floatval($_POST['value']);

    if (!empty($code)) {
        // Kiểm tra xem mã đã tồn tại chưa
        $check = $conn->query("SELECT * FROM vouchers WHERE code = '$code'");
        if ($check && $check->num_rows > 0) {
            $thongbao = "Mã giảm giá này đã tồn tại trong hệ thống!";
        } else {
            $sql = "INSERT INTO vouchers (code, type, value, active) VALUES ('$code', '$type', '$value', 1)";
            if ($conn->query($sql) === TRUE) {
                $thongbao = "Thêm mã giảm giá thành công!";
            } else {
                $thongbao = "Lỗi: " . $conn->error;
            }
        }
    }
}

// Xử lý xóa voucher
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM vouchers WHERE id = $del_id");
    header("Location: quan_ly_voucher.php");
    exit();
}

// Lấy danh sách voucher từ CSDL
$result = $conn->query("SELECT * FROM vouchers ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Voucher - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #4A2E2B; color: #fff; height: 100vh; padding: 20px; position: fixed; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; color: #F3EBE1; margin-bottom: 30px; text-align: center; }
        .sidebar a { display: block; color: #ddd; text-decoration: none; padding: 12px 10px; border-radius: 6px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #C85A32; color: #fff; font-weight: bold; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .grid-layout { display: grid; grid-template-columns: 1fr 2fr; gap: 30px; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; color: #4A2E2B; font-size: 14px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .btn-submit { background: #C85A32; color: #fff; border: none; padding: 10px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; font-size: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #f0f0f0; text-align: left; font-size: 14px; }
        th { background: #F3EBE1; color: #4A2E2B; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; }
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
    <h2 style="margin-top:0; color:#4A2E2B;">Quản Lý Mã Giảm Giá (Voucher)</h2>

    <div class="grid-layout">
        <!-- Form thêm voucher -->
        <div class="card">
            <h3 style="margin-top:0; color:#4A2E2B;">Tạo Mã Mới</h3>
            <?php if($thongbao): ?><div class="success"><?= $thongbao ?></div><?php endif; ?>

            <form action="" method="POST">
                <div class="form-group">
                    <label>Mã Voucher (Ví dụ: TET2026):</label>
                    <input type="text" name="code" required placeholder="Nhập mã viết hoa...">
                </div>
                <div class="form-group">
                    <label>Loại Giảm Giá:</label>
                    <select name="type">
                        <option value="percent">Giảm theo phần trăm (%)</option>
                        <option value="fixed">Giảm số tiền cố định (đ)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Giá Trị Giảm:</label>
                    <input type="number" name="value" required placeholder="Nhập số (vd: 10 hoặc 50000)...">
                </div>
                <button type="submit" class="btn-submit">Tạo Voucher</button>
            </form>
        </div>

        <!-- Danh sách voucher -->
        <div class="card">
            <h3 style="margin-top:0; color:#4A2E2B;">Danh Sách Mã Đang Hoạt Động</h3>
            <table>
                <thead>
                    <tr>
                        <th>Mã Code</th>
                        <th>Loại Giảm</th>
                        <th>Giá Trị</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($row['code']) ?></strong></td>
                            <td><?= $row['type'] === 'percent' ? 'Phần trăm (%)' : 'Tiền mặt (đ)' ?></td>
                            <td style="color:#C85A32; font-weight:bold;">
                                <?= $row['type'] === 'percent' ? $row['value'] . '%' : number_format($row['value'], 0, ',', '.') . ' đ' ?>
                            </td>
                            <td><span style="color: green; font-weight: bold;">Hoạt động</span></td>
                            <td>
                                <a href="quan_ly_voucher.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc muốn xóa mã này?')" style="color: #dc3545; text-decoration:none; font-weight:600;">Xóa</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; color:#777;">Chưa có mã giảm giá nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>