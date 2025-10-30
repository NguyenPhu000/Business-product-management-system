# Changelog - Chuẩn hóa cấu trúc Database

## Ngày: 28/10/2025

### Tổng quan thay đổi

Hệ thống database đã được chuẩn hóa lại để đồng bộ tên cột, khóa chính và khóa ngoại theo chuẩn InnoDB.

---

## 1. Thay đổi cấu trúc Database

### Bảng `roles`

**Trước:**

- Khóa chính: `id_role`
- Tên cột: `name_role`, `description_role`

**Sau:**

- Khóa chính: `id`
- Tên cột: `name`, `description`

### Bảng `users`

**Trước:**

- Khóa chính: `id_user`
- Khóa ngoại: `id_role` (tham chiếu `roles.id_role`)

**Sau:**

- Khóa chính: `id`
- Khóa ngoại: `role_id` (tham chiếu `roles.id`)

### Bảng `user_logs`

**Trước:**

- Khóa chính: `id_user_log`
- Khóa ngoại: `id_user` (tham chiếu `users.id_user`)

**Sau:**

- Khóa chính: `id`
- Khóa ngoại: `user_id` (tham chiếu `users.id`)

### Bảng `system_config`

**Trước:**

- Khóa chính: `key_config`
- Tên cột: `value_config`
- Khóa ngoại: `id_user` (tham chiếu `users.id_user`)

**Sau:**

- Khóa chính: `key`
- Tên cột: `value`
- Khóa ngoại: `user_id` (tham chiếu `users.id`)

### Bảng `password_reset_requests`

**Sau:**

- Khóa chính: `id`
- Khóa ngoại: `user_id`, `approved_by` (tham chiếu `users.id`)

---

## 2. Files đã được cập nhật

### Models

- ✅ `src/Models/BaseModel.php` - Cập nhật primaryKey mặc định là `id`
- ✅ `src/Models/UserModel.php` - primaryKey: `id`, SQL joins với `role_id`
- ✅ `src/Models/RoleModel.php` - primaryKey: `id`, tên cột `name`, `description`
- ✅ `src/Models/UserLogModel.php` - primaryKey: `id`, khóa ngoại `user_id`
- ✅ `src/Models/SystemConfigModel.php` - primaryKey: `key`, tên cột `value`, `user_id`
- ✅ `src/Models/PasswordResetRequestModel.php` - SQL joins với `users.id`

### Controllers

- ✅ `src/Controllers/Admin/UsersController.php` - Cập nhật `role_id` thay vì `id_role`
- ✅ `src/Controllers/Admin/RolesController.php` - Cập nhật `name`, `description`
- ✅ `src/Controllers/Admin/PasswordResetController.php` - Cập nhật `user['id']`
- ✅ `src/Controllers/Admin/LogsController.php` - Cập nhật `user_id`
- ✅ `src/Controllers/Admin/ConfigController.php` - Cập nhật `key`, `value`
- ✅ `src/Controllers/Admin/AuthController.php` - Cập nhật `user['id']`, `role.name`

### Helpers

- ✅ `src/Helpers/AuthHelper.php` - Cập nhật session với `id`, `role_id`

### Views - Admin Users

- ✅ `src/views/admin/users/index.php` - Hiển thị `user['id']`, `role.name`
- ✅ `src/views/admin/users/form.php` - Form field `role_id`, hiển thị `role['name']`

### Views - Admin Roles

- ✅ `src/views/admin/roles/index.php` - Hiển thị `role['id']`, `role['name']`
- ✅ `src/views/admin/roles/form.php` - Form fields `name`, `description`

### Views - Admin Logs

- ✅ `src/views/admin/logs/index.php` - Hiển thị `log['id']`, `user['id']`

### Views - Admin Config

- ✅ `src/views/admin/config/index.php` - Form fields `key`, `value`

### Views - Dashboard

- ✅ `src/views/admin/dashboard.php` - Hiển thị `role.name`

---

## 3. Các khóa ngoại (Foreign Keys)

### Mối quan hệ đã được chuẩn hóa:

1. **users.role_id** → **roles.id**
2. **user_logs.user_id** → **users.id**
3. **system_config.user_id** → **users.id** (ON DELETE SET NULL)
4. **password_reset_requests.user_id** → **users.id** (ON DELETE CASCADE)
5. **password_reset_requests.approved_by** → **users.id** (ON DELETE SET NULL)

---

## 4. Hướng dẫn Migration

### Bước 1: Backup database hiện tại

```sql
mysqldump -u root -p business_product_management_system > backup_before_migrate.sql
```

### Bước 2: Drop database cũ và Import database mới

```sql
DROP DATABASE IF EXISTS business_product_management_system;
CREATE DATABASE business_product_management_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE business_product_management_system;
SOURCE business_product_management_system.sql;
```

### Bước 3: Kiểm tra code

- Tất cả các file Model, Controller, View đã được cập nhật
- Kiểm tra không còn sử dụng tên cột cũ

---

## 5. Testing Checklist

### ✅ Module Users

- [ ] Danh sách users hiển thị đúng
- [ ] Tạo user mới thành công
- [ ] Sửa user thành công
- [ ] Xóa user thành công

### ✅ Module Roles

- [ ] Danh sách roles hiển thị đúng
- [ ] Tạo role mới thành công
- [ ] Sửa role thành công
- [ ] Xóa role thành công

### ✅ Module Logs

- [ ] Danh sách logs hiển thị đúng
- [ ] Filter logs theo user
- [ ] Sửa log (admin only)
- [ ] Xóa log (admin only)

### ✅ Module Config

- [ ] Danh sách config hiển thị đúng
- [ ] Thêm config mới thành công
- [ ] Sửa config thành công
- [ ] Xóa config thành công

### ✅ Module Password Reset

- [ ] Gửi yêu cầu reset password
- [ ] Admin approve/reject request
- [ ] User đổi mật khẩu sau khi được approve
- [ ] Admin tự đổi mật khẩu

### ✅ Authentication

- [ ] Đăng nhập thành công
- [ ] Session lưu đúng thông tin user
- [ ] Đăng xuất thành công
- [ ] Log hoạt động đăng nhập/đăng xuất

---

## 6. Notes quan trọng

⚠️ **LƯU Ý:**

1. **KHÔNG import lại file SQL cũ** - sẽ gây lỗi do cấu trúc đã thay đổi
2. Tất cả code đã được cập nhật để tương thích với database mới
3. Các khóa ngoại đã được thiết lập đúng với ON DELETE và ON UPDATE
4. Dữ liệu demo chỉ giữ lại tài khoản admin

---

## 7. Tài khoản Admin mặc định

```
Username: admin
Email: mnminh-cntt17@tdu.edu.vn
Password: [Xem trong database - đã được hash]
```

---

## 8. Liên hệ

Nếu có vấn đề gì trong quá trình migration, vui lòng liên hệ team dev.

---

**Ngày hoàn thành:** 28/10/2025
**Người thực hiện:** GitHub Copilot
