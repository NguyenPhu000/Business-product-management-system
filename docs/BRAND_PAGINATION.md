# Phân trang Thương hiệu - 8 sản phẩm/trang

**Ngày tạo:** 14/11/2025  
**Mục đích:** Thêm phân trang cho trang Quản lý thương hiệu, hiển thị 8 thương hiệu mỗi trang

---

## 📋 Tổng quan

Hệ thống phân trang cho phép:
- ✅ Hiển thị **8 thương hiệu** mỗi trang
- ✅ Điều hướng giữa các trang (Đầu, Trước, Sau, Cuối)
- ✅ Hiển thị thông tin tổng số thương hiệu
- ✅ Tìm kiếm không bị ảnh hưởng bởi phân trang
- ✅ Xử lý trang không hợp lệ (tự động chuyển về trang 1)

---

## 🔧 Các thay đổi

### 1. **BrandModel.php**

#### Thêm method `getAllWithPagination()`

```php
/**
 * Lấy thương hiệu với phân trang
 * 
 * @param int $page Trang hiện tại (bắt đầu từ 1)
 * @param int $perPage Số lượng/trang
 * @return array ['data' => [], 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
 */
public function getAllWithPagination(int $page = 1, int $perPage = 8): array
{
    // Đảm bảo page >= 1
    $page = max(1, $page);
    $offset = ($page - 1) * $perPage;

    // Đếm tổng số thương hiệu
    $countSql = "SELECT COUNT(DISTINCT b.id) as total FROM {$this->table} b";
    $countResult = $this->queryOne($countSql);
    $total = (int) ($countResult['total'] ?? 0);

    // Lấy dữ liệu phân trang
    $sql = "SELECT b.*, COUNT(p.id) as product_count 
            FROM {$this->table} b 
            LEFT JOIN products p ON b.id = p.brand_id 
            GROUP BY b.id 
            ORDER BY b.name ASC
            LIMIT {$perPage} OFFSET {$offset}";

    $data = $this->query($sql);

    return [
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => (int) ceil($total / $perPage)
    ];
}
```

**Giải thích:**
- `$page`: Trang hiện tại (1, 2, 3...)
- `$perPage`: Số thương hiệu mỗi trang (mặc định 8)
- `$offset`: Vị trí bắt đầu trong DB = `(page - 1) * perPage`
- Trả về mảng với:
  - `data`: Danh sách thương hiệu
  - `total`: Tổng số thương hiệu
  - `page`: Trang hiện tại
  - `perPage`: Số lượng/trang
  - `totalPages`: Tổng số trang

---

### 2. **BrandService.php**

#### Thêm method `getBrandsWithPagination()`

```php
/**
 * Lấy thương hiệu với phân trang
 * 
 * @param int $page
 * @param int $perPage
 * @return array
 */
public function getBrandsWithPagination(int $page = 1, int $perPage = 8): array
{
    return $this->brandModel->getAllWithPagination($page, $perPage);
}
```

---

### 3. **BrandController.php**

#### Cập nhật method `index()`

```php
public function index(): void
{
    $keyword = $this->input('keyword', '');
    $page = (int) $this->input('page', 1);
    $perPage = 8; // 8 thương hiệu mỗi trang

    if ($keyword) {
        // Tìm kiếm không phân trang
        $brands = $this->brandService->searchBrands($keyword);
        $pagination = null;
    } else {
        // Lấy dữ liệu với phân trang
        $result = $this->brandService->getBrandsWithPagination($page, $perPage);
        $brands = $result['data'];
        $pagination = [
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'totalPages' => $result['totalPages']
        ];
    }

    $this->view('admin/brands/index', [
        'brands' => $brands,
        'keyword' => $keyword,
        'pagination' => $pagination,
        'pageTitle' => 'Quản lý thương hiệu'
    ]);
}
```

**Thay đổi:**
- Lấy tham số `page` từ URL (`?page=2`)
- Nếu **có tìm kiếm** → không phân trang, hiện tất cả kết quả
- Nếu **không tìm kiếm** → phân trang 8 thương hiệu/trang
- Truyền `$pagination` vào view để hiển thị UI

---

### 4. **index.php (View)**

#### Thêm phần Pagination UI

```php
<?php if (isset($pagination) && $pagination && $pagination['totalPages'] > 1): ?>
<!-- Pagination -->
<div class="card-footer">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="text-muted">
                Hiển thị <?= count($brands) ?> / <?= $pagination['total'] ?> thương hiệu
            </span>
        </div>
        <nav aria-label="Phân trang thương hiệu">
            <ul class="pagination pagination-sm mb-0">
                <!-- Trang đầu -->
                <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=1">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                </li>
                
                <!-- Trang trước -->
                <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= max(1, $pagination['page'] - 1) ?>">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
                
                <!-- Các số trang -->
                <?php
                $startPage = max(1, $pagination['page'] - 2);
                $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                ?>
                
                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
                
                <!-- Trang sau -->
                <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= min($pagination['totalPages'], $pagination['page'] + 1) ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
                
                <!-- Trang cuối -->
                <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= $pagination['totalPages'] ?>">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<?php endif; ?>
```

