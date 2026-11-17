<?php
session_start();
include __DIR__ . '/../config/db.php';

$thongbao = "";
$thanhcong = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Kiểm tra mật khẩu khớp nhau
    if ($password !== $confirm_password) {
        $thongbao = "Mật khẩu nhập lại không khớp!";
    } else {
        // Kiểm tra xem tên đăng nhập đã tồn tại chưa
        $check_user = $conn->query("SELECT * FROM tai_khoan WHERE username = '$username'");
        if ($check_user && $check_user->num_rows > 0) {
            $thongbao = "Tên đăng nhập này đã được sử dụng. Vui lòng chọn tên khác!";
        } else {
            // Thêm tài khoản mới với vai trò mặc định là 'customer'
            $sql = "INSERT INTO tai_khoan (username, password, role) VALUES ('$username', '$password', 'customer')";
            if ($conn->query($sql) === TRUE) {
                $thanhcong = "Đăng ký thành công! Bạn có thể chuyển sang trang đăng nhập.";
            } else {
                $thongbao = "Lỗi hệ thống: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký Tài Khoản - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .register-card { background: #fff; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        h2 { text-align: center; color: #4A2E2B; margin-top: 0; margin-bottom: 25px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #4A2E2B; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-size: 14px; }
        .btn-register { background: #C85A32; color: #fff; border: none; padding: 12px; border-radius: 6px; font-weight: bold; width: 100%; cursor: pointer; font-size: 16px; margin-top: 10px; }
        .btn-register:hover { background: #b04c28; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 13px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; font-size: 13px; }
        .login-link { text-align: center; margin-top: 20px; font-size: 14px; color: #666; }
        .login-link a { color: #C85A32; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>

<div class="register-card">
    <h2>Đăng Ký Tài Khoản 🌿</h2>
    
    <?php if($thongbao): ?><div class="error"><?= $thongbao ?></div><?php endif; ?>
    <?php if($thanhcong): ?><div class="success"><?= $thanhcong ?></div><?php endif; ?>

    <form action="" method="POST">
        <div class="form-group">
            <label>Tên Đăng Nhập:</label>
            <input type="text" name="username" required placeholder="Nhập tên tài khoản...">
        </div>
        <div class="form-group">
            <label>Mật Khẩu:</label>
            <input type="password" name="password" required placeholder="Nhập mật khẩu...">
        </div>
        <div class="form-group">
            <label>Nhập Lại Mật Khẩu:</label>
            <input type="password" name="confirm_password" required placeholder="Xác nhận lại mật khẩu...">
        </div>
        <button type="submit" class="btn-register">Đăng Ký</button>
    </form>

    <div class="login-link">
        Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a>
    </div>
</div>

</body>
</html>