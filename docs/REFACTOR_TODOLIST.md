# 🚨 TODOLIST SỬA LỖI VI PHẠM CODING RULES

**Ngày tạo**: 8/11/2025  
**Branch hiện tại**: `develop`  
**Trạng thái**: 🔴 CẦN SỬA GẤP

---

## 📊 TỔNG QUAN VI PHẠM

Sau khi phân tích toàn bộ codebase dựa trên [`CODING_RULES.md`](docs/CODING_RULES.md), phát hiện **các vi phạm nghiêm trọng** sau:

### ❌ VI PHẠM NGHIÊM TRỌNG (Critical)

1. **CẤU TRÚC THƯ MỤC SAI** - Có 2 hệ thống song song:
   - `src/Controllers/Admin/*` ❌ SAI
   - `src/Models/*` ❌ SAI  
   - `src/modules/*/controllers/*` ✅ ĐÚNG (nhưng chưa đầy đủ)

2. **NAMESPACE SAI** - Controllers/Models dùng namespace sai:
   - `namespace Controllers\Admin;` ❌ SAI
   - `namespace Models;` ❌ SAI
   - Phải là: `namespace Modules\[ModuleName]\Controllers;` ✅

3. **LOGIC NGHIỆP VỤ TRONG CONTROLLER** - Không tuân thủ MVC:
   - `ProductController::store()` có 100+ dòng code logic
   - `ProductController::handleImageUpload()` xử lý upload trong Controller
   - Thiếu hoàn toàn **Service Layer**

4. **TRÙNG LẶP CODE** - Có 2 ProductController:
   - `src/Controllers/Admin/ProductController.php` (đang dùng) ❌
   - `src/modules/product/controllers/ProductController.php` (rỗng) ✅

---

## 🎯 KẾ HOẠCH SỬA LỖI (THEO THỨ TỰ ƯU TIÊN)

### PHASE 1: CHUẨN BỊ VÀ BACKUP (⏱️ 30 phút)

- [ ] **1.1. Tạo backup branch**
  ```bash
  git checkout develop
  git branch backup-before-refactor-$(date +%Y%m%d)
  git push origin backup-before-refactor-$(date +%Y%m%d)
  ```

- [ ] **1.2. Tạo branch refactor**
  ```bash
  git checkout -b refactor/fix-coding-violations
  ```

- [ ] **1.3. Tạo danh sách file cần di chuyển**
  - Xác định tất cả file trong `src/Controllers/Admin/*`
  - Xác định tất cả file trong `src/Models/*`
  - Map sang module tương ứng

---

### PHASE 2: REFACTOR MODULE PRODUCT (⏱️ 4-6 giờ)

#### 🎯 Mục tiêu: Di chuyển toàn bộ Product logic sang `src/modules/product/`

#### **2.1. Tạo ProductService.php** ⭐ QUAN TRỌNG NHẤT

**File**: `src/modules/product/services/ProductService.php`

**Nhiệm vụ**:
- [x] Tạo file với namespace đúng: `namespace Modules\Product\Services;`
- [ ] Di chuyển logic từ `ProductController::store()` sang `ProductService::createProduct()`
- [ ] Di chuyển logic từ `ProductController::update()` sang `ProductService::updateProduct()`
- [ ] Tạo method `ProductService::validateProductData(array $data): array`
- [ ] Tạo method `ProductService::checkSkuExists(string $sku, ?int $excludeId): bool`
- [ ] Tạo method `ProductService::prepareProductData(array $input): array`

