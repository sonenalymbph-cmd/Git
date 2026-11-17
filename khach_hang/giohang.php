<?php
session_start();
include __DIR__ . '/../config/db.php';

// Khởi tạo giỏ hàng nếu chưa có
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Xử lý thêm sản phẩm vào giỏ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $id = intval($_POST['id']);
    $name = $_POST['name'];
    $price = floatval($_POST['price']);

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id]['qty'] += 1;
    } else {
        $_SESSION['cart'][$id] = [
            'name' => $name,
            'price' => $price,
            'qty' => 1
        ];
    }
    header("Location: giohang.php");
    exit();
}

// Xử lý xóa sản phẩm khỏi giỏ
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    unset($_SESSION['cart'][$id]);
    header("Location: giohang.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Giỏ Hàng - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; margin: 0; padding: 0; color: #4A2E2B; }
        header { background: #4A2E2B; color: #fff; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 24px; }
        header a { color: #F3EBE1; text-decoration: none; font-weight: 600; margin-left: 20px; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 14px 12px; border-bottom: 1px solid #f0f0f0; text-align: left; }
        th { background: #F3EBE1; color: #4A2E2B; }
        .total-box { text-align: right; font-size: 1.3em; font-weight: bold; color: #C85A32; margin-top: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; }
        input, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
        .btn-order { background: #C85A32; color: #fff; border: none; padding: 12px 25px; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; font-size: 16px; margin-top: 15px; }
    </style>
</head>
<body>

<header>
    <h1>CRAFT HOUSE 🌿</h1>
    <div>
        <a href="index.php">← Tiếp tục mua sắm</a>
    </div>
</header>

<div class="container">
    <h2 style="margin-top:0; color:#4A2E2B;">Giỏ Hàng Của Bạn</h2>

    <?php if (!empty($_SESSION['cart'])): ?>
        <table>
            <thead>
                <tr>
                    <th>Sản Phẩm</th>
                    <th>Đơn Giá</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_price = 0;
                foreach ($_SESSION['cart'] as $id => $item): 
                    $subtotal = $item['price'] * $item['qty'];
                    $total_price += $subtotal;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                    <td><?= number_format($item['price'], 0, ',', '.') ?> đ</td>
                    <td><?= $item['qty'] ?></td>
                    <td style="color:#C85A32; font-weight:bold;"><?= number_format($subtotal, 0, ',', '.') ?> đ</td>
                    <td><a href="giohang.php?remove=<?= $id ?>" style="color: #dc3545; text-decoration:none; font-weight:600;">Xóa</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-box">Tổng đơn hàng: <?= number_format($total_price, 0, ',', '.') ?> đ</div>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">

        <h3 style="color:#4A2E2B;">Thông Tin Giao Hàng & Yêu Cầu Đặt Làm Riêng</h3>
        <form action="xu_ly_dat_hang.php" method="POST">
            <input type="hidden" name="total_price" value="<?= $total_price ?>">
            <div class="form-group">
                <label>Họ và Tên:</label>
                <input type="text" name="ten_khach" required placeholder="Nguyễn Văn A">
            </div>
            <div class="form-group">
                <label>Số Điện Thoại:</label>
                <input type="text" name="sdt" required placeholder="0901234567">
            </div>
            <div class="form-group">
                <label>Địa Chỉ Nhận Hàng:</label>
                <input type="text" name="dia_chi" required placeholder="Số nhà, đường, quận/huyện, TP.HCM">
            </div>
            <div class="form-group">
                <label>✍️ Yêu cầu tùy chỉnh riêng (Ví dụ: Thêu tên, viết thiệp, chọn màu sắc):</label>
                <textarea name="ghi_chu" rows="3" placeholder="Nhập ghi chú chi tiết cho thợ làm thủ công nếu có..."></textarea>
            </div>
            <button type="submit" class="btn-order">Xác Nhận Đặt Hàng</button>
        </form>
    <?php else: ?>
        <p style="text-align: center; color: #777; padding: 30px 0;">Giỏ hàng của bạn đang trống.</p>
        <div style="text-align: center;"><a href="index.php" style="background: #C85A32; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: bold;">Khám phá sản phẩm ngay</a></div>
    <?php endif; ?>
</div>

</body>
</html>