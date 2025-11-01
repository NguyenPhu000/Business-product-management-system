<?php

namespace Controllers\Admin;

use Core\Controller;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Models\ProductModel;
use Models\BaseModel;
use Models\DatabaseModel;

/**
 * ProductVariantModel - Quản lý biến thể
 */
class ProductVariantModel extends BaseModel
{
    protected string $table = 'product_variants';
}

/**
 * ProductVariantController - Quản lý biến thể sản phẩm
 */
class ProductVariantController extends Controller
{
    private ProductModel $productModel;
    private ProductVariantModel $variantModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->variantModel = new ProductVariantModel();
    }

    /**
     * Hiển thị danh sách biến thể của sản phẩm
     */
    public function index(int $productId): void
    {
        $product = $this->productModel->find($productId);

        if (!$product) {
            AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
            $this->redirect('/admin/products');
            return;
        }

        // Lấy danh sách biến thể
        $variants = $this->variantModel->query(
            "SELECT * FROM product_variants WHERE product_id = ? ORDER BY created_at DESC",
            [$productId]
        );

        $this->view('admin/products/variants/index', [
            'product' => $product,
            'variants' => $variants
        ]);
    }

    /**
     * Hiển thị form thêm biến thể
     */
    public function create(int $productId): void
    {
        $product = $this->productModel->find($productId);

        if (!$product) {
            AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
            $this->redirect('/admin/products');
            return;
        }

        $this->view('admin/products/variants/create', [
            'product' => $product
        ]);
    }

    /**
     * Xử lý lưu biến thể mới
     */
    public function store(int $productId): void
    {
        try {
            $product = $this->productModel->find($productId);

            if (!$product) {
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }

            // Validate
            $errors = $this->validate([
                'sku' => 'required',
                'price' => 'required|numeric',
                'unit_cost' => 'required|numeric'
            ]);

            if (!empty($errors)) {
                $errorMessages = implode('<br>', $errors);
                AuthHelper::setFlash('error', $errorMessages);
                $this->redirect("/admin/products/{$productId}/variants/create");
                return;
            }

            // Chuẩn bị attributes (JSON)
            $attributes = [];
            if ($this->input('color')) {
                $attributes['color'] = $this->input('color');
            }
            if ($this->input('size')) {
                $attributes['size'] = $this->input('size');
            }
            if ($this->input('capacity')) {
                $attributes['capacity'] = $this->input('capacity');
            }
            // Custom attributes
            if ($this->input('custom_attr_name') && $this->input('custom_attr_value')) {
                $attributes[$this->input('custom_attr_name')] = $this->input('custom_attr_value');
            }

            // Tạo biến thể
            $variantData = [
                'product_id' => $productId,
                'sku' => $this->input('sku'),
                'attributes' => json_encode($attributes, JSON_UNESCAPED_UNICODE),
                'price' => (float) $this->input('price'),
                'unit_cost' => (float) $this->input('unit_cost'),
                'barcode' => $this->input('barcode'),
                'is_active' => (int) $this->input('is_active', 1)
            ];

            $sql = "INSERT INTO product_variants (product_id, sku, attributes, price, unit_cost, barcode, is_active, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $this->variantModel->execute($sql, [
                $variantData['product_id'],
                $variantData['sku'],
                $variantData['attributes'],
                $variantData['price'],
                $variantData['unit_cost'],
                $variantData['barcode'],
                $variantData['is_active']
            ]);

            LogHelper::log('create', 'product_variant', $productId, $variantData);

            AuthHelper::setFlash('success', 'Thêm biến thể thành công!');
            $this->redirect("/admin/products/{$productId}/variants");

        } catch (\Exception $e) {
            error_log('Error creating variant: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect("/admin/products/{$productId}/variants/create");
        }
    }

    /**
     * Xóa biến thể
     */
    public function destroy(int $productId, int $variantId): void
    {
        try {
            $sql = "DELETE FROM product_variants WHERE id = ? AND product_id = ?";
            $this->variantModel->execute($sql, [$variantId, $productId]);

            LogHelper::log('delete', 'product_variant', $variantId);

            AuthHelper::setFlash('success', 'Đã xóa biến thể');
            $this->redirect("/admin/products/{$productId}/variants");

        } catch (\Exception $e) {
            error_log('Error deleting variant: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra');
            $this->redirect("/admin/products/{$productId}/variants");
        }
    }
}
