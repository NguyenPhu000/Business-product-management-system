# Tài liệu: Bắt buộc nhập số điện thoại cho Nhà cung cấp

**Ngày tạo:** 10/11/2025  
**Người thực hiện:** GitHub Copilot

## 🎯 Mục đích

Sửa logic validation cho trường **Số điện thoại** trong module Nhà cung cấp:
- ✅ **Bắt buộc phải nhập** số điện thoại (không được để trống)
- ✅ Hiển thị **tooltip/alert đẹp** khi người dùng:
  - Để trống số điện thoại
  - Nhập sai format (chứa chữ, ký tự đặc biệt)

## 📝 Các thay đổi

### 1. Backend - SupplierService.php

**File:** `src/modules/category/services/SupplierService.php`

#### Hàm `createSupplier()`
```php
// Kiểm tra phone bắt buộc và hợp lệ
$phone = !empty($data['phone']) ? trim($data['phone']) : '';

// Số điện thoại là bắt buộc
if (empty($phone)) {
    throw new Exception('Số điện thoại không được để trống');
}

// Chỉ chấp nhận chữ số với tùy chọn + ở đầu, độ dài 7-15 chữ số
if (!preg_match('/^\+?\d{7,15}$/', $phone)) {
    throw new Exception('Số điện thoại không hợp lệ. Chỉ chứa chữ số và có thể bắt đầu bằng dấu +, độ dài 7-15 ký tự.');
}

if ($this->supplierModel->phoneExists($phone)) {
    throw new Exception('Số điện thoại đã tồn tại');
}
```

#### Hàm `updateSupplier()`
```php
// Kiểm tra phone bắt buộc, hợp lệ và trùng lặp
$phone = !empty($data['phone']) ? trim($data['phone']) : '';

// Số điện thoại là bắt buộc
if (empty($phone)) {
    throw new Exception('Số điện thoại không được để trống');
}

// Chỉ chấp nhận chữ số với tùy chọn + ở đầu, độ dài 7-15 chữ số
if (!preg_match('/^\+?\d{7,15}$/', $phone)) {
    throw new Exception('Số điện thoại không hợp lệ. Chỉ chứa chữ số và có thể bắt đầu bằng dấu +, độ dài 7-15 ký tự.');
}

if ($this->supplierModel->phoneExists($phone, $id)) {
    throw new Exception('Số điện thoại đã tồn tại');
}
```

**Lưu ý:**
- Thay đổi từ `$phone ?: null` thành `$phone` vì phone là bắt buộc
- Kiểm tra rỗng trước khi kiểm tra format

### 2. Frontend - create.php

**File:** `src/views/admin/suppliers/create.php`

#### Thêm `required` vào input
```html
<div class="mb-3">
    <label for="phone" class="form-label">
        Số điện thoại <span class="text-danger">*</span>
    </label>
    <input type="tel" class="form-control" id="phone" name="phone" required>
</div>
```

#### Cập nhật JavaScript validation
```javascript
const phoneValidator = (inputEl) => {
    const phone = (inputEl.value || '').trim();
    // Số điện thoại là bắt buộc
    if (phone === '') {
        showInlineTooltip('Số điện thoại không được để trống', inputEl);
        return false;
    }
    // Kiểm tra format
    const re = /^\+?\d{7,15}$/;
    if (!re.test(phone)) {
        showInlineTooltip('Số điện thoại không hợp lệ. Chỉ chứa chữ số và có thể bắt đầu bằng dấu +, độ dài 7-15 ký tự.', inputEl);
        return false;
    }
    return true;
};
```

**Thay đổi:**
- Xóa `if (phone === '') return true;` (trước đây cho phép để trống)
- Thêm kiểm tra rỗng và hiển thị tooltip

### 3. Frontend - edit.php

**File:** `src/views/admin/suppliers/edit.php`

#### Thêm `required` vào input
```html
<div class="mb-3">
    <label for="phone" class="form-label">
        Số điện thoại <span class="text-danger">*</span>
    </label>
    <input type="tel" class="form-control" id="phone" name="phone"
           value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>" required>
</div>
```

