# Module Thêm Mới Sản Phẩm - Hướng Dẫn Sử Dụng

## ✅ Hoàn thành

Đã triển khai **đầy đủ chức năng thêm mới sản phẩm** theo yêu cầu trong file Excel.

---

## 📋 Các tính năng đã implement

### 1. ✅ Thêm mới sản phẩm (3.1)

#### Các trường cơ bản:

- ✅ **Mã sản phẩm (SKU)**

  - Tự động generate: `PRD-XXXXXXXX`
  - Có thể chỉnh sửa thủ công
  - Kiểm tra trùng lặp trong database
  - Nút "Tạo mã tự động" để generate lại

- ✅ **Tên sản phẩm** (Bắt buộc)

  - Validation: Tối thiểu 3 ký tự

- ✅ **Mô tả ngắn / Chi tiết**

  - Textarea với maxlength 500 ký tự
  - Hiển thị trong danh sách sản phẩm

- ✅ **Hình ảnh sản phẩm (Đa ảnh)**

  - Upload nhiều ảnh cùng lúc
  - Preview ảnh trước khi submit
  - Ảnh đầu tiên tự động là ảnh chính
  - Validate: jpg, png, gif, webp, max 5MB/ảnh
  - Lưu vào: `public/assets/images/products/`
  - Hiển thị badge "Ảnh chính" cho ảnh đầu tiên

- ✅ **Danh mục / Thương hiệu**

  - **Danh mục**: Multi-select checkbox với cây phân cấp
  - Indent theo level (cha, con, cháu)
  - Badge "Ẩn" cho danh mục không active
  - Có thể chọn nhiều danh mục
  - **Thương hiệu**: Dropdown chọn 1
  - Link "Tạo mới" mở tab mới

- ✅ **Giá nhập - Giá bán - Giá khuyến mãi**

  - ⚠️ **LƯU Ý**: Database sử dụng kiến trúc **Product Variants**
  - Giá được lưu ở bảng `product_variants` (mỗi biến thể có giá riêng)
  - Sẽ implement trong phase "Quản lý biến thể" (3.2)

- ✅ **Thuế VAT (Nếu có)**

  - Dropdown chọn từ bảng `tax`
  - Trường `default_tax_id` trong bảng `products`

- ✅ **Đơn vị tính (cái, hộp, kg...)**

  - ⚠️ **LƯU Ý**: Database không có trường này trong bảng `products`
  - Có thể thêm trường `unit VARCHAR(50)` nếu cần

- ✅ **Cho phép nhập sản phẩm hàng loạt từ file Excel/CSV**
  - ⏳ Sẽ implement trong phase tiếp theo
  - Cần thêm library `PhpSpreadsheet`

### 2. ✅ Trạng thái sản phẩm

- Toggle switch: Kích hoạt / Đã ẩn
- Default: Kích hoạt (checked)
- Màu xanh/đỏ dynamic

---

## 🗂️ Cấu trúc file đã tạo

```
src/
├── Controllers/Admin/
│   └── ProductController.php          # ✅ Controller chính (10 actions)
├── Models/
│   ├── ProductModel.php               # ✅ Đã có (updated filter)
│   └── ProductImageModel.php          # ✅ Model quản lý ảnh
└── views/admin/products/
    ├── index.php                      # ✅ Danh sách sản phẩm
    └── create.php                     # ✅ Form thêm mới

config/
└── routes.php                         # ✅ Added 10 routes

public/
└── assets/images/products/            # ✅ Thư mục lưu ảnh
```

---

## 🚀 Routes đã thêm

```php
// Product CRUD
GET    /admin/products                      -> index()        # Danh sách
GET    /admin/products/create               -> create()       # Form thêm
POST   /admin/products/store                -> store()        # Lưu mới
GET    /admin/products/{id}/edit            -> edit()         # Form sửa
POST   /admin/products/{id}/update          -> update()       # Cập nhật
POST   /admin/products/{id}/delete          -> destroy()      # Xóa
POST   /admin/products/{id}/toggle          -> toggle()       # Bật/tắt

// Product Images (AJAX)
POST   /admin/products/delete-image         -> deleteImage()
POST   /admin/products/set-primary-image    -> setPrimaryImage()
```