**Template code**:
```php
<?php

namespace Modules\Product\Services;

use Modules\Product\Models\ProductModel;
use Modules\Product\Models\ProductCategoryModel;
use Exception;

class ProductService
{
    private ProductModel $productModel;
    private ProductCategoryModel $productCategoryModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productCategoryModel = new ProductCategoryModel();
    }

    /**
     * Tạo sản phẩm mới
     * 
     * @param array $data Dữ liệu sản phẩm (từ form)
     * @return int ID sản phẩm vừa tạo
     * @throws Exception Nếu validation fail hoặc lỗi DB
     */
    public function createProduct(array $data): int
    {
        // 1. Validate
        $errors = $this->validateProductData($data);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        // 2. Kiểm tra SKU trùng
        if ($this->checkSkuExists($data['sku'])) {
            throw new Exception('Mã SKU đã tồn tại trong hệ thống!');
        }

        // 3. Chuẩn bị dữ liệu
        $productData = $this->prepareProductData($data);

        // 4. Tạo sản phẩm
        $productId = $this->productModel->create($productData);

        if (!$productId) {
            throw new Exception('Không thể tạo sản phẩm');
        }

        // 5. Gán danh mục
        if (!empty($data['category_ids'])) {
            $this->productCategoryModel->assignCategories($productId, $data['category_ids']);
        }

        return $productId;
    }

    /**
     * Validate dữ liệu sản phẩm
     * 
     * @param array $data
     * @return array Mảng lỗi (rỗng nếu hợp lệ)
     */
    public function validateProductData(array $data): array
    {
        $errors = [];

        // Required fields
        if (empty($data['sku'])) {
            $errors['sku'] = 'Mã SKU là bắt buộc';
        }

        if (empty($data['name']) || strlen($data['name']) < 3) {
            $errors['name'] = 'Tên sản phẩm phải có ít nhất 3 ký tự';
        }

        if (empty($data['brand_id'])) {
            $errors['brand_id'] = 'Vui lòng chọn thương hiệu';
        }

        if (empty($data['unit'])) {
            $errors['unit'] = 'Đơn vị tính là bắt buộc';
        }

        // Numeric validation
        if (!isset($data['unit_cost']) || !is_numeric($data['unit_cost'])) {
            $errors['unit_cost'] = 'Giá nhập không hợp lệ';
        }

        if (!isset($data['price']) || !is_numeric($data['price'])) {
            $errors['price'] = 'Giá bán không hợp lệ';
        }

        // Business rules
        if (isset($data['price'], $data['unit_cost']) && (float)$data['price'] < (float)$data['unit_cost']) {
            $errors['price'] = 'Giá bán phải lớn hơn hoặc bằng giá nhập';
        }

        if (!empty($data['sale_price']) && (float)$data['sale_price'] >= (float)$data['price']) {
            $errors['sale_price'] = 'Giá khuyến mãi phải nhỏ hơn giá bán';
        }

        if (empty($data['category_ids']) || !is_array($data['category_ids'])) {
            $errors['category_ids'] = 'Vui lòng chọn ít nhất một danh mục';
        }

        return $errors;
    }

    /**
     * Kiểm tra SKU đã tồn tại chưa
     * 
     * @param string $sku
     * @param int|null $excludeId ID sản phẩm bỏ qua khi update
     * @return bool
     */
    public function checkSkuExists(string $sku, ?int $excludeId = null): bool
    {
        return $this->productModel->skuExists($sku, $excludeId);
    }

    /**
     * Chuẩn bị dữ liệu sản phẩm trước khi lưu DB
     * 
     * @param array $input Dữ liệu từ form
     * @return array Dữ liệu đã chuẩn hóa
     */
    private function prepareProductData(array $input): array
    {
        return [
            'sku' => trim($input['sku']),
            'name' => trim($input['name']),
            'short_desc' => $input['short_desc'] ?? null,
            'long_desc' => $input['long_desc'] ?? null,
            'brand_id' => (int) $input['brand_id'],
            'unit' => trim($input['unit']),
            'unit_cost' => (float) $input['unit_cost'],
            'price' => (float) $input['price'],
            'sale_price' => !empty($input['sale_price']) ? (float) $input['sale_price'] : null,
            'tax_rate' => !empty($input['tax_rate']) ? (float) $input['tax_rate'] : 0.00,
            'status' => (int) ($input['status'] ?? 1)
        ];
    }

    /**
     * Cập nhật sản phẩm
     * 
     * @param int $id
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateProduct(int $id, array $data): bool
    {
        // Kiểm tra sản phẩm tồn tại
        $product = $this->productModel->find($id);
        if (!$product) {
            throw new Exception('Không tìm thấy sản phẩm');
        }

        // Validate
        $errors = $this->validateProductData($data);
        if (!empty($errors)) {
            throw new Exception(implode(', ', $errors));
        }

        // Kiểm tra SKU trùng (trừ sản phẩm hiện tại)
        if ($this->checkSkuExists($data['sku'], $id)) {
            throw new Exception('Mã SKU đã tồn tại trong hệ thống!');
        }

        // Chuẩn bị và update
        $productData = $this->prepareProductData($data);
        $result = $this->productModel->update($id, $productData);

        // Cập nhật danh mục
        if (!empty($data['category_ids'])) {
            $this->productCategoryModel->assignCategories($id, $data['category_ids']);
        }

        return $result;
    }

    /**
     * Xóa sản phẩm
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            throw new Exception('Không tìm thấy sản phẩm');
        }

        return $this->productModel->delete($id);
    }
}
```

