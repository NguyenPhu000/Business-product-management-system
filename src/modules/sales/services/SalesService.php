<?php
namespace Modules\Sales\Services;

use Modules\Sales\Models\SalesOrderModel;
use Modules\Sales\Models\SalesDetailModel;
use Modules\Inventory\Models\InventoryModel;
use Modules\Inventory\Models\InventoryTransactionModel;
use Exception;

class SalesService
{
    private SalesOrderModel $orderModel;
    private SalesDetailModel $detailModel;
    private InventoryModel $inventoryModel;
    private InventoryTransactionModel $transactionModel;

    public function __construct()
    {
        $this->orderModel = new SalesOrderModel();
        $this->detailModel = new SalesDetailModel();
        $this->inventoryModel = new InventoryModel();
        $this->transactionModel = new InventoryTransactionModel();
    }

    /**
     * Tạo phiếu xuất và cập nhật tồn kho atomically
     * $items = [ ['variant_id'=>int, 'quantity'=>int, 'sale_price'=>float], ... ]
     */
    public function createSale(?int $supplierId, array $items, int $userId, string $saleDate, ?string $note = null, ?string $customerName = null): array
    {
        if (empty($items)) {
            throw new Exception('Không có sản phẩm trong phiếu xuất');
        }

        try {
            $this->orderModel->beginTransaction();

            // Calculate totals
            $total = 0.0;
            foreach ($items as $it) {
                $total += ((int)$it['quantity'] * (float)$it['sale_price']);
            }

            // Create sales order
            $orderNumber = $this->orderModel->generateOrderNumber();
            $orderId = $this->orderModel->createOrder([
                'order_number' => $orderNumber,
                'customer_name' => $customerName ?: null,
                'total_excl_tax' => $total,
                'status' => 'completed',
                'created_by' => $userId,
                'sale_date' => $saleDate,
            ]);

            if (!$orderId) {
                throw new Exception('Không tạo được phiếu xuất');
            }

            // For each item: check stock, create detail, update inventory, record transaction
            foreach ($items as $it) {
                $variantId = (int)$it['variant_id'];
                $qty = (int)$it['quantity'];
                $salePrice = (float)$it['sale_price'];

                // Check stock
                $stock = $this->inventoryModel->getVariantStock($variantId, 'default');
                $current = (int) ($stock['quantity'] ?? 0);
                if ($current < $qty) {
                    throw new Exception('Không đủ tồn kho cho variant ' . $variantId . '. Hiện có: ' . $current . ', cần: ' . $qty);
                }

                $this->detailModel->createDetail([
                    'sales_order_id' => $orderId,
                    'product_variant_id' => $variantId,
                    'quantity' => $qty,
                    'sale_price' => $salePrice
                ]);

                // Update stock (decrement)
                $ok = $this->inventoryModel->updateStock($variantId, -$qty, 'default');
                if (!$ok) {
                    throw new Exception('Không thể cập nhật tồn kho cho variant ' . $variantId);
                }

                // Record inventory transaction (export)
                $this->transactionModel->recordTransaction([
                    'product_variant_id' => $variantId,
                    'warehouse' => 'default',
                    'type' => InventoryTransactionModel::TYPE_EXPORT,
                    'quantity_change' => -$qty,
                    'reference_type' => 'sales_order',
                    'reference_id' => $orderId,
                    'note' => $note ?: ('Xuất kho cho đơn ' . $orderNumber),
                    'created_by' => $userId
                ]);
            }

            $this->orderModel->commit();

            return [
                'success' => true,
                'sales_id' => $orderId,
                'order_number' => $orderNumber,
                'message' => 'Tạo phiếu xuất thành công #' . $orderNumber
            ];
        } catch (Exception $e) {
            try {
                $this->orderModel->rollback();
            } catch (Exception $er) {
                // ignore
            }
            throw new Exception('Lỗi khi tạo phiếu xuất: ' . $e->getMessage());
        }
    }
}

