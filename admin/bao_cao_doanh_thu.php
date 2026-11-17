<?php
session_start();
// Kiểm tra quyền Admin
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}
include __DIR__ . '/../config/db.php';

// Lấy tổng doanh thu và tổng số đơn hàng đã hoàn tất / xác nhận
$sql_stats = "SELECT SUM(tong_tien) AS tong_doanh_thu, COUNT(*) AS tong_don FROM don_hang";
$stats = $conn->query($sql_stats)->fetch_assoc();
$tong_doanh_thu = $stats['tong_doanh_thu'] ?? 0;
$tong_don = $stats['tong_don'] ?? 0;

// Lấy danh sách chi tiết các đơn hàng để lập báo cáo
$result_orders = $conn->query("SELECT * FROM don_hang ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Báo Cáo Doanh Thu - Quản Trị Craft House</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
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
            position: fixed; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
        }
        .sidebar h2 { 
            font-family: 'Playfair Display', serif; 
            font-size: 22px; 
            color: #F6F0E6; 
            margin: 0 0 5px 0; 
            text-align: center; 
            letter-spacing: 2px;
        }
        .sidebar p { 
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
            transition: 0.3s; 
        }
        .sidebar a:hover, .sidebar a.active { 
            background: #C85A32; 
            color: #fff; 
            font-weight: 600; 
        }

        /* Nội dung chính */
        .main-content { 
            margin-left: 260px; 
            padding: 40px; 
            width: calc(100% - 260px); 
        }

        h2.page-title { 
            font-family: 'Playfair Display', serif; 
            font-size: 32px; 
            margin-top: 0; 
            color: #4A2E2B; 
            margin-bottom: 25px; 
        }

        /* Thẻ thống kê tổng quan */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 25px; 
            margin-bottom: 35px; 
        }
        .stat-card { 
            background: #EFE4D6; 
            padding: 30px; 
            border-radius: 20px; 
            border: 1px solid #E2D2C0; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); 
        }
        .stat-card h4 { 
            margin: 0 0 10px 0; 
            color: #8C7368; 
            font-size: 13px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }
        .stat-card .val { 
            font-family: 'Playfair Display', serif; 
            font-size: 38px; 
            font-weight: 700; 
            color: #C85A32; 
        }

        /* Bảng báo cáo chi tiết */
        .card-table { 
            background: #fff; 
            padding: 30px; 
            border-radius: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); 
            border: 1px solid #EBE1D3; 
        }
        .card-table h3 { 
            font-family: 'Playfair Display', serif; 
            font-size: 22px; 
            margin-top: 0; 
            margin-bottom: 20px; 
            color: #4A2E2B; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            padding: 14px 12px; 
            border-bottom: 1px solid #F4ECE1; 
            text-align: left; 
            font-size: 14px; 
        }
        th { 
            background: #F3EBE1; 
            color: #4A2E2B; 
            font-weight: 700; 
        }
        .badge { 
            background: #FFF3CD; 
            color: #856404; 
            padding: 6px 12px; 
            border-radius: 6px; 
            font-size: 12px; 
            font-weight: 700; 
        }
        .btn-print {
            background: #4A2E2B;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 600;
            cursor: pointer;
            float: right;
            transition: 0.2s;
        }
        .btn-print:hover { background: #C85A32; }
    </style>
</head>
<body>

<!-- Sidebar Quản Trị -->
<div class="sidebar">
    <div>
        <h2>CRAFT HOUSE</h2>
        <p>Handmade & Soap</p>
        <a href="dashboard.php">📊 Tổng Quan</a>
        <a href="quan_ly_san_pham.php">🛍️ Quản Lý Sản Phẩm</a>
        <a href="quan_ly_don_hang.php">📦 Quản Lý Đơn Hàng</a>
        <a href="bao_cao_doanh_thu.php" class="active">📈 Báo Cáo Doanh Thu</a>
        <a href="quan_ly_voucher.php">🎟️ Quản Lý Voucher</a>
        <a href="quan_ly_danh_gia.php">⭐ Quản Lý Đánh Giá</a>
        <a href="cai_dat.php">⚙️ Cài Đặt Cửa Hàng</a>
        <a href="bao_cao_doanh_thu.php">📈 Báo Cáo Doanh Thu</a>
    </div>
    <a href="../auth/logout.php" style="color: #FFB3B3; margin-top: 20px;">🚪 Đăng Xuất</a>
</div>

<!-- Nội dung chính -->
<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2 class="page-title" style="margin-bottom:0;">Báo Cáo Doanh Thu Hệ Thống</h2>
        <button class="btn-print" onclick="window.print()">🖨️ In Báo Cáo</button>
    </div>
    <p style="color: #6E544A; margin-bottom: 30px;">Thống kê chi tiết dòng tiền và lịch sử giao dịch bán hàng thủ công.</p>

    <!-- Thẻ Tổng Quan -->
    <div class="stats-grid">
        <div class="stat-card">
            <h4>Tổng Doanh Thu Tích Lũy</h4>
            <div class="val"><?= number_format($tong_doanh_thu, 0, ',', '.') ?> đ</div>
        </div>
        <div class="stat-card">
            <h4>Tổng Số Đơn Hàng Thành Công</h4>
            <div class="val"><?= $tong_don ?> đơn</div>
        </div>
    </div>

    <!-- Bảng Chi Tiết Giao Dịch -->
    <div class="card-table">
        <h3>Lịch Sử Doanh Thu Theo Đơn Hàng</h3>
        <table>
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Số Điện Thoại</th>
                    <th>Yêu Cầu Riêng</th>
                    <th>Doanh Thu</th>
                    <th>Trạng Thái</th>
                    <th>Thời Gian Đặt</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_orders && $result_orders->num_rows > 0): ?>
                    <?php while($row = $result_orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= htmlspecialchars($row['ma_don']) ?></strong></td>
                        <td><?= htmlspecialchars($row['ten_khach']) ?></td>
                        <td><?= htmlspecialchars($row['sdt']) ?></td>
                        <td><?= !empty($row['ghi_chu']) ? htmlspecialchars($row['ghi_chu']) : '<em style="color:#888;">Không có</em>' ?></td>
                        <td style="color:#C85A32; font-weight: bold;"><?= number_format($row['tong_tien'], 0, ',', '.') ?> đ</td>
                        <td><span class="badge"><?= htmlspecialchars($row['trang_thai']) ?></span></td>
                        <td><?= htmlspecialchars($row['ngay_dat']) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: #777; padding: 30px;">Chưa có dữ liệu doanh thu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>