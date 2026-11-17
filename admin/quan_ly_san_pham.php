<?php
include __DIR__ . '/../config/db.php';

// Xử lý xóa sản phẩm nếu có yêu cầu truyền id trên URL
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM san_pham WHERE id = $del_id");
    header("Location: quan_ly_san_pham.php");
    exit();
}

$sql = "SELECT * FROM san_pham ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Sản Phẩm - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #4A2E2B; color: #fff; height: 100vh; padding: 20px; position: fixed; box-sizing: border-box; }
        .sidebar h2 { font-size: 20px; color: #F3EBE1; margin-bottom: 30px; text-align: center; }
        .sidebar a { display: block; color: #ddd; text-decoration: none; padding: 12px 10px; border-radius: 6px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #C85A32; color: #fff; font-weight: bold; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .btn-add { background: #C85A32; color: #fff; padding: 10px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; float: right; }
        .search-box { padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 6px; margin-bottom: 20px; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #f0f0f0; text-align: left; }
        th { background: #F3EBE1; color: #4A2E2B; font-weight: 600; }
        .badge-ready { background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
        .badge-order { background: #fff3cd; color: #856404; padding: 5px 10px; border-radius: 6px; font-size: 12px; font-weight: 600; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <div class="sidebar-brand">
            <h1>CRAFT HOUSE</h1>
            <p>Handmade & Soap</p>
        </div>
        <a href="dashboard.php">📊 Tổng Quan</a>
        <a href="quan_ly_san_pham.php" class="active">🛍️ Quản Lý Sản Phẩm</a>
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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
            <h2 style="margin:0; color:#4A2E2B;">Danh Sách Sản Phẩm Thủ Công</h2>
            <a href="them_san_pham.php" class="btn-add">+ Thêm Sản Phẩm Mới</a>
        </div>

        <input type="text" id="searchInput" class="search-box" onkeyup="searchProduct()" placeholder="🔍 Tìm kiếm theo tên sản phẩm...">

        <table id="productTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hình Ảnh</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Danh Mục</th>
                    <th>Giá Bán</th>
                    <th>Tồn Kho</th>
                    <th>Trạng Thái</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= $row['id'] ?></td>
                        <td>
                            <img src="../anh/<?= htmlspecialchars($row['hinhanh']) ?>" width="45" height="45" style="object-fit:cover; border-radius:6px;" onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=100'">
                        </td>
                        <td><strong><?= htmlspecialchars($row['tensp']) ?></strong></td>
                        <td><?= htmlspecialchars($row['danhmuc']) ?></td>
                        <td style="color:#C85A32; font-weight:bold;"><?= number_format($row['giaban'], 0, ',', '.') ?> đ</td>
                        <td><?= $row['tonkho'] ?></td>
                        <td>
                            <?php if($row['trangthai'] == 'cosan'): ?>
                                <span class="badge-ready">Có sẵn</span>
                            <?php else: ?>
                                <span class="badge-order">Đặt làm (3-5 ngày)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="sua_san_pham.php?id=<?= $row['id'] ?>" style="color: #007bff; text-decoration:none; font-weight:600; margin-right: 10px;">Sửa</a>
                            <a href="quan_ly_san_pham.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')" style="color: #dc3545; text-decoration:none; font-weight:600;">Xóa</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="8" style="text-align:center; color:#777;">Chưa có sản phẩm nào trong hệ thống.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchProduct() {
    const input = document.getElementById("searchInput");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("productTable");
    const rows = table.getElementsByTagName("tr");

    for (let i = 1; i < rows.length; i++) {
        const titleCell = rows[i].getElementsByTagName("td")[2];
        if (titleCell) {
            const text = titleCell.textContent || titleCell.innerText;
            rows[i].style.display = text.toLowerCase().includes(filter) ? "" : "none";
        }
    }
}
</script>

</body>
</html>