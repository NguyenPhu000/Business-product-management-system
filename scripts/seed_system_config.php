<?php

/**
 * Script thêm dữ liệu config mẫu vào bảng system_config
 */

require __DIR__ . '/../vendor/autoload.php';

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }
}

$config = require __DIR__ . '/../config/database.php';
$dsn = sprintf(
    "%s:host=%s;port=%s;dbname=%s;charset=%s",
    $config['driver'],
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Connected to database successfully.\n\n";

    // Danh sách config mẫu
    $configs = [
        // Thông tin công ty
        ['key' => 'company_name', 'value' => 'Cửa hàng ABC', 'description' => 'Tên công ty/cửa hàng'],
        ['key' => 'company_address', 'value' => '123 Nguyễn Huệ, Q.1, TP.HCM', 'description' => 'Địa chỉ công ty'],
        ['key' => 'company_phone', 'value' => '0901234567', 'description' => 'Số điện thoại công ty'],
        ['key' => 'company_email', 'value' => 'contact@abc.com', 'description' => 'Email liên hệ công ty'],
        ['key' => 'company_tax_code', 'value' => '0123456789', 'description' => 'Mã số thuế'],

        // Cấu hình hệ thống
        ['key' => 'currency', 'value' => 'VND', 'description' => 'Đơn vị tiền tệ'],
        ['key' => 'date_format', 'value' => 'd/m/Y', 'description' => 'Định dạng ngày tháng'],
        ['key' => 'records_per_page', 'value' => '20', 'description' => 'Số bản ghi mỗi trang'],
        ['key' => 'session_timeout', 'value' => '3600', 'description' => 'Thời gian timeout session (giây)'],
        ['key' => 'timezone', 'value' => 'Asia/Ho_Chi_Minh', 'description' => 'Múi giờ hệ thống'],

        // Cấu hình sản phẩm
        ['key' => 'product_code_prefix', 'value' => 'SP', 'description' => 'Tiền tố mã sản phẩm'],
        ['key' => 'allow_negative_stock', 'value' => '0', 'description' => 'Cho phép tồn kho âm (0=Không, 1=Có)'],
        ['key' => 'low_stock_threshold', 'value' => '10', 'description' => 'Ngưỡng cảnh báo hết hàng'],
        ['key' => 'max_product_images', 'value' => '5', 'description' => 'Số ảnh tối đa cho 1 sản phẩm'],

        // Cấu hình đơn hàng
        ['key' => 'order_code_prefix', 'value' => 'DH', 'description' => 'Tiền tố mã đơn hàng'],
        ['key' => 'min_order_amount', 'value' => '50000', 'description' => 'Giá trị đơn hàng tối thiểu (VNĐ)'],
        ['key' => 'order_expiry_days', 'value' => '7', 'description' => 'Số ngày hủy đơn hàng chờ xử lý'],

        // Cấu hình thuế
        ['key' => 'default_vat_rate', 'value' => '10', 'description' => 'Thuế VAT mặc định (%)'],
        ['key' => 'enable_vat_calculation', 'value' => '1', 'description' => 'Tính thuế VAT tự động (0=Không, 1=Có)'],
        ['key' => 'shipping_fee_default', 'value' => '30000', 'description' => 'Phí vận chuyển mặc định (VNĐ)'],
        ['key' => 'free_shipping_threshold', 'value' => '500000', 'description' => 'Miễn phí ship với đơn trên X VNĐ'],

        // Cấu hình bảo mật
        ['key' => 'password_min_length', 'value' => '8', 'description' => 'Độ dài mật khẩu tối thiểu'],
        ['key' => 'login_max_attempts', 'value' => '5', 'description' => 'Số lần đăng nhập sai tối đa'],
        ['key' => 'account_lockout_duration', 'value' => '1800', 'description' => 'Thời gian khóa tài khoản (giây)'],
    ];

    $inserted = 0;
    $updated = 0;
    $skipped = 0;

    foreach ($configs as $cfg) {
        // Kiểm tra key đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT * FROM system_config WHERE `key` = ?");
        $stmt->execute([$cfg['key']]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            // Cập nhật nếu value khác
            if ($existing['value'] != $cfg['value']) {
                $stmt = $pdo->prepare("
                    UPDATE system_config 
                    SET value = ?, updated_at = NOW() 
                    WHERE `key` = ?
                ");
                $stmt->execute([$cfg['value'], $cfg['key']]);
                echo "✅ Updated: {$cfg['key']} = {$cfg['value']}\n";
                $updated++;
            } else {
                echo "⏭️  Skipped: {$cfg['key']} (already exists with same value)\n";
                $skipped++;
            }
        } else {
            // Thêm mới - lưu ý: bảng system_config không có cột description
            $stmt = $pdo->prepare("
                INSERT INTO system_config (`key`, value, user_id, updated_at) 
                VALUES (?, ?, NULL, NOW())
            ");
            $stmt->execute([$cfg['key'], $cfg['value']]);
            echo "✅ Inserted: {$cfg['key']} = {$cfg['value']} ({$cfg['description']})\n";
            $inserted++;
        }
    }

    echo "\n" . str_repeat('=', 60) . "\n";
    echo "Summary:\n";
    echo "  ✅ Inserted: $inserted\n";
    echo "  🔄 Updated:  $updated\n";
    echo "  ⏭️  Skipped:  $skipped\n";
    echo "  📊 Total:    " . count($configs) . "\n";
    echo str_repeat('=', 60) . "\n\n";

    // Hiển thị tất cả config hiện có
    echo "Current configs in database:\n";
    echo str_repeat('-', 60) . "\n";
    $stmt = $pdo->query("SELECT * FROM system_config ORDER BY `key`");
    $allConfigs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($allConfigs as $cfg) {
        echo sprintf("%-30s = %s\n", $cfg['key'], $cfg['value']);
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}