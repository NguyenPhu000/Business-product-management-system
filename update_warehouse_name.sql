-- Update warehouse name from 'default' to 'Kho chính'
-- Run this in phpMyAdmin SQL tab
-- Date: 2025-11-10

UPDATE `inventory` 
SET `warehouse` = 'Kho chính' 
WHERE `warehouse` = 'default' 
   OR `warehouse` IS NULL 
   OR `warehouse` = '';

-- Verify the update
SELECT DISTINCT warehouse, COUNT(*) as count 
FROM inventory 
GROUP BY warehouse;
