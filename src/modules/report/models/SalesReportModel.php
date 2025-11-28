<?php

namespace Modules\Report\Models;

use Core\BaseModel;

/**
 * SalesReportModel - Báo cáo doanh thu - lợi nhuận
 * 
 * Chức năng:
 * - Lấy doanh thu theo sản phẩm/danh mục
 * - Tính lợi nhuận gộp theo sản phẩm
 */
class SalesReportModel extends BaseModel
{
    protected string $table = 'sales_details';
    protected string $primaryKey = 'id';

    /**
     * Lấy doanh thu theo sản phẩm trong khoảng thời gian
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getSalesRevenueByProduct(
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 100,
        int $offset = 0
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
                AVG(sd.sale_price) as average_price,
                COUNT(DISTINCT so.id) as number_of_orders
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            LEFT JOIN brands b ON p.brand_id = b.id
            WHERE {$whereClause}
            GROUP BY p.id, pv.id
            ORDER BY total_revenue DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Lấy doanh thu theo danh mục
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getSalesRevenueByCategory(
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
                c.id,
                c.name as category_name,
                SUM(sd.quantity) as total_quantity_sold,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                COUNT(DISTINCT p.id) as product_count,
                COUNT(DISTINCT so.id) as number_of_orders
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            INNER JOIN product_categories pc ON p.id = pc.product_id
            INNER JOIN categories c ON pc.category_id = c.id
            WHERE {$whereClause}
            GROUP BY c.id, c.name
            ORDER BY total_revenue DESC
        ";

        return $this->query($sql, $params);
    }

    /**
     * Lấy thống kê doanh thu tổng quát
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTotalSalesStatistics(
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
                COUNT(DISTINCT so.id) as total_orders,
                COUNT(DISTINCT p.id) as unique_products,
                SUM(sd.quantity) as total_quantity_sold,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                AVG(sd.sale_price * sd.quantity) as average_order_value
            FROM sales_details sd
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            WHERE {$whereClause}
        ";

        $result = $this->queryOne($sql, $params);

        return [
            'total_orders' => (int) ($result['total_orders'] ?? 0),
            'unique_products' => (int) ($result['unique_products'] ?? 0),
            'total_quantity_sold' => (int) ($result['total_quantity_sold'] ?? 0),
            'total_revenue' => (float) ($result['total_revenue'] ?? 0),
            'average_order_value' => (float) ($result['average_order_value'] ?? 0)
        ];
    }

    /**
     * Lấy doanh thu theo ngày
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getDailySalesRevenue(
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
                DATE(so.created_at) as sales_date,
                COUNT(DISTINCT so.id) as number_of_orders,
                SUM(sd.quantity) as total_quantity,
                SUM(sd.sale_price * sd.quantity) as daily_revenue
            FROM sales_details sd
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY DATE(so.created_at)
            ORDER BY sales_date DESC
        ";

        return $this->query($sql, $params);
    }

    /**
     * Lấy lợi nhuận gộp theo sản phẩm (doanh thu - giá vốn)
     * 
     * Sử dụng Average Cost để tính giá vốn
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getGrossProfitByProduct(
        ?string $startDate = null,
        ?string $endDate = null,
        int $limit = 100,
        int $offset = 0
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
                pv.id as variant_id,
                pv.sku as variant_sku,
                SUM(sd.quantity) as total_quantity_sold,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                AVG(pv.unit_cost) as average_unit_cost,
                (AVG(pv.unit_cost) * SUM(sd.quantity)) as total_cost_of_goods_sold,
                (SUM(sd.sale_price * sd.quantity) - (AVG(pv.unit_cost) * SUM(sd.quantity))) as gross_profit,
                CASE 
                    WHEN SUM(sd.sale_price * sd.quantity) > 0
                    THEN ROUND(((SUM(sd.sale_price * sd.quantity) - (AVG(pv.unit_cost) * SUM(sd.quantity))) / SUM(sd.sale_price * sd.quantity)) * 100, 2)
                    ELSE 0
                END as profit_margin_percent
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY p.id, pv.id
            ORDER BY gross_profit DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;

        return $this->query($sql, $params);
    }

    /**
     * Lấy thống kê lợi nhuận tổng quát
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTotalProfitStatistics(
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
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                SUM(pv.unit_cost * sd.quantity) as total_cogs,
                (SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) as total_gross_profit,
                CASE 
                    WHEN SUM(sd.sale_price * sd.quantity) > 0
                    THEN ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity)) * 100, 2)
                    ELSE 0
                END as profit_margin_percent
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
        ";

        $result = $this->queryOne($sql, $params);

        return [
            'total_revenue' => (float) ($result['total_revenue'] ?? 0),
            'total_cogs' => (float) ($result['total_cogs'] ?? 0),
            'total_gross_profit' => (float) ($result['total_gross_profit'] ?? 0),
            'profit_margin_percent' => (float) ($result['profit_margin_percent'] ?? 0)
        ];
    }
}

