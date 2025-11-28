<?php
// PurchaseService.php - Business logic cho việc tạo phiếu nhập
namespace Modules\Purchase\Services;

use Modules\Purchase\Models\PurchaseOrderModel;
use Modules\Purchase\Models\PurchaseDetailModel;
use Modules\Inventory\Models\InventoryModel;
use Modules\Inventory\Models\InventoryTransactionModel;
use Exception;

class PurchaseService
{
    private PurchaseOrderModel $orderModel;
    private PurchaseDetailModel $detailModel;
    private InventoryModel $inventoryModel;
    private InventoryTransactionModel $transactionModel;

    public function __construct()
    {
        $this->orderModel = new PurchaseOrderModel();
        $this->detailModel = new PurchaseDetailModel();
        $this->inventoryModel = new InventoryModel();
        $this->transactionModel = new InventoryTransactionModel();
    }

    /**
     * Tạo phiếu nhập và cập nhật tồn kho atomically
     *
     * $items = [ ['variant_id'=>int, 'quantity'=>int, 'import_price'=>float], ... ]
     */
    public function createPurchase(int $supplierId, array $items, int $userId, string $purchaseDate, ?string $note = null): array
    {
        if (empty($items)) {
            throw new Exception('Không có sản phẩm trong phiếu nhập');
        }

        try {
            // Begin transaction using orderModel (shared PDO connection)
            $this->orderModel->beginTransaction();

            // Calculate total
            $total = 0.0;
            foreach ($items as $it) {
                $total += ($it['quantity'] * $it['import_price']);
            }

            // Create purchase order
            $poNumber = $this->orderModel->generatePONumber();
            $orderId = $this->orderModel->createOrder([
                'po_number' => $poNumber,
                'supplier_id' => $supplierId ?: null,
                'total_amount' => $total,
                'status' => 'completed',
                'created_by' => $userId,
                'purchase_date' => $purchaseDate,
            ]);

            if (!$orderId) {
                throw new Exception('Không tạo được phiếu nhập');
            }

            // Create details and update inventory + transaction records
            foreach ($items as $it) {
                $variantId = (int)$it['variant_id'];
                $qty = (int)$it['quantity'];
                $importPrice = (float)$it['import_price'];

                $this->detailModel->createDetail([
                    'purchase_order_id' => $orderId,
                    'product_variant_id' => $variantId,
                    'quantity' => $qty,
                    'import_price' => $importPrice
                ]);

                // Update stock (upsert)
                $ok = $this->inventoryModel->updateStock($variantId, $qty, 'default');
                if (!$ok) {
                    throw new Exception('Không thể cập nhật tồn kho cho variant ' . $variantId);
                }

                // Record inventory transaction
                $this->transactionModel->recordTransaction([
                    'product_variant_id' => $variantId,
                    'warehouse' => 'default',
                    'type' => InventoryTransactionModel::TYPE_IMPORT,
                    'quantity_change' => $qty,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $orderId,
                    'note' => $note ?: ('Nhập kho từ phiếu ' . $poNumber),
                    'created_by' => $userId
                ]);
            }

            // Commit
            $this->orderModel->commit();

            return [
                'success' => true,
                'purchase_id' => $orderId,
                'po_number' => $poNumber,
                'message' => 'Tạo phiếu nhập thành công #' . $poNumber
            ];
        } catch (Exception $e) {
            // Rollback
            try {
                $this->orderModel->rollback();
            } catch (Exception $er) {
                // ignore
            }
            throw new Exception('Lỗi khi tạo phiếu nhập: ' . $e->getMessage());
        }
    }
}