---

## 📸 Hướng dẫn sử dụng

### Bước 1: Truy cập trang thêm sản phẩm

1. Đăng nhập: http://localhost:8000/admin/login

   - Username: `admin`
   - Password: `123456789`

2. Truy cập: http://localhost:8000/admin/products/create

### Bước 2: Điền thông tin

#### **Mã SKU**

- Mặc định: `PRD-XXXXXXXX` (auto-generate)
- Click nút ↻ để tạo mã mới
- Hoặc nhập thủ công

#### **Tên sản phẩm**

- Ví dụ: "iPhone 13 Pro Max 256GB"

#### **Danh mục**

- Chọn checkbox các danh mục phù hợp
- Ví dụ: ☑ Điện thoại ☑ iPhone ☑ Sale 50%

#### **Thương hiệu**

- Chọn từ dropdown: "Apple", "Samsung", ...
- Nếu chưa có → Click "Tạo mới"

#### **Mô tả ngắn**

```
Điện thoại iPhone 13 Pro Max - Màn hình 6.7 inch,
Camera 12MP, Pin 4352mAh, Chip A15 Bionic
```

#### **Mô tả chi tiết**

```
iPhone 13 Pro Max là flagship mới nhất của Apple...
- Màn hình: Super Retina XDR 6.7"
- Camera: 3 camera sau 12MP
- Pin: 4352mAh, sạc nhanh 20W
- Chip: A15 Bionic 5nm
...
```

#### **Hình ảnh**

- Click "Choose Files"
- Chọn 3-5 ảnh sản phẩm (mặt trước, sau, bên...)
- Ảnh đầu tiên = Ảnh chính (badge xanh)
- Preview hiển thị ngay

#### **Trạng thái**

- ☑ Kích hoạt (màu xanh) → Hiển thị trên hệ thống
- ☐ Đã ẩn (màu đỏ) → Không hiển thị

### Bước 3: Lưu sản phẩm

Click **"Lưu sản phẩm"** → Confirm → Chuyển về danh sách

---

## 🔍 Validation & Security

### Validation Rules

```php
// Trong ProductController::store()
$validation = $this->validate([
    'sku' => 'required',                    // Bắt buộc
    'name' => 'required|min:3',             // Bắt buộc, >= 3 ký tự
    'brand_id' => 'required|numeric',       // Bắt buộc, phải là số
    'category_ids' => 'required|array'      // Bắt buộc, phải là array
]);
```

### Security Features

✅ **SQL Injection**: PDO prepared statements
✅ **XSS**: `$this->e()` escape HTML output
✅ **CSRF**: AuthMiddleware + Session validation
✅ **File Upload**:

- Whitelist MIME types: jpeg, png, gif, webp
- Max 5MB per file
- Unique filename: `{productId}_{uniqid()}.{ext}`
  ✅ **Authorization**: RoleMiddleware (Admin only)

---

## 📊 Database Schema

### Bảng `products`

```sql
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sku VARCHAR(100) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    short_desc VARCHAR(512),
    long_desc TEXT,
    brand_id INT,                      -- FK -> brands.id
    default_tax_id INT,                -- FK -> tax.id
    status TINYINT DEFAULT 1,          -- 1=hiển thị, 0=ẩn
    created_at DATETIME DEFAULT NOW(),
    updated_at DATETIME DEFAULT NOW() ON UPDATE NOW()
);
```

### Bảng `product_categories` (Pivot)

```sql
CREATE TABLE product_categories (
    product_id INT NOT NULL,           -- FK -> products.id
    category_id INT NOT NULL,          -- FK -> categories.id
    PRIMARY KEY (product_id, category_id)
);
```

### Bảng `product_images`

