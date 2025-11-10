<?php

/**
 * VariantController.php - Quản lý biến thể sản phẩm (size, màu, ...)
 */

namespace Modules\Product\Controllers;

use Core\Controller;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Modules\Product\Models\ProductModel;
use Modules\Product\Models\VariantModel;
use Modules\Inventory\Services\InventoryService;
use Exception;

class VariantController extends Controller
{
    private ProductModel $productModel;
    private VariantModel $variantModel;
    private InventoryService $inventoryService;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->variantModel = new VariantModel();
        $this->inventoryService = new InventoryService();
    }

    /**
     * Hiển thị danh sách variants của sản phẩm
     */
    public function index(int $id): void
    {
        try {
            $product = $this->productModel->find($id);
            if (!$product) {
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }

            $variants = $this->variantModel->getByProductId($id);

            // Load inventory info cho từng variant
            foreach ($variants as &$variant) {
                try {
                    $inventory = $this->inventoryService->getStockInfo($variant['id']);
                    $variant['total_stock'] = !empty($inventory) ? array_sum(array_column($inventory, 'quantity')) : 0;
                } catch (Exception $e) {
                    $variant['total_stock'] = 0;
                }
            }

            $this->view('admin/products/variants/index', [
                'product' => $product,
                'variants' => $variants
            ]);
        } catch (Exception $e) {
            error_log('Error loading variants: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('/admin/products');
        }
    }

    /**
     * Hiển thị form tạo variant mới
     */
    public function create(int $id): void
    {
        try {
            $product = $this->productModel->find($id);
            if (!$product) {
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }

            // Tạo SKU auto
            $autoSku = $product['sku'] . '-V' . strtoupper(substr(uniqid(), -6));

            $this->view('admin/products/variants/create', [
                'product' => $product,
                'autoSku' => $autoSku
            ]);
        } catch (Exception $e) {
            error_log('Error loading variant create form: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect('/admin/products');
        }
    }

    /**
     * Xử lý tạo variant mới
     */
    public function store(int $id): void
    {
        try {
            $product = $this->productModel->find($id);
            if (!$product) {
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }

            // Validate
            $sku = trim($_POST['sku'] ?? '');
            if (empty($sku)) {
                throw new Exception('SKU không được để trống');
            }

            // Check SKU trùng
            if ($this->variantModel->skuExists($sku, $id)) {
                throw new Exception('SKU variant đã tồn tại cho sản phẩm này');
            }

            // Chuẩn bị attributes từ form
            $attributes = [];

            // Các thuộc tính chuẩn
            if (!empty($_POST['color'])) {
                $attributes['Màu sắc'] = trim($_POST['color']);
            }
            if (!empty($_POST['size'])) {
                $attributes['Kích thước'] = trim($_POST['size']);
            }
            if (!empty($_POST['capacity'])) {
                $attributes['Dung lượng'] = trim($_POST['capacity']);
            }

            // Thuộc tính tùy chỉnh
            if (!empty($_POST['custom_attr_name']) && !empty($_POST['custom_attr_value'])) {
                $attributes[trim($_POST['custom_attr_name'])] = trim($_POST['custom_attr_value']);
            }

            // Tạo variant
            $data = [
                'product_id' => $id,
                'sku' => $sku,
                'attributes' => !empty($attributes) ? json_encode($attributes, JSON_UNESCAPED_UNICODE) : null,
                'price' => floatval($_POST['price'] ?? 0),
                'unit_cost' => floatval($_POST['unit_cost'] ?? 0),
                'barcode' => trim($_POST['barcode'] ?? '') ?: null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $variantId = $this->variantModel->createVariant($data);

            // AUTO-CREATE INVENTORY RECORD & IMPORT INITIAL STOCK
            try {
                $initialStock = intval($_POST['initial_stock'] ?? 0);
                $minThreshold = intval($_POST['min_threshold'] ?? 10);

                // Import stock sẽ tự động tạo inventory record nếu chưa có
                if ($initialStock > 0) {
                    $this->inventoryService->importStock(
                        $variantId,
                        $initialStock,
                        AuthHelper::id() ?? 1,
                        'default',
                        [],
                        'Nhập kho ban đầu khi tạo variant'
                    );
                } else {
                    // Chỉ tạo record rỗng với adjust = 0
                    $this->inventoryService->adjustStock(
                        $variantId,
                        0,
                        AuthHelper::id() ?? 1,
                        'default',
                        'Tạo inventory record cho variant mới'
                    );
                }

                // Update threshold
                $this->inventoryService->updateThresholds($variantId, $minThreshold, 'default');
            } catch (Exception $e) {
                error_log('Error creating inventory for variant: ' . $e->getMessage());
                // Không throw exception để không fail tạo variant
            }

            // Log
            LogHelper::log('create', 'product_variant', $variantId, $data);

            AuthHelper::setFlash('success', 'Thêm biến thể thành công!');
            $this->redirect("/admin/products/{$id}/variants");
        } catch (Exception $e) {
            error_log('Error creating variant: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi: ' . $e->getMessage());
            $this->redirect("/admin/products/{$id}/variants/create");
        }
    }

    /**
     * Hiển thị form sửa variant
     */
    public function edit(int $id, int $variantId): void
    {
        try {
            $product = $this->productModel->find($id);
            if (!$product) {
                AuthHelper::setFlash('error', 'Không tìm thấy sản phẩm');
                $this->redirect('/admin/products');
                return;
            }

            $variant = $this->variantModel->find($variantId);
            if (!$variant || $variant['product_id'] != $id) {
                AuthHelper::setFlash('error', 'Không tìm thấy biến thể');
                $this->redirect("/admin/products/{$id}/variants");
                return;
            }

            // Load inventory info
            try {
                $inventory = $this->inventoryService->getStockInfo($variantId);
                $variant['inventory'] = $inventory;
            } catch (Exception $e) {
                $variant['inventory'] = [];
            }

            $this->view('admin/products/variants/edit', [
                'product' => $product,
                'variant' => $variant
            ]);
        } catch (Exception $e) {
            error_log('Error loading variant edit form: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi xảy ra: ' . $e->getMessage());
            $this->redirect("/admin/products/{$id}/variants");
        }
    }

    /**
     * Xử lý cập nhật variant
     */
    public function update(int $id, int $variantId): void
    {
        try {
            $variant = $this->variantModel->find($variantId);
            if (!$variant || $variant['product_id'] != $id) {
                throw new Exception('Không tìm thấy biến thể');
            }

            // Validate
            $sku = trim($_POST['sku'] ?? '');
            if (empty($sku)) {
                throw new Exception('SKU không được để trống');
            }

            // Check SKU trùng (trừ chính nó)
            if ($this->variantModel->skuExists($sku, $id, $variantId)) {
                throw new Exception('SKU variant đã tồn tại cho sản phẩm này');
            }

            // Chuẩn bị attributes từ form
            $attributes = [];

            // Các thuộc tính chuẩn
            if (!empty($_POST['color'])) {
                $attributes['Màu sắc'] = trim($_POST['color']);
            }
            if (!empty($_POST['size'])) {
                $attributes['Kích thước'] = trim($_POST['size']);
            }
            if (!empty($_POST['capacity'])) {
                $attributes['Dung lượng'] = trim($_POST['capacity']);
            }

            // Thuộc tính tùy chỉnh
            if (!empty($_POST['custom_attr_name']) && !empty($_POST['custom_attr_value'])) {
                $attributes[trim($_POST['custom_attr_name'])] = trim($_POST['custom_attr_value']);
            }

            // Update
            $data = [
                'sku' => $sku,
                'attributes' => !empty($attributes) ? json_encode($attributes, JSON_UNESCAPED_UNICODE) : null,
                'price' => floatval($_POST['price'] ?? 0),
                'unit_cost' => floatval($_POST['unit_cost'] ?? 0),
                'barcode' => trim($_POST['barcode'] ?? '') ?: null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $this->variantModel->updateVariant($variantId, $data);

            // Log
            LogHelper::log('update', 'product_variant', $variantId, $data);

            AuthHelper::setFlash('success', 'Cập nhật biến thể thành công!');
            $this->redirect("/admin/products/{$id}/variants");
        } catch (Exception $e) {
            error_log('Error updating variant: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi: ' . $e->getMessage());
            $this->redirect("/admin/products/{$id}/variants/{$variantId}/edit");
        }
    }

    /**
     * Xóa variant (và inventory records tự động xóa bởi Foreign Key CASCADE)
     */
    public function delete(int $id, int $variantId): void
    {
        try {
            $variant = $this->variantModel->find($variantId);
            if (!$variant || $variant['product_id'] != $id) {
                throw new Exception('Không tìm thấy biến thể');
            }

            // Check xem có thể xóa không (có đơn hàng chưa?)
            // TODO: Implement check orders

            // Xóa variant (inventory records tự động xóa bởi ON DELETE CASCADE)
            $this->variantModel->deleteVariant($variantId);

            // Log
            LogHelper::log('delete', 'product_variant', $variantId, $variant);

            AuthHelper::setFlash('success', 'Xóa biến thể thành công!');
        } catch (Exception $e) {
            error_log('Error deleting variant: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Có lỗi: ' . $e->getMessage());
        }

        $this->redirect("/admin/products/{$id}/variants");
    }

    /**
     * Toggle active status
     */
    public function toggle(int $id, int $variantId): void
    {
        try {
            $variant = $this->variantModel->find($variantId);
            if (!$variant || $variant['product_id'] != $id) {
                $this->error('Không tìm thấy biến thể', 404);
            }

            $newStatus = $variant['is_active'] == 1 ? 0 : 1;
            $this->variantModel->updateVariant($variantId, ['is_active' => $newStatus]);

            LogHelper::log('toggle', 'product_variant', $variantId, ['is_active' => $newStatus]);

            $this->success([
                'new_status' => $newStatus
            ], 'Đã cập nhật trạng thái');
        } catch (\Exception $e) {
            error_log('Error toggling variant: ' . $e->getMessage());
            $this->error($e->getMessage(), 500);
        }
    }
}
