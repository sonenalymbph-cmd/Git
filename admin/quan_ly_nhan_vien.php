<?php
session_start();
require_once __DIR__ . '/../config/db.php';
// IMPORTANT: Enable the admin session check used by your existing project before deployment.
mysqli_set_charset($conn, 'utf8mb4');
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function redirect_msg($type, $msg) {
    $_SESSION['nv_flash'] = [$type, $msg];
    header('Location: quan_ly_nhan_vien.php'); exit;
}
$flash = $_SESSION['nv_flash'] ?? null;
unset($_SESSION['nv_flash']);
if (empty($_SESSION['nv_csrf'])) $_SESSION['nv_csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['nv_csrf'];
$roles = ['Quản lý', 'Nhân viên bán hàng', 'Nhân viên kho', 'Nhân viên chăm sóc khách hàng', 'Nhân viên đóng gói'];
$statuses = ['Đang làm việc', 'Tạm nghỉ', 'Đã nghỉ việc'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) redirect_msg('error', 'Phiên làm việc không hợp lệ.');
    $action = $_POST['action'] ?? '';
    $id = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
    if ($action === 'delete') {
        if ($id <= 0) redirect_msg('error', 'ID không hợp lệ.');
        $stmt = $conn->prepare('DELETE FROM nhan_vien WHERE id = ?');
        $stmt->bind_param('i', $id);
        $ok = $stmt->execute(); $stmt->close();
        redirect_msg($ok ? 'success' : 'error', $ok ? 'Đã xóa nhân viên.' : 'Không thể xóa nhân viên.');
    }
    if ($action === 'save') {
        $name = trim($_POST['ho_ten'] ?? '');
        $phone = trim($_POST['so_dien_thoai'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['chuc_vu'] ?? '';
        $salary = trim($_POST['luong'] ?? '0');
        $status = $_POST['trang_thai'] ?? '';
        $start = $_POST['ngay_vao_lam'] ?? '';
        $note = trim($_POST['ghi_chu'] ?? '');
        if ($name === '' || mb_strlen($name) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150 || !preg_match('/^[0-9+().\s-]{8,20}$/', $phone) || !in_array($role, $roles, true) || !in_array($status, $statuses, true) || !is_numeric($salary) || (float)$salary < 0 || (float)$salary > 999999999 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) || !($date = DateTimeImmutable::createFromFormat('!Y-m-d', $start)) || $date->format('Y-m-d') !== $start || mb_strlen($note) > 1000) {
            redirect_msg('error', 'Thông tin không hợp lệ. Kiểm tra lại các trường nhập.');
        }
        $salary = (float)$salary;
        if ($id > 0) {
            $stmt = $conn->prepare('UPDATE nhan_vien SET ho_ten=?, so_dien_thoai=?, email=?, chuc_vu=?, luong=?, trang_thai=?, ngay_vao_lam=?, ghi_chu=? WHERE id=?');
            $stmt->bind_param('ssssdsssi', $name, $phone, $email, $role, $salary, $status, $start, $note, $id);
        } else {
            $stmt = $conn->prepare('INSERT INTO nhan_vien (ho_ten,so_dien_thoai,email,chuc_vu,luong,trang_thai,ngay_vao_lam,ghi_chu) VALUES (?,?,?,?,?,?,?,?)');
            $stmt->bind_param('ssssdsss', $name, $phone, $email, $role, $salary, $status, $start, $note);
        }
        $ok = $stmt->execute();
        $error = $stmt->errno === 1062 ? 'Email đã được sử dụng.' : 'Không thể lưu nhân viên.';
        $stmt->close();
        redirect_msg($ok ? 'success' : 'error', $ok ? 'Đã lưu thông tin nhân viên.' : $error);
    }
    redirect_msg('error', 'Thao tác không hợp lệ.');
}
$edit = null;
$editId = filter_var($_GET['edit'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
if ($editId > 0) {
    $stmt = $conn->prepare('SELECT * FROM nhan_vien WHERE id=?');
    $stmt->bind_param('i', $editId); $stmt->execute();
    $edit = $stmt->get_result()->fetch_assoc(); $stmt->close();
}
$search = trim($_GET['q'] ?? '');
if (mb_strlen($search) > 100) $search = mb_substr($search, 0, 100);
if ($search !== '') {
    $like = '%' . $search . '%';
    $stmt = $conn->prepare('SELECT * FROM nhan_vien WHERE ho_ten LIKE ? OR email LIKE ? OR so_dien_thoai LIKE ? OR chuc_vu LIKE ? ORDER BY id DESC');
    $stmt->bind_param('ssss', $like, $like, $like, $like); $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
} else {
    $res = $conn->query('SELECT * FROM nhan_vien ORDER BY id DESC');
    $rows = $res->fetch_all(MYSQLI_ASSOC);
}
?>
<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Quản Lý Nhân Viên - Craft House</title>
<style>
*{box-sizing:border-box}body{font-family:Segoe UI,Arial,sans-serif;background:#fdfbf7;margin:0;color:#4a2e2b}.sidebar{width:270px;background:#4a2e2b;color:white;min-height:100vh;padding:26px 20px;position:fixed;top:0;left:0}.sidebar h1{font-size:31px;line-height:1.3;margin:18px 0 22px}.sidebar p{margin:0 0 24px}.sidebar a{display:block;padding:13px 12px;color:#f4eae4;text-decoration:none;border-radius:7px;margin-bottom:4px}.sidebar a:hover,.sidebar a.active{background:#c85a32;color:white}.sidebar .logout{margin-top:30px;color:#ffb3b3}.main{margin-left:270px;padding:40px;max-width:1800px}.panel{background:white;border-radius:14px;padding:28px;box-shadow:0 2px 12px #0000000a}.top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}h2{margin:0 0 22px;font-size:25px}.btn{border:0;border-radius:8px;background:#c85a32;color:white;font-weight:650;padding:12px 18px;text-decoration:none;cursor:pointer;display:inline-block;font-size:14px}.btn:hover{background:#a94424}.btn.secondary{background:#eee3d9;color:#4a2e2b}.btn.danger{background:#b33d3d}.toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin:14px 0 20px}input,select,textarea{font:inherit;border:1px solid #ddd5cd;border-radius:7px;padding:11px;background:#fff;color:#3f2b28;max-width:100%}.toolbar input{width:330px}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:950px}th,td{text-align:left;padding:15px 12px;border-bottom:1px solid #eee7e1;font-size:14px}th{background:#f3eae0}td strong{color:#3c2926}.pill{border-radius:6px;padding:6px 9px;font-size:12px;background:#d8efdf;color:#166534;white-space:nowrap}.pill.pause{background:#fff1c7;color:#885d00}.pill.quit{background:#f7dada;color:#9a2828}.actions{display:flex;gap:7px;align-items:center}.actions form{margin:0}.actions .btn{padding:8px 11px}.form-box{background:#fbf6f0;border:1px solid #ead8c9;border-radius:10px;padding:20px;margin:20px 0}.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.field{display:flex;flex-direction:column;gap:6px}.field input,.field select,.field textarea{width:100%}.field.full{grid-column:1/-1}.form-actions{display:flex;gap:10px;margin-top:18px}.alert{padding:12px 16px;border-radius:8px;margin:0 0 16px;background:#e1f4e7;color:#18632e}.alert.error{background:#fde5e3;color:#9b2b20}.muted{color:#8c7b70;font-size:14px}@media(max-width:850px){.sidebar{position:static;width:100%;min-height:auto}.main{margin-left:0;padding:16px}.panel{padding:16px}.form-grid{grid-template-columns:1fr}}
</style></head><body>
<aside class="sidebar"><h1>CRAFT<br>HOUSE</h1><p>Handmade &amp; Soap</p>
<a href="dashboard.php">📊 Tổng Quan</a>
<a href="quan_ly_san_pham.php">🛍️ Quản Lý Sản Phẩm</a>
<a href="quan_ly_nhan_vien.php" class="active">👥 Quản Lý Nhân Viên</a>
<a href="quan_ly_don_hang.php">📦 Quản Lý Đơn Hàng</a>
<a href="quan_ly_voucher.php">🎟️ Quản Lý Voucher</a>
<a href="quan_ly_danh_gia.php">⭐ Quản Lý Đánh Giá</a>
<a href="cai_dat.php">⚙️ Cài Đặt Cửa Hàng</a>
<a href="bao_cao_doanh_thu.php">📈 Báo Cáo Doanh Thu</a>
<a href="../auth/logout.php" class="logout">🚪 Đăng Xuất</a></aside>
<main class="main"><section class="panel"><div class="top"><h2>Danh Sách Nhân Viên</h2><a class="btn" href="quan_ly_nhan_vien.php?add=1">+ Thêm Nhân Viên Mới</a></div>
<?php if($flash): ?><div class="alert <?= $flash[0]==='error'?'error':'' ?>"><?= h($flash[1]) ?></div><?php endif; ?>
<?php if(isset($_GET['add']) || $edit): ?>
<div class="form-box"><h3><?= $edit?'Chỉnh Sửa Nhân Viên':'Thêm Nhân Viên Mới' ?></h3>
<form method="post"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>">
<div class="form-grid">
<div class="field"><label>Họ và tên *</label><input name="ho_ten" maxlength="120" required value="<?= h($edit['ho_ten']??'') ?>"></div>
<div class="field"><label>Số điện thoại *</label><input name="so_dien_thoai" maxlength="20" required value="<?= h($edit['so_dien_thoai']??'') ?>"></div>
<div class="field"><label>Email *</label><input type="email" name="email" maxlength="150" required value="<?= h($edit['email']??'') ?>"></div>
<div class="field"><label>Chức vụ *</label><select name="chuc_vu" required><?php foreach($roles as $r): ?><option value="<?= h($r) ?>" <?= ($edit['chuc_vu']??'')===$r?'selected':'' ?>><?= h($r) ?></option><?php endforeach; ?></select></div>
<div class="field"><label>Lương (VNĐ) *</label><input type="number" min="0" max="999999999" step="1000" name="luong" required value="<?= h($edit['luong']??'0') ?>"></div>
<div class="field"><label>Ngày vào làm *</label><input type="date" name="ngay_vao_lam" required value="<?= h($edit['ngay_vao_lam']??date('Y-m-d')) ?>"></div>
<div class="field"><label>Trạng thái *</label><select name="trang_thai"><?php foreach($statuses as $s): ?><option value="<?= h($s) ?>" <?= ($edit['trang_thai']??'Đang làm việc')===$s?'selected':'' ?>><?= h($s) ?></option><?php endforeach; ?></select></div>
<div class="field full"><label>Ghi chú</label><textarea name="ghi_chu" maxlength="1000" rows="3"><?= h($edit['ghi_chu']??'') ?></textarea></div>
</div><div class="form-actions"><button class="btn" type="submit">💾 Lưu Nhân Viên</button><a class="btn secondary" href="quan_ly_nhan_vien.php">Hủy</a></div></form></div>
<?php endif; ?>
<form class="toolbar" method="get"><input name="q" value="<?= h($search) ?>" placeholder="🔍 Tìm theo tên, email, SĐT, chức vụ..."><button class="btn" type="submit">Tìm kiếm</button><a class="btn secondary" href="quan_ly_nhan_vien.php">Làm mới</a></form>
<p class="muted">Tổng số nhân viên hiển thị: <?= count($rows) ?></p>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Họ và tên</th><th>Điện thoại</th><th>Email</th><th>Chức vụ</th><th>Lương</th><th>Ngày vào làm</th><th>Trạng thái</th><th>Thao tác</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="9" style="text-align:center">Chưa có nhân viên nào.</td></tr><?php endif; ?>
<?php foreach($rows as $r): ?><tr><td>#<?= (int)$r['id'] ?></td><td><strong><?= h($r['ho_ten']) ?></strong></td><td><?= h($r['so_dien_thoai']) ?></td><td><?= h($r['email']) ?></td><td><?= h($r['chuc_vu']) ?></td><td><?= number_format((float)$r['luong'],0,',','.') ?> đ</td><td><?= h(date('d/m/Y',strtotime($r['ngay_vao_lam']))) ?></td><td><span class="pill <?= $r['trang_thai']==='Tạm nghỉ'?'pause':($r['trang_thai']==='Đã nghỉ việc'?'quit':'') ?>"><?= h($r['trang_thai']) ?></span></td><td><div class="actions"><a class="btn secondary" href="?edit=<?= (int)$r['id'] ?>">Sửa</a><form method="post" onsubmit="return confirm('Bạn chắc chắn muốn xóa nhân viên này?')"><input type="hidden" name="csrf" value="<?= h($csrf) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button type="submit" class="btn danger">Xóa</button></form></div></td></tr><?php endforeach; ?>
</tbody></table></div></section></main></body></html>
