# 📋 REFACTOR TASKS - CHI TIẾT TỪNG FILE

**Dự án**: Business Product Management System  
**Ngày tạo**: 09/11/2025  
**Mục tiêu**: Tuân thủ 100% CODING_RULES.md
**Lưu ý**: Các logic trong controller khi được tách ra và đem vào service thì không được thay đổi hay thêm logic khác.
---

## 🎯 DANH SÁCH FILE CẦN REFACTOR

### ❌ PHASE 1: CONTROLLERS CẦN DI CHUYỂN (13 files)

#### 1.1. ProductController.php
- **File hiện tại**: `src/Controllers/Admin/ProductController.php` (538 dòng)
- **File đích**: `src/modules/product/controllers/ProductController.php`
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\Product\Controllers`
- **Trạng thái**: ⚠️ CẦN TẠO SERVICE TRƯỚC
- **Dependencies cần tạo**:
  - ✅ `ProductService.php` (section 2.1)
  - ✅ `ImageService.php` (section 2.2)
- **Tasks**:
  - [ ] Tạo `ProductService::createProduct()`
  - [ ] Tạo `ProductService::updateProduct()`
  - [ ] Tạo `ProductService::deleteProduct()`
  - [ ] Tạo `ProductService::getProductsList()`
  - [ ] Tạo `ProductService::countProducts()`
  - [ ] Tạo `ProductService::getProductWithCategories()`
  - [ ] Tạo `ImageService::uploadImages()`
  - [ ] Tạo `ImageService::deleteImage()`
  - [ ] Tạo `ImageService::deleteAllProductImages()`
  - [ ] Tạo `ImageService::getProductImages()`
  - [ ] Tạo `ImageService::setPrimaryImage()`
  - [ ] Refactor Controller (chỉ routing logic)
  - [ ] Update routes trong `config/routes.php`
  - [ ] Test CRUD Product
  - [ ] Test Upload/Delete Images

---

#### 1.2. CategoryController.php
- **File hiện tại**: `src/Controllers/Admin/CategoryController.php`
- **File đích**: `src/modules/category/controllers/CategoryController.php`
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\Category\Controllers`
- **Trạng thái**: ✅ CategoryService ĐÃ CÓ
- **Tasks**:
  - [ ] Review `CategoryService` hiện có
  - [ ] Refactor Controller để dùng Service
  - [ ] Update routes
  - [ ] Test Category Tree

---

#### 1.3. BrandController.php
- **File hiện tại**: `src/Controllers/Admin/BrandController.php`
- **File đích**: `src/modules/brand/controllers/BrandController.php` (CHƯA TỒN TẠI)
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\Brand\Controllers`
- **Trạng thái**: ⚠️ CẦN TẠO MODULE MỚI
- **Tasks**:
  - [ ] Tạo thư mục `src/modules/brand/`
  - [ ] Tạo `brand/controllers/`
  - [ ] Tạo `brand/models/`
  - [ ] Tạo `brand/services/BrandService.php`
  - [ ] Tạo `brand/views/`
  - [ ] Di chuyển BrandController
  - [ ] Di chuyển BrandModel
  - [ ] Update routes

---

#### 1.4. UsersController.php
- **File hiện tại**: `src/Controllers/Admin/UsersController.php`
- **File đích**: `src/modules/user/controllers/UserController.php`
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\User\Controllers`
- **Trạng thái**: ✅ UserService ĐÃ CÓ
- **Tasks**:
  - [ ] Review `UserService` hiện có
  - [ ] Refactor Controller
  - [ ] Update routes
  - [ ] Test User CRUD

---

