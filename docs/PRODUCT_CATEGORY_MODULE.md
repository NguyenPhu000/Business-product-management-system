# Tài liệu Module Gán Sản phẩm - Danh mục

## Tổng quan

Module này cho phép **gán 1 sản phẩm vào nhiều danh mục** (many-to-many relationship) thông qua bảng trung gian `product_categories`.

## Cấu trúc Database

### Bảng product_categories (Pivot Table)
```sql
CREATE TABLE product_categories (
    product_id INT NOT NULL,
    category_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, category_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);
```

## Cấu trúc Code

### 1. Models

#### ProductCategoryModel.php
**Vị trí:** `src/Models/ProductCategoryModel.php`

**Chức năng chính:**
- `assignCategories($productId, $categoryIds)` - Gán nhiều danh mục cho 1 sản phẩm
- `getCategoriesByProduct($productId)` - Lấy danh sách danh mục của sản phẩm
- `getProductsByCategory($categoryId)` - Lấy danh sách sản phẩm theo danh mục
- `addCategory($productId, $categoryId)` - Thêm 1 danh mục cho sản phẩm
- `removeCategory($productId, $categoryId)` - Xóa 1 danh mục khỏi sản phẩm
- `removeAllCategories($productId)` - Xóa tất cả danh mục của sản phẩm
- `existsRelation($productId, $categoryId)` - Kiểm tra quan hệ đã tồn tại chưa
- `countProductsByCategory($categoryId)` - Đếm số sản phẩm trong danh mục

**Ví dụ sử dụng:**
```php
$model = new ProductCategoryModel();

// Gán sản phẩm ID=1 vào danh mục 5, 7, 9
$model->assignCategories(1, [5, 7, 9]);

// Lấy danh mục của sản phẩm ID=1
$categories = $model->getCategoriesByProduct(1);
// => [['id'=>5, 'name'=>'...'], ['id'=>7, 'name'=>'...'], ...]

// Lấy sản phẩm trong danh mục ID=5
$products = $model->getProductsByCategory(5);
```

#### ProductModel.php
**Vị trí:** `src/Models/ProductModel.php`

**Chức năng chính:**
- `getWithCategories($id)` - Lấy sản phẩm kèm danh sách danh mục (GROUP_CONCAT)
- `getProductsList($filters, $page, $perPage)` - Danh sách sản phẩm với filter và phân trang
- `countProducts($filters)` - Đếm số sản phẩm theo filter
- `updateStock($id, $quantity)` - Cập nhật tồn kho
- `skuExists($sku, $excludeId)` - Kiểm tra SKU trùng

**Filters hỗ trợ:**
- `category_id` - Lọc theo danh mục
- `keyword` - Tìm theo tên hoặc SKU
- `status` - Lọc theo trạng thái

### 2. Controllers

#### ProductCategoryController.php
**Vị trí:** `src/Controllers/Admin/ProductCategoryController.php`

**Actions:**

##### `manage($productId)` - GET
Hiển thị form gán danh mục cho sản phẩm

**Route:** `/admin/products/manage-categories/{productId}`

**Response:** View với checkbox tree danh mục

##### `update($productId)` - POST
Xử lý cập nhật danh mục cho sản phẩm

**Route:** `/admin/products/manage-categories/{productId}`

**Input:**
```php
$_POST['category_ids'] = [5, 7, 9];  // Array of category IDs
```

**Response:** Redirect với flash message

##### `addToCategory()` - POST (API)
Thêm sản phẩm vào 1 danh mục qua AJAX

**Route:** `/admin/products/add-to-category`

**Input:**
```json
{
  "product_id": 1,
  "category_id": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Đã thêm vào danh mục"
}
```

##### `removeFromCategory()` - POST (API)
Xóa sản phẩm khỏi danh mục qua AJAX

**Route:** `/admin/products/remove-from-category`

**Input:**
```json
{
  "product_id": 1,
  "category_id": 5
}
```

**Response:**
```json
{
  "success": true,
  "message": "Đã xóa khỏi danh mục"
}
```

### 3. Views

#### manage-categories.php
**Vị trí:** `src/views/admin/products/manage-categories.php`

**Chức năng:**
- Hiển thị thông tin sản phẩm
- Hiển thị cây danh mục với checkbox
- Hỗ trợ "Chọn tất cả" / "Bỏ chọn tất cả"
- Hiển thị badge "Ẩn" cho danh mục không active
- Indent theo cấp độ danh mục (level-0, level-1, level-2...)

**Features:**
- Tự động đánh dấu danh mục đã được gán
- Hỗ trợ danh mục phân cấp (hierarchical tree)
- Responsive design với Bootstrap 5

### 4. CSS

