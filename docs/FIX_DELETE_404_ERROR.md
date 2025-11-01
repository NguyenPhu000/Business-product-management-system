# Fix Log - Lỗi 404 khi xóa

## Ngày: 28/10/2025

## 🐛 Vấn đề

Khi nhấn nút "Xóa" (Delete) ở các trang Categories, Brands, Suppliers → Hiện lỗi **404 Not Found**

### Triệu chứng
- URL: `localhost:8000/admin/categories/delete/3`
- Method: GET (thay vì POST)
- Response: 404 - Trang không tìm thấy

## 🔍 Phân tích nguyên nhân

### Nguyên nhân 1: Router không match parameters đúng

**File:** `src/core/Router.php`

**Vấn đề:**
```php
// Router trả về named parameters
$params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
// => ['id' => '3']

// Nhưng controller method nhận positional parameters
public function delete(int $id): void
```

Khi gọi `call_user_func_array([$controller, 'delete'], ['id' => 3])`:
- PHP không map `['id' => 3]` vào parameter `$id`
- Method không khớp signature → 404

**Giải pháp:**
```php
// Convert named params thành positional params
$namedParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
$params = array_values($namedParams);
// => [3]
```

### Nguyên nhân 2: JavaScript inline onclick với dấu nháy

**File:** `src/views/admin/categories/index.php`, `brands/index.php`, `suppliers/index.php`

**Vấn đề:**
```php
onclick="deleteCategory(<?= $id ?>, '<?= htmlspecialchars($name) ?>')"
```

Nếu `$name = 'Danh mục "Hot"'` thì sinh ra:
```html
onclick="deleteCategory(3, 'Danh mục "Hot"')"
```
→ JavaScript syntax error!

**Giải pháp:**
Dùng **data attributes**:
```php
<button data-id="<?= $id ?>" 
        data-name="<?= htmlspecialchars($name) ?>"
        onclick="deleteCategory(this)">
```

JavaScript:
```javascript
function deleteCategory(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');
    // ...
}
```

## ✅ Các file đã sửa

### 1. src/core/Router.php

**Thay đổi:** Convert named parameters thành positional parameters

```php
// TRƯỚC
if (preg_match($pattern, $uri, $matches)) {
    $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    // ...
}

// SAU
if (preg_match($pattern, $uri, $matches)) {
    $namedParams = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    $params = array_values($namedParams); // ← Thêm dòng này
    // ...
}
```

**Thêm debug logging:**
```php
if (defined('APP_DEBUG') && APP_DEBUG) {
    error_log("Router Debug - Method: {$method}, URI: {$uri}");
    error_log("Router Debug - Matched route: {$route['path']}, Params: " . json_encode($params));
}
```

### 2. src/views/admin/categories/index.php

**Thay đổi buttons (3 chỗ):**
```php
// TRƯỚC
<button onclick="deleteCategory(<?= $id ?>, '<?= htmlspecialchars($name) ?>')">

// SAU
<button data-id="<?= $id ?>" 
        data-name="<?= htmlspecialchars($name) ?>"
        onclick="deleteCategory(this)">
```

**Thay đổi JavaScript:**
```javascript
// TRƯỚC
function deleteCategory(id, name) {
    if (confirm('...')) {
        const form = document.getElementById('deleteForm');
        form.action = '/admin/categories/delete/' + id;
        form.submit();
    }
}

// SAU
function deleteCategory(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');
    
    if (confirm('...')) {
        const form = document.getElementById('deleteForm');
        form.action = '/admin/categories/delete/' + id;
        form.method = 'POST'; // Đảm bảo POST
        form.submit();
    }
}
```

### 3. src/views/admin/brands/index.php

Tương tự categories - sửa button và JavaScript function `deleteBrand()`

### 4. src/views/admin/suppliers/index.php

Tương tự categories - sửa button và JavaScript function `deleteSupplier()`

## 🧪 Testing

### Test case 1: Xóa category
1. Vào `/admin/categories`
2. Click nút xóa (icon thùng rác đỏ)
3. Confirm popup
4. **Expected:** 
   - Nếu có sản phẩm/danh mục con: Message lỗi "Không thể xóa..."
   - Nếu trống: Message "Xóa danh mục thành công!"

