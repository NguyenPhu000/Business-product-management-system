<?php
/**
 * Script kiểm tra logo base64 trong database
 */

require_once __DIR__ . '/../config/database.php';

// Kết nối database
$config = require __DIR__ . '/../config/database.php';
$conn = new mysqli(
    $config['host'],
    $config['username'],
    $config['password'],
    $config['database']
);

if ($conn->connect_error) {
    die("❌ Kết nối database thất bại: " . $conn->connect_error . "\n");
}

echo "✅ Kết nối database thành công!\n\n";

// Lấy thông tin brands có logo
$sql = "SELECT 
    id,
    name,
    SUBSTRING(logo_url, 1, 50) as logo_preview,
    LENGTH(logo_url) as logo_size,
    is_active
FROM brands 
WHERE logo_url IS NOT NULL
ORDER BY id";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "⚠️  Không có brand nào có logo!\n";
    exit;
}

echo "📊 BRANDS VỚI BASE64 LOGO\n";
echo str_repeat("=", 100) . "\n";
printf("%-5s %-20s %-40s %-12s %-10s\n", "ID", "Name", "Logo Preview", "Size (KB)", "Active");
echo str_repeat("-", 100) . "\n";

$totalSize = 0;
while ($row = $result->fetch_assoc()) {
    $sizeKB = $row['logo_size'] / 1024;
    $totalSize += $row['logo_size'];
    
    printf(
        "%-5d %-20s %-40s %-12.2f %-10s\n",
        $row['id'],
        substr($row['name'], 0, 18),
        substr($row['logo_preview'], 0, 38) . "...",
        $sizeKB,
        $row['is_active'] ? "✓ Yes" : "✗ No"
    );
}

echo str_repeat("=", 100) . "\n";
echo "\n📈 THỐNG KÊ:\n";
echo "   • Tổng số brands có logo: " . $result->num_rows . "\n";
echo "   • Tổng dung lượng: " . number_format($totalSize / 1024, 2) . " KB\n";
echo "   • Trung bình mỗi logo: " . number_format(($totalSize / $result->num_rows) / 1024, 2) . " KB\n";

// Kiểm tra format base64
echo "\n🔍 KIỂM TRA FORMAT:\n";
$checkSql = "SELECT id, name, SUBSTRING(logo_url, 1, 30) as format_check 
             FROM brands 
             WHERE logo_url IS NOT NULL 
             LIMIT 3";

$checkResult = $conn->query($checkSql);
while ($row = $checkResult->fetch_assoc()) {
    $isBase64 = strpos($row['format_check'], 'data:image/') === 0;
    echo "   • Brand #{$row['id']} ({$row['name']}): ";
    echo $isBase64 ? "✅ Base64 Data URI\n" : "❌ Không phải Base64\n";
}

$conn->close();
echo "\n✅ Hoàn thành kiểm tra!\n";
