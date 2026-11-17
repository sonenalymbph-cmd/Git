<?php
session_start();
include __DIR__ . '/../config/db.php';
$thongbao = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $result = $conn->query("SELECT * FROM tai_khoan WHERE username = '$username' AND password = '$password'");
    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../khach_hang/index.php");
        }
        exit();
    } else {
        $thongbao = "Tên đăng nhập hoặc mật khẩu không chính xác!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập - Craft House</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fdfbf7; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; color: #4A2E2B; }
        .card { background: #fff; padding: 40px; border-radius: 12px; width: 100%; max-width: 400px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #f0eae1; }
        h2 { text-align: center; color: #4A2E2B; margin-top: 0; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; }
        input { width: 100%; padding: 12px; border: 1px solid #e2d8cd; border-radius: 8px; box-sizing: border-box; font-size: 14px; background: #fffcf9; }
        .btn { background: #C85A32; color: #fff; border: none; padding: 12px; border-radius: 8px; font-weight: bold; width: 100%; cursor: pointer; font-size: 16px; transition: 0.2s; }
        .btn:hover { background: #b04c28; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-size: 14px; }
        .link { text-align: center; margin-top: 20px; font-size: 14px; }
        .link a { color: #C85A32; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
<div class="card">
    <h2>CRAFT HOUSE 🌿</h2>
    <?php if($thongbao): ?><div class="error"><?= $thongbao ?></div><?php endif; ?>
    <form action="" method="POST">
        <div class="form-group"><label>Tên Đăng Nhập:</label><input type="text" name="username" required></div>
        <div class="form-group"><label>Mật Khẩu:</label><input type="password" name="password" required></div>
        <button type="submit" class="btn">Đăng Nhập</button>
    </form>
    <div class="link">Chưa có tài khoản? <a href="dang_ky.php">Đăng ký ngay</a></div>
</div>
</body>
</html>