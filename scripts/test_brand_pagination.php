<?php
/**
 * Test script: Kiểm tra phân trang thương hiệu
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/core/Bootstrap.php';

use Modules\Category\Services\BrandService;

$service = new BrandService();

echo "=== TEST PHÂN TRANG THƯƠNG HIỆU ===\n\n";

// Test 1: Lấy trang 1
echo "TEST 1: Lấy trang 1 (8 thương hiệu/trang)\n";
$result = $service->getBrandsWithPagination(1, 8);
echo "✅ Tổng số thương hiệu: {$result['total']}\n";
echo "✅ Trang hiện tại: {$result['page']}\n";
echo "✅ Số lượng/trang: {$result['perPage']}\n";
echo "✅ Tổng số trang: {$result['totalPages']}\n";
echo "✅ Số thương hiệu trang này: " . count($result['data']) . "\n\n";

if (!empty($result['data'])) {
    echo "Danh sách thương hiệu trang 1:\n";
    foreach ($result['data'] as $brand) {
        echo "  - ID {$brand['id']}: {$brand['name']} ({$brand['product_count']} sản phẩm)\n";
    }
    echo "\n";
}

// Test 2: Lấy trang 2 (nếu có)
if ($result['totalPages'] > 1) {
    echo "TEST 2: Lấy trang 2\n";
    $result2 = $service->getBrandsWithPagination(2, 8);
    echo "✅ Trang hiện tại: {$result2['page']}\n";
    echo "✅ Số thương hiệu trang này: " . count($result2['data']) . "\n\n";
    
    if (!empty($result2['data'])) {
        echo "Danh sách thương hiệu trang 2:\n";
        foreach ($result2['data'] as $brand) {
            echo "  - ID {$brand['id']}: {$brand['name']} ({$brand['product_count']} sản phẩm)\n";
        }
        echo "\n";
    }
} else {
    echo "TEST 2: SKIP - Chỉ có 1 trang\n\n";
}

// Test 3: Thử trang không hợp lệ (page = 0)
echo "TEST 3: Trang không hợp lệ (page = 0, phải tự động chuyển sang 1)\n";
$result3 = $service->getBrandsWithPagination(0, 8);
echo ($result3['page'] === 1 ? "✅" : "❌") . " Trang được chuyển thành: {$result3['page']}\n\n";

// Test 4: Thử trang quá lớn
echo "TEST 4: Trang quá lớn (page = 999)\n";
$result4 = $service->getBrandsWithPagination(999, 8);
echo "✅ Trang yêu cầu: 999\n";
echo "✅ Số thương hiệu trả về: " . count($result4['data']) . "\n";
echo (count($result4['data']) === 0 ? "✅" : "⚠️") . " Kết quả rỗng (đúng như mong đợi)\n\n";

echo "=== KẾT THÚC TEST ===\n";
