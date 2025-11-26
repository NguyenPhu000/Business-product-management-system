<?php

namespace Modules\Report\Models;

use Core\BaseModel;

/**
 * InventoryReportModel - Báo cáo tồn kho
 * 
 * Chức năng:
 * - Lấy danh sách sản phẩm còn hàng, hết hàng
 * - Lấy lịch sử nhập - xuất theo thời gian
 */
class InventoryReportModel extends BaseModel
{
    protected string $table = 'products';
    protected string $primaryKey = 'id';

    /**
     * Tính tồn đầu kỳ cho báo cáo (tại thời điểm startDate, có thể filter theo SKU)
     */
    public function getOpeningStock(?string $startDate = null, ?string $productSku = null): int
    {
        $where = ['1=1'];
        $params = [];
        if ($productSku) {
            $where[] = "pv.sku = ?";
            $params[] = $productSku;
        }
        if ($startDate) {
            $where[] = "it.created_at < ?";
            $params[] = $startDate . ' 00:00:00';
        }
        $whereClause = implode(' AND ', $where);
        $sql = "
            SELECT IFNULL(SUM(it.quantity_change),0) as opening_stock
            FROM inventory_transactions it
            INNER JOIN product_variants pv ON it.product_variant_id = pv.id
            WHERE {$whereClause}
        ";
        $result = $this->queryOne($sql, $params);
        return (int) ($result['opening_stock'] ?? 0);
    }

    /**
     * Lấy tồn kho của một sản phẩm cụ thể tại một ngày nhất định (cuối ngày)
     * 
     * @param string $date Y-m-d
     * @param string $productSku SKU sản phẩm
     * @return int Số lượng tồn kho
     */
    public function getStockAtDate(string $date, string $productSku = ''): int
    {
        // Nếu không có SKU, lấy tổng tất cả sản phẩm
        if (empty($productSku)) {
            $sql = "
                SELECT IFNULL(SUM(it.quantity_change), 0) as stock_at_date
                FROM inventory_transactions it
                WHERE DATE(it.created_at) <= ?
            ";
            $result = $this->queryOne($sql, [$date]);
            return (int) ($result['stock_at_date'] ?? 0);
        }

        // Nếu có SKU/tên, tìm kiếm theo:
        // - SKU variant (pv.sku)
        // - SKU sản phẩm (p.sku)
        // - Tên sản phẩm (p.name) - hỗ trợ LIKE để tìm bằng tên sản phẩm
        $sql = "
            SELECT IFNULL(SUM(it.quantity_change), 0) as stock_at_date
            FROM inventory_transactions it
            INNER JOIN product_variants pv ON it.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            WHERE DATE(it.created_at) <= ?
            AND (
                pv.sku = ? 
                OR p.sku = ? 
                OR LOWER(p.name) LIKE LOWER(?)
                OR LOWER(pv.sku) LIKE LOWER(?)
            )
        ";
        $searchTerm = '%' . $productSku . '%';
        $result = $this->queryOne($sql, [$date, $productSku, $productSku, $searchTerm, $searchTerm]);
        return (int) ($result['stock_at_date'] ?? 0);
    }

    /**
     * Lấy chi tiết tồn kho từng sản phẩm tại một ngày cụ thể
     * 
     * @param string $date Y-m-d
     * @return array Mảng [{product_name, product_sku, variant_sku, stock_at_date}, ...]
     */
    public function getProductStockDetailsAtDate(string $date): array
    {
        $sql = "
            SELECT 
                p.name as product_name,
                p.sku as product_sku,
                pv.sku as variant_sku,
                pv.id as variant_id,
                IFNULL(SUM(it.quantity_change), 0) as stock_at_date
            FROM product_variants pv
            LEFT JOIN products p ON pv.product_id = p.id
            LEFT JOIN inventory_transactions it ON it.product_variant_id = pv.id AND DATE(it.created_at) <= ?
            GROUP BY pv.id, p.id, p.name, p.sku, pv.sku
            HAVING stock_at_date > 0 OR p.id IS NOT NULL
            ORDER BY p.name ASC, pv.sku ASC
        ";
        return $this->query($sql, [$date]);
    }

