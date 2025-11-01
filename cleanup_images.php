<?php
// Script dọn dẹp ảnh không còn sử dụng

$dbConfig = require __DIR__ . '/config/database.php';
$pdo = new PDO(
    "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
    $dbConfig['username'],
    $dbConfig['password']
);

$imageDir = __DIR__ . '/public/assets/images/products/';

echo "=== DỌN DẸP ẢNH KHÔNG DÙNG ===\n\n";

// Lấy tất cả URL ảnh đang được sử dụng trong database
$stmt = $pdo->query("SELECT url FROM product_images WHERE url IS NOT NULL AND url != ''");
$usedUrls = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo "Số ảnh trong database: " . count($usedUrls) . "\n\n";

// Lấy tất cả file trong thư mục
if (!is_dir($imageDir)) {
    echo "Thư mục không tồn tại: $imageDir\n";
    exit;
}

$files = glob($imageDir . '*.*');
echo "Số file trong thư mục: " . count($files) . "\n\n";

$deleted = 0;
$kept = 0;

foreach ($files as $file) {
    $filename = basename($file);
    $url = '/assets/images/products/' . $filename;
    
    // Kiểm tra xem URL có trong database không
    if (in_array($url, $usedUrls)) {
        echo "✓ Giữ lại: $filename\n";
        $kept++;
    } else {
        echo "✗ Xóa: $filename\n";
        unlink($file);
        $deleted++;
    }
}

echo "\n=== KẾT QUẢ ===\n";
echo "Đã giữ lại: $kept file\n";
echo "Đã xóa: $deleted file\n";
echo "\n✅ Hoàn tất!\n";