#### product-category-style.css
**Vị trí:** `public/assets/css/product-category-style.css`

**Styles chính:**
- `.category-tree` - Container với scroll
- `.category-item` - Mỗi item checkbox
- `.category-item.level-{0-4}` - Indent theo cấp độ
- `.category-item:hover` - Hover effect
- `.form-check-input:checked ~ .form-check-label` - Label khi checked

## Routes

### Routes đã thêm (4 routes)

```php
// GET - Hiển thị form gán danh mục
GET /admin/products/manage-categories/{productId}
-> ProductCategoryController@manage

// POST - Cập nhật danh mục
POST /admin/products/manage-categories/{productId}
-> ProductCategoryController@update

// POST - API thêm vào danh mục
POST /admin/products/add-to-category
-> ProductCategoryController@addToCategory

// POST - API xóa khỏi danh mục
POST /admin/products/remove-from-category
-> ProductCategoryController@removeFromCategory
```

## Luồng hoạt động

### 1. Gán danh mục cho sản phẩm

**Bước 1:** User truy cập `/admin/products/manage-categories/1`

**Bước 2:** Controller gọi:
```php
$product = $this->productModel->find(1);
$categoryTree = $this->categoryModel->getCategoryTree();
$assignedCategoryIds = $this->productCategoryModel->getCategoryIdsByProduct(1);
```

**Bước 3:** View hiển thị checkbox tree với danh mục đã gán được checked

**Bước 4:** User chọn/bỏ chọn các danh mục và submit form

**Bước 5:** Controller gọi:
```php
$categoryIds = $_POST['category_ids']; // [5, 7, 9]
$this->productCategoryModel->assignCategories(1, $categoryIds);
```

**Bước 6:** Model thực hiện:
- Xóa tất cả quan hệ cũ: `DELETE FROM product_categories WHERE product_id = 1`
- Thêm quan hệ mới: `INSERT INTO product_categories (product_id, category_id) VALUES (1,5), (1,7), (1,9)`

**Bước 7:** Redirect với message: "Cập nhật danh mục thành công! Đã gán 3 danh mục."

### 2. Cascade Hide Categories

**Tính năng:** Khi ẩn danh mục cha → tự động ẩn tất cả danh mục con

**Implementation trong CategoryModel:**

```php
public function updateActiveStatus(int $id, int $isActive): bool
{
    // Nếu ẩn danh mục cha (is_active = 0)
    if ($isActive == 0) {
        // Lấy tất cả ID danh mục con
        $childrenIds = $this->getAllChildrenIds($id);
        
        // Ẩn tất cả danh mục con
        if (!empty($childrenIds)) {
            $ids = implode(',', $childrenIds);
            $this->execute(
                "UPDATE {$this->table} SET is_active = 0 WHERE id IN ($ids)"
            );
        }
    }
    
    // Cập nhật danh mục hiện tại
    return $this->update($id, ['is_active' => $isActive]);
}

private function getAllChildrenIds(int $parentId): array
{
    $children = $this->where(['parent_id' => $parentId]);
    $ids = [];
    
    foreach ($children as $child) {
        $ids[] = $child['id'];
        // Đệ quy lấy con của con
        $ids = array_merge($ids, $this->getAllChildrenIds($child['id']));
    }
    
    return $ids;
}
```

**Sử dụng trong CategoryController:**

```php
public function toggleActive(int $id): void
{
    $category = $this->categoryModel->find($id);
    $newStatus = $category['is_active'] == 1 ? 0 : 1;
    
    // Lấy số lượng danh mục con trước khi cập nhật
    $affectedChildren = 0;
    if ($newStatus == 0) {
        $affectedChildren = count($this->categoryModel->getAllChildrenIds($id));
    }
    
    // Cập nhật (cascade tự động xảy ra)
    $success = $this->categoryModel->updateActiveStatus($id, $newStatus);
    
    if ($success) {
        $message = $newStatus == 1 
            ? 'Đã hiển thị danh mục' 
            : "Đã ẩn danh mục và {$affectedChildren} danh mục con";
        
        AuthHelper::setFlash('success', $message);
    }
}
```

## Testing

### Test case 1: Gán nhiều danh mục

1. Truy cập `/admin/products/manage-categories/1`
2. Chọn 3 danh mục bất kỳ
3. Click "Lưu thay đổi"
4. Kiểm tra database: `SELECT * FROM product_categories WHERE product_id = 1`
5. Expected: 3 records

### Test case 2: Cập nhật danh mục

1. Sản phẩm đang có danh mục [1, 2, 3]
2. Bỏ chọn danh mục 2, thêm danh mục 5
3. Submit
4. Expected: Danh mục mới là [1, 3, 5]

### Test case 3: Cascade hide