---

#### **2.2. Tạo ImageService.php**

**File**: `src/modules/product/services/ImageService.php`

**Nhiệm vụ**:
- [ ] Di chuyển logic từ `ProductController::handleImageUpload()` sang `ImageService::uploadImages()`
- [ ] Tạo method `ImageService::deleteImage(int $imageId): bool`
- [ ] Tạo method `ImageService::setPrimaryImage(int $imageId, int $productId): bool`
- [ ] Tạo method `ImageService::validateImage(array $file): array`

**Template code**:
```php
<?php

namespace Modules\Product\Services;

use Modules\Product\Models\ProductImageModel;
use Exception;

class ImageService
{
    private ProductImageModel $imageModel;

    public function __construct()
    {
        $this->imageModel = new ProductImageModel();
    }

    /**
     * Upload nhiều hình ảnh cho sản phẩm
     * 
     * @param int $productId ID sản phẩm
     * @param array $files Mảng $_FILES['images']
     * @return array Mảng các URL ảnh đã upload
     * @throws Exception Nếu upload thất bại
     */
    public function uploadImages(int $productId, array $files): array
    {
        $uploadedImages = [];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            // Kiểm tra lỗi upload
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            // Validate file
            $errors = $this->validateImage([
                'name' => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'size' => $files['size'][$i]
            ]);

            if (!empty($errors)) {
                continue;
            }

            // Đọc và convert sang base64
            $imageData = file_get_contents($files['tmp_name'][$i]);
            $base64Data = base64_encode($imageData);
            $fileType = mime_content_type($files['tmp_name'][$i]);

            // Ảnh đầu tiên là ảnh chính
            $isPrimary = ($i === 0 && empty($uploadedImages)) ? 1 : 0;

            // Lưu vào DB
            $imageId = $this->imageModel->create([
                'product_id' => $productId,
                'url' => null,
                'image_data' => $base64Data,
                'mime_type' => $fileType,
                'is_primary' => $isPrimary,
                'sort_order' => $i
            ]);

            if ($imageId) {
                $uploadedImages[] = "data:{$fileType};base64,{$base64Data}";
            }
        }

        return $uploadedImages;
    }

    /**
     * Validate file hình ảnh
     * 
     * @param array $file
     * @return array Mảng lỗi (rỗng nếu hợp lệ)
     */
    private function validateImage(array $file): array
    {
        $errors = [];

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = 'File không phải là hình ảnh hợp lệ';
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Kích thước file vượt quá 5MB';
        }

        return $errors;
    }

    /**
     * Xóa hình ảnh
     * 
     * @param int $imageId
     * @return bool
     * @throws Exception
     */
    public function deleteImage(int $imageId): bool
    {
        $image = $this->imageModel->find($imageId);
        if (!$image) {
            throw new Exception('Không tìm thấy hình ảnh');
        }

        // Xóa file nếu có URL
        if (!empty($image['url'])) {
            $filePath = __DIR__ . '/../../../../public' . $image['url'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        return $this->imageModel->delete($imageId);
    }

    /**
     * Đặt ảnh chính cho sản phẩm
     * 
     * @param int $imageId
     * @param int $productId
     * @return bool
     */
    public function setPrimaryImage(int $imageId, int $productId): bool
    {
        // Bỏ primary của tất cả ảnh khác
        $this->imageModel->removePrimary($productId);

        // Đặt ảnh này là primary
        return $this->imageModel->update($imageId, ['is_primary' => 1]);
    }
}
```

