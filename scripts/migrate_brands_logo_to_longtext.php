<?php
/**
 * Migration: Thay đổi kiểu dữ liệu cột logo_url từ VARCHAR sang LONGTEXT
 * 
 * Lý do:
 * - Base64 của ảnh rất dài (có thể tới hàng trăm KB)
 * - VARCHAR(255) chỉ chứa được 255 ký tự - KHÔNG ĐỦ
 * - LONGTEXT có thể chứa tới 4GB dữ liệu
 * 
 * Cách chạy:
 * php scripts/migrate_brands_logo_to_longtext.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/database.php';

use Core\Database;

// Khởi tạo database
$db = Database::getInstance();
$pdo = $db->getConnection();

echo "====================================================\n";
echo "MIGRATION: THAY ĐỔI KIỂU DỮ LIỆU LOGO_URL\n";
echo "====================================================\n\n";

try {
    // Kiểm tra cấu trúc hiện tại
    echo "1. Kiểm tra cấu trúc cột logo_url hiện tại...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM brands LIKE 'logo_url'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "   - Kiểu hiện tại: {$column['Type']}\n";
        echo "   - Null: {$column['Null']}\n";
        echo "   - Default: {$column['Default']}\n\n";
    } else {
        echo "   ❌ Cột logo_url không tồn tại!\n";
        exit(1);
    }
    
    // Thực hiện migration
    echo "2. Thay đổi kiểu dữ liệu sang LONGTEXT...\n";
    $sql = "ALTER TABLE brands MODIFY COLUMN logo_url LONGTEXT NULL";
    
    $pdo->exec($sql);
    
    echo "   ✓ Đã thay đổi thành công!\n\n";
    
    // Kiểm tra lại
    echo "3. Kiểm tra lại cấu trúc mới...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM brands LIKE 'logo_url'");
    $column = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($column) {
        echo "   - Kiểu mới: {$column['Type']}\n";
        echo "   - Null: {$column['Null']}\n";
        echo "   - Default: {$column['Default']}\n\n";
    }
    
    echo "====================================================\n";
    echo "✅ MIGRATION HOÀN THÀNH!\n";
    echo "====================================================\n\n";
    echo "Bây giờ bạn có thể chạy script convert logo sang base64:\n";
    echo "php scripts/convert_brand_logos_to_base64.php\n";
    
} catch (Exception $e) {
    echo "\n❌ LỖI: " . $e->getMessage() . "\n";
    echo "Chi tiết: " . $e->getTraceAsString() . "\n";
    exit(1);
}