#### 1.5. AuthController.php
- **File hiện tại**: `src/Controllers/Admin/AuthController.php`
- **File đích**: `src/modules/auth/controllers/AuthController.php`
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\Auth\Controllers`
- **Trạng thái**: ✅ AuthService ĐÃ CÓ
- **Tasks**:
  - [ ] Review `AuthService` hiện có
  - [ ] Refactor Controller
  - [ ] Update routes
  - [ ] Test Login/Logout

---

#### 1.6. SupplierController.php
- **File hiện tại**: `src/Controllers/Admin/SupplierController.php`
- **File đích**: `src/modules/supplier/controllers/SupplierController.php` (CHƯA TỒN TẠI)
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\Supplier\Controllers`
- **Trạng thái**: ⚠️ CẦN TẠO MODULE MỚI
- **Tasks**:
  - [ ] Tạo thư mục `src/modules/supplier/`
  - [ ] Tạo `supplier/controllers/`
  - [ ] Tạo `supplier/models/`
  - [ ] Tạo `supplier/services/SupplierService.php`
  - [ ] Tạo `supplier/views/`
  - [ ] Di chuyển SupplierController
  - [ ] Di chuyển SupplierModel
  - [ ] Update routes

---

#### 1.7. RolesController.php
- **File hiện tại**: `src/Controllers/Admin/RolesController.php`
- **File đích**: `src/modules/auth/controllers/RoleController.php`
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\Auth\Controllers`
- **Tasks**:
  - [ ] Tạo `AuthService::getRoles()`
  - [ ] Tạo `AuthService::createRole()`
  - [ ] Tạo `AuthService::updateRole()`
  - [ ] Tạo `AuthService::deleteRole()`
  - [ ] Refactor Controller
  - [ ] Update routes

---

#### 1.8. HomeController.php
- **File hiện tại**: `src/Controllers/Admin/HomeController.php`
- **File đích**: `src/modules/dashboard/controllers/DashboardController.php` (CHƯA TỒN TẠI)
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\Dashboard\Controllers`
- **Tasks**:
  - [ ] Tạo module `dashboard`
  - [ ] Tạo `DashboardService.php`
  - [ ] Di chuyển logic dashboard
  - [ ] Update routes

---

#### 1.9. ProductCategoryController.php
- **File hiện tại**: `src/Controllers/Admin/ProductCategoryController.php`
- **File đích**: DI CHUYỂN VÀO `ProductController` hoặc `CategoryController`
- **Tasks**:
  - [ ] Xem xét gộp vào ProductController hoặc CategoryController
  - [ ] Nếu giữ riêng, di chuyển sang module product
  - [ ] Update routes

---

#### 1.10. ProductVariantController.php
- **File hiện tại**: `src/Controllers/Admin/ProductVariantController.php`
- **File đích**: `src/modules/product/controllers/VariantController.php`
- **Namespace cũ**: `Controllers\Admin`
- **Namespace mới**: `Modules\Product\Controllers`
- **Trạng thái**: ✅ ĐÃ CÓ `VariantController` trong module
- **Tasks**:
  - [ ] So sánh 2 file
  - [ ] Merge logic nếu cần
  - [ ] Xóa file cũ
  - [ ] Update routes

---

#### 1.11. ConfigController.php
- **File hiện tại**: `src/Controllers/Admin/ConfigController.php`
- **File đích**: `src/modules/system/controllers/ConfigController.php` (CHƯA TỒN TẠI)
- **Tasks**:
  - [ ] Tạo module `system`
  - [ ] Tạo `SystemService.php`
  - [ ] Di chuyển ConfigController
  - [ ] Update routes

---

#### 1.12. PasswordResetController.php
- **File hiện tại**: `src/Controllers/Admin/PasswordResetController.php`
- **File đích**: `src/modules/auth/controllers/PasswordResetController.php`
- **Tasks**:
  - [ ] Di chuyển vào module auth
  - [ ] Tích hợp với AuthService
  - [ ] Update routes

---

#### 1.13. LogsController.php
- **File hiện tại**: `src/Controllers/Admin/LogsController.php`
- **File đích**: `src/modules/system/controllers/LogsController.php`
- **Tasks**:
  - [ ] Di chuyển vào module system
  - [ ] Tạo `LogService.php`
  - [ ] Update routes