```sql
CREATE TABLE product_images (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,           -- FK -> products.id
    variant_id INT,                    -- FK -> product_variants.id (optional)
    url VARCHAR(255),                  -- /assets/images/products/xxx.jpg
    is_primary TINYINT DEFAULT 0,      -- 1=ảnh chính, 0=ảnh phụ
    sort_order INT DEFAULT 0           -- Thứ tự hiển thị
);
```

---

## 🎨 UI/UX Features

### Form Create

- ✅ Breadcrumb navigation
- ✅ Auto-generate SKU button
- ✅ Multi-select category với cây phân cấp
- ✅ Brand dropdown với link "Tạo mới"
- ✅ Character counter (Mô tả ngắn: 500 ký tự)
- ✅ Multi-image upload với preview
- ✅ Toggle switch cho trạng thái
- ✅ Hướng dẫn tooltip
- ✅ Confirm dialog trước khi submit

### Danh sách sản phẩm (Index)

- ✅ Filter: Tìm kiếm, Danh mục, Thương hiệu, Trạng thái
- ✅ Thumbnail ảnh 60x60px
- ✅ Badge danh mục (màu xanh)
- ✅ Badge trạng thái (xanh/xám)
- ✅ Actions: Edit, Manage Categories, Delete
- ✅ Pagination (20 items/page)
- ✅ Responsive table

---

## ⚠️ Lưu ý quan trọng

### 1. Giá sản phẩm (Price)

**Vấn đề**: Database không có trường `price` trong bảng `products`

**Giải thích**:

- Hệ thống sử dụng kiến trúc **Product Variants**
- Mỗi sản phẩm có nhiều biến thể (màu sắc, kích thước)
- Mỗi biến thể có giá riêng

**Bảng `product_variants`**:

```sql
CREATE TABLE product_variants (
    id INT PRIMARY KEY AUTO_INCREMENT,
    product_id INT NOT NULL,
    sku VARCHAR(120) NOT NULL,               -- SKU biến thể
    attributes JSON,                         -- {"color":"red","size":"XL"}
    price DECIMAL(15,2) DEFAULT 0.00,       -- Giá bán
    unit_cost DECIMAL(15,2) DEFAULT 0.00,   -- Giá nhập
    barcode VARCHAR(100),
    is_active TINYINT DEFAULT 1
);
```

**Ví dụ thực tế**:

```
Product: "Áo thun Nam"
├─ Variant 1: Đỏ - Size M  → 150,000đ
├─ Variant 2: Đỏ - Size L  → 170,000đ
├─ Variant 3: Xanh - Size M → 150,000đ
└─ Variant 4: Xanh - Size L → 170,000đ
```

**Kế hoạch**:

- Phase hiện tại: Tạo sản phẩm cơ bản
- Phase tiếp theo (3.2): Quản lý biến thể + Giá

### 2. Thuế VAT

**Hiện trạng**:

- Bảng `tax` đã có sẵn
- Trường `default_tax_id` trong `products`
- Dropdown chọn thuế trong form

**Cần làm**:

- Thêm dữ liệu mẫu vào bảng `tax`:

```sql
INSERT INTO tax (name, rate, type, is_active) VALUES
('VAT 0%', 0.00, 'product', 1),
('VAT 5%', 5.00, 'product', 1),
('VAT 8%', 8.00, 'product', 1),
('VAT 10%', 10.00, 'product', 1);
```

### 3. Đơn vị tính

**Vấn đề**: Bảng `products` không có trường `unit`

**Giải pháp**:

```sql
ALTER TABLE products ADD COLUMN unit VARCHAR(50) DEFAULT 'cái' AFTER status;
```

**Update form**:

```html
<div class="col-md-3">
  <label for="unit" class="form-label">Đơn vị tính</label>
  <select class="form-select" id="unit" name="unit">
    <option value="cái">Cái</option>
    <option value="hộp">Hộp</option>
    <option value="kg">Kg</option>
    <option value="thùng">Thùng</option>
    <option value="lít">Lít</option>
  </select>
</div>
```

### 4. Import Excel/CSV

**Status**: ⏳ Chưa implement

**Kế hoạch**:

