# HƯỚNG DẪN CÀI ĐẶT CHỨC NĂNG RESET PASSWORD

## Bước 1: Tạo bảng trong database

Mở phpMyAdmin tại: `http://localhost/phpmyadmin`

Chọn database `business_product_management_system` và chạy SQL sau:

```sql
CREATE TABLE IF NOT EXISTS `password_reset_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_at` datetime NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `new_password` varchar(255) DEFAULT NULL COMMENT 'Mật khẩu mới sau khi được phê duyệt',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `fk_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  CONSTRAINT `fk_reset_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Bước 2: Kiểm tra các file đã được tạo

### Models:

- ✅ `src/Models/PasswordResetRequestModel.php`

### Controllers:

- ✅ `src/Controllers/Admin/PasswordResetController.php`
- ✅ `src/Controllers/Admin/AuthController.php` (đã cập nhật)

### Views:

- ✅ `src/views/admin/password-reset/index.php`
- ✅ `src/views/auth/forgot-password.php` (đã cập nhật)

### Config:

- ✅ `config/routes.php` (đã thêm routes)

### Sidebar:

- ✅ `src/views/admin/layout/sidebar.php` (đã thêm menu "Yêu cầu đặt lại MK")

## Bước 3: Test chức năng

### Test với Admin:

1. Truy cập: `http://localhost/forgot-password`
2. Nhập email của admin
3. Mật khẩu mới sẽ hiển thị ngay lập tức
4. Đăng nhập với mật khẩu mới

### Test với User thường:

1. Truy cập: `http://localhost/forgot-password`
2. Nhập email của user (không phải admin)
3. Sẽ thấy thông báo "Yêu cầu đã được gửi, chờ admin phê duyệt"
4. Admin đăng nhập và vào menu "Yêu cầu đặt lại MK"
5. Admin nhấn "Phê duyệt" → Mật khẩu mới hiển thị
6. User đăng nhập với mật khẩu mới

## Luồng hoạt động:

### 1. Admin quên mật khẩu:

```
Nhập email → Kiểm tra role → Admin → Tạo mật khẩu mới ngay → Hiển thị
```

### 2. User thường quên mật khẩu:

```
Nhập email → Kiểm tra role → Không phải Admin → Tạo yêu cầu pending →
Admin vào trang quản lý → Nhấn "Phê duyệt" → Tạo mật khẩu mới → Hiển thị cho admin
```

## Các tính năng:

- ✅ Admin tự đổi mật khẩu ngay lập tức
- ✅ User thường phải chờ admin phê duyệt
- ✅ Admin có thể từ chối yêu cầu
- ✅ Hiển thị danh sách tất cả yêu cầu
- ✅ Hiển thị badge số lượng yêu cầu đang chờ
- ✅ Ghi log tất cả hành động
- ✅ Mật khẩu ngẫu nhiên 8 ký tự (chữ, số, ký tự đặc biệt)
- ✅ Không cho tạo nhiều yêu cầu pending cùng lúc
