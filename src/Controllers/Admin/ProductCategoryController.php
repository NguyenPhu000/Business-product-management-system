<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\ProductModel;
use Models\CategoryModel;
use Models\ProductCategoryModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * ProductCategoryController - Quản lý gán sản phẩm vào danh mục
 */
class ProductCategoryController extends Controller
{
    private ProductModel $productModel;
    private CategoryModel $categoryModel;
    private ProductCategoryModel $productCategoryModel;

    public function __construct()
    {
        // Kiểm tra xem ProductModel đã tồn tại chưa
        if (!class_exists('Models\ProductModel')) {
            // Tạo ProductModel cơ bản nếu chưa có
            $this->createBasicProductModel();
        }
        
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->productCategoryModel = new ProductCategoryModel();
    }

    /**
     * Hiển thị form gán danh mục cho sản phẩm
     */
    public function manage(int $productId): void
    {
        $product = $this->productModel->find($productId);

        if (!$product) {
            AuthHelper::setFlash('error', 'Sản phẩm không tồn tại');
            $this->redirect('/admin/products');
            return;
        }

        // Lấy tất cả danh mục (cây phân cấp)
        $categoryTree = $this->categoryModel->getCategoryTree();
        
        // Lấy danh mục đã gán cho sản phẩm
        $assignedCategoryIds = $this->productCategoryModel->getCategoryIdsByProduct($productId);

        $this->view('admin/products/manage-categories', [
            'product' => $product,
            'categoryTree' => $categoryTree,
            'assignedCategoryIds' => $assignedCategoryIds,
            'pageTitle' => 'Gán danh mục: ' . $product['name']
        ]);
    }

    /**
     * Xử lý cập nhật danh mục cho sản phẩm
     */
    public function update(int $productId): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
            return;
        }

        $product = $this->productModel->find($productId);

        if (!$product) {
            AuthHelper::setFlash('error', 'Sản phẩm không tồn tại');
            $this->redirect('/admin/products');
            return;
        }

        // Lấy danh sách category IDs từ form (checkbox)
        $categoryIds = $this->input('category_ids', []);
        
        // Đảm bảo là mảng
        if (!is_array($categoryIds)) {
            $categoryIds = [];
        }

        // Chuyển đổi sang integer
        $categoryIds = array_map('intval', $categoryIds);

        // Gán danh mục cho sản phẩm
        $success = $this->productCategoryModel->assignCategories($productId, $categoryIds);

        if ($success) {
            // Ghi log
            LogHelper::log('update_categories', 'product', $productId, [
                'category_ids' => $categoryIds,
                'category_count' => count($categoryIds)
            ]);
            
            AuthHelper::setFlash('success', 'Cập nhật danh mục thành công! Đã gán ' . count($categoryIds) . ' danh mục.');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/products/manage-categories/' . $productId);
    }

    /**
     * API: Thêm sản phẩm vào 1 danh mục
     */
    public function addToCategory(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $productId = (int) $this->input('product_id');
        $categoryId = (int) $this->input('category_id');

        if (!$productId || !$categoryId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $success = $this->productCategoryModel->addCategory($productId, $categoryId);

        if ($success) {
            $this->json(['success' => true, 'message' => 'Đã thêm vào danh mục']);
        } else {
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * API: Xóa sản phẩm khỏi danh mục
     */
    public function removeFromCategory(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $productId = (int) $this->input('product_id');
        $categoryId = (int) $this->input('category_id');

        if (!$productId || !$categoryId) {
            $this->json(['success' => false, 'message' => 'Thiếu thông tin']);
            return;
        }

        $success = $this->productCategoryModel->removeCategory($productId, $categoryId);

        if ($success) {
            $this->json(['success' => true, 'message' => 'Đã xóa khỏi danh mục']);
        } else {
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Tạo ProductModel cơ bản nếu chưa có
     */
    private function createBasicProductModel(): void
    {
        $modelCode = <<<'PHP'
<?php

namespace Models;

class ProductModel extends BaseModel
{
    protected string $table = 'products';
    protected string $primaryKey = 'id';
}
PHP;

        $modelPath = __DIR__ . '/../../Models/ProductModel.php';
        if (!file_exists($modelPath)) {
            file_put_contents($modelPath, $modelCode);
        }
    }
}
