<?php
// Run migration to add base64 support

try {
    $pdo = new PDO('mysql:host=100.106.99.41;dbname=business_product_management_system', 'dev', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Running migration...\n\n";
    
    // Add image_data column
    $pdo->exec("ALTER TABLE product_images ADD COLUMN image_data LONGTEXT NULL COMMENT 'Base64 encoded image data' AFTER url");
    echo "✓ Added column: image_data\n";
    
    // Modify url to allow NULL
    $pdo->exec("ALTER TABLE product_images MODIFY COLUMN url VARCHAR(255) NULL");
    echo "✓ Modified column: url (now nullable)\n";
    
    // Add mime_type column
    $pdo->exec("ALTER TABLE product_images ADD COLUMN mime_type VARCHAR(50) NULL COMMENT 'image/jpeg, image/png, etc.' AFTER image_data");
    echo "✓ Added column: mime_type\n";
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "⚠️ Columns already exist. Skipping migration.\n";
    } else {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
}
