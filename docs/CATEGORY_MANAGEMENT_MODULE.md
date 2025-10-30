# Module Quản lý Danh mục Sản phẩm

## 📋 Tổng quan

Module quản lý danh mục sản phẩm bao gồm 3 phần chính:
1. **Categories** - Quản lý danh mục sản phẩm (cấu trúc cây phân cấp)
2. **Brands** - Quản lý thương hiệu
3. **Suppliers** - Quản lý nhà cung cấp

## 🏗️ Kiến trúc

### Mô hình MVC
```
src/
├── Models/
│   ├── CategoryModel.php      # Model danh mục
│   ├── BrandModel.php          # Model thương hiệu
│   └── SupplierModel.php       # Model nhà cung cấp
├── Controllers/Admin/
│   ├── CategoryController.php  # Controller danh mục
│   ├── BrandController.php     # Controller thương hiệu
│   └── SupplierController.php  # Controller nhà cung cấp
└── views/admin/
    ├── categories/
    │   ├── index.php           # Danh sách danh mục
    │   ├── create.php          # Thêm danh mục
    │   └── edit.php            # Sửa danh mục
    ├── brands/
    │   ├── index.php
    │   ├── create.php
    │   └── edit.php
    └── suppliers/
        ├── index.php
        ├── create.php
        ├── edit.php
        └── detail.php

public/assets/css/
├── category-style.css          # CSS riêng cho danh mục
├── brand-style.css             # CSS riêng cho thương hiệu
└── supplier-style.css          # CSS riêng cho nhà cung cấp
```

## 🗄️ Database Schema

### Bảng `categories`
```sql
- id (PK)
- name (tên danh mục)
- slug (URL thân thiện)
- parent_id (FK → categories.id) - Danh mục cha
- is_active (trạng thái hiển thị)
- sort_order (thứ tự sắp xếp)
```

**Tính năng:**
- Hỗ trợ cấu trúc cây phân cấp cha-con (parent-child)
- Tự động generate slug từ tên
- Xóa dấu tiếng Việt
- Kiểm tra không được chọn chính nó/danh mục con làm cha

### Bảng `brands`
```sql
- id (PK)
- name (tên thương hiệu)
- description (mô tả)
- logo_url (đường dẫn logo)
- is_active (trạng thái)
```

**Tính năng:**
- Quản lý logo thương hiệu
- Đếm số lượng sản phẩm theo thương hiệu
- Kiểm tra không cho xóa nếu còn sản phẩm

### Bảng `suppliers`
```sql
- id (PK)
- name (tên nhà cung cấp)
- contact (người liên hệ)
- phone (số điện thoại)
- email (email)
- address (địa chỉ)
- is_active (trạng thái)
```

**Tính năng:**
- Validate email, phone
- Kiểm tra trùng lặp email/phone
- Lịch sử đơn hàng
- Thống kê tổng giá trị

### Bảng `product_categories` (Bảng trung gian)
```sql
- product_id (FK → products.id)
- category_id (FK → categories.id)
- PK: (product_id, category_id)
```

**Mục đích:** Một sản phẩm có thể thuộc nhiều danh mục

## 🔌 Kết nối PDO

### Core\Database.php
```php
// Singleton Pattern
$db = Database::getInstance();
$connection = $db->getConnection();

// Query methods
$db->query($sql, $params);      // SELECT nhiều rows
$db->queryOne($sql, $params);   // SELECT 1 row
$db->execute($sql, $params);    // INSERT/UPDATE/DELETE
$db->insert($sql, $params);     // INSERT và trả về ID

// Transaction
$db->beginTransaction();
$db->commit();
$db->rollback();
```

## 📡 API Routes

### Categories
```
GET    /admin/categories                  # Danh sách
GET    /admin/categories/create           # Form thêm
POST   /admin/categories/store            # Lưu mới
GET    /admin/categories/edit/{id}        # Form sửa
POST   /admin/categories/update/{id}      # Cập nhật
POST   /admin/categories/delete/{id}      # Xóa
POST   /admin/categories/toggle-active/{id} # Bật/tắt
```

### Brands
```
GET    /admin/brands                      # Danh sách
GET    /admin/brands/create               # Form thêm
POST   /admin/brands/store                # Lưu mới
GET    /admin/brands/edit/{id}            # Form sửa
POST   /admin/brands/update/{id}          # Cập nhật
POST   /admin/brands/delete/{id}          # Xóa
POST   /admin/brands/toggle-active/{id}   # Bật/tắt
```

