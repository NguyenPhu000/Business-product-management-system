<?php

/**
 * Create test variant for testing
 * Run: php create_test_variant.php
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Load config
$dbConfig = require __DIR__ . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}",
        $dbConfig['username'],
        $dbConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "✅ Kết nối database thành công!\n\n";

    // Lấy product đầu tiên
    $stmt = $pdo->query("SELECT id, name, sku, price, unit_cost FROM products LIMIT 1");
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "❌ Không có sản phẩm nào. Vui lòng tạo sản phẩm trước.\n";
        exit(1);
    }

    echo "📦 Sử dụng Product #{$product['id']}: {$product['name']}\n";
    echo "   SKU: {$product['sku']}\n\n";

    // Tạo variant
    $variantSku = $product['sku'] . '-VAR-TEST-' . strtoupper(substr(uniqid(), -6));
    $attributes = json_encode([
        'Màu sắc' => 'Đen',
        'Dung lượng' => '256GB'
    ], JSON_UNESCAPED_UNICODE);

    $stmt = $pdo->prepare("
        INSERT INTO product_variants 
        (product_id, sku, attributes, price, unit_cost, barcode, is_active, created_at) 
        VALUES 
        (?, ?, ?, ?, ?, ?, 1, NOW())
    ");

    $stmt->execute([
        $product['id'],
        $variantSku,
        $attributes,
        $product['price'] ?? 1000000,
        $product['unit_cost'] ?? 800000,
        'BARCODE-' . time()
    ]);

    $variantId = $pdo->lastInsertId();

    echo "✅ Tạo variant thành công!\n";
    echo "   Variant ID: {$variantId}\n";
    echo "   SKU: {$variantSku}\n";
    echo "   Attributes: Màu sắc=Đen, Dung lượng=256GB\n\n";

    // Tạo inventory record
    $stmt = $pdo->prepare("
        INSERT INTO inventory 
        (product_variant_id, warehouse, quantity, min_threshold, last_updated) 
        VALUES 
        (?, 'default', 0, 10, NOW())
    ");

    $stmt->execute([$variantId]);

    echo "✅ Tạo inventory record thành công!\n";
    echo "   Warehouse: default\n";
    echo "   Quantity: 0\n";
    echo "   Min Threshold: 10\n\n";

    // Tạo transaction nhập kho ban đầu
    $initialStock = 50;
    $stmt = $pdo->prepare("
        INSERT INTO inventory_transactions 
        (product_variant_id, warehouse, type, quantity_change, reference_type, reference_id, note, created_by, created_at) 
        VALUES 
        (?, 'default', 'import', ?, 'manual', NULL, 'Nhập kho ban đầu khi tạo variant test', 1, NOW())
    ");

    $stmt->execute([$variantId, $initialStock]);

    echo "✅ Tạo transaction nhập kho ban đầu!\n";
    echo "   Type: import\n";
    echo "   Quantity: +{$initialStock}\n\n";

    // Update inventory quantity
    $stmt = $pdo->prepare("UPDATE inventory SET quantity = ? WHERE product_variant_id = ?");
    $stmt->execute([$initialStock, $variantId]);

    echo "✅ Cập nhật tồn kho: {$initialStock} đơn vị\n\n";

    echo "========== TEST VARIANT CREATED ==========\n";
    echo "🎉 Đã tạo xong variant test với đầy đủ inventory và transaction!\n\n";

    echo "🌐 Test URLs:\n";
    echo "   - View Variants: http://localhost/admin/products/{$product['id']}/variants\n";
    echo "   - Inventory Detail: http://localhost/admin/inventory/detail/{$variantId}\n";
    echo "   - Adjust Stock: http://localhost/admin/inventory/adjust/{$variantId}\n\n";

    echo "✨ Giờ có thể chạy: php test_inventory_crud.php\n";
} catch (PDOException $e) {
    echo "❌ Lỗi database: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    exit(1);
}
