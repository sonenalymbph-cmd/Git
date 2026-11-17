<?php
session_start();
require_once __DIR__ . '/../config/db.php';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm này!');
}

$stmt = $conn->prepare('SELECT * FROM san_pham WHERE id = ? LIMIT 1');
if (!$stmt) {
    http_response_code(500);
    exit('Không thể tải thông tin sản phẩm.');
}
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result ? $result->fetch_assoc() : null;
$stmt->close();
if (!$product) {
    http_response_code(404);
    exit('Không tìm thấy sản phẩm này!');
}

// Ưu tiên ảnh đặt theo ID (1.jpg, 2.jpg, ...), giống trang chủ.
// Nếu chưa có, sử dụng tên file được lưu trong cột hinhanh của database.
$imageSrc = '';
$folder = realpath(__DIR__ . '/../anh');
$extensions = ['jpg', 'jpeg', 'png', 'webp'];
if ($folder !== false) {
    foreach ($extensions as $extension) {
        $filename = $id . '.' . $extension;
        if (is_file($folder . DIRECTORY_SEPARATOR . $filename)) {
            $imageSrc = '../anh/' . rawurlencode($filename);
            break;
        }
    }
    if ($imageSrc === '' && !empty($product['hinhanh'])) {
        $filename = basename(str_replace('\\', '/', (string)$product['hinhanh']));
        if ($filename !== '' && strtolower($filename) !== 'default.jpg' && is_file($folder . DIRECTORY_SEPARATOR . $filename)) {
            $imageSrc = '../anh/' . rawurlencode($filename);
        }
    }
}
$stock = max(0, (int)$product['tonkho']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($product['tensp']) ?> - CRAFT HOUSE</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f6f0e6; margin: 0; color: #4a2e2b; }
        header { background: #4a2e2b; color: white; padding: 22px 4%; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; }
        header h1 { margin: 0; font-size: 25px; }
        header a { color: #f3ebe1; text-decoration: none; font-weight: 600; margin-left: 22px; }
        header a:hover { color: #f2c94c; }
        .container { max-width: 1120px; margin: 42px auto; background: #fff; padding: 38px; border-radius: 18px; box-shadow: 0 6px 24px rgba(74,46,43,.07); display: grid; grid-template-columns: 1fr 1fr; gap: 42px; }
        .product-image img { display: block; width: 100%; height: 430px; object-fit: cover; border-radius: 14px; }
        .no-image { height: 430px; display: flex; align-items: center; justify-content: center; background: #faf4ec; border-radius: 14px; color: #896b5c; text-align: center; }
        .product-details { display: flex; flex-direction: column; justify-content: space-between; }
        h2 { margin: 0 0 22px; font-size: 29px; line-height: 1.3; }
        .category { margin-bottom: 16px; }
        .price { font-size: 27px; color: #c85a32; font-weight: 700; margin: 24px 0 16px; }
        .badge { display: inline-block; padding: 7px 12px; border-radius: 7px; font-size: 13px; font-weight: 700; }
        .badge-ready { background: #d4edda; color: #155724; }
        .badge-order { background: #fff3cd; color: #856404; }
        .description { color: #6e544a; line-height: 1.7; }
        .form-group { margin: 24px 0 16px; }
        label { font-weight: 600; display: block; margin-bottom: 8px; }
        input[type=number] { width: 100px; padding: 11px; border: 1px solid #d9c9bb; border-radius: 7px; font-size: 16px; text-align: center; }
        .btn-add-cart { background: #c85a32; color: white; border: 0; padding: 15px; border-radius: 8px; font-weight: 700; cursor: pointer; width: 100%; font-size: 16px; }
        .btn-add-cart:hover { background: #ad4928; }
        .btn-add-cart:disabled { background: #b9ada5; cursor: not-allowed; }
        .back-link { display: inline-block; margin-top: 22px; text-decoration: none; color: #6e544a; font-weight: 600; }
        @media(max-width: 760px) { .container { grid-template-columns: 1fr; margin: 18px 12px; padding: 20px; gap: 24px; } .product-image img, .no-image { height: 320px; } header a { margin-left: 12px; } }
    </style>
</head>
<body>
<header>
    <h1>CRAFT HOUSE 🌿</h1>
    <nav><a href="index.php">Trang Chủ</a><a href="giohang.php">🛒 Giỏ Hàng</a></nav>
</header>
<main class="container">
    <div class="product-image">
        <?php if ($imageSrc !== ''): ?>
            <img src="<?= e($imageSrc) ?>" alt="<?= e($product['tensp']) ?>">
        <?php else: ?>
            <div class="no-image">Chưa có ảnh sản phẩm</div>
        <?php endif; ?>
    </div>
    <div class="product-details">
        <div>
            <h2><?= e($product['tensp']) ?></h2>
            <p class="category"><strong>Danh mục:</strong> <?= e($product['danhmuc']) ?></p>
            <?php if ($product['trangthai'] === 'cosan'): ?>
                <span class="badge badge-ready">Trạng thái: Có sẵn (Giao ngay)</span>
            <?php else: ?>
                <span class="badge badge-order">Trạng thái: Đặt làm thủ công (3-5 ngày)</span>
            <?php endif; ?>
            <div class="price"><?= number_format((float)$product['giaban'], 0, ',', '.') ?> đ</div>
            <p class="description">Sản phẩm được chế tác hoàn toàn thủ công bằng sự tỉ mỉ và tâm huyết, mang lại dấu ấn riêng biệt cho không gian của bạn.</p>
        </div>
        <div>
            <form action="giohang.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= $id ?>">
                <input type="hidden" name="name" value="<?= e($product['tensp']) ?>">
                <input type="hidden" name="price" value="<?= e($product['giaban']) ?>">
                <div class="form-group">
                    <label for="qty">Số lượng:</label>
                    <input type="number" id="qty" name="qty" value="<?= $stock > 0 ? 1 : 0 ?>" min="<?= $stock > 0 ? 1 : 0 ?>" max="<?= $stock ?>" <?= $stock === 0 ? 'disabled' : '' ?>>
                </div>
                <button type="submit" class="btn-add-cart" <?= $stock === 0 ? 'disabled' : '' ?>><?= $stock === 0 ? 'Tạm hết hàng' : 'Thêm Vào Giỏ Hàng' ?></button>
            </form>
            <a href="index.php" class="back-link">← Quay lại cửa hàng</a>
        </div>
    </div>
</main>
</body>
</html>