**UI bao gồm:**
- ⏮️ **Trang đầu** (`<<`)
- ◀️ **Trang trước** (`<`)
- 🔢 **Các số trang** (hiện tối đa 5 số)
- ▶️ **Trang sau** (`>`)
- ⏭️ **Trang cuối** (`>>`)
- 📊 **Thông tin** (Hiển thị X / Y thương hiệu)

---

## 🧪 Kiểm tra

### Test script: `test_brand_pagination.php`

Kết quả test:
```
✅ Tổng số thương hiệu: 7
✅ Trang hiện tại: 1
✅ Số lượng/trang: 8
✅ Tổng số trang: 1
✅ Số thương hiệu trang này: 7

Danh sách thương hiệu trang 1:
  - ID 8: Android (0 sản phẩm)
  - ID 1: Apple (7 sản phẩm)
  - ID 4: Casio (1 sản phẩm)
  ...
```

### Test thủ công

1. **Khởi động server:**
   ```bash
   php -S localhost:8000 -t public
   ```

2. **Truy cập:**
   ```
   http://localhost:8000/admin/brands
   ```

3. **Thử các URL:**
   | URL | Kết quả |
   |-----|---------|
   | `/admin/brands` | Trang 1 (8 thương hiệu) |
   | `/admin/brands?page=2` | Trang 2 (8 thương hiệu tiếp theo) |
   | `/admin/brands?page=0` | Tự động chuyển về trang 1 |
   | `/admin/brands?page=999` | Hiển thị rỗng (trang không tồn tại) |
   | `/admin/brands?keyword=Apple` | Tìm kiếm (không phân trang) |

---

## 📊 Ví dụ sử dụng

### Tình huống 1: Có 25 thương hiệu

```
Trang 1: 8 thương hiệu (ID 1-8)
Trang 2: 8 thương hiệu (ID 9-16)
Trang 3: 8 thương hiệu (ID 17-24)
Trang 4: 1 thương hiệu (ID 25)
```

**UI phân trang:**
```
[<<] [<] [1] [2] [3] [4] [>] [>>]
     ↑ Trang hiện tại (active)
```

### Tình huống 2: Có 7 thương hiệu (hiện tại)

```
Trang 1: 7 thương hiệu (tất cả)
```

**UI phân trang:** Ẩn (chỉ hiện khi có > 1 trang)

---

## ⚙️ Tùy chỉnh

### Thay đổi số lượng thương hiệu/trang

**File:** `BrandController.php`, dòng 32

```php
$perPage = 8; // Thay đổi số này (ví dụ: 10, 15, 20...)
```

### Thay đổi số trang hiển thị trong pagination

**File:** `index.php`, dòng ~150

```php
$startPage = max(1, $pagination['page'] - 2); // Hiện 5 trang
$endPage = min($pagination['totalPages'], $pagination['page'] + 2);

// Để hiện 7 trang:
$startPage = max(1, $pagination['page'] - 3);
$endPage = min($pagination['totalPages'], $pagination['page'] + 3);
```

---

## 🔍 Lưu ý

### 1. Tìm kiếm không phân trang
- Khi người dùng tìm kiếm, hệ thống sẽ **hiển thị TẤT CẢ** kết quả (không phân trang)
- Lý do: Tránh bỏ sót kết quả khi tìm kiếm

### 2. URL parameter
- Tham số `page` được truyền qua URL: `?page=2`
- Nếu không có `page` → mặc định trang 1
- Nếu `page <= 0` → tự động chuyển về trang 1

### 3. Trang không tồn tại
- Nếu `page > totalPages` → hiển thị rỗng (không có lỗi)
- Có thể thêm redirect về trang cuối nếu muốn

---

## ✅ Checklist

- [x] Thêm method `getAllWithPagination()` vào `BrandModel`
- [x] Thêm method `getBrandsWithPagination()` vào `BrandService`
- [x] Cập nhật `BrandController->index()` xử lý phân trang
- [x] Cập nhật view hiển thị pagination UI
- [x] Tạo test script kiểm tra phân trang
- [x] Kiểm tra syntax không lỗi
- [x] Test thủ công trên trình duyệt
- [x] Tài liệu hướng dẫn

---

## 📁 File đã thay đổi

| File | Thay đổi |
|------|----------|
| `BrandModel.php` | Thêm `getAllWithPagination()` |
| `BrandService.php` | Thêm `getBrandsWithPagination()` |
| `BrandController.php` | Cập nhật `index()` xử lý page parameter |
| `index.php` | Thêm pagination UI |
| `test_brand_pagination.php` | Script test mới |

---

**✨ Phân trang đã hoạt động! Truy cập `/admin/brands` để xem kết quả.**
