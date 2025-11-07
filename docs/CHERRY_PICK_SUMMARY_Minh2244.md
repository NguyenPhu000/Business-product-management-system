# Cherry-picked Features from Minh2244 Branch

**Ngày**: 2024-11-07  
**Branch**: merge-test/develop  
**Source**: origin/Minh2244

---

## 📋 Tóm tắt thay đổi

Đã cherry-pick các tính năng phân quyền (authorization) và bảo mật tốt hơn từ nhánh Minh2244:

### ✅ Files đã thay đổi:

1. ✨ **src/Helpers/AuthHelper.php** - Thêm 5 methods phân quyền mới
2. ✨ **src/Middlewares/AdminOnlyMiddleware.php** - File mới (middleware chỉ Admin)
3. ✨ **config/constants.php** - Thêm constant ROLE_OWNER
4. ✨ **add_role_owner.sql** - Migration thêm role Owner

### 🔒 Backup:

- Branch backup: `backup/merge-test-develop`
- Rollback: `git reset --hard backup/merge-test-develop`

---

## 🎯 Tính năng mới

### 1. AuthHelper.php - 5 Methods mới

#### 1.1 `isOwner(): bool`

Kiểm tra user hiện tại có quyền **Chủ tiệm** không.

```php
// Example
if (AuthHelper::isOwner()) {
    echo "Bạn là Chủ tiệm";
}
```

#### 1.2 `isAdminOrOwner(): bool`

Kiểm tra user có quyền **quản lý cao** (Admin HOẶC Chủ tiệm).

```php
// Example - Chỉ Admin/Owner mới xem được báo cáo
if (!AuthHelper::isAdminOrOwner()) {
    http_response_code(403);
    echo "Chỉ Admin/Chủ tiệm mới xem được báo cáo";
    exit;
}
```

#### 1.3 `getRoleLevel(int $roleId): int`

Lấy **level quyền** của một role.

**Quy tắc phân cấp**:

- Admin (role_id=1): Level 3 (cao nhất)
- Owner (role_id=5): Level 2
- Sales Staff (role_id=2): Level 1
- Warehouse Manager (role_id=3): Level 1

```php
// Example
$adminLevel = AuthHelper::getRoleLevel(ROLE_ADMIN); // 3
$ownerLevel = AuthHelper::getRoleLevel(ROLE_OWNER); // 2
$staffLevel = AuthHelper::getRoleLevel(ROLE_SALES_STAFF); // 1
```

#### 1.4 `hasHigherRoleThan(int $targetRoleId): bool`

Kiểm tra user hiện tại có quyền **CAO HƠN** role được chỉ định không.

**Lưu ý**: Level bằng nhau = KHÔNG có quyền cao hơn.

```php
// Example - Admin có thể quản lý Owner
if (AuthHelper::isAdmin()) {
    $canManageOwner = AuthHelper::hasHigherRoleThan(ROLE_OWNER); // true
}

// Example - Owner KHÔNG thể quản lý Admin
if (AuthHelper::isOwner()) {
    $canManageAdmin = AuthHelper::hasHigherRoleThan(ROLE_ADMIN); // false
}

// Example - Sales Staff KHÔNG thể quản lý Warehouse Manager (level bằng nhau)
if (AuthHelper::hasRole(ROLE_SALES_STAFF)) {
    $canManageWarehouse = AuthHelper::hasHigherRoleThan(ROLE_WAREHOUSE_MANAGER); // false
}
```

#### 1.5 `canManageRole(int $targetRoleId): bool`

Kiểm tra user có thể **quản lý (edit/delete)** user với role được chỉ định không.

**Quy tắc**:

- Chỉ quyền CAO HƠN mới được quản lý quyền THẤP HƠN
- Quyền BẰNG NHAU không được quản lý lẫn nhau
- Không thể xóa tài khoản đang đăng nhập (check riêng ở controller)

```php
// Example - Trong UserController
public function delete(string $id): void
{
    $userId = (int) $id;
    $user = $this->userModel->find($userId);

    // Không cho xóa chính mình
    if ($userId == AuthHelper::id()) {
        $this->error('Không thể xóa tài khoản đang đăng nhập', 400);
        return;
    }

    // Kiểm tra quyền quản lý
    if (!AuthHelper::canManageRole($user['role_id'])) {
        $this->error('Bạn không có quyền xóa user này', 403);
        return;
    }

    // Xóa user
    $this->userModel->delete($userId);
    $this->success(null, 'Xóa user thành công');
}
```