---

#### **2.3. Refactor ProductController mới**

**File**: `src/modules/product/controllers/ProductController.php`

**Nhiệm vụ**:
- [ ] Xóa toàn bộ nội dung cũ (đang rỗng)
- [ ] Viết lại Controller sử dụng ProductService và ImageService
- [ ] Controller chỉ chứa: nhận request → gọi service → trả về view/response

**Template code**:
```php
<?php

namespace Modules\Product\Controllers;

use Core\Controller;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Modules\Product\Services\ProductService;
use Modules\Product\Services\ImageService;
use Models\CategoryModel;
use Models\BrandModel;
use Exception;

/**
 * ProductController - Quản lý sản phẩm (theo MVC Pattern)
 */
class ProductController extends Controller
{
    private ProductService $productService;
    private ImageService $imageService;
    private CategoryModel $categoryModel;
    private BrandModel $brandModel;

    public function __construct()
    {
        parent::__construct();
        $this->productService = new ProductService();
        $this->imageService = new ImageService();
        $this->categoryModel = new CategoryModel();
        $this->brandModel = new BrandModel();
    }

    /**
     * Hiển thị danh sách sản phẩm
     */
    public function index(): void
    {
        $page = (int) ($this->input('page') ?? 1);
        $perPage = 20;

        $filters = [
            'category_id' => $this->input('category_id'),
            'brand_id' => $this->input('brand_id'),
            'keyword' => $this->input('keyword'),
            'status' => $this->input('status'),
            'sort_by' => $this->input('sort_by', 'created_at_desc')
        ];

        $products = $this->productService->getProductsList($filters, $page, $perPage);
        $totalProducts = $this->productService->countProducts($filters);
        $totalPages = ceil($totalProducts / $perPage);

        $categories = $this->categoryModel->getFlatCategoryTree();
        $brands = $this->brandModel->all();

        $this->view('admin/products/index', [
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'filters' => $filters,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalProducts' => $totalProducts
        ]);
    }

    /**
     * Hiển thị form thêm sản phẩm mới
     */
    public function create(): void
    {
        $categories = $this->categoryModel->getFlatCategoryTree();
        $brands = $this->brandModel->all();
        $autoSku = 'PRD-' . strtoupper(uniqid());

        $this->view('admin/products/create', [
            'categories' => $categories,
            'brands' => $brands,
            'autoSku' => $autoSku
        ]);
    }

    /**
     * Xử lý lưu sản phẩm mới
     */
    public function store(): void
    {
        try {
            // Gọi service để tạo sản phẩm
            $productId = $this->productService->createProduct($_POST);

            // Xử lý upload hình ảnh (nếu có)
            if (!empty($_FILES['images']['name'][0])) {
                $uploadedImages = $this->imageService->uploadImages($productId, $_FILES['images']);
                
                if (empty($uploadedImages)) {
                    AuthHelper::setFlash('warning', 'Sản phẩm đã được tạo nhưng không có hình ảnh nào được tải lên');
                }
            }

            // Log hành động
            LogHelper::log('create', 'product', $productId, $_POST);

            AuthHelper::setFlash('success', 'Thêm sản phẩm thành công!');
            $this->redirect('/admin/products');

        } catch (Exception $e) {
            error_log('Error creating product: ' . $e->getMessage());
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/products/create');
        }
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     */
    public function edit(int $id): void
    {
        try {
            $product = $this->productService->getProductWithCategories($id);
            $categories = $this->categoryModel->getFlatCategoryTree();
            $brands = $this->brandModel->all();
            $images = $this->imageService->getProductImages($id);

            $assignedCategoryIds = !empty($product['category_ids']) 
                ? explode(',', $product['category_ids']) 
                : [];

            $this->view('admin/products/edit', [
                'product' => $product,
                'categories' => $categories,
                'brands' => $brands,
                'assignedCategoryIds' => $assignedCategoryIds,
                'images' => $images
            ]);

        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/products');
        }
    }

    /**
     * Xử lý cập nhật sản phẩm
     */
    public function update(int $id): void
    {
        try {
            // Gọi service để update
            $this->productService->updateProduct($id, $_POST);

            // Xử lý upload hình ảnh mới (nếu có)
            if (!empty($_FILES['images']['name'][0])) {
                $this->imageService->uploadImages($id, $_FILES['images']);
            }

            // Log
            LogHelper::log('update', 'product', $id, $_POST);

            AuthHelper::setFlash('success', 'Cập nhật sản phẩm thành công!');
            $this->redirect('/admin/products');

        } catch (Exception $e) {
            error_log('Error updating product: ' . $e->getMessage());
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect("/admin/products/{$id}/edit");
        }
    }

    /**
     * Xóa sản phẩm
     */
    public function destroy(int $id): void
    {
        try {
            // Lấy thông tin sản phẩm để log
            $product = $this->productService->getProduct($id);

            // Xóa tất cả hình ảnh
            $this->imageService->deleteAllProductImages($id);

            // Xóa sản phẩm (cascade sẽ xóa categories)
            $this->productService->deleteProduct($id);

            // Log
            LogHelper::log('delete', 'product', $id, $product);

            AuthHelper::setFlash('success', 'Xóa sản phẩm thành công!');

        } catch (Exception $e) {
            error_log('Error deleting product: ' . $e->getMessage());
            AuthHelper::setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/products');
    }

    /**
     * Xóa hình ảnh sản phẩm (AJAX)
     */
    public function deleteImage(): void
    {
        try {
            $imageId = (int) $this->input('image_id');
            $this->imageService->deleteImage($imageId);
            $this->json(['success' => true, 'message' => 'Đã xóa hình ảnh']);

        } catch (Exception $e) {
            error_log('Error deleting image: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Đặt ảnh chính (AJAX)
     */
    public function setPrimaryImage(): void
    {
        try {
            $imageId = (int) $this->input('image_id');
            $productId = (int) $this->input('product_id');

            $this->imageService->setPrimaryImage($imageId, $productId);
            $this->json(['success' => true, 'message' => 'Đã đặt làm ảnh chính']);

        } catch (Exception $e) {
            error_log('Error setting primary image: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
```

