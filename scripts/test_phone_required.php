<?php
/**
 * Test script: Kiểm tra validation số điện thoại bắt buộc
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/core/Bootstrap.php';

use Modules\Category\Services\SupplierService;

$service = new SupplierService();

echo "=== TEST VALIDATION SỐ ĐIỆN THOẠI BẮT BUỘC ===\n\n";

// Test case 1: Không nhập số điện thoại (để trống)
echo "TEST 1: Để trống số điện thoại\n";
try {
    $data = [
        'name' => 'Test Supplier',
        'phone' => '',  // Để trống
        'is_active' => 1
    ];
    $service->createSupplier($data);
    echo "❌ LỖI: Không báo lỗi khi để trống số điện thoại!\n\n";
} catch (Exception $e) {
    echo "✅ OK: Đã bắt được lỗi: " . $e->getMessage() . "\n\n";
}

// Test case 2: Không gửi key phone
echo "TEST 2: Không gửi key phone trong data\n";
try {
    $data = [
        'name' => 'Test Supplier 2',
        // Không có key 'phone'
        'is_active' => 1
    ];
    $service->createSupplier($data);
    echo "❌ LỖI: Không báo lỗi khi không gửi phone!\n\n";
} catch (Exception $e) {
    echo "✅ OK: Đã bắt được lỗi: " . $e->getMessage() . "\n\n";
}

// Test case 3: Nhập số hợp lệ (nên thành công)
echo "TEST 3: Nhập số điện thoại hợp lệ\n";
try {
    $data = [
        'name' => 'Test Supplier Valid ' . time(),
        'phone' => '0901234567',  // Hợp lệ
        'is_active' => 1
    ];
    $id = $service->createSupplier($data);
    echo "✅ OK: Tạo nhà cung cấp thành công với ID: $id\n\n";
} catch (Exception $e) {
    echo "❌ LỖI: Không tạo được với số hợp lệ: " . $e->getMessage() . "\n\n";
}

// Test case 4: Nhập số không hợp lệ (có chữ)
echo "TEST 4: Nhập số điện thoại có chữ\n";
try {
    $data = [
        'name' => 'Test Supplier Invalid',
        'phone' => 'abc123456',  // Không hợp lệ
        'is_active' => 1
    ];
    $service->createSupplier($data);
    echo "❌ LỖI: Không báo lỗi khi nhập số có chữ!\n\n";
} catch (Exception $e) {
    echo "✅ OK: Đã bắt được lỗi: " . $e->getMessage() . "\n\n";
}

// Test case 5: Nhập số có ký tự đặc biệt
echo "TEST 5: Nhập số điện thoại có ký tự đặc biệt\n";
try {
    $data = [
        'name' => 'Test Supplier Special',
        'phone' => '090-123-4567',  // Không hợp lệ
        'is_active' => 1
    ];
    $service->createSupplier($data);
    echo "❌ LỖI: Không báo lỗi khi nhập số có ký tự đặc biệt!\n\n";
} catch (Exception $e) {
    echo "✅ OK: Đã bắt được lỗi: " . $e->getMessage() . "\n\n";
}

// Test case 6: Nhập số quá ngắn
echo "TEST 6: Nhập số điện thoại quá ngắn\n";
try {
    $data = [
        'name' => 'Test Supplier Short',
        'phone' => '12345',  // Quá ngắn (< 7)
        'is_active' => 1
    ];
    $service->createSupplier($data);
    echo "❌ LỖI: Không báo lỗi khi nhập số quá ngắn!\n\n";
} catch (Exception $e) {
    echo "✅ OK: Đã bắt được lỗi: " . $e->getMessage() . "\n\n";
}

echo "=== KẾT THÚC TEST ===\n";
