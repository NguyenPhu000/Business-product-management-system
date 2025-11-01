<?php

namespace Controllers\Admin;

use Core\Controller;
use Core\Request;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Models\ProductModel;
use Models\ProductCategoryModel;
use Models\ProductImageModel;
use Models\CategoryModel;
use Models\BrandModel;

/**
 * ProductController - Quản lý sản phẩm
 */
class ProductController extends Controller
{
    private ProductModel $productModel;
    private ProductCategoryModel $productCategoryModel;
    private ProductImageModel $productImageModel;
    private CategoryModel $categoryModel;
    private BrandModel $brandModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productCategoryModel = new ProductCategoryModel();
        $this->productImageModel = new ProductImageModel();
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

        // Lấy bộ lọc
        $filters = [
            'category_id' => $this->input('category_id'),
            'brand_id' => $this->input('brand_id'),
            'keyword' => $this->input('keyword'),
            'status' => $this->input('status'),
            'sort_by' => $this->input('sort_by', 'created_at_desc')
        ];

        // Lấy danh sách sản phẩm
        $products = $this->productModel->getProductsList($filters, $page, $perPage);
        $totalProducts = $this->productModel->countProducts($filters);
        $totalPages = ceil($totalProducts / $perPage);

        // Lấy danh mục và thương hiệu để filter (dạng phẳng)
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
        // Lấy danh sách danh mục và thương hiệu (dạng phẳng để hiển thị checkbox)
        $categories = $this->categoryModel->getFlatCategoryTree();
        $brands = $this->brandModel->all();

        // Tạo SKU tự động (có thể edit)
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
            // Validate dữ liệu
            $errors = $this->validate([
                'sku' => 'required',
                'name' => 'required|min:3',
                'brand_id' => 'required|numeric',
                'unit' => 'required',
                'unit_cost' => 'required|numeric',
                'price' => 'required|numeric'
            ]);

            // Kiểm tra category_ids riêng vì là array
            if (empty($_POST['category_ids'])) {
                $errors['category_ids'] = 'Vui lòng chọn ít nhất một danh mục';
            }

            // Validate giá bán phải >= giá nhập
            $unitCost = (float) $this->input('unit_cost', 0);
            $price = (float) $this->input('price', 0);
            $salePrice = $this->input('sale_price') ? (float) $this->input('sale_price') : null;

            if ($price < $unitCost) {
                $errors['price'] = 'Giá bán phải lớn hơn hoặc bằng giá nhập';
            }

            if ($salePrice && $salePrice >= $price) {
                $errors['sale_price'] = 'Giá khuyến mãi phải nhỏ hơn giá bán';
            }

            if (!empty($errors)) {
                $errorMessages = implode('<br>', $errors);
                AuthHelper::setFlash('error', $errorMessages);
                $this->redirect('/admin/products/create');
                return;
            }

            // Kiểm tra SKU trùng
            if ($this->productModel->skuExists($this->input('sku'))) {
                AuthHelper::setFlash('error', 'Mã SKU đã tồn tại trong hệ thống!');
                $this->redirect('/admin/products/create');
                return;
            }

            // Chuẩn bị dữ liệu sản phẩm
            $productData = [
                'sku' => $this->input('sku'),
                'name' => $this->input('name'),
                'short_desc' => $this->input('short_desc'),
                'long_desc' => $this->input('long_desc'),
                'brand_id' => (int) $this->input('brand_id'),
                'unit' => $this->input('unit'),
                'unit_cost' => $unitCost,
                'price' => $price,
                'sale_price' => $salePrice,
                'tax_rate' => $this->input('tax_rate') ? (float) $this->input('tax_rate') : 0.00,
                'status' => (int) $this->input('status', 1)
            ];

            // Lưu sản phẩm
            $productId = $this->productModel->create($productData);

            if (!$productId) {
                throw new \Exception('Không thể tạo sản phẩm');
            }

            // Gán danh mục cho sản phẩm
            $categoryIds = $this->input('category_ids', []);
            if (!empty($categoryIds)) {
                $this->productCategoryModel->assignCategories($productId, $categoryIds);
            }

            // Xử lý upload hình ảnh
            if (!empty($_FILES['images']['name'][0])) {
                $uploadedImages = $this->handleImageUpload($productId);
                
                if (empty($uploadedImages)) {
                    AuthHelper::setFlash('warning', 'Sản phẩm đã được tạo nhưng không có hình ảnh nào được tải lên');
                }
            }

            // Log hành động
            LogHelper::log('create', 'product', $productId, $productData);

