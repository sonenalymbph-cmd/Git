<?php
session_start();
include __DIR__ . '/../config/db.php';

$danhmuc = isset($_GET['danhmuc']) ? $_GET['danhmuc'] : '';

if ($danhmuc != '') {
    $danhmuc_safe = $conn->real_escape_string($danhmuc);
    $sql = "SELECT * FROM san_pham WHERE danhmuc = '$danhmuc_safe' ORDER BY id DESC";
} else {
    $sql = "SELECT * FROM san_pham ORDER BY id DESC";
}
// Chỉ dùng ảnh đã gán cho sản phẩm; không lấy ảnh ngẫu nhiên.
function layAnhSanPham($row, $imageColumn) {
    $id = (int)$row['id'];
    $folder = __DIR__ . '/../anh/';
    $baseUrl = '../anh/';

    // Ảnh đặt theo ID có thể là jpg, jpeg, png hoặc webp.
    foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
        if (is_file($folder . $id . '.' . $ext)) {
            return $baseUrl . $id . '.' . $ext;
        }
    }

    // Nếu bảng san_pham có cột ảnh, sử dụng giá trị đã lưu.
    if ($imageColumn && !empty($row[$imageColumn])) {
        $saved = trim((string)$row[$imageColumn]);
        if (preg_match('~^https?://~i', $saved)) {
            return $saved;
        }
        $filename = basename(str_replace('\\', '/', $saved));
        if ($filename !== '.' && $filename !== '..' && is_file($folder . $filename)) {
            return $baseUrl . rawurlencode($filename);
        }
    }
    // Chỉ dùng ảnh Internet cho các sản phẩm chưa có ảnh cục bộ.
    // Chủ đề riêng theo ID, không thay thế ảnh đã lưu trong thư mục anh.
    $fallbacks = [
        6 => 'https://loremflickr.com/640/640/resin,hair,accessory?lock=6006',
        7 => 'https://loremflickr.com/640/640/crochet,handbag?lock=7007',
        8 => 'https://loremflickr.com/640/640/chunky,knit,bag?lock=8008',
    ];
    return $fallbacks[$id] ?? '';
}