---

## 🗂️ PHASE 2: MODELS CẦN DI CHUYỂN (14 files)

### 2.1. ProductModel.php
- **File hiện tại**: `src/Models/ProductModel.php`
- **File đích**: `src/modules/product/models/ProductModel.php`
- **Namespace cũ**: `Models`
- **Namespace mới**: `Modules\Product\Models`
- **Trạng thái**: ⚠️ CÓ TRÙNG LẶP với `src/modules/product/models/ProductModel.php`
- **Tasks**:
  - [ ] So sánh 2 file
  - [ ] Merge các method từ file cũ sang file mới
  - [ ] Đảm bảo tên bảng đúng với database.md
  - [ ] Xóa file cũ sau khi merge
  - [ ] Update namespace imports trong các file khác

---

### 2.2. CategoryModel.php
- **File hiện tại**: `src/Models/CategoryModel.php`
- **File đích**: `src/modules/category/models/CategoryModel.php`
- **Namespace cũ**: `Models`
- **Namespace mới**: `Modules\Category\Models`
- **Trạng thái**: ⚠️ CÓ TRÙNG LẶP
- **Tasks**:
  - [ ] So sánh 2 file
  - [ ] Merge các method
  - [ ] Xóa file cũ

---

### 2.3. BrandModel.php
- **File hiện tại**: `src/Models/BrandModel.php`
- **File đích**: `src/modules/brand/models/BrandModel.php`
- **Namespace cũ**: `Models`
- **Namespace mới**: `Modules\Brand\Models`
- **Tasks**:
  - [ ] Tạo thư mục `brand/models/`
  - [ ] Di chuyển file
  - [ ] Đổi namespace
  - [ ] Update imports

---

### 2.4. UserModel.php
- **File hiện tại**: `src/Models/UserModel.php`
- **File đích**: `src/modules/user/models/UserModel.php` HOẶC `src/modules/auth/models/UserModel.php`
- **Namespace cũ**: `Models`
- **Namespace mới**: `Modules\User\Models` hoặc `Modules\Auth\Models`
- **Trạng thái**: ⚠️ CÓ TRÙNG LẶP Ở 2 NƠI
- **Tasks**:
  - [ ] Quyết định đặt trong module nào (user hoặc auth)
  - [ ] Merge 3 file UserModel (Models, User, Auth)
  - [ ] Xóa 2 file còn lại
  - [ ] Update imports

---

### 2.5. RoleModel.php
- **File hiện tại**: `src/Models/RoleModel.php`
- **File đích**: `src/modules/auth/models/RoleModel.php`
- **Namespace cũ**: `Models`
- **Namespace mới**: `Modules\Auth\Models`
- **Trạng thái**: ⚠️ CÓ TRÙNG LẶP
- **Tasks**:
  - [ ] So sánh 2 file
  - [ ] Merge
  - [ ] Xóa file cũ

---

### 2.6. SupplierModel.php
- **File hiện tại**: `src/Models/SupplierModel.php`
- **File đích**: `src/modules/supplier/models/SupplierModel.php`
- **Tasks**:
  - [ ] Di chuyển
  - [ ] Đổi namespace
  - [ ] Update imports

---

### 2.7. ProductCategoryModel.php
- **File hiện tại**: `src/Models/ProductCategoryModel.php`
- **File đích**: `src/modules/product/models/ProductCategoryModel.php`
- **Trạng thái**: ⚠️ CÓ TRÙNG LẶP
- **Tasks**:
  - [ ] So sánh
  - [ ] Merge
  - [ ] Xóa cũ

---

### 2.8. ProductImageModel.php
- **File hiện tại**: `src/Models/ProductImageModel.php`
- **File đích**: `src/modules/product/models/ProductImageModel.php`
- **Trạng thái**: ⚠️ CÓ TRÙNG LẶP
- **Tasks**:
  - [ ] So sánh
  - [ ] Merge
  - [ ] Xóa cũ