---

#### **2.4. Di chuyển Models**

**Nhiệm vụ**:
- [ ] Di chuyển `src/Models/ProductModel.php` → `src/modules/product/models/ProductModel.php`
- [ ] Di chuyển `src/Models/ProductCategoryModel.php` → `src/modules/product/models/ProductCategoryModel.php`
- [ ] Di chuyển `src/Models/ProductImageModel.php` → `src/modules/product/models/ProductImageModel.php`
- [ ] Đổi namespace từ `Models` → `Modules\Product\Models`

---

#### **2.5. Update Routes**

**File**: `config/routes.php`

**Nhiệm vụ**:
- [ ] Đổi route từ `Controllers\Admin\ProductController` sang `Modules\Product\Controllers\ProductController`

```php
// ❌ Cũ (SAI)
'/admin/products' => ['controller' => 'Controllers\Admin\ProductController', 'action' => 'index'],

// ✅ Mới (ĐÚNG)
'/admin/products' => ['controller' => 'Modules\Product\Controllers\ProductController', 'action' => 'index'],
```

---

#### **2.6. Update Autoload**

**Nhiệm vụ**:
- [ ] Chạy `composer dump-autoload`
- [ ] Test lại tất cả routes của Product

---

### PHASE 3: REFACTOR CÁC MODULE KHÁC (⏱️ 8-12 giờ)