---

### 2. AdminOnlyMiddleware.php (File mới)

Middleware chuyên dụng cho các chức năng **CHỈ ADMIN** (không cho Chủ tiệm).

#### Mục đích:

- Bảo vệ các tính năng nhạy cảm như **System Config**, **Role Management**
- Chỉ Admin (role_id=1) mới được truy cập
- Owner (role_id=5) KHÔNG được truy cập

#### Sử dụng:

```php
// File: config/routes.php
use Middlewares\AdminOnlyMiddleware;

// Cấu hình hệ thống - CHỈ ADMIN
$router->add('/admin/system-config', 'SystemConfigController@index', [AdminOnlyMiddleware::class]);
$router->add('/admin/system-config/update', 'SystemConfigController@update', [AdminOnlyMiddleware::class]);

// Quản lý vai trò - CHỈ ADMIN
$router->add('/admin/roles', 'RolesController@index', [AdminOnlyMiddleware::class]);
$router->add('/admin/roles/edit/{id}', 'RolesController@edit', [AdminOnlyMiddleware::class]);
```

#### So sánh với RoleMiddleware:

| Middleware            | Ai được phép? | Dùng cho                            |
| --------------------- | ------------- | ----------------------------------- |
| `RoleMiddleware`      | Admin + Owner | Dashboard, Reports, Products, Sales |
| `AdminOnlyMiddleware` | CHỈ Admin     | System Config, Role Management      |

---

### 3. ROLE_OWNER Constant

Thêm constant mới vào `config/constants.php`:

```php
define('ROLE_OWNER', 5); // Owner - Chủ tiệm/Chủ cửa hàng
```

**Phân cấp đầy đủ**:

- `ROLE_ADMIN = 1` - Level 3 (cao nhất)
- `ROLE_SALES_STAFF = 2` - Level 1
- `ROLE_WAREHOUSE_MANAGER = 3` - Level 1
- `ROLE_OWNER = 5` - Level 2

---

## 📊 Use Cases thực tế

### Use Case 1: Quản lý User

```php
// UserController.php
public function edit(string $id): void
{
    $userId = (int) $id;
    $user = $this->userModel->find($userId);

    // Kiểm tra quyền quản lý
    if (!AuthHelper::canManageRole($user['role_id'])) {
        AuthHelper::setFlash('error', 'Bạn không có quyền sửa user này');
        $this->redirect('/admin/users');
        return;
    }

    // Hiển thị form edit
    $this->view('admin/users/edit', ['user' => $user]);
}
```

**Kết quả**:

- ✅ Admin có thể sửa: Owner, Sales Staff, Warehouse Manager
- ✅ Owner có thể sửa: Sales Staff, Warehouse Manager
- ❌ Owner KHÔNG thể sửa: Admin
- ❌ Sales Staff KHÔNG thể sửa: Owner, Admin, Warehouse Manager

---

### Use Case 2: Phân quyền xem báo cáo

```php
// ReportController.php
public function financialReport(): void
{
    // Chỉ Admin hoặc Owner mới xem được báo cáo tài chính
    if (!AuthHelper::isAdminOrOwner()) {
        $this->error('Chỉ Admin/Chủ tiệm mới xem được báo cáo tài chính', 403);
        return;
    }

    // Hiển thị báo cáo
    $data = $this->reportModel->getFinancialData();
    $this->view('admin/reports/financial', $data);
}
```

---

### Use Case 3: System Config - Chỉ Admin

```php
// routes.php
use Middlewares\AdminOnlyMiddleware;

// Cấu hình hệ thống - CHỈ ADMIN (Owner KHÔNG được truy cập)
$router->add('/admin/system-config', 'SystemConfigController@index', [AdminOnlyMiddleware::class]);
```

**Kết quả**:

- ✅ Admin có thể truy cập System Config
- ❌ Owner bị chặn với 403 Forbidden
- ❌ Staff bị chặn với 403 Forbidden

---

## 🔧 Migration Database

Chạy file `add_role_owner.sql` để thêm role Owner:

```bash
# Cách 1: MySQL CLI
mysql -u root -p business_product_management < add_role_owner.sql

# Cách 2: PHPMyAdmin
# - Mở PHPMyAdmin
# - Chọn database
# - Tab SQL
# - Copy nội dung add_role_owner.sql và Execute
```