---

### 2.9. TaxModel.php
- **File hiện tại**: `src/Models/TaxModel.php`
- **File đích**: `src/modules/finance/models/TaxModel.php` (MODULE MỚI)
- **Tasks**:
  - [ ] Tạo module finance
  - [ ] Di chuyển TaxModel
  - [ ] Đổi namespace

---

### 2.10. SystemConfigModel.php
- **File hiện tại**: `src/Models/SystemConfigModel.php`
- **File đích**: `src/modules/system/models/SystemConfigModel.php`
- **Tasks**:
  - [ ] Di chuyển vào module system
  - [ ] Đổi namespace

---

### 2.11. UserLogModel.php
- **File hiện tại**: `src/Models/UserLogModel.php`
- **File đích**: `src/modules/system/models/UserLogModel.php`
- **Tasks**:
  - [ ] Di chuyển
  - [ ] Đổi namespace

---

### 2.12. PasswordResetRequestModel.php
- **File hiện tại**: `src/Models/PasswordResetRequestModel.php`
- **File đích**: `src/modules/auth/models/PasswordResetRequestModel.php`
- **Tasks**:
  - [ ] Di chuyển
  - [ ] Đổi namespace

---

### 2.13-2.14. BaseModel.php & DatabaseModel.php
- **File**: `src/Models/BaseModel.php`, `src/Models/DatabaseModel.php`
- **Trạng thái**: ✅ GIỮ NGUYÊN (core models)
- **Tasks**: KHÔNG CẦN DI CHUYỂN

---

## 📦 PHASE 3: TẠO MODULE MỚI

### 3.1. Module Brand
```
src/modules/brand/
├── controllers/
│   └── BrandController.php
├── models/
│   └── BrandModel.php
├── services/
│   └── BrandService.php
└── views/
    ├── index.php
    ├── create.php
    └── edit.php
```
**Tasks**:
- [ ] Tạo cấu trúc thư mục
- [ ] Tạo BrandService
- [ ] Di chuyển BrandController
- [ ] Di chuyển BrandModel
- [ ] Di chuyển views từ `src/views/admin/brands/`

---

### 3.2. Module Supplier
```
src/modules/supplier/
├── controllers/
│   └── SupplierController.php
├── models/
│   └── SupplierModel.php
├── services/
│   └── SupplierService.php
└── views/
    ├── index.php
    ├── create.php
    ├── edit.php
    └── detail.php
```
**Tasks**:
- [ ] Tạo cấu trúc thư mục
- [ ] Tạo SupplierService
- [ ] Di chuyển Controller
- [ ] Di chuyển Model
- [ ] Di chuyển views

---

### 3.3. Module System
```
src/modules/system/
├── controllers/
│   ├── ConfigController.php
│   └── LogsController.php
├── models/
│   ├── SystemConfigModel.php
│   └── UserLogModel.php
└── services/
    ├── SystemService.php
    └── LogService.php
```
**Tasks**:
- [ ] Tạo cấu trúc
- [ ] Tạo Services
- [ ] Di chuyển Controllers
- [ ] Di chuyển Models

---

### 3.4. Module Dashboard
```
src/modules/dashboard/
├── controllers/
│   └── DashboardController.php
├── services/
│   └── DashboardService.php
└── views/
    └── index.php
```
**Tasks**:
- [ ] Tạo module
- [ ] Tạo DashboardService (tổng hợp thống kê)
- [ ] Di chuyển HomeController → DashboardController
- [ ] Di chuyển view dashboard

---

### 3.5. Module Finance (nếu cần)
```
src/modules/finance/
├── models/
│   └── TaxModel.php
└── services/
    └── TaxService.php
```

---

## 🔧 PHASE 4: UPDATE ROUTES

