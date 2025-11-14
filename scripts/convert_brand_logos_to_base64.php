<?php
/**
 * Script chuyển đổi logo thương hiệu từ file URL sang base64
 * 
 * Chức năng:
 * - Đọc tất cả brands có logo_url dạng đường dẫn file
 * - Convert từng file thành base64
 * - Cập nhật lại vào database
 * - Giữ lại file gốc để backup (không xóa)
 * 
 * Cách chạy:
 * php scripts/convert_brand_logos_to_base64.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Core\Database;

// Khởi tạo database
$db = Database::getInstance();
$pdo = $db->getConnection();

echo "====================================================\n";
echo "CHUYỂN ĐỔI LOGO THƯƠNG HIỆU SANG BASE64\n";
echo "====================================================\n\n";

try {
    // Lấy tất cả brands có logo_url
    $stmt = $pdo->query("SELECT id, name, logo_url FROM brands WHERE logo_url IS NOT NULL AND logo_url != ''");
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Tìm thấy " . count($brands) . " thương hiệu có logo\n\n";
    
    if (empty($brands)) {
        echo "Không có logo nào cần convert!\n";
        exit(0);
    }
    
    $successCount = 0;
    $skipCount = 0;
    $errorCount = 0;
    
    foreach ($brands as $brand) {
        echo "[{$brand['id']}] {$brand['name']}: ";
        
        $logoUrl = $brand['logo_url'];
        
        // Kiểm tra nếu đã là base64 thì bỏ qua
        if (strpos($logoUrl, 'data:image') === 0) {
            echo "ĐÃ LÀ BASE64 - BỎ QUA\n";
            $skipCount++;
            continue;
        }
        
        // Tạo đường dẫn tuyệt đối đến file
        $filePath = __DIR__ . '/../public' . $logoUrl;
        
        // Kiểm tra file có tồn tại không
        if (!file_exists($filePath)) {
            echo "FILE KHÔNG TỒN TẠI: {$filePath}\n";
            $errorCount++;
            continue;
        }
        
        // Đọc file và convert sang base64
        $imageData = file_get_contents($filePath);
        if ($imageData === false) {
            echo "LỖI ĐỌC FILE\n";
            $errorCount++;
            continue;
        }
        
        // Lấy MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        // Tạo base64 string
        $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
        
        // Cập nhật vào database
        $updateStmt = $pdo->prepare("UPDATE brands SET logo_url = ? WHERE id = ?");
        $result = $updateStmt->execute([$base64, $brand['id']]);
        
        if ($result) {
            echo "THÀNH CÔNG ✓ (" . number_format(strlen($base64)) . " bytes)\n";
            $successCount++;
        } else {
            echo "LỖI CẬP NHẬT DATABASE\n";
            $errorCount++;
        }
    }
    
    echo "\n====================================================\n";
    echo "KẾT QUẢ:\n";
    echo "- Thành công: {$successCount}\n";
    echo "- Bỏ qua: {$skipCount}\n";
    echo "- Lỗi: {$errorCount}\n";
    echo "====================================================\n";
    
    if ($errorCount > 0) {
        echo "\n⚠️  CÓ LỖI XẢY RA! Vui lòng kiểm tra lại.\n";
        exit(1);
    }
    
    echo "\n✅ HOÀN THÀNH!\n";
    echo "\nLưu ý: Các file logo gốc vẫn được giữ lại tại /public/assets/images/brands/\n";
    echo "Bạn có thể xóa thủ công nếu muốn.\n";
    
} catch (Exception $e) {
    echo "\n❌ LỖI: " . $e->getMessage() . "\n";
    exit(1);
}
