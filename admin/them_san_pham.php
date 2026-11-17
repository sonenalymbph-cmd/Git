<?php
include __DIR__ . '/../config/db.php';

$thongbao = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tensp = $conn->real_escape_string($_POST['tensp']);
    $danhmuc = $conn->real_escape_string($_POST['danhmuc']);
    $giaban = floatval($_POST['giaban']);
    $tonkho = intval($_POST['tonkho']);
    $trangthai = $conn->real_escape_string($_POST['trangthai']);
    
    $hinhanh = "default.jpg";
    if (isset($_FILES['hinhanh']) && $_FILES['hinhanh']['error'] === 0) {
        $hinhanh = time() . '_' . basename($_FILES['hinhanh']['name']);
        @mkdir('../assets/images', 0777, true);
        move_uploaded_file($_FILES['hinhanh']['tmp_name'], '../assets/images/' . $hinhanh);
    }

    $sql = "INSERT INTO san_pham (tensp, danhmuc, giaban, tonkho, trangthai, hinhanh) VALUES ('$tensp', '$danhmuc', '$giaban', '$tonkho', '$trangthai', '$hinhanh')";
    if ($conn->query($sql) === TRUE) {
        $thongbao = "Thêm sản phẩm thành công!";
    } else {
        $thongbao = "Lỗi cơ sở dữ liệu: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Sản Phẩm - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #4A2E2B; color: #fff; height: 100vh; padding: 20px; position: fixed; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; color: #F3EBE1; margin-bottom: 30px; text-align: center; }
        .sidebar a { display: block; color: #ddd; text-decoration: none; padding: 12px 10px; border-radius: 6px; margin-bottom: 5px; }
        .sidebar a:hover { background: #C85A32; color: #fff; font-weight: bold; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .card { background: #fff; padding: 30px; border-radius: 12px; max-width: 650px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #4A2E2B; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .btn-submit { background: #C85A32; color: #fff; border: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
        a.back { text-decoration: none; color: #666; display: inline-block; margin-top: 15px; font-weight: 600; }
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
        <h2 style="margin-top:0; color:#4A2E2B;">Thêm Sản Phẩm Thủ Công Mới</h2>
        <?php if($thongbao): ?><div class="success"><?= $thongbao ?></div><?php endif; ?>
        
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên Sản Phẩm:</label>
                <input type="text" name="tensp" required placeholder="Ví dụ: Nến thơm đậu nành thảo mộc">
            </div>
            <div class="form-group">
                <label>Danh Mục:</label>
                <select name="danhmuc">
                    <option value="Nến thơm">Nến thơm</option>
                    <option value="Gốm sứ">Gốm sứ</option>
                    <option value="Túi vải">Túi vải / Macrame</option>
                    <option value="Phụ kiện">Phụ kiện thủ công</option>
                </select>
            </div>
            <div class="form-group">
                <label>Giá Bán (đ):</label>
                <input type="number" name="giaban" required placeholder="150000">
            </div>
            <div class="form-group">
                <label>Số Lượng Tồn Kho:</label>
                <input type="number" name="tonkho" value="10" required>
            </div>
            <div class="form-group">
                <label>Trạng Thái:</label>
                <select name="trangthai">
                    <option value="cosan">Có sẵn (Giao ngay)</option>
                    <option value="datlam">Đặt làm (3-5 ngày)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Hình Ảnh Sản Phẩm:</label>
                <input type="file" name="hinhanh">
            </div>
            <button type="submit" class="btn-submit">Lưu Sản Phẩm</button>
        </form>
        <a href="quan_ly_san_pham.php" class="back">← Quay lại danh sách sản phẩm</a>
    </div>
</div>

</body>
</html>