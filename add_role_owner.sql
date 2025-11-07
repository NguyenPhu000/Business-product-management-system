-- Migration: Thêm role Owner (id=5) vào bảng roles
-- Ngày: 2024-11-07
-- Mục đích: Hỗ trợ phân quyền nâng cao từ nhánh Minh2244

-- Kiểm tra và thêm role Owner nếu chưa tồn tại
INSERT INTO roles (id, name, description, created_at, updated_at)
SELECT 5, 'Chủ tiệm', 'Chủ cửa hàng - Quyền quản lý toàn bộ cửa hàng (cao hơn Staff, thấp hơn Admin)', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM roles WHERE id = 5
);

-- Hiển thị kết quả
SELECT * FROM roles ORDER BY id;
