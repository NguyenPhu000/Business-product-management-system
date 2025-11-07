# So sánh Branch: merge-test/develop vs origin/Minh2244

**Ngày**: 2024
**Mục đích**: So sánh và cherry-pick các tính năng phân quyền (authorization) và bảo mật tốt hơn từ nhánh Minh2244

---

## 📊 Tổng quan

### Các file khác biệt liên quan đến Auth/Security:

1. ✅ **src/Helpers/AuthHelper.php** - ⭐ Minh2244 TỐT HƠN
2. ✅ **src/Middlewares/AdminOnlyMiddleware.php** - ⭐ CHỈ CÓ Ở Minh2244 (cần thêm)
3. ⚖️ **src/Middlewares/RoleMiddleware.php** - GIỐNG NHAU (cả 2 nhánh giống nhau)
4. ⚠️ **src/Controllers/Admin/RolesController.php** - Minh2244 bỏ chức năng tạo/xóa role
5. 🔍 **src/Controllers/Admin/AuthController.php** - Cần kiểm tra thêm

---

## 🔍 Chi tiết so sánh

### 1. AuthHelper.php ⭐ MINH2244 TỐT HƠN

**Nhánh hiện tại (merge-test/develop)**: 137 dòng
- ✅ Có: startSession, login, logout, check, user, id, isAdmin, hasRole
- ✅ Có: setFlash, getFlash, checkTimeout
- ❌ Thiếu: isOwner, isAdminOrOwner
- ❌ Thiếu: getRoleLevel (logic phân cấp quyền)
- ❌ Thiếu: hasHigherRoleThan (so sánh quyền)
- ❌ Thiếu: canManageRole (kiểm tra quyền quản lý)

**Nhánh Minh2244**: 208 dòng (+71 dòng)
- ✅ TẤT CẢ tính năng của nhánh hiện tại
- ✨ **MỚI**: `isOwner()` - Kiểm tra quyền Chủ tiệm
- ✨ **MỚI**: `isAdminOrOwner()` - Kiểm tra quyền quản lý cao (Admin hoặc Chủ tiệm)
- ✨ **MỚI**: `getRoleLevel(int $roleId): int` - Lấy level quyền
  ```
  Quy tắc: Admin (3) > Chủ tiệm (2) > Sales Staff (1) = Warehouse Manager (1)
  ```
- ✨ **MỚI**: `hasHigherRoleThan(int $targetRoleId): bool` - So sánh quyền
- ✨ **MỚI**: `canManageRole(int $targetRoleId): bool` - Kiểm tra quyền quản lý user

**📌 KẾT LUẬN**: Minh2244 có hệ thống phân quyền phức tạp hơn, cho phép phân cấp quyền theo level. TỐT HƠN nhánh hiện tại.

---

### 2. AdminOnlyMiddleware.php ⭐ CHỈ CÓ Ở MINH2244

**Nhánh hiện tại**: ❌ KHÔNG CÓ FILE NÀY

**Nhánh Minh2244**: ✅ CÓ FILE NÀY
- Mục đích: Middleware chuyên dụng cho các chức năng CHỈ ADMIN (không cho Chủ tiệm)
- Dùng cho: Cấu hình hệ thống (System Config)
- Logic:
  1. Kiểm tra đăng nhập
  2. Kiểm tra `isAdmin()` - chỉ cho Admin, không cho Chủ tiệm
  3. Trả về 403 Forbidden nếu không đủ quyền

**📌 KẾT LUẬN**: Cần THÊM FILE NÀY từ Minh2244. Hữu ích cho phân quyền chặt chẽ hơn.

---

### 3. RoleMiddleware.php ⚖️ GIỐNG NHAU

**So sánh**: CẢ 2 NHÁNH GIỐNG HỆT NHAU
- Kiểm tra đăng nhập
- Kiểm tra quyền admin
- Trả về 403 nếu không đủ quyền

**📌 KẾT LUẬN**: Không cần thay đổi.

---

### 4. RolesController.php ⚠️ MINH2244 BỎ CHỨC NĂNG

**Nhánh hiện tại (merge-test/develop)**:
- ✅ Có: index, create, store, edit, update, delete
- ✅ Có thể: TẠO role mới
- ✅ Có thể: XÓA role (nếu không có user nào dùng)

**Nhánh Minh2244**:
- ✅ Có: index, edit, update
- ❌ BỎ: create, store (không cho tạo role mới)
- ❌ BỎ: delete (không cho xóa role)
- ✅ Thêm check: Chỉ Admin mới được sửa vai trò

**Lý do Minh2244 bỏ**:
- Roles trong database là cố định (1=Admin, 2=Sales Staff, 3=Warehouse Manager, 5=Owner)
- Không cần tạo/xóa role động
- Chỉ cần SỬA mô tả/tên role