1. Tạo cây danh mục: Cha (ID=1) → Con (ID=2) → Cháu (ID=3)
2. Ẩn danh mục Cha
3. Kiểm tra: `SELECT is_active FROM categories WHERE id IN (2, 3)`
4. Expected: Cả 2 đều is_active = 0

### Test case 4: API add/remove

```javascript
// Test add
fetch('/admin/products/add-to-category', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({product_id: 1, category_id: 5})
});
// Expected: {success: true, message: "Đã thêm vào danh mục"}

// Test remove
fetch('/admin/products/remove-from-category', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({product_id: 1, category_id: 5})
});
// Expected: {success: true, message: "Đã xóa khỏi danh mục"}
```

## Security

### Middleware Protection
Tất cả routes đều protected bởi:
- `AuthMiddleware` - Yêu cầu đăng nhập
- `RoleMiddleware` - Chỉ admin mới truy cập

### SQL Injection Prevention
- Sử dụng PDO prepared statements
- Tất cả params đều được bind
- Không có raw SQL với user input

### Input Validation
```php
// Trong ProductCategoryController::update()
$categoryIds = $this->input('category_ids', []);
if (!is_array($categoryIds)) {
    $categoryIds = [];
}
$categoryIds = array_map('intval', $categoryIds); // Cast to integer
```

## Tích hợp với hệ thống hiện tại

### 1. Thêm vào menu Products (nếu chưa có)

**File:** `src/views/admin/layouts/sidebar.php`

```php
<!-- Thêm submenu "Gán danh mục" vào menu Products -->
<li class="nav-item">
    <a class="nav-link" href="/admin/products">
        <i class="bi bi-box"></i> Sản phẩm
    </a>
</li>
```

### 2. Thêm nút trong danh sách sản phẩm

**File:** `src/views/admin/products/index.php`

```php
<td>
    <a href="/admin/products/manage-categories/<?= $product['id'] ?>" 
       class="btn btn-sm btn-info">
        <i class="bi bi-tags"></i> Danh mục
    </a>
</td>
```

### 3. Hiển thị danh mục trong chi tiết sản phẩm

```php
$product = $productModel->getWithCategories($id);
echo "Danh mục: " . $product['category_names']; 
// => "Điện thoại, Smartphone, iPhone"
```

## Troubleshooting

### Lỗi: "Undefined type 'Models\ProductModel'"

**Nguyên nhân:** ProductModel.php chưa được tạo

**Giải pháp:** Controller tự động tạo file nếu chưa có
```php
private function createBasicProductModel(): void
{
    // Code tự động tạo ProductModel
}
```

### Lỗi: "Undefined property '$db'"

**Nguyên nhân:** Sử dụng `$this->db->query()` thay vì `$this->query()`

**Giải pháp:** ProductModel extends BaseModel, sử dụng methods từ parent class
```php
// Sai:
$this->db->queryOne($sql, $params);

// Đúng:
$this->queryOne($sql, $params);
```

### Lỗi: PDO named parameters không hoạt động

**Nguyên nhân:** DatabaseModel sử dụng positional parameters (?)

**Giải pháp:** Chuyển từ `:name` sang `?`
```php
// Sai:
$sql = "WHERE category_id = :id";
$params = ['id' => 5];

// Đúng:
$sql = "WHERE category_id = ?";
$params = [5];
```

## Changelog

### v1.0.0 (2024)
- ✅ Tạo ProductCategoryModel với 15+ methods
- ✅ Tạo ProductModel với filter và pagination
- ✅ Tạo ProductCategoryController với 4 actions
- ✅ Tạo view manage-categories.php
- ✅ Tạo CSS file riêng
- ✅ Thêm 4 routes mới
- ✅ Implement cascade hide cho categories
- ✅ Fix lint errors (method naming conflicts)
- ✅ Convert named params → positional params

## Kế hoạch phát triển

### Phase 2
- [ ] Tạo ProductController full CRUD
- [ ] Tích hợp category assignment vào form thêm/sửa sản phẩm
- [ ] Thêm bulk actions (gán nhiều sản phẩm vào 1 danh mục)
- [ ] Export danh sách sản phẩm theo danh mục (Excel/PDF)

### Phase 3
- [ ] Thống kê sản phẩm theo danh mục (charts)
- [ ] Filter danh mục theo nhiều điều kiện
- [ ] Sắp xếp thứ tự sản phẩm trong danh mục (drag & drop)
- [ ] API RESTful cho mobile app

## Tài liệu tham khảo

- [Database Schema](../business_product_management_system.sql)
- [Category Management Module](./CATEGORY_MANAGEMENT_MODULE.md)
- [MVC Architecture](../README.md)
- [PDO Documentation](https://www.php.net/manual/en/book.pdo.php)