    /**
     * Lấy lịch sử tồn kho của một sản phẩm (tất cả các ngày, tương tự như report filter)
     * 
     * @param string $productSku
     * @return array Mảng [{date, opening_balance, total_import, total_export, closing_balance}, ...]
     */
    public function getProductStockHistory(string $productSku): array
    {
        // Lấy tất cả giao dịch của sản phẩm này, sắp xếp theo ngày
        $sql = "
            SELECT 
                DATE(it.created_at) as transaction_date,
                it.quantity_change,
                it.type
            FROM inventory_transactions it
            INNER JOIN product_variants pv ON it.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            WHERE pv.sku = ? OR p.sku = ? OR LOWER(p.name) LIKE LOWER(?) OR LOWER(pv.sku) LIKE LOWER(?)
            ORDER BY it.created_at ASC
        ";
        $searchTerm = '%' . $productSku . '%';
        $transactions = $this->query($sql, [$productSku, $productSku, $searchTerm, $searchTerm]);

        // Tổng hợp theo ngày như trong getInventoryOverTimeReport
        $dailyBalances = [];
        $runningBalance = 0;
        $currentDate = null;
        $currentDayData = null;

        foreach ($transactions as $tr) {
            $date = $tr['transaction_date'];
            
            // Nếu chuyển sang ngày mới
            if ($date !== $currentDate) {
                // Lưu ngày cũ nếu có
                if ($currentDayData !== null) {
                    $currentDayData['closing_balance'] = $runningBalance;
                    $dailyBalances[] = $currentDayData;
                }
                
                // Khởi tạo ngày mới
                $currentDate = $date;
                $currentDayData = [
                    'date' => $date,
                    'opening_balance' => $runningBalance,
                    'total_import' => 0,
                    'total_export' => 0,
                    'closing_balance' => 0
                ];
            }

            // Cập nhật tổng import/export
            if ($tr['type'] === 'import') {
                $currentDayData['total_import'] += $tr['quantity_change'];
                $runningBalance += $tr['quantity_change'];
            } elseif ($tr['type'] === 'export') {
                $currentDayData['total_export'] += abs($tr['quantity_change']);
                $runningBalance -= abs($tr['quantity_change']);
            } else {
                // adjust - có thể tăng hoặc giảm
                if ($tr['quantity_change'] >= 0) {
                    $currentDayData['total_import'] += $tr['quantity_change'];
                    $runningBalance += $tr['quantity_change'];
                } else {
                    $currentDayData['total_export'] += abs($tr['quantity_change']);
                    $runningBalance -= abs($tr['quantity_change']);
                }
            }
        }

        // Lưu ngày cuối cùng
        if ($currentDayData !== null) {
            $currentDayData['closing_balance'] = $runningBalance;
            $dailyBalances[] = $currentDayData;
        }

        return $dailyBalances;
    }

