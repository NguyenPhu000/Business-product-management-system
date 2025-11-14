# Hướng Dẫn Migration Logo Thương Hiệu Sang Base64

## 📋 Tổng Quan

Tài liệu này mô tả quá trình chuyển đổi lưu trữ logo thương hiệu từ **đường dẫn file** sang **Base64** trong database.

## ✅ Đã Hoàn Thành

### 1. Cập Nhật Cấu Trúc Database

**File:** `scripts/migrate_brands_logo_to_longtext.php`

```sql
ALTER TABLE brands 
MODIFY COLUMN logo_url LONGTEXT NULL 
COMMENT 'Logo thương hiệu (Base64 data URI)';
```

- ✅ Đã chạy thành công migration
- ✅ Cột `logo_url` đã được chuyển từ `VARCHAR(255)` sang `LONGTEXT`
- ✅ Có thể lưu trữ chuỗi base64 lớn (lên đến ~16MB)

### 2. Convert Dữ Liệu Cũ

**File:** `scripts/convert_brand_logos_to_base64.php`

✅ **Đã convert thành công 5 logo:**
1. Apple - `/assets/images/brands/brand_1762742340_691f5044489.png`
2. Xiaomi - `/assets/images/brands/brand_176176718Z_6902bd2eea8.png`
3. MSI - `/assets/images/brands/brand_1761789518_6902c64e4e.png`
4. Casio - `/assets/images/brands/brand_1761792819_6902d33356c.png`
5. Nokia - `/assets/images/brands/brand_1762742149_6911f8b57de.png`

**Kết quả:**
```
✅ Tất cả logo đã được convert sang base64 thành công!
📊 Thống kê:
   - Tổng số brands: 5
   - Số logo đã convert: 5
   - Tỷ lệ thành công: 100%
```

### 3. Cập Nhật Code

#### BrandService (`src/modules/category/services/BrandService.php`)

✅ **Method `handleLogoUpload()`** - Convert upload sang base64:
```php
private function handleLogoUpload(): ?string
{
    // Đọc file upload
    $imageData = file_get_contents($file['tmp_name']);
    
    // Convert sang base64 với data URI scheme
    $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    
    return $base64;
}
```

✅ **Validate upload:**
- Kích thước tối đa: 5MB
- Định dạng hỗ trợ: JPG, PNG, GIF, WEBP
- Auto convert sang base64 khi upload

✅ **Method `updateBrand()`:**
- Giữ logo cũ nếu không upload mới
- Không cần xóa file cũ (vì đã là base64)

✅ **Method `deleteBrand()`:**
- Không cần xóa file logo (vì đã là base64)

#### Views

✅ **`src/views/admin/brands/index.php`:**
```php
<img src="<?= htmlspecialchars($brand['logo_url']) ?>" 
     alt="<?= htmlspecialchars($brand['name']) ?>" 
     class="brand-logo img-thumbnail"
     style="max-width: 80px; max-height: 80px; object-fit: contain;">
```

✅ **`src/views/admin/brands/create.php`:**
- Input file upload với preview
- Validation JavaScript cho kích thước và định dạng
- Preview real-time khi chọn file

✅ **`src/views/admin/brands/edit.php`:**
- Hiển thị logo hiện tại (base64)
- Upload logo mới để thay thế
- Preview logo mới trước khi lưu

## 🎯 Ưu Điểm Của Base64

### 1. **Tính Di Động**
- ✅ Không phụ thuộc vào file system
- ✅ Dễ dàng backup/restore cùng database
- ✅ Không lo mất file khi di chuyển server

### 2. **Quản Lý Đơn Giản**
- ✅ Không cần quản lý thư mục uploads
- ✅ Không cần xóa file khi xóa brand
- ✅ Transactions database đầy đủ

### 3. **Bảo Mật**
- ✅ Không lo lộ đường dẫn file
- ✅ Không thể truy cập trực tiếp qua URL
- ✅ Kiểm soát quyền truy cập qua database

### 4. **Performance**
- ✅ Giảm số request HTTP (embedded trong HTML)
- ✅ Không cần cấu hình static file serving
- ✅ Cache cùng với data