**File**: `config/routes.php`

### Tasks:
- [ ] Update tất cả routes Product → `Modules\Product\Controllers\ProductController`
- [ ] Update tất cả routes Category → `Modules\Category\Controllers\CategoryController`
- [ ] Update tất cả routes Brand → `Modules\Brand\Controllers\BrandController`
- [ ] Update tất cả routes User → `Modules\User\Controllers\UserController`
- [ ] Update tất cả routes Auth → `Modules\Auth\Controllers\AuthController`
- [ ] Update tất cả routes Supplier → `Modules\Supplier\Controllers\SupplierController`
- [ ] Update routes Role → `Modules\Auth\Controllers\RoleController`
- [ ] Update routes Config → `Modules\System\Controllers\ConfigController`
- [ ] Update routes Logs → `Modules\System\Controllers\LogsController`
- [ ] Update routes Dashboard → `Modules\Dashboard\Controllers\DashboardController`
- [ ] Test tất cả routes (không có 404)

---

## 🧪 PHASE 5: TESTING CHECKLIST

### 5.1. Module Product
- [ ] Xem danh sách sản phẩm (pagination, filter)
- [ ] Tạo sản phẩm mới (với ảnh)
- [ ] Sửa sản phẩm
- [ ] Xóa sản phẩm
- [ ] Upload nhiều ảnh
- [ ] Xóa ảnh
- [ ] Đặt ảnh chính
- [ ] Gán category cho sản phẩm
- [ ] Filter theo category, brand

### 5.2. Module Category
- [ ] Xem tree categories
- [ ] Tạo category mới
- [ ] Tạo sub-category
- [ ] Sửa category
- [ ] Xóa category

### 5.3. Module Brand
- [ ] CRUD Brand
- [ ] Filter sản phẩm theo brand

### 5.4. Module User
- [ ] CRUD User
- [ ] Gán role
- [ ] Test permissions

### 5.5. Module Auth
- [ ] Login
- [ ] Logout
- [ ] Register (nếu có)
- [ ] Password reset
- [ ] CRUD Roles
- [ ] Assign permissions

### 5.6. Module Supplier
- [ ] CRUD Supplier
- [ ] View supplier details

### 5.7. Integration Tests
- [ ] Tạo Purchase Order với Supplier
- [ ] Tạo Sales Order với Product
- [ ] Kiểm tra inventory update
- [ ] Kiểm tra reports

---

## 📄 PHASE 6: UPDATE COMPOSER & AUTOLOAD

**File**: `composer.json`

### Current autoload:
```json
"autoload": {
    "psr-4": {
        "Controllers\\": "src/Controllers/",
        "Models\\": "src/Models/",
        "Modules\\": "src/modules/",
        "Core\\": "src/core/",
        "Helpers\\": "src/Helpers/",
        "Middlewares\\": "src/Middlewares/"
    }
}
```

### New autoload (sau khi xóa Controllers\\ và Models\\):
```json
"autoload": {
    "psr-4": {
        "Modules\\": "src/modules/",
        "Core\\": "src/core/",
        "Helpers\\": "src/Helpers/",
        "Middlewares\\": "src/Middlewares/"
    }
}
```

### Tasks:
- [ ] Xóa `"Controllers\\"` khỏi autoload
- [ ] Xóa `"Models\\"` khỏi autoload (SAU KHI đã xóa thư mục)
- [ ] Chạy `composer dump-autoload`
- [ ] Test lại tất cả

---

## 🗑️ PHASE 7: XÓA FILE CŨ

### ⚠️ CHỈ XÓA SAU KHI ĐÃ TEST KỸ

