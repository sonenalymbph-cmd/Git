<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
$conn->set_charset('utf8mb4');
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
if (empty($_SESSION['gia_csrf'])) $_SESSION['gia_csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['gia_csrf'];
function flash_redirect($type, $message) {
    $_SESSION['gia_flash'] = [$type, $message];
    header('Location: quan_ly_gia_ca.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($csrf, (string)($_POST['csrf'] ?? ''))) flash_redirect('error', 'Phiên làm việc không hợp lệ.');
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    if (!$id || $id < 1 || !in_array($action, ['set', 'adjust'], true)) flash_redirect('error', 'Dữ liệu không hợp lệ.');
    $stmt = $conn->prepare('SELECT giaban FROM san_pham WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$product) flash_redirect('error', 'Không tìm thấy sản phẩm.');
    $old = (float)$product['giaban'];
    $raw = trim((string)($_POST['value'] ?? ''));
    if ($raw === '' || !is_numeric($raw) || !is_finite((float)$raw)) flash_redirect('error', 'Vui lòng nhập số hợp lệ.');
    $value = (float)$raw;
    if ($action === 'set') {
        $new = $value;
    } else {
        if ($value < -100 || $value > 1000) flash_redirect('error', 'Phần trăm phải từ -100 đến 1000.');
        $new = round($old * (1 + $value / 100));
    }
    if ($new < 0 || $new > 9999999999999) flash_redirect('error', 'Giá phải từ 0 đến 9.999.999.999.999 đ.');
    $stmt = $conn->prepare('UPDATE san_pham SET giaban = ? WHERE id = ?');
    $stmt->bind_param('di', $new, $id);
    $ok = $stmt->execute();
    $stmt->close();
    flash_redirect($ok ? 'success' : 'error', $ok ? 'Đã cập nhật giá sản phẩm #' . $id . ' thành ' . number_format($new, 0, ',', '.') . ' đ.' : 'Không thể cập nhật giá.');
}
$flash = $_SESSION['gia_flash'] ?? null;
unset($_SESSION['gia_flash']);
$products = [];
$result = $conn->query('SELECT id, tensp, danhmuc, giaban, tonkho, trangthai FROM san_pham ORDER BY id DESC');
while ($row = $result->fetch_assoc()) $products[] = $row;
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Quản Lý Giá Cả - CRAFT HOUSE</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f6f0e6;color:#4a2e2b;font-family:'Segoe UI',Arial,sans-serif}.sidebar{width:260px;position:fixed;inset:0 auto 0 0;background:#4a2e2b;color:#fff;padding:30px 20px;overflow:auto}.brand{text-align:center;margin-bottom:32px}.brand h1{margin:0;font-size:27px}.brand p{color:#e8bda8;font-size:12px;letter-spacing:2px}.sidebar a{display:block;padding:13px 15px;color:#f3e8de;text-decoration:none;border-radius:12px;margin:5px 0}.sidebar a:hover,.sidebar a.active{background:#c85a32;color:white}.main{margin-left:260px;padding:38px;max-width:1750px}.card{background:white;padding:28px;border-radius:18px;box-shadow:0 4px 18px #4a2e2b0d}h2{margin:0 0 8px;font-size:27px}.subtitle{color:#8a7167;margin:0 0 25px}.toolbar{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}input,select,button{font:inherit}input,select{padding:10px 12px;border:1px solid #ddcfc1;border-radius:9px;background:#fff;min-width:0}.search{width:340px;max-width:100%}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:970px}th,td{text-align:left;padding:15px 12px;border-bottom:1px solid #eee5dc;vertical-align:middle}th{background:#f1e8dd;font-size:14px}td{font-size:14px}.price{color:#c85a32;font-weight:700;white-space:nowrap}.tag{display:inline-block;background:#e6f3e9;color:#226e39;padding:6px 9px;border-radius:7px;font-size:12px}.form-row{display:flex;gap:7px;align-items:center;flex-wrap:wrap}.form-row input{width:115px}.form-row select{width:130px}.btn{background:#c85a32;color:white;border:0;border-radius:8px;padding:10px 14px;cursor:pointer;font-weight:600}.btn:hover{background:#a94927}.flash{padding:14px;border-radius:10px;margin-bottom:20px;background:#e8f5e9;color:#256b37}.flash.error{background:#fff0e9;color:#a22c20}.note{font-size:13px;color:#7a6258;margin-top:20px;line-height:1.6}@media(max-width:900px){.sidebar{position:static;width:auto;padding:15px}.sidebar a{display:inline-block}.main{margin:0;padding:18px}.card{padding:16px}}
</style>
</head>
<body>
<aside class="sidebar"><div class="brand"><h1>CRAFT HOUSE</h1><p>HANDMADE & SOAP</p></div>
<a href="dashboard.php">📊 Tổng Quan</a>
<a href="quan_ly_san_pham.php">🛍️ Quản Lý Sản Phẩm</a>
<a href="quan_ly_gia_ca.php" class="active">💰 Quản Lý Giá Cả</a>
<a href="quan_ly_don_hang.php">📦 Quản Lý Đơn Hàng</a>
<a href="quan_ly_nhan_vien.php">👥 Quản Lý Nhân Viên</a>
<a href="quan_ly_voucher.php">🎟️ Quản Lý Voucher</a>
<a href="quan_ly_danh_gia.php">⭐ Quản Lý Đánh Giá</a>
<a href="cai_dat.php">⚙️ Cài Đặt Cửa Hàng</a>
<a href="bao_cao_doanh_thu.php">📈 Báo Cáo Doanh Thu</a>
<a href="../auth/logout.php">🚪 Đăng Xuất</a></aside>
<main class="main"><section class="card"><h2>💰 Quản Lý Giá Cả Sản Phẩm</h2><p class="subtitle">Chỉnh sửa giá bán các sản phẩm handmade của cửa hàng.</p>
<?php if ($flash): ?><div class="flash <?= $flash[0] === 'error' ? 'error' : '' ?>"><?= e($flash[1]) ?></div><?php endif; ?>
<div class="toolbar"><input class="search" id="search" type="search" placeholder="🔎 Tìm theo tên hoặc danh mục..."><span id="count" style="align-self:center;color:#846c61"><?= count($products) ?> sản phẩm</span></div>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Tên sản phẩm</th><th>Danh mục</th><th>Giá hiện tại</th><th>Tồn kho</th><th>Điều chỉnh giá</th></tr></thead><tbody>
<?php foreach ($products as $p): ?><tr class="product-row" data-search="<?= e(mb_strtolower($p['tensp'].' '.$p['danhmuc'], 'UTF-8')) ?>">
<td>#<?= (int)$p['id'] ?></td><td><strong><?= e($p['tensp']) ?></strong></td><td><?= e($p['danhmuc']) ?></td><td class="price"><?= number_format((float)$p['giaban'], 0, ',', '.') ?> đ</td><td><?= (int)$p['tonkho'] ?></td>
<td><form method="post" class="form-row"><input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><select name="action" aria-label="Kiểu điều chỉnh"><option value="set">Đặt giá (đ)</option><option value="adjust">Thay đổi (%)</option></select><input type="number" name="value" step="any" required min="0" placeholder="Giá mới" aria-label="Giá trị thay đổi"><button class="btn" type="submit" onclick="return confirm('Bạn có chắc muốn cập nhật giá sản phẩm này?')">Lưu giá</button></form></td></tr><?php endforeach; ?>
<?php if (!$products): ?><tr><td colspan="6">Chưa có sản phẩm.</td></tr><?php endif; ?>
</tbody></table></div>
<p class="note">Đặt giá (đ): nhập giá bán mới, ví dụ 180000. Thay đổi (%): nhập 10 để tăng 10%, hoặc -10 để giảm 10%. Giá cập nhật trong bảng <code>san_pham</code>, cột <code>giaban</code>. Giá đơn hàng đã đặt không được thay đổi bởi trang này.</p></section></main>
<script>
const search=document.getElementById('search');
search.addEventListener('input',()=>{let count=0;document.querySelectorAll('.product-row').forEach(row=>{const visible=row.dataset.search.includes(search.value.toLocaleLowerCase('vi').trim());row.hidden=!visible;if(visible)count++});document.getElementById('count').textContent=count+' sản phẩm';});
document.querySelectorAll('select[name="action"]').forEach(select=>select.addEventListener('change',()=>{const input=select.form.querySelector('input[name="value"]');input.min=select.value==='set'?'0':'-100';input.placeholder=select.value==='set'?'Giá mới':'% thay đổi';}));
</script>
</body></html>
