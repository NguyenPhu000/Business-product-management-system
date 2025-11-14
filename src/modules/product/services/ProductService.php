<?php

namespace Modules\Product\Services;

use Modules\Product\Models\ProductModel;
use Modules\Product\Models\ProductCategoryModel;
use Modules\Product\Models\VariantModel;
use Modules\Category\Models\CategoryModel;
use Modules\Inventory\Services\InventoryService;
use Exception;

/**
 * ProductService - Xử lý business logic cho sản phẩm
 */
class ProductService
{
    private ProductModel $productModel;
    private ProductCategoryModel $productCategoryModel;
    private CategoryModel $categoryModel;
    private VariantModel $variantModel;
    private InventoryService $inventoryService;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->productCategoryModel = new ProductCategoryModel();
        $this->categoryModel = new CategoryModel();
        $this->variantModel = new VariantModel();
        $this->inventoryService = new InventoryService();
    }

    /**
     * Lấy danh sách sản phẩm với filter và pagination
     * 
     * @param array $filters Bộ lọc (category_id, brand_id, keyword, status, sort_by)
     * @param int $page Trang hiện tại
     * @param int $perPage Số items mỗi trang
     * @return array Danh sách sản phẩm
     */
    public function getProductsList(array $filters, int $page, int $perPage): array
    {
        return $this->productModel->getProductsList($filters, $page, $perPage);
    }

    /**
     * Đếm tổng số sản phẩm theo filter
     * 
     * @param array $filters Bộ lọc
     * @return int Tổng số sản phẩm
     */
    public function countProducts(array $filters): int
    {
        return $this->productModel->countProducts($filters);
    }

    /**
     * Lấy sản phẩm kèm danh mục
     * 
     * @param int $id ID sản phẩm
     * @return array|null Thông tin sản phẩm hoặc null
     */
    public function getProductWithCategories(int $id): ?array
    {
        return $this->productModel->getWithCategories($id);
    }

    /**
     * Lấy sản phẩm kèm thông tin tồn kho
     * 
     * @param int $id ID sản phẩm
     * @return array|null Thông tin sản phẩm + inventory
     */
    public function getProductWithInventory(int $id): ?array
    {
        $product = $this->productModel->getWithCategories($id);
        if (!$product) {
            return null;
        }

        // Lấy danh sách variants
        $variants = $this->variantModel->getByProductId($id);

        // Lấy thông tin tồn kho cho từng variant
        foreach ($variants as &$variant) {
            try {
                $inventory = $this->inventoryService->getStockInfo($variant['id']);
                $variant['inventory'] = $inventory;
                $variant['total_stock'] = !empty($inventory) ? array_sum(array_column($inventory, 'quantity')) : 0;
            } catch (Exception $e) {
                // Nếu lỗi, set default
                $variant['inventory'] = [];
                $variant['total_stock'] = 0;
                error_log("Error loading inventory for variant {$variant['id']}: " . $e->getMessage());
            }
        }

        $product['variants'] = $variants;

        return $product;
    }

    /**
     * Lấy thông tin sản phẩm
     * 
     * @param int $id ID sản phẩm
     * @return array|null
     */
    public function getProduct(int $id): ?array
    {
        return $this->productModel->find($id);
    }

    /**
     * Tạo sản phẩm mới
     * 
     * @param array $data Dữ liệu từ form
     * @return int ID sản phẩm vừa tạo
     * @throws Exception Nếu validation fail hoặc lỗi DB
     */
    public function createProduct(array $data): int
    {
        // Validate dữ liệu
        $errors = $this->validateProductData($data);
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }

        // Kiểm tra SKU trùng
        if ($this->checkSkuExists($data['sku'])) {
            throw new Exception('Mã SKU đã tồn tại trong hệ thống!');
        }

        // Chuẩn bị dữ liệu
        $productData = $this->prepareProductData($data);

        // Lưu sản phẩm
        $productId = $this->productModel->create($productData);

        if (!$productId) {
            throw new Exception('Không thể tạo sản phẩm');
        }

        // Gán danh mục cho sản phẩm
        if (!empty($data['category_ids'])) {
            $this->productCategoryModel->assignCategories($productId, $data['category_ids']);
        }

        return $productId;
    }

    /**
     * Cập nhật sản phẩm
     * 
     * @param int $id ID sản phẩm
     * @param array $data Dữ liệu từ form
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

        // Validate dữ liệu
        $errors = $this->validateProductData($data);
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }

        // Kiểm tra SKU trùng (ngoại trừ sản phẩm hiện tại)
        if ($this->checkSkuExists($data['sku'], $id)) {
            throw new Exception('Mã SKU đã tồn tại trong hệ thống!');
        }

        // Chuẩn bị và cập nhật dữ liệu
        $productData = $this->prepareProductData($data);
        $result = $this->productModel->update($id, $productData);

        // Cập nhật danh mục
        if (isset($data['category_ids'])) {
            $this->productCategoryModel->assignCategories($id, $data['category_ids']);
        }

        return $result;
    }

    /**
     * Xóa sản phẩm
     * 
     * @param int $id ID sản phẩm
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

    /**
     * Toggle trạng thái sản phẩm
     * 
     * @param int $id ID sản phẩm
     * @return array ['old_status' => int, 'new_status' => int]
     * @throws Exception
     */
    public function toggleStatus(int $id): array
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            throw new Exception('Không tìm thấy sản phẩm');
        }

        $newStatus = $product['status'] == 1 ? 0 : 1;
        $this->productModel->update($id, ['status' => $newStatus]);

        return [
            'old_status' => $product['status'],
            'new_status' => $newStatus
        ];
    }

    /**
     * Validate dữ liệu sản phẩm
     * 
     * @param array $data Dữ liệu từ form
     * @return array Mảng lỗi (rỗng nếu hợp lệ)
     */
    private function validateProductData(array $data): array
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

        // Business rules - Giá bán phải >= giá nhập
        $unitCost = (float) ($data['unit_cost'] ?? 0);
        $price = (float) ($data['price'] ?? 0);
        $salePrice = !empty($data['sale_price']) ? (float) $data['sale_price'] : null;

        if ($price < $unitCost) {
            $errors['price'] = 'Giá bán phải lớn hơn hoặc bằng giá nhập';
        }

        if ($salePrice && $salePrice >= $price) {
            $errors['sale_price'] = 'Giá khuyến mãi phải nhỏ hơn giá bán';
        }

        // Kiểm tra category_ids
        if (empty($data['category_ids']) || !is_array($data['category_ids'])) {
            $errors['category_ids'] = 'Vui lòng chọn ít nhất một danh mục';
        } else {
            // Validate: Chỉ được chọn 1 danh mục cha và các con của nó
            $categoryValidation = $this->validateCategorySelection($data['category_ids']);
            if (!$categoryValidation['valid']) {
                $errors['category_ids'] = $categoryValidation['message'];
            }
        }

        return $errors;
    }

    /**
     * Kiểm tra SKU đã tồn tại chưa
     * 
     * @param string $sku Mã SKU
     * @param int|null $excludeId ID sản phẩm bỏ qua (khi update)
     * @return bool
     */
    private function checkSkuExists(string $sku, ?int $excludeId = null): bool
    {
        return $this->productModel->skuExists($sku, $excludeId);
    }

    /**
     * Validate lựa chọn danh mục: chỉ cho phép chọn 1 danh mục cha và các con của nó
     * 
     * @param array $categoryIds Mảng ID danh mục đã chọn
     * @return array ['valid' => bool, 'message' => string]
     */
    private function validateCategorySelection(array $categoryIds): array
    {
        // Lấy thông tin tất cả các category đã chọn
        $selectedCategories = [];
        foreach ($categoryIds as $categoryId) {
            $category = $this->categoryModel->find((int) $categoryId);
            if ($category) {
                $selectedCategories[] = $category;
            }
        }

        // Lọc các danh mục cha (parent_id = null hoặc 0)
        $parentCategories = array_filter($selectedCategories, function ($cat) {
            return empty($cat['parent_id']) || $cat['parent_id'] == 0;
        });

        // Kiểm tra: chỉ được chọn tối đa 1 danh mục cha
        if (count($parentCategories) > 1) {
            return [
                'valid' => false,
                'message' => 'Không được chọn nhiều hơn 1 danh mục cha! Vui lòng chỉ chọn 1 danh mục cha và các danh mục con của nó.'
            ];
        }

        // Nếu có chọn danh mục cha
        if (count($parentCategories) === 1) {
            $parentCategory = reset($parentCategories);
            $parentId = $parentCategory['id'];

            // Kiểm tra tất cả các danh mục con phải thuộc parent này
            $childCategories = array_filter($selectedCategories, function ($cat) {
                return !empty($cat['parent_id']) && $cat['parent_id'] != 0;
            });

            foreach ($childCategories as $childCategory) {
                if ($childCategory['parent_id'] != $parentId) {
                    return [
                        'valid' => false,
                        'message' => 'Các danh mục con phải thuộc về cùng một danh mục cha! Danh mục "' .
                            htmlspecialchars($childCategory['name']) . '" không thuộc danh mục cha "' .
                            htmlspecialchars($parentCategory['name']) . '".'
                    ];
                }
            }
        } else {
            // Nếu không chọn danh mục cha nào, chỉ chọn danh mục con
            // Kiểm tra tất cả danh mục con phải có cùng parent_id
            $childCategories = array_filter($selectedCategories, function ($cat) {
                return !empty($cat['parent_id']) && $cat['parent_id'] != 0;
            });

            if (count($childCategories) > 0) {
                // Lấy parent_id của danh mục con đầu tiên
                $firstChild = reset($childCategories);
                $expectedParentId = $firstChild['parent_id'];

                // Kiểm tra tất cả các danh mục con khác có cùng parent_id không
                foreach ($childCategories as $childCategory) {
                    if ($childCategory['parent_id'] != $expectedParentId) {
                        return [
                            'valid' => false,
                            'message' => 'Các danh mục con phải thuộc về cùng một danh mục cha!'
                        ];
                    }
                }
            }
        }

        return [
            'valid' => true,
            'message' => ''
        ];
    }

    /**
     * Chuẩn bị dữ liệu sản phẩm trước khi lưu DB
     * 
     * @param array $data Dữ liệu từ form
     * @return array Dữ liệu đã chuẩn hóa
     */
    private function prepareProductData(array $data): array
    {
        return [
            'sku' => $data['sku'],
            'name' => $data['name'],
            'short_desc' => $data['short_desc'] ?? null,
            'long_desc' => $data['long_desc'] ?? null,
            'brand_id' => (int) $data['brand_id'],
            'unit' => $data['unit'],
            'unit_cost' => (float) $data['unit_cost'],
            'price' => (float) $data['price'],
            'sale_price' => !empty($data['sale_price']) ? (float) $data['sale_price'] : null,
            'tax_rate' => !empty($data['tax_rate']) ? (float) $data['tax_rate'] : 0.00,
            'status' => (int) ($data['status'] ?? 1)
        ];
    }
}