## ⚠️ Lưu Ý

### 1. **Kích Thước Database**
- Base64 làm tăng kích thước ~33% so với file gốc
- File 100KB → Base64 ~133KB
- Cần monitor disk space của database

### 2. **Giới Hạn Upload**
- Tối đa 5MB per logo
- Khuyến nghị: optimize ảnh trước khi upload
- Nên dùng PNG/JPG với quality 80-90%

### 3. **Performance**
- SELECT * sẽ load cả base64 (lớn)
- Nên SELECT theo column cần thiết
- Có thể tách logo ra table riêng nếu cần optimize

### 4. **Compatibility**
- ✅ Tất cả browser hiện đại đều hỗ trợ data URI
- ✅ Không giới hạn độ dài trong LONGTEXT
- ✅ MySQL/MariaDB hỗ trợ tốt

## 🔧 Maintenance

### Kiểm Tra Dữ Liệu

```sql
-- Xem số brands có logo
SELECT COUNT(*) as total_with_logo
FROM brands 
WHERE logo_url IS NOT NULL;

-- Xem kích thước logo
SELECT 
    id,
    name,
    LENGTH(logo_url) as logo_size_bytes,
    ROUND(LENGTH(logo_url) / 1024, 2) as logo_size_kb
FROM brands 
WHERE logo_url IS NOT NULL;

-- Xem brands không có logo
SELECT id, name 
FROM brands 
WHERE logo_url IS NULL 
ORDER BY name;
```

### Backup Logo

```sql
-- Export brands với logo
SELECT * FROM brands INTO OUTFILE '/tmp/brands_backup.csv'
FIELDS TERMINATED BY ',' 
ENCLOSED BY '"'
LINES TERMINATED BY '\n';
```

### Rollback (Nếu Cần)

Nếu cần quay lại cách cũ (lưu file):

1. Tạo script extract base64 → file
2. Update database với đường dẫn file mới
3. Sửa lại BrandService để lưu file thay vì base64

## 📊 Thống Kê Hiện Tại

- **Tổng brands:** 5
- **Brands có logo:** 5 (100%)
- **Logo format:** Base64 Data URI
- **Average logo size:** ~50-150KB (base64)
- **Database impact:** ~500KB tổng cho 5 logos

## 🚀 Sử Dụng

### 1. Upload Logo Mới

```php
// Form HTML
<input type="file" name="logo_image" accept="image/*">

// Backend tự động convert sang base64
$brandId = $brandService->createBrand([
    'name' => 'Apple',
    'description' => 'Tech company',
    'is_active' => 1
]);
// $_FILES['logo_image'] sẽ được xử lý tự động
```

### 2. Hiển Thị Logo

```php
// Trong view
<?php if ($brand['logo_url']): ?>
    <img src="<?= htmlspecialchars($brand['logo_url']) ?>" 
         alt="<?= htmlspecialchars($brand['name']) ?>">
<?php endif; ?>
```

### 3. Update Logo

```php
// Upload file mới qua form
// BrandService sẽ tự convert và update

$brandService->updateBrand($id, [
    'name' => 'New Name',
    'description' => 'New desc'
]);
// Nếu có $_FILES['logo_image'], logo sẽ được update
```

## ✅ Checklist Migration

- [x] Backup database
- [x] Tạo migration script
- [x] Chạy ALTER TABLE
- [x] Convert dữ liệu cũ sang base64
- [x] Cập nhật BrandService
- [x] Test upload mới
- [x] Test update
- [x] Test delete
- [x] Kiểm tra hiển thị trên views
- [x] Tạo tài liệu hướng dẫn

## 📝 Tài Liệu Tham Khảo

- [Data URI Scheme](https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/Data_URIs)
- [MySQL LONGTEXT](https://dev.mysql.com/doc/refman/8.0/en/blob.html)
- [Base64 Encoding](https://en.wikipedia.org/wiki/Base64)

---

**Ngày tạo:** 10/11/2025  
**Người thực hiện:** GitHub Copilot  
**Trạng thái:** ✅ Hoàn thành