            AuthHelper::setFlash('success', 'Thêm sản phẩm thành công!');
            $this->redirect('/admin/products');

        } catch (\Exception $e) {
            error_log('Error creating product: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('/admin/products/create');
        }
    }

    /**
     * Hiển thị form chỉnh sửa sản phẩm
     */
    public function edit(int $id): void
    {
        $product = $this->productModel->getWithCategories($id);

        if (!$product) {
            AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
            $this->redirect('/admin/products');
            return;
        }

        // Lấy danh mục và thương hiệu (dạng phẳng)
        $categories = $this->categoryModel->getFlatCategoryTree();
        $brands = $this->brandModel->all();

        // Lấy danh mục đã gán
        $assignedCategoryIds = !empty($product['category_ids']) 
            ? explode(',', $product['category_ids']) 
            : [];

        // Lấy hình ảnh sản phẩm
        $images = $this->productImageModel->getByProduct($id);

        $this->view('admin/products/edit', [
            'product' => $product,
            'categories' => $categories,
            'brands' => $brands,
            'assignedCategoryIds' => $assignedCategoryIds,
            'images' => $images
        ]);
    }

    /**
     * Xử lý cập nhật sản phẩm
     */
    public function update(int $id): void
    {
        try {
            $product = $this->productModel->find($id);

            if (!$product) {
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }

            // Validate
            $errors = $this->validate([
                'sku' => 'required',
                'name' => 'required|min:3',
                'brand_id' => 'required|numeric',
                'unit' => 'required',
                'unit_cost' => 'required|numeric',
                'price' => 'required|numeric'
            ]);

            // Kiểm tra category_ids riêng
            if (empty($_POST['category_ids'])) {
                $errors['category_ids'] = 'Vui lòng chọn ít nhất một danh mục';
            }

            // Validate giá bán phải >= giá nhập
            $unitCost = (float) $this->input('unit_cost', 0);
            $price = (float) $this->input('price', 0);
            $salePrice = $this->input('sale_price') ? (float) $this->input('sale_price') : null;

            if ($price < $unitCost) {
                $errors['price'] = 'Giá bán phải lớn hơn hoặc bằng giá nhập';
            }

            if ($salePrice && $salePrice >= $price) {
                $errors['sale_price'] = 'Giá khuyến mãi phải nhỏ hơn giá bán';
            }

            if (!empty($errors)) {
                $errorMessages = implode('<br>', $errors);
                AuthHelper::setFlash('error', $errorMessages);
                $this->redirect("/admin/products/{$id}/edit");
                return;
            }

            // Kiểm tra SKU trùng (ngoại trừ sản phẩm hiện tại)
            if ($this->productModel->skuExists($this->input('sku'), $id)) {
                AuthHelper::setFlash('error', 'Mã SKU đã tồn tại trong hệ thống!');
                $this->redirect("/admin/products/{$id}/edit");
                return;
            }

            // Cập nhật dữ liệu sản phẩm
            $productData = [
                'sku' => $this->input('sku'),
                'name' => $this->input('name'),
                'short_desc' => $this->input('short_desc'),
                'long_desc' => $this->input('long_desc'),
                'brand_id' => (int) $this->input('brand_id'),
                'unit' => $this->input('unit'),
                'unit_cost' => $unitCost,
                'price' => $price,
                'sale_price' => $salePrice,
                'tax_rate' => $this->input('tax_rate') ? (float) $this->input('tax_rate') : 0.00,
                'status' => (int) $this->input('status', 1)
            ];

            $this->productModel->update($id, $productData);

            // Cập nhật danh mục
            $categoryIds = $this->input('category_ids', []);
            $this->productCategoryModel->assignCategories($id, $categoryIds);

            // Xử lý upload hình ảnh mới
            if (!empty($_FILES['images']['name'][0])) {
                $this->handleImageUpload($id);
            }

            // Log hành động
            LogHelper::log('update', 'product', $id, $productData);

            AuthHelper::setFlash('success', 'Cập nhật sản phẩm thành công!');
            $this->redirect('/admin/products');

        } catch (\Exception $e) {
            error_log('Error updating product: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect("/admin/products/{$id}/edit");
        }
    }

    /**
     * Xóa sản phẩm
     */
    public function destroy(int $id): void
    {
        error_log("=== START DELETE PRODUCT ID: $id ===");
        
        try {
            error_log("Step 1: Finding product...");
            $product = $this->productModel->find($id);

            if (!$product) {
                error_log("ERROR: Product not found");
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }
            
            error_log("Step 2: Product found: " . $product['name']);

            // Xóa hình ảnh trên server (chỉ xóa file nếu có URL, bỏ qua base64)
            error_log("Step 3: Getting images...");
            $images = $this->productImageModel->getByProduct($id);
            error_log("Found " . count($images) . " images");
            
            foreach ($images as $image) {
                // Chỉ xóa file nếu có URL (ảnh cũ), không xóa ảnh base64
                if (!empty($image['url'])) {
                    error_log("Deleting image file: " . $image['url']);
                    $this->deleteImageFile($image['url']);
                } else {
                    error_log("Skipping base64 image ID: " . $image['id']);
                }
            }

            // Xóa sản phẩm (cascade sẽ xóa categories, images trong DB)
            error_log("Step 4: Deleting product from database...");
            $result = $this->productModel->delete($id);
            error_log("Delete result: " . ($result ? 'SUCCESS' : 'FAILED'));

            // Log
            error_log("Step 5: Logging action...");
            LogHelper::log('delete', 'product', $id, $product);

            error_log("Step 6: Setting flash message...");
            AuthHelper::setFlash('success', 'Xóa sản phẩm thành công!');
            
            error_log("=== END DELETE PRODUCT ID: $id (SUCCESS) ===");

        } catch (\Exception $e) {
            error_log('ERROR deleting product: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }

        error_log("Redirecting to /admin/products");
        $this->redirect('/admin/products');
    }

    /**
     * Bật/tắt trạng thái sản phẩm
     */
    public function toggle(int $id): void
    {
        try {
            $product = $this->productModel->find($id);

            if (!$product) {
                $this->json(['success' => false, 'message' => 'Không tìm thấy sản phẩm']);
                return;
            }

            $newStatus = $product['status'] == 1 ? 0 : 1;
            $this->productModel->update($id, ['status' => $newStatus]);

            // Log
            LogHelper::log('update', 'product', $id, ['old_status' => $product['status'], 'new_status' => $newStatus]);

            $message = $newStatus == 1 ? 'Đã kích hoạt sản phẩm' : 'Đã ẩn sản phẩm';
            $this->json(['success' => true, 'message' => $message]);

        } catch (\Exception $e) {
            error_log('Error toggling product status: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Xử lý upload nhiều hình ảnh
     */
    private function handleImageUpload(int $productId): array
    {
        $uploadedImages = [];

        // Xử lý từng file
        $fileCount = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            // Kiểm tra lỗi upload
            if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $fileName = $_FILES['images']['name'][$i];
            $fileTmpName = $_FILES['images']['tmp_name'][$i];
            $fileSize = $_FILES['images']['size'][$i];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($fileTmpName);

            if (!in_array($fileType, $allowedTypes)) {
                continue;
            }

            // Validate file size (max 5MB)
            if ($fileSize > 5 * 1024 * 1024) {
                continue;
            }

            // Đọc file và convert sang base64
            $imageData = file_get_contents($fileTmpName);
            $base64Data = base64_encode($imageData);
            
            // Ảnh đầu tiên là ảnh chính
            $isPrimary = ($i === 0 && empty($uploadedImages)) ? 1 : 0;

            // Lưu vào database (base64 + mime type)
            $imageId = $this->productImageModel->create([
                'product_id' => $productId,
                'url' => null, // Không cần lưu URL nữa
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
     * Xóa file hình ảnh
     */
    private function deleteImageFile(string $imageUrl): bool
    {
        $filePath = __DIR__ . '/../../../public' . $imageUrl;
        
        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }

    /**
     * Xóa hình ảnh sản phẩm (AJAX)
     */
    public function deleteImage(): void
    {
        try {
            $imageId = (int) $this->input('image_id');
            
            if (!$imageId) {
                $this->json(['success' => false, 'message' => 'ID hình ảnh không hợp lệ']);
                return;
            }

            $image = $this->productImageModel->find($imageId);

            if (!$image) {
                $this->json(['success' => false, 'message' => 'Không tìm thấy hình ảnh']);
                return;
            }

            // Xóa file
            $this->deleteImageFile($image['url']);

            // Xóa record trong DB
            $this->productImageModel->delete($imageId);

            $this->json(['success' => true, 'message' => 'Đã xóa hình ảnh']);

        } catch (\Exception $e) {
            error_log('Error deleting image: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra']);
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

            if (!$imageId || !$productId) {
                $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                return;
            }

            // Bỏ primary của tất cả ảnh khác
            $this->productImageModel->removePrimary($productId);

            // Đặt ảnh này là primary
            $this->productImageModel->update($imageId, ['is_primary' => 1]);

            $this->json(['success' => true, 'message' => 'Đã đặt làm ảnh chính']);

        } catch (\Exception $e) {
            error_log('Error setting primary image: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }
}