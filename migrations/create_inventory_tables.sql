-- ============================================
-- Migration: ALTER Inventory Tables
-- Description: Cập nhật bảng inventory và inventory_transactions đã tồn tại
-- Date: 2025-11-10
-- Note: Bảng inventory và inventory_transactions ĐÃ TỒN TẠI trong Database.md
--       Migration này chỉ thêm cột mới và indexes
-- ============================================

-- 1. Kiểm tra và thêm cột cho bảng inventory (nếu chưa có)
ALTER TABLE `inventory` 
    ADD COLUMN IF NOT EXISTS `reserved_quantity` INT DEFAULT 0 COMMENT 'Số lượng đang giữ chỗ (đặt hàng)' AFTER `min_threshold`;

-- Đổi tên cột last_updated thành created_at/updated_at (nếu cần)
-- Note: Bảng hiện tại có last_updated, giữ nguyên để không phá vỡ dữ liệu cũ
-- ALTER TABLE `inventory` 
--     CHANGE COLUMN `last_updated` `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 2. Cập nhật bảng inventory_transactions - Thêm các type mới và cột quantity_after
ALTER TABLE `inventory_transactions`
    MODIFY COLUMN `type` ENUM('import', 'export', 'adjust', 'transfer', 'return') NOT NULL COMMENT 'Loại giao dịch';

ALTER TABLE `inventory_transactions`
    ADD COLUMN IF NOT EXISTS `quantity_after` INT DEFAULT 0 COMMENT 'Tồn kho sau giao dịch' AFTER `quantity_change`;

-- 3. Thêm indexes nếu chưa có
ALTER TABLE `inventory`
    ADD INDEX IF NOT EXISTS `idx_variant` (`product_variant_id`),
    ADD INDEX IF NOT EXISTS `idx_warehouse` (`warehouse`),
    ADD INDEX IF NOT EXISTS `idx_quantity` (`quantity`);

ALTER TABLE `inventory_transactions`
    ADD INDEX IF NOT EXISTS `idx_variant` (`product_variant_id`),
    ADD INDEX IF NOT EXISTS `idx_warehouse` (`warehouse`),
    ADD INDEX IF NOT EXISTS `idx_type` (`type`),
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`),
    ADD INDEX IF NOT EXISTS `idx_created_by` (`created_by`),
    ADD INDEX IF NOT EXISTS `idx_reference` (`reference_type`, `reference_id`);

-- 3. Thêm indexes nếu chưa có
ALTER TABLE `inventory`
    ADD INDEX IF NOT EXISTS `idx_variant` (`product_variant_id`),
    ADD INDEX IF NOT EXISTS `idx_warehouse` (`warehouse`),
    ADD INDEX IF NOT EXISTS `idx_quantity` (`quantity`);

ALTER TABLE `inventory_transactions`
    ADD INDEX IF NOT EXISTS `idx_variant` (`product_variant_id`),
    ADD INDEX IF NOT EXISTS `idx_warehouse` (`warehouse`),
    ADD INDEX IF NOT EXISTS `idx_type` (`type`),
    ADD INDEX IF NOT EXISTS `idx_created_at` (`created_at`),
    ADD INDEX IF NOT EXISTS `idx_created_by` (`created_by`),
    ADD INDEX IF NOT EXISTS `idx_reference` (`reference_type`, `reference_id`);

-- 4. Tạo stored procedure để xử lý giao dịch kho
DELIMITER $$

DROP PROCEDURE IF EXISTS `sp_stock_transaction`$$
CREATE PROCEDURE `sp_stock_transaction`(
    IN p_variant_id INT,
    IN p_warehouse VARCHAR(150),
    IN p_type VARCHAR(20),
    IN p_quantity_change INT,
    IN p_note TEXT,
    IN p_user_id INT,
    IN p_reference_type VARCHAR(50),
    IN p_reference_id INT
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
        `last_updated` = NOW()
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
    COALESCE(i.reserved_quantity, 0) AS reserved_quantity,
    i.last_updated,
    pv.sku AS variant_sku,
    pv.attributes,
    pv.price,
    pv.unit_cost AS cost,
    p.id AS product_id,
    p.name AS product_name,
    p.sku AS product_sku,
    CASE 
        WHEN i.quantity <= 0 THEN 'out_of_stock'
        WHEN i.quantity <= i.min_threshold THEN 'low_stock'
        ELSE 'in_stock'
    END AS stock_status,
    (i.quantity * pv.unit_cost) AS stock_value
FROM inventory i
INNER JOIN product_variants pv ON i.product_variant_id = pv.id
INNER JOIN products p ON pv.product_id = p.id;

-- 6. Tạo view cho lịch sử giao dịch với thông tin đầy đủ
CREATE OR REPLACE VIEW `v_inventory_transactions` AS
SELECT 
    it.id,
    it.product_variant_id,
    it.warehouse,
    it.type,
    it.quantity_change,
    COALESCE(it.quantity_after, 0) AS quantity_after,
    it.reference_type,
    it.reference_id,
    it.note,
    it.created_by,
    it.created_at,
    pv.sku AS variant_sku,
    p.name AS product_name,
    p.sku AS product_sku,
    u.full_name AS created_by_fullname,
    u.username AS created_by_name
FROM inventory_transactions it
INNER JOIN product_variants pv ON it.product_variant_id = pv.id
INNER JOIN products p ON pv.product_id = p.id
LEFT JOIN users u ON it.created_by = u.id;

-- ============================================
-- Tự động tạo inventory records cho các variants đã tồn tại
-- ============================================

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
-- Run: mysql -h 100.106.99.41 -u dev business_product_management_system < migrations/create_inventory_tables.sql