Áp dụng tương tự cho các module còn lại theo thứ tự:

#### **3.1. Module Category**
- [ ] Tạo `CategoryService.php` (đã có rồi ✅)
- [ ] Refactor `src/Controllers/Admin/CategoryController.php` → `src/modules/category/controllers/CategoryController.php`
- [ ] Di chuyển `src/Models/CategoryModel.php` → `src/modules/category/models/CategoryModel.php`
- [ ] Update routes

#### **3.2. Module Brand**
- [ ] Tạo `src/modules/brand/` (mới hoàn toàn)
- [ ] Tạo `BrandService.php`
- [ ] Refactor `BrandController` → module
- [ ] Di chuyển `BrandModel` → module

#### **3.3. Module User**
- [ ] Refactor `src/Controllers/Admin/UsersController.php` → `src/modules/user/controllers/UserController.php`
- [ ] Di chuyển `src/Models/UserModel.php` → `src/modules/user/models/UserModel.php` (hoặc dùng trong auth)
- [ ] Update `UserService` (đã có)

#### **3.4. Module Auth**
- [ ] Refactor `src/Controllers/Admin/AuthController.php` → `src/modules/auth/controllers/AuthController.php`
- [ ] Di chuyển `RoleModel`, `UserModel` vào auth module

#### **3.5. Module Supplier**
- [ ] Tạo `src/modules/supplier/`
- [ ] Tạo `SupplierService.php`
- [ ] Refactor `SupplierController` → module
- [ ] Di chuyển `SupplierModel` → module

#### **3.6. Modules khác (Inventory, Sales, Purchase, Report)**
- Đã đúng cấu trúc ✅ - chỉ cần review code

---

### PHASE 4: XÓA CÁC FILE CŨ (⏱️ 1 giờ)

**⚠️ CHỈ XÓA SAU KHI ĐÃ TEST KỸ TẤT CẢ CHỨC NĂNG**

- [ ] **4.1. Xóa thư mục `src/Controllers/Admin/`**
  ```bash
  # Backup trước
  mkdir -p backup/Controllers
  mv src/Controllers/Admin backup/Controllers/
  
  # Kiểm tra không có lỗi
  # Nếu OK, xóa hẳn
  rm -rf backup/Controllers
  ```

- [ ] **4.2. Xóa các Model trong `src/Models/` đã di chuyển**
  - Giữ lại: `BaseModel.php`, `DatabaseModel.php`
  - Xóa: `ProductModel`, `CategoryModel`, `BrandModel`, `UserModel`, `SupplierModel`, `ProductCategoryModel`, `ProductImageModel`, `RoleModel`, `PasswordResetRequestModel`

- [ ] **4.3. Update `composer.json`**
  - Xóa autoload cho `Controllers\\` và `Models\\` cũ
  - Giữ lại `Modules\\`

---

### PHASE 5: TESTING & DOCUMENTATION (⏱️ 2-3 giờ)

#### **5.1. Test từng module**
- [ ] Test Product: CRUD, upload ảnh, gán category
- [ ] Test Category: CRUD, tree structure
- [ ] Test Brand: CRUD
- [ ] Test User: CRUD, roles
- [ ] Test Auth: Login, logout, register
- [ ] Test Supplier: CRUD

#### **5.2. Test integration**
- [ ] Tạo sản phẩm mới với category và brand
- [ ] Upload nhiều ảnh
- [ ] Update sản phẩm
- [ ] Xóa sản phẩm (cascade)
- [ ] Filter sản phẩm theo category, brand