#### Cập nhật JavaScript validation
```javascript
const editForm = document.querySelector('form[action^="/admin/suppliers/update/"]');
if (editForm) {
    editForm.addEventListener('submit', function(e) {
        const phoneEl = document.getElementById('phone');
        const phone = phoneEl.value.trim();
        
        // Số điện thoại là bắt buộc
        if (phone === '') {
            e.preventDefault();
            showInlineTooltip('Số điện thoại không được để trống', phoneEl);
            phoneEl.focus();
            return false;
        }
        
        // Kiểm tra format
        const re = /^\+?\d{7,15}$/;
        if (!re.test(phone)) {
            e.preventDefault();
            showInlineTooltip('Số điện thoại không hợp lệ. Chỉ chứa chữ số và có thể bắt đầu bằng dấu +, độ dài 7-15 ký tự.', phoneEl);
            phoneEl.focus();
            return false;
        }
    });
    
    // Validation khi blur (rời khỏi input)
    const phoneEl = document.getElementById('phone');
    if (phoneEl) {
        phoneEl.addEventListener('blur', function() {
            const phone = phoneEl.value.trim();
            
            if (phone === '') {
                showInlineTooltip('Số điện thoại không được để trống', phoneEl);
                return;
            }
            
            const re = /^\+?\d{7,15}$/;
            if (!re.test(phone)) {
                showInlineTooltip('Số điện thoại không hợp lệ. Chỉ chứa chữ số và có thể bắt đầu bằng dấu +, độ dài 7-15 ký tự.', phoneEl);
            }
        });
    }
}
```

**Thay đổi:**
- Thêm kiểm tra rỗng trong event submit
- Thêm validation khi blur để phản hồi ngay lập tức

## 🧪 Testing

### Script test tự động
**File:** `scripts/test_phone_required.php`

Kết quả test:
```
✅ TEST 1: Để trống số điện thoại - Bắt lỗi thành công
✅ TEST 2: Không gửi key phone - Bắt lỗi thành công  
✅ TEST 3: Nhập số hợp lệ - Tạo thành công (hoặc báo trùng nếu đã tồn tại)
✅ TEST 4: Nhập số có chữ - Bắt lỗi thành công
✅ TEST 5: Nhập số có ký tự đặc biệt - Bắt lỗi thành công
✅ TEST 6: Nhập số quá ngắn - Bắt lỗi thành công
```

### Test thủ công

1. **Khởi động dev server:**
   ```bash
   php -S localhost:8000 -t public
   ```

2. **Truy cập trang tạo nhà cung cấp:**
   ```
   http://localhost:8000/admin/suppliers/create
   ```

3. **Các trường hợp test:**

   | Hành động | Kết quả mong đợi |
   |-----------|------------------|
   | Để trống số điện thoại và blur | Hiện tooltip "Số điện thoại không được để trống" |
   | Để trống và submit form | Hiện tooltip "Số điện thoại không được để trống" |
   | Nhập `abc123` | Hiện tooltip "Số điện thoại không hợp lệ..." |
   | Nhập `090-123-4567` | Hiện tooltip "Số điện thoại không hợp lệ..." |
   | Nhập `0901234567` | ✅ Không có lỗi, form submit thành công |
   | Nhập `+84901234567` | ✅ Không có lỗi, form submit thành công |

## 🎨 UI/UX

### Tooltip hiển thị khi validation lỗi

**Đặc điểm:**
- ✅ Hiển thị bên trên input (hoặc bên phải nếu không đủ không gian)
- ✅ Màu trắng với viền mờ, icon cam (orange `#ff7a00`)
- ✅ Mũi tên chỉ xuống (hoặc sang trái) tùy vị trí
- ✅ Tự động ẩn sau 6 giây hoặc khi focus vào input
- ✅ Có nút đóng (×) để tắt thủ công

**CSS:** `public/assets/css/supplier-style.css`  
**JavaScript:** Hàm `showInlineTooltip()` trong create.php và edit.php

## 📊 Tổng kết thay đổi

| File | Thay đổi |
|------|----------|
| `SupplierService.php` | Thêm kiểm tra bắt buộc phone trong `createSupplier()` và `updateSupplier()` |
| `create.php` | Thêm `required`, cập nhật `phoneValidator()` |
| `edit.php` | Thêm `required`, cập nhật validation submit + blur |
| `test_phone_required.php` | Script test mới (6 test cases) |

## ✅ Checklist hoàn thành

- [x] Backend validation bắt buộc phone
- [x] Frontend HTML thêm `required` attribute
- [x] Frontend JS validation khi blur
- [x] Frontend JS validation khi submit
- [x] Tooltip hiển thị đẹp mắt
- [x] Test script tự động
- [x] Kiểm tra syntax không lỗi
- [x] Tài liệu hướng dẫn

## 🚀 Hướng dẫn sử dụng

### Người dùng cuối:
1. Truy cập trang tạo/sửa nhà cung cấp
2. Nhập thông tin, **bắt buộc phải nhập số điện thoại**
3. Nếu nhập sai hoặc để trống, tooltip sẽ hiện ngay khi blur hoặc submit

### Developer:
```bash
# Test validation
php scripts/test_phone_required.php

# Chạy dev server
php -S localhost:8000 -t public
```

---

**Ghi chú:** Validation này áp dụng cả phía client (JS) và server (PHP) để đảm bảo an toàn dữ liệu.
