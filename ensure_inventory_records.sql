-- Ensure all variants have inventory records
-- Run this in phpMyAdmin SQL tab after updating warehouse names
-- Date: 2025-11-10

-- First, update all existing 'default' warehouse to 'Kho chính'
UPDATE `inventory` 
SET `warehouse` = 'Kho chính' 
WHERE `warehouse` = 'default' 
   OR `warehouse` IS NULL 
   OR `warehouse` = '';

-- Insert inventory records for variants that don't have them yet
INSERT INTO `inventory` (`variant_id`, `warehouse`, `quantity`, `min_threshold`, `last_updated`)
SELECT 
    v.id,
    'Kho chính',
    0,
    10,
    NOW()
FROM `product_variants` v
WHERE NOT EXISTS (
    SELECT 1 FROM `inventory` i 
    WHERE i.variant_id = v.id 
    AND i.warehouse = 'Kho chính'
);

-- Verify results
SELECT 
    'Total Variants' as label,
    COUNT(*) as count
FROM `product_variants`
UNION ALL
SELECT 
    'Variants with Inventory' as label,
    COUNT(DISTINCT variant_id) as count
FROM `inventory`
UNION ALL
SELECT 
    'Kho chính Records' as label,
    COUNT(*) as count
FROM `inventory`
WHERE warehouse = 'Kho chính';