#### **5.3. Update Documentation**
- [ ] Cập nhật `README.md` với cấu trúc mới
- [ ] Cập nhật `CODING_RULES.md` nếu cần
- [ ] Tạo `REFACTOR_CHANGELOG.md` ghi lại những gì đã thay đổi

---

### PHASE 6: MERGE VÀ DEPLOY (⏱️ 1 giờ)

- [ ] **6.1. Commit**
  ```bash
  git add .
  git commit -m "refactor: fix coding violations - move all to modules structure"
  ```

- [ ] **6.2. Push và tạo PR**
  ```bash
  git push origin refactor/fix-coding-violations
  ```

- [ ] **6.3. Review và merge vào develop**

- [ ] **6.4. Deploy lên staging để test thêm**

- [ ] **6.5. Merge vào main (nếu OK)**

---

## 📝 CHECKLIST CUỐI CÙNG

Trước khi đóng task, kiểm tra:

- [ ] ✅ Tất cả Controllers nằm trong `src/modules/[module]/controllers/`
- [ ] ✅ Tất cả Models nằm trong `src/modules/[module]/models/`
- [ ] ✅ Tất cả Services nằm trong `src/modules/[module]/services/`
- [ ] ✅ Namespace đúng: `Modules\[ModuleName]\[Type]`
- [ ] ✅ Controllers chỉ chứa routing logic (max 20-30 dòng/method)
- [ ] ✅ Business logic nằm trong Service
- [ ] ✅ Không còn hard code
- [ ] ✅ Tất cả comment bằng tiếng Việt, PHPDoc đầy đủ
- [ ] ✅ Code đã tuân thủ 100% `CODING_RULES.md`
- [ ] ✅ Đã test tất cả chức năng
- [ ] ✅ Đã xóa code cũ không dùng nữa
- [ ] ✅ `composer dump-autoload` chạy OK
- [ ] ✅ Không có lỗi PHP warnings/errors
- [ ] ✅ Database schema không thay đổi

---

## 🚀 HƯỚNG DẪN BẮT ĐẦU

### Bước 1: Backup và tạo branch
```bash
cd /d/HocTap/Business-product-management-system
git checkout develop
git pull origin develop
git branch backup-before-refactor-20251108
git push origin backup-before-refactor-20251108
git checkout -b refactor/fix-coding-violations
```

### Bước 2: Bắt đầu với ProductService
```bash
# Tạo file
mkdir -p src/modules/product/services
touch src/modules/product/services/ProductService.php

# Copy template code từ section 2.1 vào file
# Sau đó test ngay
composer dump-autoload
```

### Bước 3: Từng bước theo Phase 2

---

## 📞 LƯU Ý QUAN TRỌNG

1. **KHÔNG được skip bước nào** - Làm tuần tự từ Phase 1 → 6
2. **Test sau mỗi module** - Đừng refactor hết rồi mới test
3. **Commit nhỏ, thường xuyên** - Mỗi module xong là commit
4. **Backup trước khi xóa** - Tạo backup branch trước
5. **Review code sau khi xong** - Kiểm tra lại 1 lượt

---

**Ước tính thời gian tổng**: 20-30 giờ (3-4 ngày làm việc)

**Người thực hiện**: [Tên bạn]  
**Ngày bắt đầu**: __/__/____  
**Ngày hoàn thành dự kiến**: __/__/____

---

## 🎯 MỤC TIÊU CUỐI CÙNG

Sau khi hoàn thành refactor, codebase sẽ:

✅ Tuân thủ 100% `CODING_RULES.md`  
✅ Cấu trúc module rõ ràng, dễ mở rộng  
✅ Logic tách bạch: Controller → Service → Model  
✅ Dễ maintain, dễ test  
✅ Chuẩn MVC pattern  
✅ Namespace chuẩn PSR-4  
✅ Code sạch, dễ đọc, dễ hiểu  

**Good luck! 🚀**
