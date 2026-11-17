<?php
include __DIR__ . '/../config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Lấy thông tin sản phẩm cần sửa
$stmt = $conn->prepare("SELECT * FROM san_pham WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: quan_ly_san_pham.php");
    exit();
}

$msg = '';

// Xử lý khi nhấn Submit Cập Nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tensp = $_POST['tensp'];
    $danhmuc = $_POST['danhmuc'];
    $giaban = $_POST['giaban'];
    $tonkho = $_POST['tonkho'];
    $trangthai = $_POST['trangthai'];
    $hinhanh = $product['hinhanh']; // Giữ tên ảnh cũ mặc định

    // Nếu người dùng tải lên ảnh mới
    if (isset($_FILES['hinhanh_file']) && $_FILES['hinhanh_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['hinhanh_file']['tmp_name'];
        $fileName = $_FILES['hinhanh_file']['name'];
        
        // Tạo tên file mới tránh bị trùng
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        $newFileName = time() . '_' . uniqid() . '.' . $fileExtension;
        
        $uploadFileDir = __DIR__ . '/../anh/';
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        $dest_path = $uploadFileDir . $newFileName;
        if(move_uploaded_file($fileTmpPath, $dest_path)) {
            $hinhanh = $newFileName;
        }
    }

    // Cập nhật CSDL
    $updateStmt = $conn->prepare("UPDATE san_pham SET tensp = ?, danhmuc = ?, giaban = ?, tonkho = ?, trangthai = ?, hinhanh = ? WHERE id = ?");
    $updateStmt->bind_param("ssdissi", $tensp, $danhmuc, $giaban, $tonkho, $trangthai, $hinhanh, $id);

    if ($updateStmt->execute()) {
        header("Location: quan_ly_san_pham.php");
        exit();
    } else {
        $msg = "Có lỗi xảy ra khi cập nhật!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa Sản Phẩm - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; display: flex; }
        .sidebar { width: 250px; background: #4A2E2B; color: #fff; height: 100vh; padding: 20px; position: fixed; box-sizing: border-box; }
        .sidebar a { display: block; color: #ddd; text-decoration: none; padding: 12px 10px; border-radius: 6px; margin-bottom: 5px; }
        .sidebar a:hover, .sidebar a.active { background: #C85A32; color: #fff; font-weight: bold; }
        .main-content { margin-left: 250px; padding: 40px; width: calc(100% - 250px); box-sizing: border-box; }
        .card { background: #fff; padding: 25px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); max-width: 600px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #4A2E2B; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-submit { background: #C85A32; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
        .btn-cancel { background: #6c757d; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; text-decoration: none; margin-left: 10px; }
        .img-preview { width: 80px; height: 80px; object-fit: cover; border-radius: 6px; margin-top: 5px; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="sidebar-brand">
        <h1>CRAFT HOUSE</h1>
        <p>Handmade & Soap</p>
    </div>
    <a href="dashboard.php">📊 Tổng Quan</a>
    <a href="quan_ly_san_pham.php" class="active">🛍️ Quản Lý Sản Phẩm</a>
</div>

<div class="main-content">
    <div class="card">
        <h2 style="color:#4A2E2B; margin-top:0;">Cập Nhật Sản Phẩm #<?= $product['id'] ?></h2>
        
        <?php if($msg): ?>
            <p style="color:red;"><?= $msg ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Tên sản phẩm:</label>
                <input type="text" name="tensp" value="<?= htmlspecialchars($product['tensp']) ?>" required>
            </div>

            <div class="form-group">
                <label>Danh mục:</label>
                <input type="text" name="danhmuc" value="<?= htmlspecialchars($product['danhmuc']) ?>" required>
            </div>

            <div class="form-group">
                <label>Giá bán (VNĐ):</label>
                <input type="number" name="giaban" value="<?= $product['giaban'] ?>" required>
            </div>

            <div class="form-group">
                <label>Tồn kho:</label>
                <input type="number" name="tonkho" value="<?= $product['tonkho'] ?>" required>
            </div>

            <div class="form-group">
                <label>Trạng thái:</label>
                <select name="trangthai">
                    <option value="cosan" <?= $product['trangthai'] == 'cosan' ? 'selected' : '' ?>>Có sẵn</option>
                    <option value="datlam" <?= $product['trangthai'] == 'datlam' ? 'selected' : '' ?>>Đặt làm</option>
                </select>
            </div>

            <div class="form-group">
                <label>Hình ảnh hiện tại:</label><br>
                <img src="../anh/<?= htmlspecialchars($product['hinhanh']) ?>" class="img-preview" onerror="this.src='https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=100'"><br>
                <label style="margin-top:10px;">Chọn ảnh mới (nếu muốn đổi):</label>
                <input type="file" name="hinhanh_file" accept="image/*">
            </div>

            <div style="margin-top:20px;">
                <button type="submit" class="btn-submit">Lưu Cập Nhật</button>
                <a href="quan_ly_san_pham.php" class="btn-cancel">Hủy</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>