<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Models\InventoryTransactionModel;
use Exception;

/**
 * StockTransactionService - Quản lý lịch sử giao dịch kho
 * 
 * Chức năng:
 * - Ghi nhận và truy vấn lịch sử giao dịch
 * - Tạo báo cáo xuất nhập tồn
 * - Export dữ liệu transaction
 */
class StockTransactionService
{
    private InventoryTransactionModel $transactionModel;

    public function __construct()
    {
        $this->transactionModel = new InventoryTransactionModel();
    }

    /**
     * Ghi nhận giao dịch NHẬP KHO
     * 
     * @param int $variantId ID của variant
     * @param int $quantity Số lượng nhập
     * @param int $userId ID người thực hiện
     * @param string $warehouse Tên kho
     * @param array $reference Thông tin tham chiếu
     * @param string|null $note Ghi chú
     * @return int Transaction ID
     */
    public function recordImport(
        int $variantId,
        int $quantity,
        int $userId,
        string $warehouse = 'default',
        array $reference = [],
        ?string $note = null
    ): int {
        return $this->transactionModel->recordTransaction([
            'product_variant_id' => $variantId,
            'warehouse' => $warehouse,
            'type' => InventoryTransactionModel::TYPE_IMPORT,
            'quantity_change' => $quantity,
            'reference_type' => $reference['type'] ?? null,
            'reference_id' => $reference['id'] ?? null,
            'note' => $note,
            'created_by' => $userId
        ]);
    }

    /**
     * Ghi nhận giao dịch XUẤT KHO
     * 
     * @param int $variantId ID của variant
     * @param int $quantity Số lượng xuất
     * @param int $userId ID người thực hiện
     * @param string $warehouse Tên kho
     * @param array $reference Thông tin tham chiếu
     * @param string|null $note Ghi chú
     * @return int Transaction ID
     */
    public function recordExport(
        int $variantId,
        int $quantity,
        int $userId,
        string $warehouse = 'default',
        array $reference = [],
        ?string $note = null
    ): int {
        return $this->transactionModel->recordTransaction([
            'product_variant_id' => $variantId,
            'warehouse' => $warehouse,
            'type' => InventoryTransactionModel::TYPE_EXPORT,
            'quantity_change' => -$quantity,
            'reference_type' => $reference['type'] ?? null,
            'reference_id' => $reference['id'] ?? null,
            'note' => $note,
            'created_by' => $userId
        ]);
    }

    /**
     * Ghi nhận giao dịch ĐIỀU CHỈNH
     * 
     * @param int $variantId ID của variant
     * @param int $difference Chênh lệch (có thể âm)
     * @param int $userId ID người thực hiện
     * @param string $warehouse Tên kho
     * @param string $reason Lý do điều chỉnh
     * @return int Transaction ID
     */
    public function recordAdjustment(
        int $variantId,
        int $difference,
        int $userId,
        string $warehouse = 'default',
        string $reason = ''
    ): int {
        return $this->transactionModel->recordTransaction([
            'product_variant_id' => $variantId,
            'warehouse' => $warehouse,
            'type' => InventoryTransactionModel::TYPE_ADJUST,
            'quantity_change' => $difference,
            'reference_type' => 'manual_adjustment',
            'reference_id' => null,
            'note' => $reason,
            'created_by' => $userId
        ]);
    }