    /**
     * Lấy danh sách sản phẩm với tồn kho theo trạng thái
     * 
     * @param string $status 'in_stock' | 'low_stock' | 'out_of_stock'
     * @param int $limit Số lượng records
     * @param int $offset Offset
     * @return array Danh sách sản phẩm
     */
    public function getProductsByStockStatus(string $status = '', int $limit = 100, int $offset = 0): array
    {
        $where = ['1=1'];
        $params = [];

        // Status filter
        if ($status === 'in_stock') {
            $where[] = "i.quantity > i.min_threshold";
        } elseif ($status === 'low_stock') {
            $where[] = "i.quantity > 0 AND i.quantity <= i.min_threshold";
        } elseif ($status === 'out_of_stock') {
            $where[] = "i.quantity = 0";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT 
                p.id,
                p.name as product_name,
                p.sku as product_sku,
                b.name as brand_name,
                pv.id as variant_id,
                pv.sku as variant_sku,
                pv.attributes as variant_attributes,
                pv.price as variant_price,
                i.warehouse,
                i.quantity as current_stock,
                i.min_threshold,
                CASE 
                    WHEN i.quantity = 0 THEN 'out_of_stock'
                    WHEN i.quantity <= i.min_threshold THEN 'low_stock'
                    ELSE 'in_stock'
                END as stock_status,
                i.last_updated
            FROM products p
            INNER JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN inventory i ON pv.id = i.product_variant_id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE {$whereClause}
            ORDER BY p.name ASC, pv.sku ASC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Đếm tổng số sản phẩm theo status
     * 
     * @param string $status
     * @return int
     */
    public function countByStockStatus(string $status = ''): int
    {
        $where = ['1=1'];
        $params = [];

        if ($status === 'in_stock') {
            $where[] = "i.quantity > i.min_threshold";
        } elseif ($status === 'low_stock') {
            $where[] = "i.quantity > 0 AND i.quantity <= i.min_threshold";
        } elseif ($status === 'out_of_stock') {
            $where[] = "i.quantity = 0";
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT COUNT(DISTINCT p.id) as total
            FROM products p
            INNER JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN inventory i ON pv.id = i.product_variant_id
            WHERE {$whereClause}
        ";

        $result = $this->queryOne($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Lấy thống kê tồn kho tổng quát
     * 
     * @return array ['in_stock' => int, 'low_stock' => int, 'out_of_stock' => int, 'total_variants' => int]
     */
    public function getStockStatistics(): array
    {
        $sql = "
            SELECT 
                SUM(CASE WHEN i.quantity > i.min_threshold THEN 1 ELSE 0 END) as in_stock,
                SUM(CASE WHEN i.quantity > 0 AND i.quantity <= i.min_threshold THEN 1 ELSE 0 END) as low_stock,
                SUM(CASE WHEN i.quantity = 0 THEN 1 ELSE 0 END) as out_of_stock,
                COUNT(DISTINCT pv.id) as total_variants
            FROM product_variants pv
            LEFT JOIN inventory i ON pv.id = i.product_variant_id
        ";

        $result = $this->queryOne($sql);

        return [
            'in_stock' => (int) ($result['in_stock'] ?? 0),
            'low_stock' => (int) ($result['low_stock'] ?? 0),
            'out_of_stock' => (int) ($result['out_of_stock'] ?? 0),
            'total_variants' => (int) ($result['total_variants'] ?? 0)
        ];
    }

    /**
     * Lấy lịch sử nhập - xuất hàng theo thời gian
     * 
     * @param string $type 'import' | 'export' | 'all'
     * @param string|null $startDate Ngày bắt đầu (Y-m-d)
     * @param string|null $endDate Ngày kết thúc (Y-m-d)
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTransactionHistory(
        string $type = 'all',
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 100,
        int $offset = 0,
        ?string $productSku = null
    ): array {
        $where = ['1=1'];
        $params = [];

        // Type filter
        if ($type === 'import') {
            $where[] = "it.type = 'import'";
        } elseif ($type === 'export') {
            $where[] = "it.type = 'export'";
        } elseif ($type === 'adjust') {
            $where[] = "it.type = 'adjust'";
        }

        // Date range filter
        if ($startDate) {
            $where[] = "DATE(it.created_at) >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = "DATE(it.created_at) <= ?";
            $params[] = $endDate;
        }

        // Product SKU / name filter (product or variant SKU or product name)
        if ($productSku) {
            $where[] = "(pv.sku = ? OR p.sku = ? OR p.name LIKE ?)";
            $params[] = $productSku;
            $params[] = $productSku;
            $params[] = '%' . $productSku . '%';
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT 
                it.id,
                it.product_variant_id,
                it.type,
                it.quantity_change,
                it.warehouse,
                it.reference_type,
                it.reference_id,
                it.note,
                p.id as product_id,
                p.name as product_name,
                p.sku as product_sku,
                pv.id as variant_id,
                pv.sku as variant_sku,
                pv.attributes,
                u.username as created_by_username,
                it.created_at
            FROM inventory_transactions it
            INNER JOIN product_variants pv ON it.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN users u ON it.created_by = u.id
            WHERE {$whereClause}
            ORDER BY it.created_at ASC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Đếm lịch sử giao dịch
     * 
     * @param string $type
     * @param string|null $startDate
     * @param string|null $endDate
     * @return int
     */
    public function countTransactions(
        string $type = 'all',
        ?string $startDate = null,
        ?string $endDate = null
    ): int {
        $where = ['1=1'];
        $params = [];

        if ($type === 'import') {
            $where[] = "it.type = 'import'";
        } elseif ($type === 'export') {
            $where[] = "it.type = 'export'";
        } elseif ($type === 'adjust') {
            $where[] = "it.type = 'adjust'";
        }

        if ($startDate) {
            $where[] = "DATE(it.created_at) >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = "DATE(it.created_at) <= ?";
            $params[] = $endDate;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT COUNT(*) as total
            FROM inventory_transactions it
            WHERE {$whereClause}
        ";

        $result = $this->queryOne($sql, $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Lấy tổng nhập - xuất hàng theo ngày
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getDailyTransactionSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $where = ['1=1'];
        $params = [];

        if ($startDate) {
            $where[] = "DATE(it.created_at) >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = "DATE(it.created_at) <= ?";
            $params[] = $endDate;
        }

        $whereClause = implode(' AND ', $where);

        $sql = "
            SELECT 
                DATE(it.created_at) as transaction_date,
                it.type,
                COUNT(*) as transaction_count,
                SUM(ABS(it.quantity_change)) as total_quantity
            FROM inventory_transactions it
            WHERE {$whereClause}
            GROUP BY DATE(it.created_at), it.type
            ORDER BY transaction_date DESC
        ";

        return $this->query($sql, $params);
    }

    /**
     * Lấy tên sản phẩm từ SKU (tìm theo variant SKU, product SKU, hoặc tên sản phẩm)
     * 
     * @param string $sku
     * @return string|null
     */
    public function getProductNameBySku(string $sku): ?string
    {
        $sql = "
            SELECT p.name
            FROM products p
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE pv.sku = ? OR p.sku = ? OR LOWER(p.name) LIKE LOWER(?)
            LIMIT 1
        ";
        $searchTerm = '%' . $sku . '%';
        $result = $this->queryOne($sql, [$sku, $sku, $searchTerm]);
        
        return $result ? $result['name'] : null;
    }
}
