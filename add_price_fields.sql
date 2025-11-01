-- Thêm các cột giá, đơn vị tính, thuế VAT vào bảng products
-- Chạy từng câu lệnh một trong phpMyAdmin

-- 1. Thêm cột đơn vị tính
ALTER TABLE products ADD COLUMN unit VARCHAR(50) DEFAULT 'cái' AFTER status;

-- 2. Thêm cột giá nhập
ALTER TABLE products ADD COLUMN unit_cost DECIMAL(15,2) DEFAULT 0.00 AFTER unit;

-- 3. Thêm cột giá bán
ALTER TABLE products ADD COLUMN price DECIMAL(15,2) DEFAULT 0.00 AFTER unit_cost;

-- 4. Thêm cột giá khuyến mãi
ALTER TABLE products ADD COLUMN sale_price DECIMAL(15,2) NULL AFTER price;

-- 5. Thêm cột thuế VAT
ALTER TABLE products ADD COLUMN tax_id INT NULL AFTER sale_price;

-- 6. Thêm index cho tax_id
ALTER TABLE products ADD KEY idx_tax_id (tax_id);

-- 7. Thêm dữ liệu thuế VAT vào bảng tax
-- Kiểm tra cấu trúc bảng tax trước, nếu không có cột type, is_active thì bỏ qua
-- Nếu bảng tax chỉ có: id, name, rate
INSERT INTO tax (name, rate) VALUES
('Không chịu thuế', 0.00),
('VAT 5%', 5.00),
('VAT 8%', 8.00),
('VAT 10%', 10.00)
ON DUPLICATE KEY UPDATE rate=rate;