$result = $conn->query($sql);
$categories = $conn->query("SELECT DISTINCT danhmuc FROM san_pham");
// Tự nhận diện cột lưu tên ảnh trong bảng sản phẩm.
$imageColumn = null;
$columns = $conn->query("SHOW COLUMNS FROM san_pham");
if ($columns) {
    $availableColumns = [];
    while ($column = $columns->fetch_assoc()) $availableColumns[] = $column['Field'];
    foreach (['hinhanh', 'hinh_anh', 'anh', 'image', 'image_url', 'anhsp'] as $candidate) {
        if (in_array($candidate, $availableColumns, true)) { $imageColumn = $candidate; break; }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>CRAFT HOUSE — Tiệm Đồ Thủ Công & Handmade</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        * { box-sizing: border-box; }
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background: #F6F0E6; /* Nền kem mộc organic */
            margin: 0; 
            padding: 0; 
            color: #4A2E2B; 
        }

        /* Header giữ nguyên các nút chức năng */
        header { 
            background: #4A2E2B; 
            color: #fff; 
            padding: 20px 50px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        header h1 { 
            font-family: 'Playfair Display', serif; 
            margin: 0; 
            font-size: 24px; 
            letter-spacing: 2px; 
            color: #F6F0E6;
        }
        header .nav-links a { 
            color: #E5D5C5; 
            text-decoration: none; 
            font-weight: 600; 
            margin-left: 25px; 
            font-size: 14px;
            transition: 0.2s;
        }
        header .nav-links a:hover { color: #C85A32; }

        .container { max-width: 1200px; margin: 40px auto; padding: 0 20px; }

        /* HERO BANNER ĐẶC TRƯNG PHONG CÁCH WASH HOUSE */
        .hero-banner {
            background: #EFE4D6;
            border-radius: 28px;
            padding: 50px;
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
            font-size: 11px;
            color: #C85A32;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .hero-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 48px;
            line-height: 1.1;
            color: #4A2E2B;
            margin: 0 0 15px 0;
        }
        .hero-text p { color: #6E544A; font-size: 15px; margin-bottom: 25px; line-height: 1.5; }
        .btn-shop {
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
        .btn-shop:hover { background: #AA4723; }

        .hero-visual {
            width: 39%;
            height: 300px;
            background: #C85A32 url('https://images.unsplash.com/photo-1494438639946-1ebd1d20bf85?auto=format&fit=crop&w=900&q=85') center/cover no-repeat;
            border-top-left-radius: 120px;
            border-bottom-left-radius: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .badge-stamp {
            width: 110px;
            height: 110px;
            background: #F2C94C;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #4A2E2B;
            font-size: 10px;
            font-weight: 800;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            transform: rotate(-10deg);
        }

        /* THANH ĐỆM TÍNH NĂNG MÀU TÍM PASTEL */
        .feature-bar {
            background: #D8CEE8;
            border-radius: 16px;
            padding: 18px 30px;
            display: flex;
            justify-content: space-around;
            margin-bottom: 35px;
            color: #4A2E2B;
            font-size: 13px;
            font-weight: 600;
        }
        .feature-item { display: flex; align-items: center; gap: 8px; }

        /* BỘ LỌC DANH MỤC */
        .filter-bar { 
            display: flex; 
            gap: 12px; 
            margin-bottom: 30px; 
            flex-wrap: wrap; 
            justify-content: center; 
        }
        .filter-btn { 
            background: #fff; 
            border: 1px solid #E2D2C0; 
            padding: 10px 22px; 
            border-radius: 25px; 
            text-decoration: none; 
            color: #4A2E2B; 
            font-weight: 600; 
            font-size: 13px; 
            transition: all 0.2s; 
        }
        .filter-btn:hover, .filter-btn.active { 
            background: #C85A32; 
            color: #fff; 
            border-color: #C85A32; 
            box-shadow: 0 4px 10px rgba(200, 90, 50, 0.2);
        }

        /* LƯỚI SẢN PHẨM */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            margin-bottom: 20px;
            color: #4A2E2B;
        }
        .product-grid { 
            display: grid; 
            grid-template-columns: repeat(4, 1fr); 
            gap: 22px; 
        }
        .product-card { 
            background: #EFE4D6; 
            border-radius: 18px; 
            overflow: hidden; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); 
            border: 1px solid #E2D2C0; 
            display: flex; 
            flex-direction: column; 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
        }
        .product-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 8px 20px rgba(74, 46, 43, 0.08); 
        }
        .product-photo { display: block; padding: 10px; text-decoration: none; }
        .photo-missing { display: flex; align-items: center; justify-content: center;
            min-height: 200px; color: #80665b; background: #fff8f0;
            border-radius: 14px; text-align: center; padding: 12px; }
        .photo-missing[hidden] { display: none; }
        .product-card img { 
            width: 100%; 
            height: 220px; 
            object-fit: cover; 
            background: #fff;
            border-radius: 14px;
            padding: 0;
        }
        .product-info { 
            padding: 18px; 
            flex-grow: 1; 
            display: flex; 
            flex-direction: column; 
            justify-content: space-between; 
        }
        .product-title { 
            font-weight: 700; 
            font-size: 16px; 
            margin-bottom: 6px; 
            color: #4A2E2B; 
            text-decoration: none; 
            display: block; 
            line-height: 1.3;
        }
        .product-title:hover { color: #C85A32; }
        .category-tag { font-size: 12px; color: #8C7368; margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 1px; }
        .price { color: #C85A32; font-weight: 700; font-size: 16px; margin-bottom: 12px; }
        
        .badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; margin-bottom: 10px; width: fit-content; }
        .badge-ready { background: #d4edda; color: #155724; }
        .badge-order { background: #fff3cd; color: #856404; }

        .btn-buy { 
            background: #C85A32; 
            color: #fff; 
            border: none; 
            padding: 11px; 
            border-radius: 10px; 
            cursor: pointer; 
            font-weight: 700; 
            text-align: center; 
            text-decoration: none; 
            display: block; 
            width: 100%; 
            font-size: 13px;
            transition: 0.2s; 
        }
        .btn-buy:hover { background: #AA4723; }
        .inspiration { margin: 35px 0 42px; }
        .inspiration h3 { font-family: 'Playfair Display', serif; font-size: 27px; margin-bottom: 8px; }
        .inspiration p { color: #80665b; margin: 0 0 18px; }
        .inspiration-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 18px; }
        .inspiration-item { position: relative; overflow: hidden; border-radius: 18px; height: 235px; background: #e5d5c5; }
        .inspiration-item img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
        .inspiration-item:hover img { transform: scale(1.06); }
        .inspiration-item span { position: absolute; left: 15px; bottom: 15px; padding: 9px 14px; border-radius: 20px; background: rgba(255,250,244,.95); font-weight: 700; }
        @media(max-width: 850px) { header {padding:18px 22px;flex-wrap:wrap;gap:15px;} .container {margin:20px auto;} .hero-banner {padding:25px;} .hero-text h2 {font-size:34px;} .product-grid {grid-template-columns:repeat(2,1fr);} .feature-bar {gap:12px;flex-wrap:wrap;} }
        @media(max-width: 540px) { .hero-banner {flex-direction:column;align-items:stretch;gap:20px;} .hero-text {max-width:100%;} .hero-visual {width:100%;height:210px;} .inspiration-grid {grid-template-columns:1fr;} .inspiration-item {height:210px;} .product-grid {grid-template-columns:1fr;} header .nav-links a {margin:0 12px 8px 0;display:inline-block;} }
    </style>
</head>
<body>

<!-- Header giữ nguyên các nút giỏ hàng và quản trị -->
<header>
    <h1>CRAFT HOUSE 🌿</h1>
    <div class="nav-links">
        <a href="index.php">Trang Chủ</a>
        <a href="giohang.php">🛒 Giỏ Hàng</a>
        <a href="lich_su_mua_hang.php">📦 Đơn Hàng Của Tôi</a>
        <a href="../admin/dashboard.php" style="background: #C85A32; padding: 8px 16px; border-radius: 20px; color: #fff;">Quản Trị Admin</a>
    </div>
</header>

<div class="container">
    
    <!-- Hero Banner phong cách Wash House -->
    <div class="hero-banner">
        <div class="hero-text">
            <div class="subtitle">Natural & Handmade Collection</div>
            <h2>Chạm vào nét đẹp thủ công.</h2>
            <p>Sản phẩm thủ công mộc mạc, tỉ mỉ từng chi tiết dành riêng cho không gian ấm cúng của bạn.</p>
            <a href="#sanpham" class="btn-shop">Khám Phá Ngay →</a>
        </div>
        <div class="hero-visual">
            <div class="badge-stamp">
                <span>MADE BY</span>
                <span style="font-size: 12px;">🌿</span>
                <span>HAND LOVE</span>
            </div>
        </div>
    </div>

    <!-- Thanh tính năng tím pastel -->
    <div class="feature-bar">
        <div class="feature-item">🌱 100% Nguyên Liệu Tự Nhiên</div>
        <div class="feature-item">🐰 Chế Tác Thủ Công Tỉ Mỉ</div>
        <div class="feature-item">♻️ Thân Thiện Môi Trường</div>
    </div>

    <h3 class="section-title" id="sanpham">Bộ sưu tập sản phẩm</h3>

    <!-- Mỗi sản phẩm chỉ có một ảnh. Ưu tiên anh/ID.jpg, rồi đến tên ảnh trong database. -->
    <div class="product-grid">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="product-card">
                <?php $imageSrc = layAnhSanPham($row, $imageColumn); ?>
                <a class="product-photo" href="chitiet.php?id=<?= (int)$row['id'] ?>">
                    <?php if ($imageSrc !== ''): ?>
                        <img loading="lazy"
                             src="<?= htmlspecialchars($imageSrc, ENT_QUOTES, 'UTF-8') ?>"
                             alt="<?= htmlspecialchars($row['tensp'], ENT_QUOTES, 'UTF-8') ?>"
                             onerror="this.style.display='none';this.nextElementSibling.hidden=false;">
                        <span class="photo-missing" hidden>Chưa có ảnh sản phẩm</span>
                    <?php else: ?>
                        <span class="photo-missing">Chưa có ảnh sản phẩm</span>
                    <?php endif; ?>
                </a>
                <div class="product-info">
                    <div>
                        <span class="category-tag"><?= htmlspecialchars($row['danhmuc']) ?></span>
                        <a href="chitiet.php?id=<?= $row['id'] ?>" class="product-title"><?= htmlspecialchars($row['tensp']) ?></a>
                        
                        <?php if($row['trangthai'] == 'cosan'): ?>
                            <span class="badge badge-ready">Có sẵn (Giao ngay)</span>
                        <?php else: ?>
                            <span class="badge badge-order">Đặt làm (3-5 ngày)</span>
                        <?php endif; ?>
                        
                        <div class="price"><?= number_format($row['giaban'], 0, ',', '.') ?> đ</div>
                    </div>
                    
                    <form action="giohang.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="name" value="<?= htmlspecialchars($row['tensp']) ?>">
                        <input type="hidden" name="price" value="<?= $row['giaban'] ?>">
                        <button type="submit" class="btn-buy">+ Thêm Vào Giỏ Hàng</button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="grid-column: span 4; text-align: center; color: #777; padding: 40px 0;">Không tìm thấy sản phẩm nào.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>