#### 7.1. Xóa Controllers cũ
```powershell
# Backup
New-Item -ItemType Directory -Force -Path "backup\Controllers"
Copy-Item -Path "src\Controllers\Admin\*" -Destination "backup\Controllers\" -Recurse

# Kiểm tra backup
Get-ChildItem -Path "backup\Controllers" -Recurse

# Test tất cả chức năng (NẾU OK, tiếp tục)

# Xóa thư mục Controllers/Admin
Remove-Item -Path "src\Controllers\Admin" -Recurse -Force

# Xóa thư mục Controllers nếu rỗng
if ((Get-ChildItem "src\Controllers" | Measure-Object).Count -eq 0) {
    Remove-Item -Path "src\Controllers" -Force
}
```

#### 7.2. Xóa Models cũ
```powershell
# Backup
New-Item -ItemType Directory -Force -Path "backup\Models"
Copy-Item -Path "src\Models\*" -Destination "backup\Models\" -Exclude "BaseModel.php","DatabaseModel.php"

# Xóa từng file (GIỮ LẠI BaseModel.php và DatabaseModel.php)
Remove-Item -Path "src\Models\ProductModel.php" -Force
Remove-Item -Path "src\Models\CategoryModel.php" -Force
Remove-Item -Path "src\Models\BrandModel.php" -Force
Remove-Item -Path "src\Models\UserModel.php" -Force
Remove-Item -Path "src\Models\RoleModel.php" -Force
Remove-Item -Path "src\Models\SupplierModel.php" -Force
Remove-Item -Path "src\Models\ProductCategoryModel.php" -Force
Remove-Item -Path "src\Models\ProductImageModel.php" -Force
Remove-Item -Path "src\Models\TaxModel.php" -Force
Remove-Item -Path "src\Models\SystemConfigModel.php" -Force
Remove-Item -Path "src\Models\UserLogModel.php" -Force
Remove-Item -Path "src\Models\PasswordResetRequestModel.php" -Force

# Kiểm tra chỉ còn BaseModel và DatabaseModel
Get-ChildItem -Path "src\Models"
```

---

## 📊 PROGRESS TRACKING

### ✅ Completed:
- [x] Phân tích codebase
- [x] Tạo TODO list chi tiết
- [x] Xác định 13 Controllers cần di chuyển
- [x] Xác định 14 Models cần di chuyển
- [x] Xác định 4 module mới cần tạo

### 🔄 In Progress:
- [ ] PHASE 1: Chuẩn bị & Backup
- [ ] PHASE 2: Refactor Module Product
- [ ] PHASE 3: Refactor các module khác
- [ ] PHASE 4: Update Routes
- [ ] PHASE 5: Testing
- [ ] PHASE 6: Update Composer
- [ ] PHASE 7: Xóa file cũ

### ⏰ Estimated Timeline:
- **PHASE 1**: 0.5 giờ
- **PHASE 2 (Product)**: 6 giờ
- **PHASE 3 (Other modules)**: 10 giờ
- **PHASE 4 (Routes)**: 1 giờ
- **PHASE 5 (Testing)**: 3 giờ
- **PHASE 6 (Composer)**: 0.5 giờ
- **PHASE 7 (Cleanup)**: 1 giờ
- **TOTAL**: ~22 giờ (3 ngày làm việc)

---

## 📝 NOTES

### Lưu ý quan trọng:
1. **Test sau mỗi module** - Không refactor hết rồi mới test
2. **Commit thường xuyên** - Mỗi module xong là commit
3. **Backup trước khi xóa** - Luôn có bản backup
4. **Update routes ngay** - Sau khi di chuyển controller
5. **Run composer dump-autoload** - Sau mỗi lần đổi namespace

### Thứ tự ưu tiên:
1. **Product** (quan trọng nhất, phức tạp nhất)
2. **Category** (liên quan Product)
3. **Brand** (liên quan Product)
4. **User & Auth** (quan trọng cho security)
5. **Supplier** (liên quan Purchase)
6. **System** (Config, Logs)
7. **Dashboard** (ít phụ thuộc)

---

**Last updated**: 09/11/2025  
**Status**: 🔴 READY TO START
