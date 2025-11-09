<?php

namespace Modules\Product\Services;

use Models\ProductModel;
use Models\ProductCategoryModel;
use Exception;

/**
 * ProductService - Xử lý business logic cho sản phẩm
 */
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