### Suppliers
```
GET    /admin/suppliers                   # Danh sách
GET    /admin/suppliers/create            # Form thêm
POST   /admin/suppliers/store             # Lưu mới
GET    /admin/suppliers/edit/{id}         # Form sửa
POST   /admin/suppliers/update/{id}       # Cập nhật
POST   /admin/suppliers/delete/{id}       # Xóa
GET    /admin/suppliers/detail/{id}       # Chi tiết
POST   /admin/suppliers/toggle-active/{id} # Bật/tắt
```

## 🎨 Giao diện

### Category Index
- Hiển thị cây danh mục (tree view)
- Hiển thị bảng danh sách (table view)
- Badge trạng thái (active/inactive)
- Thao tác: Sửa, Xóa

### Category Create/Edit
- Form nhập tên danh mục
- Tự động generate slug
- Chọn danh mục cha (dropdown)
- Thứ tự sắp xếp
- Toggle trạng thái

### CSS tách riêng
- `category-style.css` - Style cây danh mục
- `brand-style.css` - Style thương hiệu
- `supplier-style.css` - Style nhà cung cấp

## ✅ Validation

### Category
- Tên danh mục: bắt buộc
- Slug: unique, auto-generate
- Parent: không được chọn chính nó/danh mục con

### Brand
- Tên thương hiệu: bắt buộc, unique
- Logo: optional (URL)

### Supplier
- Tên: bắt buộc
- Email: validate format, unique
- Phone: unique
- Contact, Address: optional

## 🔒 Bảo mật

- **Authentication**: Yêu cầu đăng nhập (AuthMiddleware)
- **Authorization**: Chỉ Admin (RoleMiddleware)
- **CSRF Protection**: TODO
- **SQL Injection**: Sử dụng Prepared Statements (PDO)
- **XSS Protection**: htmlspecialchars() cho output

## 📊 Logging

Tất cả thao tác CRUD được ghi log:
```php
LogHelper::log('create', 'category', $id, $data);
LogHelper::log('update', 'brand', $id, $data);
LogHelper::log('delete', 'supplier', $id, $data);
```

## 🚀 Cách sử dụng

### 1. Import Database
```bash
mysql -u root -p business_product_management_system < business_product_management_system.sql
```

### 2. Cấu hình Database
File: `config/database.php`
```php
'host' => 'localhost',
'database' => 'business_product_management_system',
'username' => 'root',
'password' => '',
```

### 3. Truy cập
```
http://localhost/admin/categories
http://localhost/admin/brands
http://localhost/admin/suppliers
```

### 4. Đăng nhập
- **Username**: admin
- **Email**: mnminh-cntt17@tdu.edu.vn
- **Password**: (xem trong database)

## 📝 Models Methods

### CategoryModel
```php
getAllWithParent()              # Lấy tất cả + tên parent
getParentCategories()           # Lấy danh mục gốc
getChildren($parentId)          # Lấy danh mục con
getCategoryTree()               # Lấy cây đầy đủ
getBreadcrumb($categoryId)      # Lấy đường dẫn
generateSlug($name)             # Tạo slug
slugExists($slug, $excludeId)   # Kiểm tra slug
canDelete($id)                  # Kiểm tra có thể xóa
```

### BrandModel
```php
getAllWithProductCount()        # Lấy tất cả + số SP
nameExists($name, $excludeId)   # Kiểm tra tên
getActiveBrands()               # Lấy brand active
canDelete($id)                  # Kiểm tra có thể xóa
toggleActive($id)               # Bật/tắt
search($keyword)                # Tìm kiếm
```

### SupplierModel
```php
getAllWithOrderCount()          # Lấy tất cả + số ĐH
emailExists($email, $excludeId) # Kiểm tra email
phoneExists($phone, $excludeId) # Kiểm tra phone
getActiveSuppliers()            # Lấy NCC active
getOrderHistory($id, $limit)    # Lịch sử ĐH
getTotalOrderValue($id)         # Tổng giá trị
search($keyword)                # Tìm kiếm
```

## 🧪 Testing

TODO: Viết unit tests với PHPUnit

## 📚 Tài liệu tham khảo

- [PSR-4 Autoloading](https://www.php-fig.org/psr/psr-4/)
- [PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
- [Bootstrap 5 Docs](https://getbootstrap.com/docs/5.0/)

## 👥 Đóng góp

1. Fork repository
2. Tạo branch mới: `git checkout -b feature/category-management`
3. Commit: `git commit -m 'Add category tree view'`
4. Push: `git push origin feature/category-management`
5. Tạo Pull Request

## 📄 License

MIT License - Xem file LICENSE

---

**Ngày tạo:** 28/10/2025  
**Phiên bản:** 1.0.0  
**Branch:** thanhbao