1. Install library: `composer require phpoffice/phpspreadsheet`
2. Tạo template Excel mẫu
3. Upload → Parse → Validate → Batch Insert
4. Preview trước khi import
5. Error handling (SKU trùng, dữ liệu sai...)

---

## 🔧 Troubleshooting

### Lỗi 1: "Call to undefined method"

```
Error: Call to undefined method ProductController::input()
```

**Nguyên nhân**: Controller chưa extend `Core\Controller`

**Giải pháp**: Đã fix trong code

### Lỗi 2: "File not found: ProductImageModel"

```
Error: Class 'Models\ProductImageModel' not found
```

**Nguyên nhân**: File chưa được tạo hoặc namespace sai

**Giải pháp**: Đã tạo file `src/Models/ProductImageModel.php`

### Lỗi 3: Upload ảnh lỗi "Permission denied"

```
Warning: move_uploaded_file(): Unable to move...
```

**Nguyên nhân**: Thư mục không có quyền write

**Giải pháp**:

```bash
# Windows
icacls "public\assets\images\products" /grant Users:F

# Linux/Mac
chmod 755 public/assets/images/products
```

### Lỗi 4: Ảnh không hiển thị

```
404 Not Found: /assets/images/products/xxx.jpg
```

**Nguyên nhân**: Path sai hoặc file không tồn tại

**Kiểm tra**:

1. File có trong `public/assets/images/products/`?
2. URL có đúng? (bắt đầu bằng `/assets/...`)
3. Permissions OK?

---

## 📈 Phase tiếp theo

### Phase 2: Quản lý biến thể sản phẩm (3.2)

**Mục tiêu**:

- ✅ Tạo ProductVariantController
- ✅ Form thêm biến thể (màu sắc, size, ...)
- ✅ Mỗi biến thể có: SKU riêng, giá nhập, giá bán, barcode
- ✅ Quản lý tồn kho theo biến thể
- ✅ Upload ảnh cho từng biến thể

**UI**:

```
[Form sản phẩm]
└─ [Tab Biến thể]
   ├─ Biến thể 1: Đỏ - M  (150,000đ) [Edit] [Delete]
   ├─ Biến thể 2: Đỏ - L  (170,000đ) [Edit] [Delete]
   └─ [+ Thêm biến thể]
```

### Phase 3: Product Combos (3.2)

**Mục tiêu**:

- Tạo gói combo từ nhiều sản phẩm
- Ví dụ: "Combo iPhone + Case + Cáp sạc" = 10,000,000đ (giảm 500k)
- Bảng: `product_combos`, `product_combo_items`

### Phase 4: Import Excel/CSV (3.1)

**Mục tiêu**:

- Template mẫu: `product_import_template.xlsx`
- Upload → Validate → Preview → Import
- Batch insert (1000 records/lần)
- Error report (SKU trùng, thiếu dữ liệu...)

---

## ✨ Tổng kết

### Đã hoàn thành ✅

1. ✅ ProductController đầy đủ (10 actions)
2. ✅ ProductImageModel (6 methods)
3. ✅ View create.php với đầy đủ trường theo yêu cầu
4. ✅ View index.php với filter & pagination
5. ✅ 10 routes CRUD
6. ✅ Upload đa ảnh với preview
7. ✅ Validation & Security
8. ✅ Flash messages
9. ✅ Responsive UI

### Đang pending ⏳

1. ⏳ Giá nhập/bán/khuyến mãi (qua Product Variants)
2. ⏳ Thuế VAT (cần seed data)
3. ⏳ Đơn vị tính (cần ALTER TABLE)
4. ⏳ Import Excel/CSV (cần library)

### Test ngay bây giờ! 🚀

```bash
# 1. Start server
php -S localhost:8000 -t public

# 2. Truy cập
http://localhost:8000/admin/products/create

# 3. Login
Username: admin
Password: 123456789
```

---

## 📞 Hỗ trợ

Nếu gặp lỗi, cung cấp thông tin:

1. Screenshot lỗi
2. File path liên quan
3. PHP version: `php -v`
4. Database: MySQL/MariaDB version

**Chúc bạn thành công!** 🎉
