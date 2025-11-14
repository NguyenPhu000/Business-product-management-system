# 🎨 Brand Logo Base64 - Quick Guide

## ✅ Đã Hoàn Thành

Hệ thống lưu trữ logo thương hiệu đã được chuyển đổi từ **file system** sang **Base64** trong database.

### 📊 Thống Kê

- ✅ **5/5 brands** đã có logo dạng Base64
- ✅ Database column: `logo_url` → **LONGTEXT**
- ✅ Format: **Data URI** (`data:image/jpeg;base64,...`)
- ✅ Average size: **~167 KB** per logo

## 🚀 Sử Dụng

### 1. Thêm Brand Mới Với Logo

```php
// Form HTML
<form method="POST" enctype="multipart/form-data" action="/admin/brands/store">
    <input type="text" name="name" required>
    <input type="file" name="logo_image" accept="image/*">
    <button type="submit">Lưu</button>
</form>
```

### 2. Hiển Thị Logo

```php
<?php if (!empty($brand['logo_url'])): ?>
    <img src="<?= htmlspecialchars($brand['logo_url']) ?>" 
         alt="<?= htmlspecialchars($brand['name']) ?>">
<?php endif; ?>
```

### 3. Cập Nhật Logo

Upload file mới qua form edit, hệ thống tự động:
- ✅ Convert sang Base64
- ✅ Validate file (max 5MB, JPG/PNG/GIF/WEBP)
- ✅ Lưu vào database
- ✅ Không cần xóa file cũ

## 📁 Files Quan Trọng

| File | Mô tả |
|------|-------|
| `src/modules/category/services/BrandService.php` | Logic xử lý upload & convert Base64 |
| `src/modules/category/models/BrandModel.php` | Database operations |
| `src/views/admin/brands/*.php` | Views hiển thị & upload |
| `scripts/convert_brand_logos_to_base64.php` | Script convert dữ liệu cũ |
| `scripts/migrate_brands_logo_to_longtext.php` | Migration database |
| `scripts/check_base64_logos.php` | Kiểm tra logo trong DB |
| `scripts/test_brand_base64.php` | Test toàn bộ chức năng |
| `docs/BRAND_LOGO_BASE64_MIGRATION.md` | Tài liệu chi tiết |

## 🔧 Maintenance Scripts

```powershell
# Kiểm tra logo trong database
php scripts/check_base64_logos.php

# Test toàn bộ chức năng
php scripts/test_brand_base64.php

# Convert logo mới (nếu cần)
php scripts/convert_brand_logos_to_base64.php
```

## ⚠️ Lưu Ý

1. **Upload Limits:**
   - Max file size: **5 MB**
   - Formats: JPG, PNG, GIF, WEBP
   - Auto validation & error handling

2. **Database:**
   - Column `logo_url`: **LONGTEXT** (max ~16MB)
   - Base64 tăng size ~33% so với file gốc
   - Nên optimize ảnh trước khi upload

3. **Performance:**
   - Logo được cache cùng data
   - Giảm HTTP requests
   - Không cần static file serving

## 📚 Tài Liệu Chi Tiết

Xem file: [`docs/BRAND_LOGO_BASE64_MIGRATION.md`](./BRAND_LOGO_BASE64_MIGRATION.md)

---

**Status:** ✅ Production Ready  
**Last Updated:** 10/11/2025
