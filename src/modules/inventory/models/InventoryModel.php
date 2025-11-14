<?php

namespace Modules\Inventory\Models;

use Core\BaseModel;

/**
 * InventoryModel - Quản lý tồn kho theo variant
 * 
 * Table: inventory
 * Fields: id, product_variant_id, warehouse, quantity, min_threshold, last_updated
 */
class InventoryModel extends BaseModel
{
    protected string $table = 'inventory';
    protected string $primaryKey = 'id';

    /**
     * Lấy tồn kho của 1 variant tại warehouse
     * 
     * @param int $variantId ID của variant
     * @param string $warehouse Tên kho (default: 'default')
     * @return array|null Thông tin tồn kho hoặc null
     */
    public function getVariantStock(int $variantId, string $warehouse = 'default'): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE product_variant_id = ? AND warehouse = ?";
        return $this->queryOne($sql, [$variantId, $warehouse]);
    }

    /**
     * Lấy tồn kho của 1 variant ở tất cả các kho (warehouse)
     *
     * @param int $variantId
     * @return array Mảng các bản ghi: [ ['warehouse'=>..., 'quantity'=>..., 'min_threshold'=>..., 'last_updated'=>...], ... ]
     */
    public function getVariantAllWarehouses(int $variantId): array
    {
        $sql = "SELECT warehouse, quantity, min_threshold, last_updated
                FROM {$this->table}
                WHERE product_variant_id = ?";

        return $this->query($sql, [$variantId]);
    }

    /**
     * Lấy tổng tồn kho của product (aggregate từ tất cả variants)
     * 
     * @param int $productId ID của product
     * @return array Danh sách tồn kho theo warehouse
     */
    public function getProductStock(int $productId): array
    {
        $sql = "SELECT 
                    i.warehouse,
                    SUM(i.quantity) as total_quantity,
                    MIN(i.min_threshold) as min_threshold,
                    COUNT(DISTINCT i.product_variant_id) as variant_count
                FROM {$this->table} i
                INNER JOIN product_variants pv ON i.product_variant_id = pv.id
                WHERE pv.product_id = ?
                GROUP BY i.warehouse";

        return $this->query($sql, [$productId]);
    }

    /**
     * Lấy danh sách tồn kho với thông tin product và variant
     * 
     * @param array $filters Filter conditions
     * @param int $limit Số lượng records
     * @param int $offset Offset cho pagination
     * @return array Danh sách tồn kho
     */
    public function getInventoryListWithDetails(array $filters, int $limit, int $offset): array
    {
        $sql = "SELECT 
                    p.id as product_id,
                    p.name as product_name,
                    p.sku as product_sku,
                    p.brand_id,
                    GROUP_CONCAT(DISTINCT pc.category_id) as category_ids,
                    pv.id as variant_id,
                    pv.sku as variant_sku,
                    pv.attributes as variant_attributes,
                    pv.price as variant_price,
                    i.warehouse,
                    i.quantity,
                    i.min_threshold,
                    i.last_updated,
                    CASE 
                        WHEN i.quantity = 0 THEN 'out_of_stock'
                        WHEN i.quantity <= i.min_threshold THEN 'low_stock'
                        ELSE 'in_stock'
                    END as stock_status
                FROM {$this->table} i
                INNER JOIN product_variants pv ON i.product_variant_id = pv.id
                INNER JOIN products p ON pv.product_id = p.id
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                WHERE 1=1";

        $params = [];

        // Filter by warehouse
        if (!empty($filters['warehouse'])) {
            $sql .= " AND i.warehouse = ?";
            $params[] = $filters['warehouse'];
        }

        // Filter by stock status
        if (!empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'low') {
                $sql .= " AND i.quantity <= i.min_threshold AND i.quantity > 0";
            } elseif ($filters['stock_status'] === 'out') {
                $sql .= " AND i.quantity = 0";
            }
        }

        // Filter by brand
        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $filters['brand_id'];
        }

        // Filter by quantity range
        if (isset($filters['quantity_min']) && $filters['quantity_min'] !== '') {
            $sql .= " AND i.quantity >= ?";
            $params[] = (int)$filters['quantity_min'];
        }
        if (isset($filters['quantity_max']) && $filters['quantity_max'] !== '') {
            $sql .= " AND i.quantity <= ?";
            $params[] = (int)$filters['quantity_max'];
        }

        // Search by product name or SKU
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR pv.sku LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        // Group by để xử lý multiple categories
        $sql .= " GROUP BY i.id, p.id, pv.id";

        // Filter by category (after GROUP BY, use HAVING)
        if (!empty($filters['category_id'])) {
            $sql .= " HAVING FIND_IN_SET(?, category_ids) > 0";
            $params[] = $filters['category_id'];
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'last_updated';
        $sortOrder = $filters['sort_order'] ?? 'DESC';

        $allowedSortFields = ['quantity', 'min_threshold', 'last_updated', 'product_name'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'last_updated';
        }

        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';

        if ($sortBy === 'product_name') {
            $sql .= " ORDER BY p.name {$sortOrder}";
        } else {
            $sql .= " ORDER BY i.{$sortBy} {$sortOrder}";
        }

        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Cập nhật số lượng tồn kho (cộng dồn)
     * 
     * @param int $variantId ID của variant
     * @param int $quantityChange Số lượng thay đổi (có thể âm)
     * @param string $warehouse Tên kho
     * @return bool Thành công hay không
     */
    public function updateStock(int $variantId, int $quantityChange, string $warehouse = 'default'): bool
    {
        // Upsert: Insert nếu chưa có, Update nếu đã có
        $sql = "INSERT INTO {$this->table} 
                (product_variant_id, warehouse, quantity, last_updated)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    quantity = quantity + VALUES(quantity),
                    last_updated = NOW()";

        return $this->execute($sql, [$variantId, $warehouse, $quantityChange]);
    }

    /**
     * Set số lượng tồn kho cụ thể (không cộng dồn)
     * 
     * @param int $variantId ID của variant
     * @param int $newQuantity Số lượng mới
     * @param string $warehouse Tên kho
     * @return bool Thành công hay không
     */
    public function setStock(int $variantId, int $newQuantity, string $warehouse = 'default'): bool
    {
        $sql = "INSERT INTO {$this->table} 
                (product_variant_id, warehouse, quantity, last_updated)
                VALUES (?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                    quantity = ?,
                    last_updated = NOW()";

        return $this->execute($sql, [$variantId, $warehouse, $newQuantity, $newQuantity]);
    }

    /**
     * Lấy danh sách sản phẩm sắp hết hàng (low stock)
     * 
     * @param int $limit Số lượng records
     * @return array Danh sách sản phẩm
     */
    public function getLowStockProducts(int $limit = 50): array
    {
        $sql = "SELECT 
                    p.id as product_id,
                    p.name as product_name,
                    p.sku as product_sku,
                    pv.id as variant_id,
                    pv.sku as variant_sku,
                    pv.attributes as variant_attributes,
                    i.warehouse,
                    i.quantity,
                    i.min_threshold,
                    i.last_updated
                FROM {$this->table} i
                INNER JOIN product_variants pv ON i.product_variant_id = pv.id
                INNER JOIN products p ON pv.product_id = p.id
                WHERE i.quantity <= i.min_threshold AND i.quantity > 0
                ORDER BY (i.quantity - i.min_threshold) ASC, i.last_updated DESC
                LIMIT ?";

        return $this->query($sql, [$limit]);
    }

    /**
     * Lấy danh sách sản phẩm hết hàng
     * 
     * @param int $limit Số lượng records
     * @return array Danh sách sản phẩm
     */
    public function getOutOfStockProducts(int $limit = 50): array
    {
        $sql = "SELECT 
                    p.id as product_id,
                    p.name as product_name,
                    p.sku as product_sku,
                    pv.id as variant_id,
                    pv.sku as variant_sku,
                    pv.attributes as variant_attributes,
                    i.warehouse,
                    i.last_updated
                FROM {$this->table} i
                INNER JOIN product_variants pv ON i.product_variant_id = pv.id
                INNER JOIN products p ON pv.product_id = p.id
                WHERE i.quantity = 0
                ORDER BY i.last_updated DESC
                LIMIT ?";

        return $this->query($sql, [$limit]);
    }

    /**
     * Đếm tổng số bản ghi tồn kho (cho pagination)
     * 
     * @param array $filters Filter conditions
     * @return int Tổng số bản ghi
     */
    public function countInventoryRecords(array $filters): int
    {
        $sql = "SELECT COUNT(DISTINCT i.id) as total
                FROM {$this->table} i
                INNER JOIN product_variants pv ON i.product_variant_id = pv.id
                INNER JOIN products p ON pv.product_id = p.id
                LEFT JOIN product_categories pc ON p.id = pc.product_id
                WHERE 1=1";

        $params = [];

        // Filter by warehouse
        if (!empty($filters['warehouse'])) {
            $sql .= " AND i.warehouse = ?";
            $params[] = $filters['warehouse'];
        }

        // Filter by stock status
        if (!empty($filters['stock_status'])) {
            if ($filters['stock_status'] === 'low') {
                $sql .= " AND i.quantity <= i.min_threshold AND i.quantity > 0";
            } elseif ($filters['stock_status'] === 'out') {
                $sql .= " AND i.quantity = 0";
            }
        }

        // Filter by brand
        if (!empty($filters['brand_id'])) {
            $sql .= " AND p.brand_id = ?";
            $params[] = $filters['brand_id'];
        }

        // Filter by quantity range
        if (isset($filters['quantity_min']) && $filters['quantity_min'] !== '') {
            $sql .= " AND i.quantity >= ?";
            $params[] = (int)$filters['quantity_min'];
        }
        if (isset($filters['quantity_max']) && $filters['quantity_max'] !== '') {
            $sql .= " AND i.quantity <= ?";
            $params[] = (int)$filters['quantity_max'];
        }

        // Search by product name or SKU
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ? OR pv.sku LIKE ?)";
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
            $params[] = $search;
        }

        // For category filter, we need a subquery approach since we're using DISTINCT
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.id IN (
                SELECT DISTINCT product_id 
                FROM product_categories 
                WHERE category_id = ?
            )";
            $params[] = $filters['category_id'];
        }

        $result = $this->queryOne($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Thống kê tồn kho tổng quan
     * 
     * @return array Thống kê
     */
    public function getStockStats(): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT pv.product_id) as total_products,
                    COUNT(DISTINCT i.product_variant_id) as total_variants,
                    SUM(i.quantity) as total_quantity,
                    SUM(CASE WHEN i.quantity <= i.min_threshold AND i.quantity > 0 THEN 1 ELSE 0 END) as low_stock_count,
                    SUM(CASE WHEN i.quantity = 0 THEN 1 ELSE 0 END) as out_of_stock_count,
                    SUM(i.quantity * pv.unit_cost) as total_inventory_value
                FROM {$this->table} i
                INNER JOIN product_variants pv ON i.product_variant_id = pv.id";

        $result = $this->queryOne($sql, []);

        return [
            'total_products' => (int) ($result['total_products'] ?? 0),
            'total_variants' => (int) ($result['total_variants'] ?? 0),
            'total_quantity' => (int) ($result['total_quantity'] ?? 0),
            'low_stock_count' => (int) ($result['low_stock_count'] ?? 0),
            'out_of_stock_count' => (int) ($result['out_of_stock_count'] ?? 0),
            'total_inventory_value' => (float) ($result['total_inventory_value'] ?? 0)
        ];
    }

    /**
     * Cập nhật ngưỡng cảnh báo
     * 
     * @param int $variantId ID của variant
     * @param int $minThreshold Ngưỡng tối thiểu
     * @param string $warehouse Tên kho
     * @return bool Thành công hay không
     */
    public function updateThreshold(int $variantId, int $minThreshold, string $warehouse = 'default'): bool
    {
        $sql = "UPDATE {$this->table} 
                SET min_threshold = ?, last_updated = NOW()
                WHERE product_variant_id = ? AND warehouse = ?";

        return $this->execute($sql, [$minThreshold, $variantId, $warehouse]);
    }
}
