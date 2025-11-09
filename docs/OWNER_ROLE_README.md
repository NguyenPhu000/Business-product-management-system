# Phân quyền "Chủ tiệm" (Owner Role)

## Tổng quan

Role **Chủ tiệm** (ID: 5) được thêm vào hệ thống với quyền hạn cao, nằm giữa Admin và các nhân viên khác.

## Cấp độ phân quyền

```
1. Admin (ID: 1) - Cao nhất
   ├─ Toàn quyền hệ thống
   └─ Truy cập Cấu hình hệ thống

2. Chủ tiệm (ID: 5) - Cao thứ 2
   ├─ Quản lý toàn bộ hệ thống
   ├─ Quản lý người dùng
   ├─ Quản lý vai trò
   ├─ Xem log hoạt động
   ├─ Phê duyệt yêu cầu đặt lại mật khẩu
   └─ ❌ KHÔNG truy cập được Cấu hình hệ thống

3. Sales Staff (ID: 2) - Thấp hơn
   └─ Quản lý bán hàng, đơn hàng

4. Warehouse Manager (ID: 3) - Thấp hơn
   └─ Quản lý tồn kho, kho hàng
```

## Chức năng Chủ tiệm có thể truy cập

### ✅ Được phép:

- Dashboard
- Quản lý người dùng (Users)
- Quản lý vai trò (Roles)
- Log hoạt động (Logs)
- Yêu cầu đặt lại mật khẩu (Password Reset)
- Tất cả module kinh doanh khác (nếu có)

### ❌ Không được phép:

- **Cấu hình hệ thống** (System Config) - Chỉ dành cho Admin

## Thay đổi kỹ thuật

### 1. Constants mới (`config/constants.php`)

```php
define('ROLE_ADMIN', 1);
define('ROLE_SALES_STAFF', 2);
define('ROLE_WAREHOUSE_MANAGER', 3);
define('ROLE_OWNER', 5); // Chủ tiệm
```

### 2. Helper methods mới (`src/Helpers/AuthHelper.php`)

```php
AuthHelper::isOwner()          // Kiểm tra là Chủ tiệm
AuthHelper::isAdminOrOwner()   // Kiểm tra là Admin HOẶC Chủ tiệm
```

### 3. Middleware

- **RoleMiddleware** - Cho phép cả Admin và Chủ tiệm
- **AdminOnlyMiddleware** (MỚI) - Chỉ cho phép Admin

### 4. Routes

```php
// Các route thông thường - Admin và Chủ tiệm
$router->get('/admin/users', '...', [AuthMiddleware::class, RoleMiddleware::class]);

// Route Cấu hình hệ thống - CHỈ Admin
$router->get('/admin/config', '...', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
```

### 5. Sidebar menu (`src/views/admin/layout/sidebar.php`)

- Menu "Quản lý công ty" hiển thị cho cả Admin và Chủ tiệm
- Submenu "Cấu hình hệ thống" chỉ hiển thị cho Admin

## Tài khoản test

### User Chủ tiệm mặc định:

```
Username: chutiem
Email: chutiem@example.com
Password: 123456
Role: Chủ tiệm (ID: 5)
```

### Tạo user Chủ tiệm mới:

```bash
php scripts/create_owner_user.php
```

## Kiểm tra phân quyền

### Test case 1: Đăng nhập với Chủ tiệm

1. Login với user `chutiem` / `123456`
2. ✅ Có thể truy cập: Dashboard, Users, Roles, Logs, Password Reset
3. ❌ Menu "Cấu hình hệ thống" không hiển thị
4. ❌ Truy cập trực tiếp `/admin/config` → 403 Forbidden

### Test case 2: Đăng nhập với Admin

1. Login với user `admin`
2. ✅ Có thể truy cập tất cả, bao gồm "Cấu hình hệ thống"

### Test case 3: Đăng nhập với Sales Staff

1. Login với user Sales Staff
2. ❌ Không thể truy cập phần "Quản lý công ty"

## Migration

Nếu bạn đã có database cũ, chạy:

```bash
# 1. Thêm role Chủ tiệm vào bảng roles
php scripts/add_owner_role.php

# 2. Tạo user test (optional)
php scripts/create_owner_user.php

# 3. Cập nhật role cho user hiện có (nếu cần)
# Truy cập phpMyAdmin và chạy:
UPDATE users SET role_id = 5 WHERE id = <user_id>;
```

## Files đã thay đổi

1. ✅ `config/constants.php` - Thêm ROLE_OWNER
2. ✅ `src/Helpers/AuthHelper.php` - Thêm isOwner(), isAdminOrOwner()
3. ✅ `src/Middlewares/RoleMiddleware.php` - Cho phép Admin và Owner
4. ✅ `src/Middlewares/AdminOnlyMiddleware.php` - Middleware mới (CHỈ Admin)
5. ✅ `config/routes.php` - Cập nhật routes /admin/config
6. ✅ `src/views/admin/layout/sidebar.php` - Ẩn menu Cấu hình với Owner
7. ✅ `scripts/add_owner_role.php` - Script thêm role
8. ✅ `scripts/create_owner_user.php` - Script tạo user test

## Ghi chú

- Role ID 4 bị bỏ qua để tránh conflict với dữ liệu cũ
- Chủ tiệm có quyền tương đương Admin trừ Cấu hình hệ thống
- Có thể mở rộng thêm middleware khác nếu cần phân quyền chi tiết hơn
