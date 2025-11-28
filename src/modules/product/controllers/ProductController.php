<?php

namespace Modules\Product\Controllers;

use Core\Controller;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Modules\Product\Services\ProductService;
use Modules\Product\Services\ImageService;
use Modules\Category\Models\CategoryModel;
use Modules\Category\Models\BrandModel;
use Exception;

/**
 * ProductController - Quản lý sản phẩm (theo MVC Pattern)
 * Controller chỉ xử lý routing, gọi service và trả về view
 */
class ProductController extends Controller
{
    private ProductService $productService;
    private ImageService $imageService;
    private CategoryModel $categoryModel;
    private BrandModel $brandModel;

    public function __construct()
    {
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

        // Lấy bộ lọc
        $filters = [
            'category_id' => $this->input('category_id'),
            'brand_id' => $this->input('brand_id'),
            'keyword' => $this->input('keyword'),
            'status' => $this->input('status'),
            'sort_by' => $this->input('sort_by', 'created_at_desc')
        ];

        // Gọi service để lấy dữ liệu
        $products = $this->productService->getProductsList($filters, $page, $perPage);
        $totalProducts = $this->productService->countProducts($filters);
        $totalPages = ceil($totalProducts / $perPage);

        // Lấy danh mục và thương hiệu để filter
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
            // Lấy sản phẩm kèm thông tin inventory
            $product = $this->productService->getProductWithInventory($id);
            $product = $this->productService->getProductWithCategories($id);

            if (!$product) {
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }

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
            error_log('Error loading product edit form: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
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
            $this->redirect("/admin/products/edit/{$id}");
        }
    }

    /**
     * Xóa sản phẩm
     */
    public function destroy(int $id): void
    {
        error_log("=== START DELETE PRODUCT ID: $id ===");

        try {
            // Lấy thông tin sản phẩm để log
            $product = $this->productService->getProduct($id);

            if (!$product) {
                error_log("ERROR: Product not found");
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }

            error_log("Step 2: Product found: " . $product['name']);

            // Xóa tất cả hình ảnh
            error_log("Step 3: Deleting all product images...");
            $this->imageService->deleteAllProductImages($id);

            // Xóa sản phẩm (cascade sẽ xóa categories)
            error_log("Step 4: Deleting product from database...");
            $this->productService->deleteProduct($id);

            // Log
            error_log("Step 5: Logging action...");
            LogHelper::log('delete', 'product', $id, $product);

            AuthHelper::setFlash('success', 'Xóa sản phẩm thành công!');
            error_log("=== END DELETE PRODUCT ID: $id (SUCCESS) ===");
        } catch (Exception $e) {
            error_log('ERROR deleting product: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }

        error_log("Redirecting to /admin/products");
        $this->redirect('/admin/products');
    }

    /**
     * Bật/tắt trạng thái sản phẩm (AJAX)
     */
    public function toggle(int $id): void
    {
        try {
            $result = $this->productService->toggleStatus($id);

            // Log
            LogHelper::log('update', 'product', $id, $result);

            $message = $result['new_status'] == 1 ? 'Đã kích hoạt sản phẩm' : 'Đã ẩn sản phẩm';
            $this->json(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            error_log('Error toggling product status: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
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

            if (!$imageId || !$productId) {
                $this->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
                return;
            }

            $this->imageService->setPrimaryImage($imageId, $productId);
            $this->json(['success' => true, 'message' => 'Đã đặt làm ảnh chính']);
        } catch (Exception $e) {
            error_log('Error setting primary image: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