**📌 KẾT LUẬN**: 
- Nếu hệ thống có **roles cố định** → Dùng Minh2244 (an toàn hơn)
- Nếu hệ thống cần **tạo role động** → Giữ nhánh hiện tại
- **KHUYẾN NGHỊ**: Dùng Minh2244 (roles cố định an toàn hơn)

---

### 5. AuthController.php 🔍 CẦN KIỂM TRA

Chưa so sánh chi tiết. Cần xem thêm.

---

## 🎯 Quyết định Cherry-pick

### ✅ CẦN LẤY TỪ MINH2244:

1. **AuthHelper.php** - ⭐ ƯU TIÊN CAO
   - Lý do: Có thêm 5 methods hỗ trợ phân quyền phức tạp
   - Tính năng mới: isOwner, isAdminOrOwner, getRoleLevel, hasHigherRoleThan, canManageRole
   - Tác động: Cải thiện đáng kể khả năng phân quyền

2. **AdminOnlyMiddleware.php** - ⭐ ƯU TIÊN CAO
   - Lý do: File mới, không có ở nhánh hiện tại
   - Tính năng: Middleware chuyên dụng cho chức năng chỉ Admin
   - Tác động: Bảo mật tốt hơn cho System Config

3. **RolesController.php** - ⚖️ TÙY DỰ ÁN
   - Lý do: Loại bỏ tạo/xóa role động (an toàn hơn nếu roles cố định)
   - **KHUYẾN NGHỊ**: Lấy từ Minh2244 nếu dự án dùng roles cố định

### ❌ KHÔNG CẦN LẤY:

1. **RoleMiddleware.php** - Giống nhau
2. **AuthController.php** - Cần kiểm tra thêm (chưa rõ)

---

## 🚀 Kế hoạch thực hiện

### Bước 1: Backup nhánh hiện tại
```bash
git branch backup/merge-test-develop
```

### Bước 2: Cherry-pick AuthHelper.php
```bash
git checkout origin/Minh2244 -- src/Helpers/AuthHelper.php
```

### Bước 3: Thêm AdminOnlyMiddleware.php
```bash
git checkout origin/Minh2244 -- src/Middlewares/AdminOnlyMiddleware.php
```

### Bước 4: (Tùy chọn) Cherry-pick RolesController.php
```bash
git checkout origin/Minh2244 -- src/Controllers/Admin/RolesController.php
```

### Bước 5: Kiểm tra constants.php
Đảm bảo có định nghĩa `ROLE_OWNER`:
```php
define('ROLE_OWNER', 5);
```

### Bước 6: Test
- Test đăng nhập/đăng xuất
- Test phân quyền Admin/Owner/Staff
- Test middleware AdminOnlyMiddleware

---

## ⚠️ LƯU Ý QUAN TRỌNG

### 1. Thêm constant ROLE_OWNER
File `config/constants.php` hiện tại có:
```php
ROLE_ADMIN = 1
ROLE_SALES_STAFF = 2
ROLE_WAREHOUSE_MANAGER = 3
```

**CẦN THÊM**:
```php
define('ROLE_OWNER', 5);
```

### 2. Database cần có role Owner
Kiểm tra bảng `roles`:
```sql
SELECT * FROM roles WHERE id = 5;
```

Nếu chưa có, thêm:
```sql
INSERT INTO roles (id, name, description) VALUES (5, 'Chủ tiệm', 'Quyền quản lý toàn bộ cửa hàng');
```

### 3. Cập nhật routes
Nếu dùng AdminOnlyMiddleware, cần thêm vào routes:
```php
// routes.php
use Middlewares\AdminOnlyMiddleware;
use Middlewares\RoleMiddleware;

// Chỉ Admin
$router->add('/admin/system-config', 'SystemConfigController@index', [AdminOnlyMiddleware::class]);

// Admin hoặc Chủ tiệm
$router->add('/admin/dashboard', 'DashboardController@index', [RoleMiddleware::class]);
```

---

## 📝 Kết luận

**Nhánh Minh2244 TỐT HƠN** về mặt phân quyền và bảo mật:
- ✅ Hệ thống phân cấp quyền theo level (getRoleLevel)
- ✅ Logic so sánh quyền (hasHigherRoleThan)
- ✅ Kiểm tra quyền quản lý (canManageRole)
- ✅ Middleware chuyên dụng cho Admin (AdminOnlyMiddleware)
- ✅ An toàn hơn với roles cố định (không cho tạo/xóa role tùy ý)

**KHUYẾN NGHỊ**: Cherry-pick các tính năng từ Minh2244.
