<?php
session_start();
// Kiểm tra quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include __DIR__ . '/../config/db.php';

// Lấy số liệu thực tế từ CSDL
$doanhthu = $conn->query("SELECT SUM(tong_tien) AS total FROM don_hang")->fetch_assoc()['total'] ?? 0;
$donhang = $conn->query("SELECT COUNT(*) AS total FROM don_hang")->fetch_assoc()['total'] ?? 0;
$sanpham = $conn->query("SELECT COUNT(*) AS total FROM san_pham")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Quản Trị - Craft House</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        * { box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #F6F0E6; 
            margin: 0; 
            display: flex; 
            color: #4A2E2B; 
        }

        /* Sidebar bên trái */
        .sidebar { 
            width: 260px; 
            background: #4A2E2B; 
            color: #fff; 
            min-height: 100vh; 
            padding: 30px 20px; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between;
            position: fixed;
        }
        .sidebar-brand h1 { 
            font-family: 'Playfair Display', serif; 
            font-size: 24px; 
            color: #F6F0E6; 
            margin: 0 0 5px 0; 
            text-align: center;
            letter-spacing: 2px;
        }
        .sidebar-brand p { 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 3px; 
            color: #C85A32; 
            text-align: center; 
            margin-bottom: 35px;
            font-weight: 700;
        }
        .sidebar a { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
            color: #E5D5C5; 
            text-decoration: none; 
            padding: 12px 18px; 
            border-radius: 25px; 
            margin-bottom: 8px; 
            font-size: 14px; 
            font-weight: 500;
            transition: all 0.3s ease; 
        }
        .sidebar a:hover, .sidebar a.active { 
            background: #C85A32; 
            color: #fff; 
            font-weight: 600; 
        }

        /* Khung nội dung chính */
        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
            width: calc(100% - 260px); 
        }

        /* Banner Hero Vòm Cong */
        .hero-banner { 
            background: #EFE4D6; 
            border-radius: 24px; 
            padding: 35px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            position: relative; 
            overflow: hidden;
            margin-bottom: 25px;
            border: 1px solid #E2D2C0;
        }
        .hero-text { max-width: 55%; }
        .hero-text .subtitle { 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            font-size: 12px; 
            color: #C85A32; 
            font-weight: 700; 
            margin-bottom: 10px; 
        }
        .hero-text h2 { 
            font-family: 'Playfair Display', serif; 
            font-size: 38px; 
            line-height: 1.1; 
            color: #4A2E2B; 
            margin: 0 0 12px 0; 
        }
        .hero-text p { color: #6E544A; font-size: 14px; margin-bottom: 20px; line-height: 1.5; }
        .btn-primary { 
            display: inline-block; 
            background: #C85A32; 
            color: #fff; 
            padding: 12px 28px; 
            border-radius: 30px; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 14px; 
            transition: 0.3s;
        }
        .btn-primary:hover { background: #AA4723; }

        .hero-visual {
            width: 32%;
            height: 160px;
            background: #C85A32;
            border-top-left-radius: 90px;
            border-bottom-left-radius: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .badge-stamp {
            width: 100px;
            height: 100px;
            background: #F2C94C;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #4A2E2B;
            font-size: 9px;
            font-weight: 800;
            padding: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transform: rotate(-10deg);
        }

        /* Thanh đệm tính năng */
        .feature-bar {
            background: #D8CEE8; 
            border-radius: 16px;
            padding: 16px 30px;
            display: flex;
            justify-content: space-around;
            margin-bottom: 25px;
            color: #4A2E2B;
            font-size: 13px;
            font-weight: 600;
        }
        .feature-item { display: flex; align-items: center; gap: 8px; }

        /* Lưới thống kê */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px; 
            margin-bottom: 30px;
        }
        .stat-card { 
            background: #fff; 
            padding: 22px; 
            border-radius: 16px; 
            border: 1px solid #EBE1D3; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card h4 { margin: 0 0 8px 0; color: #8C7368; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
        .stat-card .val { font-family: 'Playfair Display', serif; font-size: 30px; font-weight: 700; color: #C85A32; }

        /* SECTION NÚT BẤM CHỨC NĂNG (QUICK ACTIONS) */
        .section-title { font-family: 'Playfair Display', serif; font-size: 24px; margin-bottom: 18px; color: #4A2E2B; }
        
        .action-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 20px; 
        }

        .action-card {
            background: #EFE4D6;
            border: 1px solid #E2D2C0;
            border-radius: 20px;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-start;
            text-decoration: none;
            color: #4A2E2B;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .action-card:hover {
            background: #fff;
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(74, 46, 43, 0.08);
            border-color: #C85A32;
        }

        .action-icon {
            font-size: 32px;
            background: #F6F0E6;
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
            border: 1px solid #E2D2C0;
            transition: 0.3s;
        }

        .action-card:hover .action-icon {
            background: #C85A32;
            color: #fff;
            border-color: #C85A32;
        }

        .action-card h4 {
            margin: 0 0 6px 0;
            font-size: 18px;
            font-weight: 700;
        }

        .action-card p {
            margin: 0 0 20px 0;
            font-size: 13px;
            color: #7A6258;
            line-height: 1.4;
        }

        .action-btn-link {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #C85A32;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .action-card:hover .action-btn-link {
            color: #AA4723;
        }
    </style>
</head>
<body>

<!-- Sidebar Quản Trị -->
<div class="sidebar">
    <div>
        <div class="sidebar-brand">
            <h1>CRAFT HOUSE</h1>
            <p>Handmade & Soap</p>
        </div>
        <a href="dashboard.php" class="active">📊 Tổng Quan</a>
        <a href="quan_ly_san_pham.php">🛍️ Quản Lý Sản Phẩm</a>
        <a href="quan_ly_gia_ca.php">💰 Quản Lý Giá Cả</a>
        <a href="quan_ly_don_hang.php">📦 Quản Lý Đơn Hàng</a>
        <a href="quan_ly_nhan_vien.php">👥 Quản Lý Nhân Viên</a>
        <a href="quan_ly_voucher.php">🎟️ Quản Lý Voucher</a>
        <a href="quan_ly_danh_gia.php">⭐ Quản Lý Đánh Giá</a>
        <a href="cai_dat.php">⚙️ Cài Đặt Cửa Hàng</a>
        <a href="bao_cao_doanh_thu.php">📈 Báo Cáo Doanh Thu</a>
    </div>
    <a href="../auth/logout.php" style="color: #FFB3B3; margin-top: 30px;">🚪 Đăng Xuất</a>
</div>

<!-- Main Content -->
<div class="main-content">

    <!-- Hero Section Banner -->
    <div class="hero-banner">
        <div class="hero-text">
            <div class="subtitle">Bảng Quản Trị Hệ Thống</div>
            <h2>Good clean fun.</h2>
            <p>Tổng quan tình hình kinh doanh các sản phẩm xà bông, nến thơm và đồ thủ công mộc mạc.</p>
            <a href="quan_ly_san_pham.php" class="btn-primary">Thêm Sản Phẩm Mới +</a>
        </div>
        <div class="hero-visual">
            <div class="badge-stamp">
                <span>HANDMADE</span>
                <span style="font-size: 14px;">🌿</span>
                <span>WITH LOVE</span>
            </div>
        </div>
    </div>

    <!-- Feature Bar -->
    <div class="feature-bar">
        <div class="feature-item">🌱 100% Nguyên Liệu Tự Nhiên</div>
        <div class="feature-item">🐰 Chế Tác Thủ Công</div>
        <div class="feature-item">♻️ Bao Bì Thân Thiện Môi Trường</div>
    </div>

    <!-- Thống kê -->
    <div class="stats-grid">
        <div class="stat-card">
            <h4>Tổng Doanh Thu</h4>
            <div class="val"><?= number_format($doanhthu, 0, ',', '.') ?> đ</div>
        </div>
        <div class="stat-card">
            <h4>Đơn Hàng Đã Đặt</h4>
            <div class="val"><?= $donhang ?></div>
        </div>
        <div class="stat-card">
            <h4>Sản Phẩm Đang Bán</h4>
            <div class="val"><?= $sanpham ?></div>
        </div>
    </div>

    <!-- PHẦN NÚT BẤM CHỨC NĂNG QUAN TRỌNG (THAY THẾ KHOẢNH SẢN PHẨM CŨ) -->
    <h3 class="section-title">Chức Năng Quản Lý Nhanh</h3>
    <div class="action-grid">

        <!-- Nút 1: Quản lý Sản Phẩm -->
        <a href="quan_ly_san_pham.php" class="action-card">
            <div>
                <div class="action-icon">🛍️</div>
                <h4>Quản Lý Sản Phẩm</h4>
                <p>Thêm mới, sửa thông tin, cập nhật giá và số lượng kho các mặt hàng thủ công.</p>
            </div>
            <div class="action-btn-link">Truy cập ngay →</div>
        </a>

        <!-- Nút 2: Quản lý Đơn Hàng -->
        <a href="quan_ly_don_hang.php" class="action-card">
            <div>
                <div class="action-icon">📦</div>
                <h4>Quản Lý Đơn Hàng</h4>
                <p>Xem danh sách khách mua, kiểm tra ghi chú thêu tên/tùy chỉnh và duyệt trạng thái.</p>
            </div>
            <div class="action-btn-link">Truy cập ngay →</div>
        </a>

        <!-- Nút 3: Quản lý Voucher -->
        <a href="quan_ly_voucher.php" class="action-card">
            <div>
                <div class="action-icon">🎟️</div>
                <h4>Mã Giảm Giá (Voucher)</h4>
                <p>Tạo và quản lý các mã khuyến mãi giảm theo % hoặc số tiền cố định cho khách.</p>
            </div>
            <div class="action-btn-link">Truy cập ngay →</div>
        </a>

        <!-- Nút 4: Quản lý Đánh Giá -->
        <a href="quan_ly_danh_gia.php" class="action-card">
            <div>
                <div class="action-icon">⭐</div>
                <h4>Đánh Giá & Phản Hồi</h4>
                <p>Xem bình luận, số sao chấm của khách hàng về sản phẩm xà bông/đồ handmade.</p>
            </div>
            <div class="action-btn-link">Truy cập ngay →</div>
        </a>

        <!-- Nút 5: Cài Đặt Shop & VietQR -->
        <a href="cai_dat.php" class="action-card">
            <div>
                <div class="action-icon">⚙️</div>
                <h4>Cài Đặt VietQR & Shop</h4>
                <p>Thiết lập tên tiệm, số điện thoại và thông tin tài khoản ngân hàng nhận tiền QR.</p>
            </div>
            <div class="action-btn-link">Truy cập ngay →</div>
        </a>

        <!-- Nút 6: Xem Trang Khách Hàng -->
        <a href="../khach_hang/index.php" target="_blank" class="action-card">
            <div>
                <div class="action-icon">🌐</div>
                <h4>Xem Trang Cửa Hàng</h4>
                <p>Mở giao diện khách hàng để trải nghiệm thử việc xem sản phẩm và đặt mua.</p>
            </div>
            <div class="action-btn-link">Mở giao diện khách ↗</div>
        </a>

        <!-- Nút 7: Quản lý Nhân Viên -->
        <a href="quan_ly_nhan_vien.php" class="action-card">
            <div>
                <div class="action-icon">👥</div>
                <h4>Quản Lý Nhân Viên</h4>
                <p>Thêm, sửa, tìm kiếm và quản lý thông tin nhân viên cửa hàng.</p>
            </div>
            <div class="action-btn-link">Truy cập ngay →</div>
        </a>

        <a href="quan_ly_gia_ca.php" class="action-card">
            <div><div class="action-icon">💰</div><h4>Quản Lý Giá Cả</h4>
            <p>Xem bảng giá, cập nhật giá bán và điều chỉnh tăng giảm theo phần trăm.</p></div>
            <div class="action-btn-link">Truy cập ngay →</div>
        </a>
    </div>

</div>

</body>
</html>