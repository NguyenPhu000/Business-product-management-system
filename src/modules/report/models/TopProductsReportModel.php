<?php

namespace Modules\Report\Models;

use Core\BaseModel;

/**
 * TopProductsReportModel - Báo cáo top sản phẩm
 * 
 * Chức năng:
 * - Sản phẩm bán chạy nhất
 * - Sản phẩm tồn kho lâu, ít bán
 */
class TopProductsReportModel extends BaseModel
{
    protected string $table = 'sales_details';
    protected string $primaryKey = 'id';

    /**
     * Lấy sản phẩm bán chạy nhất (Top N)
     * 
     * @param int $topN Số lượng top sản phẩm
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTopSellingProducts(
        int $topN = 10,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $where = ['1=1'];
        $params = [];

        if ($startDate) {
            $where[] = "DATE(so.created_at) >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = "DATE(so.created_at) <= ?";
            $params[] = $endDate;
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
                SUM(sd.quantity) as total_quantity_sold,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                COUNT(DISTINCT so.id) as number_of_orders,
                AVG(sd.sale_price) as average_price
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE {$whereClause}
            GROUP BY p.id, pv.id
            ORDER BY total_quantity_sold DESC
            LIMIT ?
        ";

        $params[] = $topN;

        return $this->query($sql, $params);
    }

    /**
     * Lấy sản phẩm tồn kho lâu, ít bán (slow moving inventory)
     * 
     * Tiêu chí:
     * - Tồn kho cao
     * - Ngày bán cuối cùng đã lâu (hoặc chưa từng bán)
     * - Nhập cuối cùng cách đây lâu
     * 
     * @param int $topN
     * @param int $daysThreshold Số ngày để coi là "lâu" (default: 30 days)
     * @return array
     */
    public function getSlowMovingInventory(
        int $topN = 10,
        int $daysThreshold = 30
    ): array {
        $sql = "
            SELECT 
                p.id,
                p.name as product_name,
                p.sku as product_sku,
                b.name as brand_name,
                pv.id as variant_id,
                pv.sku as variant_sku,
                i.warehouse,
                i.quantity as current_stock,
                i.min_threshold,
                i.last_updated,
                COALESCE(last_import.import_date, pv.created_at) as last_import_date,
                COALESCE(last_sale.last_sale_date, NULL) as last_sale_date,
                DATEDIFF(NOW(), COALESCE(last_import.import_date, pv.created_at)) as days_since_import,
                DATEDIFF(NOW(), COALESCE(last_sale.last_sale_date, pv.created_at)) as days_since_last_sale,
                COALESCE(last_sale.last_quantity_sold, 0) as last_quantity_sold_30days
            FROM inventory i
            INNER JOIN product_variants pv ON i.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN (
                SELECT 
                    product_variant_id,
                    MAX(created_at) as import_date
                FROM inventory_transactions
                WHERE type IN ('import', 'adjust')
                GROUP BY product_variant_id
            ) last_import ON pv.id = last_import.product_variant_id
            LEFT JOIN (
                SELECT 
                    product_variant_id,
                    MAX(so.created_at) as last_sale_date,
                    SUM(sd.quantity) as last_quantity_sold_30days
                FROM sales_details sd
                INNER JOIN sales_orders so ON sd.sales_order_id = so.id
                WHERE so.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY product_variant_id
            ) last_sale ON pv.id = last_sale.product_variant_id
            WHERE i.quantity > i.min_threshold
                AND (
                    DATEDIFF(NOW(), COALESCE(last_sale.last_sale_date, pv.created_at)) >= ?
                    OR last_sale.last_sale_date IS NULL
                )
            ORDER BY i.quantity DESC, days_since_last_sale DESC
            LIMIT ?
        ";

        $params = [$daysThreshold, $topN];

        return $this->query($sql, $params);
    }

    /**
     * Lấy sản phẩm không bao giờ bán (dead stock)
     * 
     * @param int $topN
     * @return array
     */
    public function getDeadStock(int $topN = 10): array
    {
        $sql = "
            SELECT 
                p.id,
                p.name as product_name,
                p.sku as product_sku,
                b.name as brand_name,
                pv.id as variant_id,
                pv.sku as variant_sku,
                i.warehouse,
                i.quantity as current_stock,
                i.last_updated,
                COALESCE(last_import.import_date, pv.created_at) as last_import_date,
                DATEDIFF(NOW(), COALESCE(last_import.import_date, pv.created_at)) as days_in_stock
            FROM inventory i
            INNER JOIN product_variants pv ON i.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN (
                SELECT 
                    product_variant_id,
                    MAX(created_at) as import_date
                FROM inventory_transactions
                WHERE type IN ('import', 'adjust')
                GROUP BY product_variant_id
            ) last_import ON pv.id = last_import.product_variant_id
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            WHERE i.quantity > 0 AND sd.id IS NULL
            ORDER BY i.quantity DESC, days_in_stock DESC
            LIMIT ?
        ";

        return $this->query($sql, [$topN]);
    }

    /**
     * Lấy sản phẩm có chi phí cao nhất (high value products)
     * 
     * @param int $topN
     * @return array
     */
    public function getHighValueProducts(int $topN = 10): array
    {
        $sql = "
            SELECT 
                p.id,
                p.name as product_name,
                p.sku as product_sku,
                b.name as brand_name,
                pv.id as variant_id,
                pv.sku as variant_sku,
                pv.unit_cost,
                pv.price,
                i.quantity as current_stock,
                i.warehouse,
                (i.quantity * pv.unit_cost) as stock_value
            FROM inventory i
            INNER JOIN product_variants pv ON i.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            ORDER BY stock_value DESC, pv.unit_cost DESC
            LIMIT ?
        ";

        return $this->query($sql, [$topN]);
    }

    /**
     * Lấy top sản phẩm có lợi nhuận cao nhất
     * 
     * @param int $topN
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTopProfitProducts(
        int $topN = 10,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $where = ['1=1'];
        $params = [];

        if ($startDate) {
            $where[] = "DATE(so.created_at) >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = "DATE(so.created_at) <= ?";
            $params[] = $endDate;
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
                SUM(sd.quantity) as total_quantity_sold,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                AVG(pv.unit_cost) as average_unit_cost,
                (SUM(sd.sale_price * sd.quantity) - (AVG(pv.unit_cost) * SUM(sd.quantity))) as gross_profit,
                ROUND(((SUM(sd.sale_price * sd.quantity) - (AVG(pv.unit_cost) * SUM(sd.quantity))) / SUM(sd.sale_price * sd.quantity)) * 100, 2) as profit_margin
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE {$whereClause}
            GROUP BY p.id, pv.id
            ORDER BY gross_profit DESC
            LIMIT ?
        ";

        $params[] = $topN;

        return $this->query($sql, $params);
    }
}

