<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Models\InventoryModel;
use Modules\Inventory\Models\InventoryTransactionModel;
use Exception;

/**
 * InventoryService - Business Logic cho quản lý tồn kho
 * 
 * Chức năng:
 * - Kiểm tra và cập nhật tồn kho
 * - Nhập/Xuất/Điều chỉnh kho với transaction atomicity
 * - Validation business rules
 * - Integration hooks cho Purchase/Sales modules
 */
class InventoryService
{
    private InventoryModel $inventoryModel;
    private InventoryTransactionModel $transactionModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryModel();
        $this->transactionModel = new InventoryTransactionModel();
    }

    /**
     * Kiểm tra tồn kho có đủ để xuất không
     * 
     * @param int $variantId ID của variant
     * @param int $quantityNeeded Số lượng cần
     * @param string $warehouse Tên kho
     * @return array ['available' => bool, 'current_stock' => int, 'needed' => int]
     */
    public function checkStock(int $variantId, int $quantityNeeded, string $warehouse = 'default'): array
    {
        $stock = $this->inventoryModel->getVariantStock($variantId, $warehouse);
        $currentStock = $stock['quantity'] ?? 0;

        return [
            'available' => $currentStock >= $quantityNeeded,
            'current_stock' => $currentStock,
            'needed' => $quantityNeeded,
            'shortage' => max(0, $quantityNeeded - $currentStock)
        ];
    }

    /**
     * Nhập kho (Import)
     * 
     * @param int $variantId ID của variant
     * @param int $quantity Số lượng nhập (phải > 0)
     * @param int $userId ID người thực hiện
     * @param string $warehouse Tên kho
     * @param array $reference Thông tin tham chiếu ['type' => 'purchase_order', 'id' => 123]
     * @param string|null $note Ghi chú
     * @return array ['success' => bool, 'new_stock' => int, 'transaction_id' => int]
     * @throws Exception
     */
    public function importStock(
        int $variantId,
        int $quantity,
        int $userId,
        string $warehouse = 'default',
        array $reference = [],
        ?string $note = null
    ): array {
        // Validation
        if ($quantity <= 0) {
            throw new Exception("Số lượng nhập phải lớn hơn 0");
        }

        try {
            // Begin transaction
            $this->inventoryModel->beginTransaction();

            // Cập nhật tồn kho
            $success = $this->inventoryModel->updateStock($variantId, $quantity, $warehouse);

            if (!$success) {
                throw new Exception("Không thể cập nhật tồn kho");
            }

            // Ghi nhận transaction
            $transactionId = $this->transactionModel->recordTransaction([
                'product_variant_id' => $variantId,
                'warehouse' => $warehouse,
                'type' => InventoryTransactionModel::TYPE_IMPORT,
                'quantity_change' => $quantity,
                'reference_type' => $reference['type'] ?? null,
                'reference_id' => $reference['id'] ?? null,
                'note' => $note,
                'created_by' => $userId
            ]);

            // Commit transaction
            $this->inventoryModel->commit();

            // Lấy tồn kho mới
            $newStock = $this->inventoryModel->getVariantStock($variantId, $warehouse);

            return [
                'success' => true,
                'new_stock' => $newStock['quantity'] ?? $quantity,
                'transaction_id' => $transactionId,
                'message' => "Nhập kho thành công: +{$quantity}"
            ];
        } catch (Exception $e) {
            $this->inventoryModel->rollback();
            throw new Exception("Lỗi nhập kho: " . $e->getMessage());
        }
    }

    /**
     * Xuất kho (Export)
     * 
     * @param int $variantId ID của variant
     * @param int $quantity Số lượng xuất (phải > 0)
     * @param int $userId ID người thực hiện
     * @param string $warehouse Tên kho
     * @param array $reference Thông tin tham chiếu
     * @param string|null $note Ghi chú
     * @param bool $allowNegative Cho phép xuất âm (default: false)
     * @return array ['success' => bool, 'new_stock' => int, 'transaction_id' => int]
     * @throws Exception
     */
    public function exportStock(
        int $variantId,
        int $quantity,
        int $userId,
        string $warehouse = 'default',
        array $reference = [],
        ?string $note = null,
        bool $allowNegative = false
    ): array {
        // Validation
        if ($quantity <= 0) {
            throw new Exception("Số lượng xuất phải lớn hơn 0");
        }

        // Kiểm tra tồn kho
        if (!$allowNegative) {
            $stockCheck = $this->checkStock($variantId, $quantity, $warehouse);
            if (!$stockCheck['available']) {
                throw new Exception(
                    "Không đủ hàng để xuất. " .
                        "Tồn kho hiện tại: {$stockCheck['current_stock']}, " .
                        "Cần: {$quantity}, " .
                        "Thiếu: {$stockCheck['shortage']}"
                );
            }
        }

        try {
            // Begin transaction
            $this->inventoryModel->beginTransaction();

            // Cập nhật tồn kho (số âm = xuất)
            $success = $this->inventoryModel->updateStock($variantId, -$quantity, $warehouse);

            if (!$success) {
                throw new Exception("Không thể cập nhật tồn kho");
            }

            // Ghi nhận transaction
            $transactionId = $this->transactionModel->recordTransaction([
                'product_variant_id' => $variantId,
                'warehouse' => $warehouse,
                'type' => InventoryTransactionModel::TYPE_EXPORT,
                'quantity_change' => -$quantity,
                'reference_type' => $reference['type'] ?? null,
                'reference_id' => $reference['id'] ?? null,
                'note' => $note,
                'created_by' => $userId
            ]);

            // Commit transaction
            $this->inventoryModel->commit();

            // Lấy tồn kho mới
            $newStock = $this->inventoryModel->getVariantStock($variantId, $warehouse);

            return [
                'success' => true,
                'new_stock' => $newStock['quantity'] ?? 0,
                'transaction_id' => $transactionId,
                'message' => "Xuất kho thành công: -{$quantity}"
            ];
        } catch (Exception $e) {
            $this->inventoryModel->rollback();
            throw new Exception("Lỗi xuất kho: " . $e->getMessage());
        }
    }

    /**
     * Điều chỉnh tồn kho (Adjustment)
     * Dùng khi kiểm kê, sửa sai số liệu
     * 
     * @param int $variantId ID của variant
     * @param int $newQuantity Số lượng mới (giá trị tuyệt đối)
     * @param int $userId ID người thực hiện
     * @param string $warehouse Tên kho
     * @param string $reason Lý do điều chỉnh
     * @return array ['success' => bool, 'old_stock' => int, 'new_stock' => int, 'difference' => int]
     * @throws Exception
     */
    public function adjustStock(
        int $variantId,
        int $newQuantity,
        int $userId,
        string $warehouse = 'default',
        string $reason = ''
    ): array {
        // Validation
        if ($newQuantity < 0) {
            throw new Exception("Số lượng mới không được âm");
        }

        if (empty($reason)) {
            throw new Exception("Phải có lý do điều chỉnh");
        }

        try {
            // Begin transaction
            $this->inventoryModel->beginTransaction();

            // Lấy tồn kho hiện tại
            $currentStock = $this->inventoryModel->getVariantStock($variantId, $warehouse);
            $oldQuantity = $currentStock['quantity'] ?? 0;
            $difference = $newQuantity - $oldQuantity;

            // Set tồn kho mới
            $success = $this->inventoryModel->setStock($variantId, $newQuantity, $warehouse);

            if (!$success) {
                throw new Exception("Không thể cập nhật tồn kho");
            }

            // Ghi nhận transaction
            $transactionId = $this->transactionModel->recordTransaction([
                'product_variant_id' => $variantId,
                'warehouse' => $warehouse,
                'type' => InventoryTransactionModel::TYPE_ADJUST,
                'quantity_change' => $difference,
                'reference_type' => 'manual_adjustment',
                'reference_id' => null,
                'note' => "Điều chỉnh: {$oldQuantity} → {$newQuantity}. Lý do: {$reason}",
                'created_by' => $userId
            ]);

            // Commit transaction
            $this->inventoryModel->commit();

            return [
                'success' => true,
                'old_stock' => $oldQuantity,
                'new_stock' => $newQuantity,
                'difference' => $difference,
                'transaction_id' => $transactionId,
                'message' => "Điều chỉnh tồn kho: {$oldQuantity} → {$newQuantity} (" . ($difference >= 0 ? "+{$difference}" : $difference) . ")"
            ];
        } catch (Exception $e) {
            $this->inventoryModel->rollback();
            throw new Exception("Lỗi điều chỉnh tồn kho: " . $e->getMessage());
        }
    }

    /**
     * Chuyển kho giữa các warehouse
     * 
     * @param int $variantId ID của variant
     * @param int $quantity Số lượng chuyển
     * @param string $fromWarehouse Kho nguồn
     * @param string $toWarehouse Kho đích
     * @param int $userId ID người thực hiện
     * @param string|null $note Ghi chú
     * @return array ['success' => bool, 'from_stock' => int, 'to_stock' => int]
     * @throws Exception
     */
    public function transferStock(
        int $variantId,
        int $quantity,
        string $fromWarehouse,
        string $toWarehouse,
        int $userId,
        ?string $note = null
    ): array {
        // Validation
        if ($quantity <= 0) {
            throw new Exception("Số lượng chuyển phải lớn hơn 0");
        }

        if ($fromWarehouse === $toWarehouse) {
            throw new Exception("Kho nguồn và kho đích không được trùng nhau");
        }

        // Kiểm tra tồn kho nguồn
        $stockCheck = $this->checkStock($variantId, $quantity, $fromWarehouse);
        if (!$stockCheck['available']) {
            throw new Exception(
                "Kho nguồn không đủ hàng. " .
                    "Tồn kho: {$stockCheck['current_stock']}, " .
                    "Cần chuyển: {$quantity}"
            );
        }

        try {
            // Begin transaction
            $this->inventoryModel->beginTransaction();

            // Xuất từ kho nguồn
            $this->inventoryModel->updateStock($variantId, -$quantity, $fromWarehouse);

            // Nhập vào kho đích
            $this->inventoryModel->updateStock($variantId, $quantity, $toWarehouse);

            // Ghi nhận 2 transactions
            $noteText = $note ?? "Chuyển kho: {$fromWarehouse} → {$toWarehouse}";

            $this->transactionModel->recordTransaction([
                'product_variant_id' => $variantId,
                'warehouse' => $fromWarehouse,
                'type' => InventoryTransactionModel::TYPE_EXPORT,
                'quantity_change' => -$quantity,
                'reference_type' => 'transfer',
                'reference_id' => null,
                'note' => $noteText,
                'created_by' => $userId
            ]);

            $this->transactionModel->recordTransaction([
                'product_variant_id' => $variantId,
                'warehouse' => $toWarehouse,
                'type' => InventoryTransactionModel::TYPE_IMPORT,
                'quantity_change' => $quantity,
                'reference_type' => 'transfer',
                'reference_id' => null,
                'note' => $noteText,
                'created_by' => $userId
            ]);

            // Commit transaction
            $this->inventoryModel->commit();

            // Lấy tồn kho mới
            $fromStock = $this->inventoryModel->getVariantStock($variantId, $fromWarehouse);
            $toStock = $this->inventoryModel->getVariantStock($variantId, $toWarehouse);

            return [
                'success' => true,
                'from_stock' => $fromStock['quantity'] ?? 0,
                'to_stock' => $toStock['quantity'] ?? 0,
                'message' => "Chuyển kho thành công: {$quantity} từ {$fromWarehouse} sang {$toWarehouse}"
            ];
        } catch (Exception $e) {
            $this->inventoryModel->rollback();
            throw new Exception("Lỗi chuyển kho: " . $e->getMessage());
        }
    }

    /**
     * Lấy thông tin tồn kho chi tiết
     * 
     * @param int $variantId ID của variant
     * @param string|null $warehouse Tên kho (null = tất cả kho)
     * @return array Thông tin tồn kho
     */
    public function getStockInfo(int $variantId, ?string $warehouse = null): array
    {
        if ($warehouse !== null) {
            // Lấy tồn kho 1 warehouse
            $stock = $this->inventoryModel->getVariantStock($variantId, $warehouse);

            if (!$stock) {
                return [
                    'variant_id' => $variantId,
                    'warehouse' => $warehouse,
                    'quantity' => 0,
                    'min_threshold' => 0,
                    'status' => 'no_record'
                ];
            }

            return [
                'variant_id' => $variantId,
                'warehouse' => $warehouse,
                'quantity' => (int) $stock['quantity'],
                'min_threshold' => (int) $stock['min_threshold'],
                'last_updated' => $stock['last_updated'],
                'status' => $this->determineStockStatus($stock['quantity'], $stock['min_threshold'])
            ];
        } else {
            // Lấy tồn kho tất cả warehouses
            // TODO: Implement getVariantAllWarehouses() in Model
            return [
                'variant_id' => $variantId,
                'total_quantity' => 0,
                'warehouses' => []
            ];
        }
    }

    /**
     * Lấy danh sách tồn kho với pagination
     * 
     * @param array $filters Bộ lọc
     * @param int $page Trang hiện tại
     * @param int $perPage Số lượng/trang
     * @return array ['data' => array, 'pagination' => array]
     */
    public function getStockList(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;

        $data = $this->inventoryModel->getInventoryListWithDetails($filters, $perPage, $offset);

        // TODO: Implement count method for total records
        $total = count($data); // Placeholder

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Lấy danh sách cảnh báo sắp hết hàng
     * 
     * @param int $limit Số lượng records
     * @return array Danh sách sản phẩm low stock
     */
    public function getLowStockAlerts(int $limit = 50): array
    {
        $lowStockProducts = $this->inventoryModel->getLowStockProducts($limit);
        $outOfStockProducts = $this->inventoryModel->getOutOfStockProducts($limit);

        return [
            'low_stock' => $lowStockProducts,
            'out_of_stock' => $outOfStockProducts,
            'total_alerts' => count($lowStockProducts) + count($outOfStockProducts)
        ];
    }

    /**
     * Tính giá trị tồn kho
     * 
     * @param int|null $productId ID của product (null = tất cả)
     * @param string|null $warehouse Tên kho (null = tất cả)
     * @return array ['total_value' => float, 'breakdown' => array]
     */
    public function calculateStockValue(?int $productId = null, ?string $warehouse = null): array
    {
        // TODO: Join với product_variants.unit_cost để tính giá trị
        // Placeholder implementation
        return [
            'total_value' => 0.0,
            'breakdown' => []
        ];
    }

    /**
     * Cập nhật ngưỡng cảnh báo cho variant
     * 
     * @param int $variantId ID của variant
     * @param int $minThreshold Ngưỡng tối thiểu
     * @param string $warehouse Tên kho
     * @return bool Thành công hay không
     */
    public function updateThresholds(int $variantId, int $minThreshold, string $warehouse = 'default'): bool
    {
        if ($minThreshold < 0) {
            throw new Exception("Ngưỡng cảnh báo không được âm");
        }

        return $this->inventoryModel->updateThreshold($variantId, $minThreshold, $warehouse);
    }

    /**
     * Helper: Xác định trạng thái tồn kho
     * 
     * @param int $quantity Số lượng hiện tại
     * @param int $threshold Ngưỡng cảnh báo
     * @return string 'out_of_stock' | 'low_stock' | 'in_stock'
     */
    private function determineStockStatus(int $quantity, int $threshold): string
    {
        if ($quantity == 0) {
            return 'out_of_stock';
        } elseif ($quantity <= $threshold) {
            return 'low_stock';
        } else {
            return 'in_stock';
        }
    }
}
