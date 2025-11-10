-- ============================================
-- Migration: Create Inventory Tables
-- Description: Tạo bảng quản lý tồn kho và giao dịch kho
-- Date: 2025-11-10
-- ============================================

-- 1. Bảng inventory: Quản lý tồn kho theo variant và warehouse
CREATE TABLE IF NOT EXISTS `inventory` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_variant_id` INT UNSIGNED NOT NULL COMMENT 'ID của variant sản phẩm',
    `warehouse` VARCHAR(50) NOT NULL DEFAULT 'default' COMMENT 'Tên kho (default, warehouse1, warehouse2...)',
    `quantity` INT NOT NULL DEFAULT 0 COMMENT 'Số lượng tồn kho',
    `min_threshold` INT NOT NULL DEFAULT 10 COMMENT 'Ngưỡng tồn kho tối thiểu (cảnh báo)',
    `reserved_quantity` INT NOT NULL DEFAULT 0 COMMENT 'Số lượng đang giữ chỗ (đặt hàng)',
    `last_import_at` DATETIME NULL COMMENT 'Thời điểm nhập kho gần nhất',
    `last_export_at` DATETIME NULL COMMENT 'Thời điểm xuất kho gần nhất',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    KEY `idx_variant` (`product_variant_id`),
    KEY `idx_warehouse` (`warehouse`),
    KEY `idx_quantity` (`quantity`),
    UNIQUE KEY `unique_variant_warehouse` (`product_variant_id`, `warehouse`),
    
    -- Foreign Key
    CONSTRAINT `fk_inventory_variant` 
        FOREIGN KEY (`product_variant_id`) 
        REFERENCES `product_variants` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Quản lý tồn kho theo variant và kho';

-- 2. Bảng inventory_transactions: Lịch sử giao dịch kho
CREATE TABLE IF NOT EXISTS `inventory_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `product_variant_id` INT UNSIGNED NOT NULL COMMENT 'ID của variant',
    `warehouse` VARCHAR(50) NOT NULL COMMENT 'Kho thực hiện giao dịch',
    `type` ENUM('import', 'export', 'adjust', 'transfer', 'return') NOT NULL COMMENT 'Loại giao dịch',
    `quantity_change` INT NOT NULL COMMENT 'Số lượng thay đổi (+/-)',
    `quantity_after` INT NOT NULL COMMENT 'Tồn kho sau giao dịch',
    `reference_type` VARCHAR(50) NULL COMMENT 'Loại tham chiếu (order, purchase_order, etc.)',
    `reference_id` INT UNSIGNED NULL COMMENT 'ID tham chiếu',
    `note` TEXT NULL COMMENT 'Ghi chú, lý do điều chỉnh',
    `created_by` INT UNSIGNED NOT NULL COMMENT 'Người thực hiện',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    -- Indexes
    KEY `idx_variant` (`product_variant_id`),
    KEY `idx_warehouse` (`warehouse`),
    KEY `idx_type` (`type`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_created_by` (`created_by`),
    KEY `idx_reference` (`reference_type`, `reference_id`),
    
    -- Foreign Keys
    CONSTRAINT `fk_trans_variant` 
        FOREIGN KEY (`product_variant_id`) 
        REFERENCES `product_variants` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_trans_user` 
        FOREIGN KEY (`created_by`) 
        REFERENCES `users` (`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Lịch sử giao dịch kho hàng';

-- 3. Tạo trigger để tự động tạo bản ghi inventory khi thêm variant mới
DELIMITER $$

DROP TRIGGER IF EXISTS `after_variant_insert`$$
CREATE TRIGGER `after_variant_insert`
AFTER INSERT ON `product_variants`
FOR EACH ROW
BEGIN
    -- Tự động tạo inventory record cho kho mặc định
    INSERT INTO `inventory` (`product_variant_id`, `warehouse`, `quantity`, `min_threshold`)
    VALUES (NEW.id, 'default', 0, 10)
    ON DUPLICATE KEY UPDATE `product_variant_id` = NEW.id;
END$$

DELIMITER ;

-- 4. Tạo stored procedure để xử lý giao dịch kho
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_stock_transaction`$$
CREATE PROCEDURE `sp_stock_transaction`(
    IN p_variant_id INT UNSIGNED,
    IN p_warehouse VARCHAR(50),
    IN p_type VARCHAR(20),
    IN p_quantity_change INT,
    IN p_note TEXT,
    IN p_user_id INT UNSIGNED,
    IN p_reference_type VARCHAR(50),
    IN p_reference_id INT UNSIGNED
)
BEGIN
    DECLARE v_current_quantity INT DEFAULT 0;
    DECLARE v_new_quantity INT DEFAULT 0;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Lấy số lượng hiện tại (với lock)
    SELECT `quantity` INTO v_current_quantity
    FROM `inventory`
    WHERE `product_variant_id` = p_variant_id 
        AND `warehouse` = p_warehouse
    FOR UPDATE;
    
    -- Nếu chưa có record, tạo mới
    IF v_current_quantity IS NULL THEN
        INSERT INTO `inventory` (`product_variant_id`, `warehouse`, `quantity`, `min_threshold`)
        VALUES (p_variant_id, p_warehouse, 0, 10);
        SET v_current_quantity = 0;
    END IF;
    
    -- Tính số lượng mới
    SET v_new_quantity = v_current_quantity + p_quantity_change;
    
    -- Kiểm tra số lượng âm (chỉ cảnh báo, không chặn)
    IF v_new_quantity < 0 THEN
        SET v_new_quantity = 0;
    END IF;
    
    -- Cập nhật inventory
    UPDATE `inventory`
    SET 
        `quantity` = v_new_quantity,
        `last_import_at` = IF(p_quantity_change > 0, NOW(), `last_import_at`),
        `last_export_at` = IF(p_quantity_change < 0, NOW(), `last_export_at`)
    WHERE `product_variant_id` = p_variant_id 
        AND `warehouse` = p_warehouse;
    
    -- Ghi log transaction
    INSERT INTO `inventory_transactions` (
        `product_variant_id`,
        `warehouse`,
        `type`,
        `quantity_change`,
        `quantity_after`,
        `reference_type`,
        `reference_id`,
        `note`,
        `created_by`
    ) VALUES (
        p_variant_id,
        p_warehouse,
        p_type,
        p_quantity_change,
        v_new_quantity,
        p_reference_type,
        p_reference_id,
        p_note,
        p_user_id
    );
    
    COMMIT;
    
    -- Return kết quả
    SELECT v_new_quantity AS new_quantity, v_current_quantity AS old_quantity;
END$$

DELIMITER ;

-- 5. Tạo view để xem tồn kho với thông tin sản phẩm
CREATE OR REPLACE VIEW `v_inventory_stock` AS
SELECT 
    i.id,
    i.product_variant_id,
    i.warehouse,
    i.quantity,
    i.min_threshold,
    i.reserved_quantity,
    i.last_import_at,
    i.last_export_at,
    pv.sku AS variant_sku,
    pv.attributes,
    pv.price,
    pv.cost,
    p.id AS product_id,
    p.name AS product_name,
    p.sku AS product_sku,
    p.unit,
    c.name AS category_name,
    CASE 
        WHEN i.quantity <= 0 THEN 'out_of_stock'
        WHEN i.quantity <= i.min_threshold THEN 'low_stock'
        ELSE 'in_stock'
    END AS stock_status,
    (i.quantity * pv.cost) AS stock_value
FROM inventory i
INNER JOIN product_variants pv ON i.product_variant_id = pv.id
INNER JOIN products p ON pv.product_id = p.id
LEFT JOIN categories c ON p.category_id = c.id;

-- 6. Tạo view cho lịch sử giao dịch với thông tin đầy đủ
CREATE OR REPLACE VIEW `v_inventory_transactions` AS
SELECT 
    it.id,
    it.product_variant_id,
    it.warehouse,
    it.type,
    it.quantity_change,
    it.quantity_after,
    it.reference_type,
    it.reference_id,
    it.note,
    it.created_by,
    it.created_at,
    pv.sku AS variant_sku,
    p.name AS product_name,
    p.sku AS product_sku,
    u.fullname AS created_by_fullname,
    u.username AS created_by_name
FROM inventory_transactions it
INNER JOIN product_variants pv ON it.product_variant_id = pv.id
INNER JOIN products p ON pv.product_id = p.id
LEFT JOIN users u ON it.created_by = u.id;

-- ============================================
-- Chèn dữ liệu mẫu (nếu cần)
-- ============================================

-- Tự động tạo inventory records cho các variants đã tồn tại
INSERT INTO `inventory` (`product_variant_id`, `warehouse`, `quantity`, `min_threshold`)
SELECT 
    pv.id,
    'default',
    0,
    10
FROM `product_variants` pv
WHERE NOT EXISTS (
    SELECT 1 FROM `inventory` i 
    WHERE i.product_variant_id = pv.id 
        AND i.warehouse = 'default'
);

-- ============================================
-- Hoàn tất migration
-- ============================================
-- Run: mysql -u root -p business_product_management < migrations/create_inventory_tables.sql
