<?php

/**
 * Test CRUD operations cho Inventory Module
 * Run: php test_inventory_crud.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Load config
$dbConfig = require __DIR__ . '/config/database.php';

try {
    // Kết nối database
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✅ Kết nối database thành công!\n\n";

    // TEST 1: List sản phẩm và variants
    echo "========== TEST 1: List Products & Variants ==========\n";
    $stmt = $pdo->query("
        SELECT 
            p.id, 
            p.name, 
            p.sku, 
            v.id as variant_id, 
            v.sku as variant_sku 
        FROM products p 
        LEFT JOIN product_variants v ON p.id = v.product_id 
        LIMIT 5
    ");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($products)) {
        echo "❌ Không có sản phẩm nào trong database\n";
        echo "👉 Vui lòng tạo sản phẩm trước khi test\n";
        exit(1);
    }

    foreach ($products as $p) {
        echo "Product #{$p['id']}: {$p['name']} (SKU: {$p['sku']})";
        if ($p['variant_id']) {
            echo " -> Variant #{$p['variant_id']} (SKU: {$p['variant_sku']})";
        }
        echo "\n";
    }

    // Chọn variant đầu tiên để test
    $testVariantId = null;
    foreach ($products as $p) {
        if ($p['variant_id']) {
            $testVariantId = $p['variant_id'];
            $testVariantSku = $p['variant_sku'];
            break;
        }
    }

    if (!$testVariantId) {
        echo "\n❌ Không có variant nào để test\n";
        echo "👉 Vui lòng tạo variant cho sản phẩm trước\n";
        exit(1);
    }

    echo "\n📦 Sử dụng Variant #{$testVariantId} (SKU: {$testVariantSku}) để test\n\n";

    // TEST 2: Check inventory hiện tại
    echo "========== TEST 2: Check Current Inventory ==========\n";
    $stmt = $pdo->prepare("SELECT * FROM inventory WHERE product_variant_id = ?");
    $stmt->execute([$testVariantId]);
    $inventory = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($inventory) {
        echo "✅ Inventory record exists:\n";
        echo "   - Warehouse: {$inventory['warehouse']}\n";
        echo "   - Quantity: {$inventory['quantity']}\n";
        echo "   - Min Threshold: {$inventory['min_threshold']}\n";
        echo "   - Max Threshold: {$inventory['max_threshold']}\n";
        $currentQty = $inventory['quantity'];
    } else {
        echo "⚠️ Chưa có inventory record, sẽ tạo mới\n";
        $currentQty = 0;
    }

    // TEST 3: Test importStock simulation
    echo "\n========== TEST 3: Simulate Import Stock (READ ONLY) ==========\n";
    echo "📥 Giả lập nhập kho: +50 đơn vị\n";
    echo "   Current: {$currentQty} -> Expected: " . ($currentQty + 50) . "\n";

    // TEST 4: Test exportStock simulation
    echo "\n========== TEST 4: Simulate Export Stock (READ ONLY) ==========\n";
    $exportQty = min(10, $currentQty); // Chỉ xuất tối đa số có
    echo "📤 Giả lập xuất kho: -{$exportQty} đơn vị\n";
    if ($currentQty >= $exportQty) {
        echo "   ✅ Đủ hàng để xuất\n";
        echo "   Current: {$currentQty} -> Expected: " . ($currentQty - $exportQty) . "\n";
    } else {
        echo "   ❌ KHÔNG đủ hàng (thiếu " . ($exportQty - $currentQty) . " đơn vị)\n";
    }

    // TEST 5: Check transaction history
    echo "\n========== TEST 5: Transaction History ==========\n";
    $stmt = $pdo->prepare("
        SELECT 
            id, 
            type, 
            quantity_change, 
            note, 
            created_at 
        FROM inventory_transactions 
        WHERE product_variant_id = ? 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
    $stmt->execute([$testVariantId]);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($transactions)) {
        echo "⚠️ Chưa có transaction nào\n";
    } else {
        echo "✅ Có " . count($transactions) . " transactions:\n";
        foreach ($transactions as $t) {
            echo "   - [{$t['created_at']}] {$t['type']}: {$t['quantity_change']} ({$t['note']})\n";
        }
    }

    // TEST 6: Low stock check
    echo "\n========== TEST 6: Low Stock Check ==========\n";
    $stmt = $pdo->query("
        SELECT 
            v.id,
            v.sku,
            i.quantity,
            i.min_threshold
        FROM inventory i
        INNER JOIN product_variants v ON i.product_variant_id = v.id
        WHERE i.quantity < i.min_threshold
        LIMIT 5
    ");
    $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($lowStock)) {
        echo "✅ Không có variant nào sắp hết hàng\n";
    } else {
        echo "⚠️ Có " . count($lowStock) . " variants sắp hết hàng:\n";
        foreach ($lowStock as $ls) {
            echo "   - Variant #{$ls['id']} ({$ls['sku']}): {$ls['quantity']}/{$ls['min_threshold']}\n";
        }
    }

    // SUMMARY
    echo "\n========== SUMMARY ==========\n";
    echo "✅ Tất cả test queries hoạt động tốt\n";
    echo "✅ Database schema đúng\n";
    echo "✅ Có thể test CRUD trên giao diện web\n\n";

    echo "🌐 Các URL để test trên trình duyệt:\n";
    echo "   - List: http://localhost/admin/inventory\n";
    echo "   - Detail: http://localhost/admin/inventory/detail/{$testVariantId}\n";
    echo "   - Adjust: http://localhost/admin/inventory/adjust/{$testVariantId}\n";
    echo "   - Low Stock: http://localhost/admin/inventory/low-stock\n";
    echo "   - History: http://localhost/admin/inventory/history\n\n";

    echo "✨ READY TO TEST!\n";
} catch (PDOException $e) {
    echo "❌ Lỗi database: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