    /**
     * Lấy lịch sử giao dịch với filter và pagination
     * 
     * @param array $filters Bộ lọc
     * @param int $page Trang hiện tại
     * @param int $perPage Số lượng/trang
     * @return array ['data' => array, 'pagination' => array]
     */
    public function getTransactionHistory(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $offset = ($page - 1) * $perPage;

        // Lấy data
        $data = $this->transactionModel->getTransactionsWithFilter($filters, $perPage, $offset);

        // Đếm tổng số
        $total = $this->transactionModel->countTransactions($filters);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => $total > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $total)
            ]
        ];
    }

    /**
     * Tạo báo cáo xuất nhập tồn
     * 
     * @param array $params Tham số báo cáo
     * @return array Dữ liệu báo cáo
     */
    public function generateTransactionReport(array $params = []): array
    {
        $warehouse = $params['warehouse'] ?? null;
        $fromDate = $params['from_date'] ?? null;
        $toDate = $params['to_date'] ?? null;

        // Lấy thống kê tổng quan
        $stats = $this->transactionModel->getTransactionStats($warehouse, $fromDate, $toDate);

        // Tính tổng hợp
        $totalImport = 0;
        $totalExport = 0;
        $totalAdjustment = 0;
        $transactionCount = 0;

        foreach ($stats as $stat) {
            $transactionCount += (int) $stat['transaction_count'];

            switch ($stat['type']) {
                case InventoryTransactionModel::TYPE_IMPORT:
                    $totalImport = (int) $stat['total_increase'];
                    break;
                case InventoryTransactionModel::TYPE_EXPORT:
                    $totalExport = (int) $stat['total_decrease'];
                    break;
                case InventoryTransactionModel::TYPE_ADJUST:
                    $totalAdjustment = (int) $stat['total_increase'] - (int) $stat['total_decrease'];
                    break;
            }
        }

        return [
            'period' => [
                'from' => $fromDate,
                'to' => $toDate,
                'warehouse' => $warehouse ?? 'all'
            ],
            'summary' => [
                'total_transactions' => $transactionCount,
                'total_import' => $totalImport,
                'total_export' => $totalExport,
                'total_adjustment' => $totalAdjustment,
                'net_change' => $totalImport - $totalExport + $totalAdjustment
            ],
            'breakdown' => $stats
        ];
    }

    /**
     * Lấy lịch sử của 1 variant
     * 
     * @param int $variantId ID của variant
     * @param string|null $warehouse Tên kho
     * @param int $limit Số lượng records
     * @return array Danh sách giao dịch
     */
    public function getVariantTransactionHistory(
        int $variantId,
        ?string $warehouse = null,
        int $limit = 100
    ): array {
        return $this->transactionModel->getVariantHistory($variantId, $warehouse, $limit);
    }

    /**
     * Lấy lịch sử của 1 product (tất cả variants)
     * 
     * @param int $productId ID của product
     * @param int $limit Số lượng records
     * @return array Danh sách giao dịch
     */
    public function getProductTransactionHistory(int $productId, int $limit = 100): array
    {
        return $this->transactionModel->getProductHistory($productId, $limit);
    }

    /**
     * Export dữ liệu transaction ra CSV
     * 
     * @param array $filters Bộ lọc
     * @return string CSV content
     */
    public function exportToCSV(array $filters = []): string
    {
        // Lấy tất cả transactions theo filter (không giới hạn)
        $transactions = $this->transactionModel->getTransactionsWithFilter($filters, 10000, 0);

        // Thêm UTF-8 BOM để Excel nhận diện tiếng Việt đúng
        $csv = "\xEF\xBB\xBF";

        // Tạo CSV header với tiếng Việt
        $csv .= "ID,Sản phẩm,Biến thể,Kho,Loại,Thay đổi,Tham chiếu,Ghi chú,Người tạo,Thời gian\n";

        // Thêm data rows
        foreach ($transactions as $t) {
            $csv .= implode(',', [
                $t['id'],
                '"' . str_replace('"', '""', $t['product_name']) . '"',
                '"' . str_replace('"', '""', $t['variant_sku']) . '"',
                $t['warehouse'],
                $t['type'],
                $t['quantity_change'],
                $t['reference_type'] ? "{$t['reference_type']}#{$t['reference_id']}" : '',
                '"' . str_replace('"', '""', $t['note'] ?? '') . '"',
                $t['created_by_name'] ?? $t['created_by'],
                $t['created_at']
            ]) . "\n";
        }

        return $csv;
    }

    /**
     * Thống kê giao dịch theo khoảng thời gian
     * 
     * @param string $fromDate Ngày bắt đầu (Y-m-d)
     * @param string $toDate Ngày kết thúc (Y-m-d)
     * @param string|null $warehouse Tên kho
     * @return array Thống kê
     */
    public function getStatsByDateRange(
        string $fromDate,
        string $toDate,
        ?string $warehouse = null
    ): array {
        return $this->transactionModel->getTransactionStats($warehouse, $fromDate, $toDate);
    }
}