### Test case 2: Xóa brand
1. Vào `/admin/brands`
2. Click nút xóa
3. Confirm
4. **Expected:** Xóa thành công hoặc lỗi nghiệp vụ

### Test case 3: Xóa supplier
1. Vào `/admin/suppliers`
2. Click nút xóa
3. Confirm
4. **Expected:** Xóa thành công hoặc lỗi nghiệp vụ

### Test case 4: Tên có ký tự đặc biệt
Thử với name = `Danh mục "Hot" & 'New'`
- **Expected:** JavaScript không lỗi, confirm popup hiển thị đúng tên

## 📊 Impact Analysis

### Files affected: 4 files
- ✅ `src/core/Router.php` - Core routing
- ✅ `src/views/admin/categories/index.php` - Categories view
- ✅ `src/views/admin/brands/index.php` - Brands view
- ✅ `src/views/admin/suppliers/index.php` - Suppliers view

### Breaking changes: None
- Backward compatible
- Không ảnh hưởng đến các controller khác

### Performance impact: Minimal
- `array_values()` là O(n) nhưng n rất nhỏ (thường 1-2 params)
- Debug logging chỉ chạy khi `APP_DEBUG = true`

## 🔐 Security improvements

### Before
```php
onclick="deleteCategory(<?= $id ?>, '<?= htmlspecialchars($name) ?>')"
```
- Có thể bị XSS nếu `$name` chứa `');maliciousCode();`
- htmlspecialchars() trong inline onclick không đủ an toàn

### After
```php
data-name="<?= htmlspecialchars($name) ?>"
```
- Dữ liệu được encode trong HTML attribute
- JavaScript lấy data từ DOM → an toàn hơn
- Tách biệt data và code

## 📝 Notes

### Debug mode
Để bật debug logging, thêm vào `config/constants.php`:
```php
define('APP_DEBUG', true);
```

Check logs tại:
- PHP error log: Xem trong terminal PHP server
- Browser console: F12 → Console

### Cache busting
Nếu vẫn thấy lỗi sau khi sửa:
1. Hard refresh: `Ctrl + Shift + R` (hoặc `Ctrl + F5`)
2. Clear browser cache
3. Restart PHP server:
   ```powershell
   # Stop server (Ctrl+C)
   # Start lại
   php -S localhost:8000 -t public
   ```

## 🚀 Future improvements

### 1. Sử dụng AJAX thay vì form submit
```javascript
function deleteCategory(btn) {
    const id = btn.getAttribute('data-id');
    
    if (confirm('...')) {
        fetch('/admin/categories/delete/' + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Xóa row khỏi table mà không reload
                btn.closest('tr').remove();
            } else {
                alert(data.message);
            }
        });
    }
}
```

### 2. Thêm CSRF protection
```php
// Controller
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// View
<form>
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
</form>

// Controller delete
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}
```

### 3. Soft delete thay vì hard delete
```sql
ALTER TABLE categories ADD COLUMN deleted_at TIMESTAMP NULL;
```

```php
// Thay vì DELETE
UPDATE categories SET deleted_at = NOW() WHERE id = ?
```

## ✅ Checklist

- [x] Sửa Router.php - convert params
- [x] Sửa categories/index.php - data attributes
- [x] Sửa brands/index.php - data attributes
- [x] Sửa suppliers/index.php - data attributes
- [x] Thêm debug logging
- [x] Test delete category
- [x] Test delete brand
- [x] Test delete supplier
- [x] Test với tên có ký tự đặc biệt
- [x] Viết tài liệu

## 🎯 Kết luận

Lỗi 404 khi xóa đã được fix hoàn toàn. Nguyên nhân chính là:
1. Router không convert params đúng kiểu
2. JavaScript inline onclick không an toàn

Giải pháp áp dụng là best practices:
- Positional parameters cho method calls
- Data attributes thay vì inline JavaScript
- Form POST đảm bảo method đúng

Tất cả các trang CRUD (Categories, Brands, Suppliers) đã hoạt động bình thường.
