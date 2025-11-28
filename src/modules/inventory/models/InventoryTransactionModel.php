<?php

namespace Modules\Inventory\Models;

use Core\BaseModel;

/**
 * InventoryTransactionModel - Quản lý lịch sử giao dịch kho
 * 
 * Table: inventory_transactions
 * Fields: id, product_variant_id, warehouse, type, quantity_change, 
 *         reference_type, reference_id, note, created_by, created_at
 */
class InventoryTransactionModel extends BaseModel
{
    protected string $table = 'inventory_transactions';
    protected string $primaryKey = 'id';

    // Transaction types
    public const TYPE_IMPORT = 'import';      // Nhập kho
    public const TYPE_EXPORT = 'export';      // Xuất kho
    public const TYPE_ADJUST = 'adjust';      // Điều chỉnh
    public const TYPE_RETURN = 'return';      // Trả hàng

    /**
     * Ghi nhận giao dịch mới
     * 
     * @param array $data Dữ liệu giao dịch
     * @return int ID của transaction
     */
    public function recordTransaction(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (product_variant_id, warehouse, type, quantity_change, 
                 reference_type, reference_id, note, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $params = [
            $data['product_variant_id'],
            $data['warehouse'] ?? 'default',
            $data['type'],
            $data['quantity_change'],
            $data['reference_type'] ?? null,
            $data['reference_id'] ?? null,
            $data['note'] ?? null,
            $data['created_by']
        ];

        $this->execute($sql, $params);
        return (int) $this->lastInsertId();
    }

    /**
     * Lấy lịch sử giao dịch của 1 variant
     * 
     * @param int $variantId ID của variant
     * @param string $warehouse Tên kho (null = tất cả kho)
     * @param int $limit Số lượng records
     * @param int $offset Offset cho pagination
     * @return array Danh sách giao dịch
     */
    public function getVariantHistory(int $variantId, ?string $warehouse = null, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT 
                    t.*,
                    u.username as created_by_name,
                    u.full_name as created_by_fullname
                FROM {$this->table} t
                LEFT JOIN users u ON t.created_by = u.id
                WHERE t.product_variant_id = ?";

        $params = [$variantId];

        if ($warehouse !== null) {
            $sql .= " AND t.warehouse = ?";
            $params[] = $warehouse;
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Lấy lịch sử giao dịch của 1 product (tất cả variants)
     * 
     * @param int $productId ID của product
     * @param int $limit Số lượng records
     * @param int $offset Offset cho pagination
     * @return array Danh sách giao dịch
     */
    public function getProductHistory(int $productId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT 
                    t.*,
                    pv.sku as variant_sku,
                    pv.attributes as variant_attributes,
                    u.username as created_by_name,
                    u.full_name as created_by_fullname
                FROM {$this->table} t
                INNER JOIN product_variants pv ON t.product_variant_id = pv.id
                LEFT JOIN users u ON t.created_by = u.id
                WHERE pv.product_id = ?
                ORDER BY t.created_at DESC
                LIMIT ? OFFSET ?";

        return $this->query($sql, [$productId, $limit, $offset]);
    }

    /**
     * Lấy giao dịch với bộ lọc linh hoạt
     * 
     * @param array $filters Điều kiện lọc
     * @param int $limit Số lượng records
     * @param int $offset Offset cho pagination
     * @return array Danh sách giao dịch
     */
    public function getTransactionsWithFilter(array $filters, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT 
                    t.*,
                    p.name as product_name,
                    p.sku as product_sku,
                    pv.sku as variant_sku,
                    pv.attributes as variant_attributes,
                    u.username as created_by_name,
                    u.full_name as created_by_fullname
                FROM {$this->table} t
                INNER JOIN product_variants pv ON t.product_variant_id = pv.id
                INNER JOIN products p ON pv.product_id = p.id
                LEFT JOIN users u ON t.created_by = u.id
                WHERE 1=1";

        $params = [];

        // Filter by warehouse
        if (!empty($filters['warehouse'])) {
            $sql .= " AND t.warehouse = ?";
            $params[] = $filters['warehouse'];
        }

        // Filter by transaction type
        if (!empty($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }

        // Filter by reference
        if (!empty($filters['reference_type'])) {
            $sql .= " AND t.reference_type = ?";
            $params[] = $filters['reference_type'];

            if (!empty($filters['reference_id'])) {
                $sql .= " AND t.reference_id = ?";
                $params[] = $filters['reference_id'];
            }
        }

        // Filter by date range
        if (!empty($filters['from_date'])) {
            $sql .= " AND t.created_at >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND t.created_at <= ?";
            $params[] = $filters['to_date'] . ' 23:59:59';
        }

        // Search by product name or SKU
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR pv.sku LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Đếm số lượng giao dịch theo filter
     * 
     * @param array $filters Điều kiện lọc
     * @return int Tổng số giao dịch
     */
    public function countTransactions(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} t
                INNER JOIN product_variants pv ON t.product_variant_id = pv.id
                INNER JOIN products p ON pv.product_id = p.id
                WHERE 1=1";

        $params = [];

        // Apply same filters as getTransactionsWithFilter
        if (!empty($filters['warehouse'])) {
            $sql .= " AND t.warehouse = ?";
            $params[] = $filters['warehouse'];
        }

        if (!empty($filters['type'])) {
            $sql .= " AND t.type = ?";
            $params[] = $filters['type'];
        }

        if (!empty($filters['reference_type'])) {
            $sql .= " AND t.reference_type = ?";
            $params[] = $filters['reference_type'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND t.created_at >= ?";
            $params[] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND t.created_at <= ?";
            $params[] = $filters['to_date'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR pv.sku LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        $result = $this->queryOne($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Thống kê giao dịch theo loại
     * 
     * @param string|null $warehouse Tên kho (null = tất cả kho)
     * @param string|null $fromDate Ngày bắt đầu
     * @param string|null $toDate Ngày kết thúc
     * @return array Thống kê
     */
    public function getTransactionStats(?string $warehouse = null, ?string $fromDate = null, ?string $toDate = null): array
    {
        $sql = "SELECT 
                    t.type,
                    COUNT(*) as transaction_count,
                    SUM(CASE WHEN t.quantity_change > 0 THEN t.quantity_change ELSE 0 END) as total_increase,
                    SUM(CASE WHEN t.quantity_change < 0 THEN ABS(t.quantity_change) ELSE 0 END) as total_decrease
                FROM {$this->table} t
                WHERE 1=1";

        $params = [];

        if ($warehouse !== null) {
            $sql .= " AND t.warehouse = ?";
            $params[] = $warehouse;
        }

        if ($fromDate !== null) {
            $sql .= " AND t.created_at >= ?";
            $params[] = $fromDate;
        }

        if ($toDate !== null) {
            $sql .= " AND t.created_at <= ?";
            $params[] = $toDate . ' 23:59:59';
        }

        $sql .= " GROUP BY t.type";

        return $this->query($sql, $params);
    }
}