**Script SQL**:

```sql
INSERT INTO roles (id, name, description, created_at, updated_at)
SELECT 5, 'Chủ tiệm', 'Chủ cửa hàng - Quyền quản lý toàn bộ cửa hàng (cao hơn Staff, thấp hơn Admin)', NOW(), NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM roles WHERE id = 5
);
```

---

## ✅ Kiểm tra sau khi cherry-pick

### 1. Kiểm tra Syntax

```bash
php -l src/Helpers/AuthHelper.php
php -l src/Middlewares/AdminOnlyMiddleware.php
php -l config/constants.php
```

✅ Kết quả: **No syntax errors detected**

### 2. Kiểm tra Database

```sql
SELECT * FROM roles ORDER BY id;
```

Kết quả mong đợi:

```
| id | name               | description                          |
|----|--------------------|------------------------------------- |
| 1  | Admin              | Quản trị viên hệ thống               |
| 2  | Sales Staff        | Nhân viên bán hàng                   |
| 3  | Warehouse Manager  | Quản lý kho                          |
| 5  | Chủ tiệm           | Quyền quản lý toàn bộ cửa hàng       |
```

### 3. Test Phân quyền

```php
// Test trong controller hoặc tạo file test_auth.php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config/constants.php';

use Helpers\AuthHelper;

AuthHelper::startSession();

// Test getRoleLevel
echo "Admin level: " . AuthHelper::getRoleLevel(ROLE_ADMIN) . "\n"; // 3
echo "Owner level: " . AuthHelper::getRoleLevel(ROLE_OWNER) . "\n"; // 2
echo "Staff level: " . AuthHelper::getRoleLevel(ROLE_SALES_STAFF) . "\n"; // 1

// Test hasHigherRoleThan (giả sử đang đăng nhập Admin)
$_SESSION['user_role'] = ROLE_ADMIN;
var_dump(AuthHelper::hasHigherRoleThan(ROLE_OWNER)); // true
var_dump(AuthHelper::hasHigherRoleThan(ROLE_SALES_STAFF)); // true

// Test canManageRole
var_dump(AuthHelper::canManageRole(ROLE_OWNER)); // true (Admin quản lý được Owner)
```

---

## ⚠️ Lưu ý quan trọng

### 1. Không được xóa backup branch

```bash
# ĐỪNG XÓA branch này
git branch -D backup/merge-test-develop
```

### 2. Cập nhật routes.php

Nếu có System Config hoặc chức năng nhạy cảm, thêm `AdminOnlyMiddleware`:

```php
use Middlewares\AdminOnlyMiddleware;
use Middlewares\RoleMiddleware;

// Chỉ Admin
$router->add('/admin/system-config', 'SystemConfigController@index', [AdminOnlyMiddleware::class]);

// Admin + Owner
$router->add('/admin/dashboard', 'DashboardController@index', [RoleMiddleware::class]);
```

### 3. Update Controllers

Các controller quản lý User cần thêm check `canManageRole()`:

```php
// UserController.php - edit method
if (!AuthHelper::canManageRole($user['role_id'])) {
    $this->error('Bạn không có quyền sửa user này', 403);
    return;
}
```

---

## 🔄 Rollback (nếu cần)

Nếu gặp vấn đề, rollback về trạng thái trước:

```bash
# Rollback về backup branch
git reset --hard backup/merge-test-develop

# Xóa các file unstaged
git clean -fd
```

---

## 📝 Kết luận

✅ **Đã hoàn thành cherry-pick** các tính năng phân quyền từ Minh2244:

- AuthHelper.php: +5 methods phân quyền mới
- AdminOnlyMiddleware.php: Middleware chuyên dụng cho Admin
- ROLE_OWNER constant: Hỗ trợ role Chủ tiệm
- Migration SQL: Thêm role Owner vào database

✅ **Kiểm tra syntax**: Không có lỗi
✅ **Backup**: Branch `backup/merge-test-develop` đã tạo
✅ **Tài liệu**: File này + BRANCH_COMPARISON_Minh2244.md

🚀 **Bước tiếp theo**:

1. Chạy migration `add_role_owner.sql`
2. Cập nhật routes.php (thêm AdminOnlyMiddleware cho System Config)
3. Cập nhật UserController (thêm check canManageRole)
4. Test đầy đủ hệ thống phân quyền
5. Commit changes
