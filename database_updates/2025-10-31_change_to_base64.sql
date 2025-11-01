-- Migration: Chuyển lưu ảnh từ URL sang Base64
-- Date: 2025-10-31
-- Author: MinhKhoi

-- Thêm cột image_data để lưu base64
ALTER TABLE `product_images` 
ADD COLUMN `image_data` LONGTEXT NULL COMMENT 'Base64 encoded image data' AFTER `url`;

-- Giữ lại cột url để tương thích ngược (có thể NULL)
ALTER TABLE `product_images` 
MODIFY COLUMN `url` VARCHAR(255) NULL;

-- Thêm cột mime_type để lưu loại file
ALTER TABLE `product_images` 
ADD COLUMN `mime_type` VARCHAR(50) NULL COMMENT 'image/jpeg, image/png, etc.' AFTER `image_data`;
