<?php

namespace Modules\Report\Models;

use Core\BaseModel;

/**
 * SalesAnalyticsModel - Phân tích doanh thu toàn diện
 * 
 * Chức năng:
 * - Lấy KPI doanh thu, lợi nhuận, đơn hàng
 * - Xu hướng doanh thu theo ngày/tháng
 * - Top sản phẩm, danh mục
 * - Phân tích chi tiết sản phẩm/danh mục
 */
class SalesAnalyticsModel extends BaseModel
{
    protected string $table = 'sales_orders';
    protected string $primaryKey = 'id';

    /**
     * Lấy KPI tóm tắt (Total Revenue, Gross Profit, Orders, AOV, etc)
     */
    public function getSummaryKPIs(?string $startDate = null, ?string $endDate = null): array
    {
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
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                COUNT(DISTINCT so.customer_name) as unique_customers
            FROM sales_orders so
            LEFT JOIN sales_details sd ON so.id = sd.sales_order_id
            WHERE {$whereClause}
        ";

        $result = $this->queryOne($sql, $params);
        
        // Tính AOV = Total Revenue / Total Orders
        $aov = ($result['total_orders'] && $result['total_revenue']) 
            ? ($result['total_revenue'] / $result['total_orders']) 
            : 0;
        return [
            'total_orders' => (int) ($result['total_orders'] ?? 0),
            'total_revenue' => (float) ($result['total_revenue'] ?? 0),
            'average_order_value' => (float) $aov,
            'unique_customers' => (int) ($result['unique_customers'] ?? 0)
        ];
    }

    /**
     * Lấy tổng lợi nhuận gộp (Doanh thu - COGS)
     */
    public function getGrossProfitKPI(?string $startDate = null, ?string $endDate = null): float
    {
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
                SUM(pv.unit_cost * sd.quantity) as total_cost
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
        ";

        $result = $this->queryOne($sql, $params);
        $revenue = (float) ($result['total_revenue'] ?? 0);
        $cost = (float) ($result['total_cost'] ?? 0);
        
        return $revenue - $cost;
    }

    /**
     * Lấy xu hướng doanh thu theo ngày (cho Line Chart)
     */
    public function getDailyRevenuesTrend(?string $startDate = null, ?string $endDate = null): array
    {
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
                DATE(so.created_at) as sale_date,
                COUNT(DISTINCT so.id) as order_count,
                SUM(sd.sale_price * sd.quantity) as daily_revenue,
                SUM(pv.unit_cost * sd.quantity) as daily_cost
            FROM sales_orders so
            LEFT JOIN sales_details sd ON so.id = sd.sales_order_id
            LEFT JOIN product_variants pv ON sd.product_variant_id = pv.id
            WHERE {$whereClause}
            GROUP BY DATE(so.created_at)
            ORDER BY sale_date ASC
        ";

        return $this->query($sql, $params);
    }

    /**
     * Lấy Top N sản phẩm bán chạy nhất (theo số lượng bán)
     */
    public function getTopProductsByRevenue(int $topN = 10, ?string $startDate = null, ?string $endDate = null): array
    {
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
                SUM(sd.quantity) as total_quantity_sold,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                SUM(pv.unit_cost * sd.quantity) as total_cost,
                (SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) as gross_profit,
                ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2) as profit_margin_percent,
                COUNT(DISTINCT so.id) as order_count
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY p.id, p.name, p.sku
            ORDER BY total_revenue DESC
            LIMIT ?
        ";

        $params[] = $topN;
        return $this->query($sql, $params);
    }

    /**
     * Lấy doanh thu theo danh mục CHA (gộp theo parent category)
     * Ghi chú: Hiển thị TẤT CẢ danh mục (kể cả chưa bán hàng) và sản phẩm không có danh mục
     */
    public function getRevenueByCategory(?string $startDate = null, ?string $endDate = null): array
    {
        $where = [];
        $params = [];

        if ($startDate) {
            $where[] = "DATE(so.created_at) >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = "DATE(so.created_at) <= ?";
            $params[] = $endDate;
        }

        $whereClause = empty($where) ? '1=1' : implode(' AND ', $where);

        $sql = "
            SELECT 
                c.id,
                c.name as category_name,
                COALESCE(SUM(sd.sale_price * sd.quantity), 0) as category_revenue,
                COALESCE(SUM(pv.unit_cost * sd.quantity), 0) as category_cost,
                COALESCE((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)), 0) as category_profit,
                COALESCE(COUNT(DISTINCT so.id), 0) as order_count,
                COALESCE(COUNT(DISTINCT sd.product_variant_id), 0) as product_variant_count,
                0 as sort_order
            FROM categories c
            LEFT JOIN product_categories pc ON c.id = pc.category_id
            LEFT JOIN products p ON pc.product_id = p.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            LEFT JOIN sales_orders so ON sd.sales_order_id = so.id AND {$whereClause}
            GROUP BY c.id, c.name
            
            UNION ALL
            
            SELECT 
                0 as id,
                'Không có danh mục' as category_name,
                COALESCE(SUM(sd.sale_price * sd.quantity), 0) as category_revenue,
                COALESCE(SUM(pv.unit_cost * sd.quantity), 0) as category_cost,
                COALESCE((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)), 0) as category_profit,
                COALESCE(COUNT(DISTINCT so.id), 0) as order_count,
                COALESCE(COUNT(DISTINCT sd.product_variant_id), 0) as product_variant_count,
                1 as sort_order
            FROM product_variants pv
            LEFT JOIN products p ON pv.product_id = p.id
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            LEFT JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE pc.category_id IS NULL AND {$whereClause}
            
            ORDER BY sort_order ASC, category_revenue DESC
        ";

        return $this->query($sql, $params);
    }

    /**
     * Lấy doanh thu theo danh mục CHA (gộp theo parent category) với filter danh mục
     * Ghi chú: Dùng khi người dùng click vào danh mục nào đó
     */
    public function getRevenueByCategoryFiltered(int $categoryId, ?string $startDate = null, ?string $endDate = null): array
    {
        $where = ['cat.id = ?'];
        $params = [$categoryId];

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
                COALESCE(cat.id, 0) as id,
                COALESCE(cat.name, 'Không có danh mục') as category_name,
                COALESCE(SUM(sd.sale_price * sd.quantity), 0) as category_revenue,
                COALESCE(SUM(pv.unit_cost * sd.quantity), 0) as category_cost,
                COALESCE((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)), 0) as category_profit,
                COALESCE(COUNT(DISTINCT so.id), 0) as order_count,
                COALESCE(COUNT(DISTINCT sd.product_variant_id), 0) as product_variant_count,
                CASE WHEN cat.id = 0 THEN 1 ELSE 0 END as sort_order
            FROM (
                -- Lấy tất cả danh mục cha
                SELECT id, name FROM categories WHERE parent_id IS NULL
                UNION ALL
                -- Thêm nhóm 'Không có danh mục'
                SELECT 0, 'Không có danh mục'
            ) cat
            LEFT JOIN product_categories pc ON cat.id = pc.category_id
            LEFT JOIN products p ON pc.product_id = p.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            LEFT JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY cat.id, cat.name
            ORDER BY sort_order ASC, category_revenue DESC
        ";

        return $this->query($sql, $params);
    }

    /**
     * Lấy danh sách tất cả sản phẩm với chi tiết doanh thu, lợi nhuận
     */
    public function getProductSalesDetails(?string $startDate = null, ?string $endDate = null, int $limit = 100, int $offset = 0): array
    {
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
                c.name as category_name,
                pv.sku as variant_sku,
                pv.unit_cost as cost_price,
                SUM(sd.quantity) as quantity_sold,
                AVG(sd.sale_price) as avg_sale_price,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                SUM(pv.unit_cost * sd.quantity) as total_cost,
                (SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) as gross_profit,
                ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2) as profit_margin_percent
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN product_categories pc ON p.id = pc.product_id
            INNER JOIN categories c ON pc.category_id = c.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY p.id, p.name, p.sku, c.name, pv.id, pv.sku, pv.unit_cost
            HAVING SUM(sd.quantity) > 0
            ORDER BY total_revenue DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;
        return $this->query($sql, $params);
    }

    /**
     * Lấy danh sách sản phẩm theo danh mục (với filter danh mục)
     * Dùng khi người dùng click vào danh mục nào đó
     */
    public function getProductSalesDetailsByCategory(int $categoryId, ?string $startDate = null, ?string $endDate = null, int $limit = 100, int $offset = 0): array
    {
        $where = [];
        $params = [];

        // Lọc theo danh mục cha
        if ($categoryId === 0) {
            // Lấy sản phẩm không có danh mục
            $where[] = "p.id NOT IN (SELECT DISTINCT product_id FROM product_categories)";
        } else {
            // Lấy sản phẩm trong danh mục hoặc các danh mục con của nó
            $where[] = "(pc.category_id = ? OR c.parent_id = ?)";
            $params[] = $categoryId;
            $params[] = $categoryId;
        }

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
                COALESCE(c.name, 'Không có danh mục') as category_name,
                pv.sku as variant_sku,
                pv.unit_cost as cost_price,
                SUM(sd.quantity) as quantity_sold,
                AVG(sd.sale_price) as avg_sale_price,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                SUM(pv.unit_cost * sd.quantity) as total_cost,
                (SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) as gross_profit,
                ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2) as profit_margin_percent
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY p.id, p.name, p.sku, c.name, pv.id, pv.sku, pv.unit_cost
            HAVING SUM(sd.quantity) > 0
            ORDER BY total_revenue DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $limit;
        $params[] = $offset;
        return $this->query($sql, $params);
    }

    /**
     * Lấy danh sách nhà sản xuất (brand) theo danh mục (với filter danh mục)
     * Dùng khi người dùng click vào danh mục nào đó
     */
    public function getBrandSalesDetailsByCategory(int $categoryId, ?string $startDate = null, ?string $endDate = null): array
    {
        $where = [];
        $params = [];

        // Lọc theo danh mục cha
        if ($categoryId === 0) {
            // Lấy thương hiệu của sản phẩm không có danh mục
            $where[] = "p.id NOT IN (SELECT DISTINCT product_id FROM product_categories)";
        } else {
            // Lấy thương hiệu của sản phẩm trong danh mục hoặc các danh mục con của nó
            $where[] = "(pc.category_id = ? OR c.parent_id = ?)";
            $params[] = $categoryId;
            $params[] = $categoryId;
        }

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
                COALESCE(b.id, 0) as brand_id,
                COALESCE(b.name, 'Không có thương hiệu') as brand_name,
                COUNT(DISTINCT so.id) as order_count,
                COUNT(DISTINCT pv.id) as product_count,
                SUM(sd.quantity) as total_quantity,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                SUM(pv.unit_cost * sd.quantity) as total_cost,
                (SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) as total_profit,
                CASE 
                    WHEN SUM(sd.sale_price * sd.quantity) > 0 THEN ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2)
                    ELSE 0
                END as profit_margin_percent,
                CASE WHEN COALESCE(b.id, 0) = 0 THEN 1 ELSE 0 END as sort_order
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY COALESCE(b.id, 0), COALESCE(b.name, 'Không có thương hiệu'), CASE WHEN COALESCE(b.id, 0) = 0 THEN 1 ELSE 0 END
            ORDER BY sort_order ASC, total_revenue DESC
        ";        return $this->query($sql, $params);
    }
    public function countProductsSold(?string $startDate = null, ?string $endDate = null): int
    {
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
            SELECT COUNT(DISTINCT p.id) as total
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY p.id
            HAVING SUM(sd.quantity) > 0
        ";

        $result = $this->query($sql, $params);
        return count($result);
    }

    /**
     * Lấy danh sách danh mục CHA với doanh thu chi tiết (gộp theo parent category)
     * Ghi chú: Hiển thị TẤT CẢ danh mục (kể cả chưa bán hàng) và sản phẩm không có danh mục
     */
    public function getCategorySalesDetails(?string $startDate = null, ?string $endDate = null): array
    {
        $where = [];
        $params = [];

        if ($startDate) {
            $where[] = "DATE(so.created_at) >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $where[] = "DATE(so.created_at) <= ?";
            $params[] = $endDate;
        }

        $whereClause = empty($where) ? '1=1' : implode(' AND ', $where);

        $sql = "
            SELECT 
                c.id,
                c.name as category_name,
                COALESCE(COUNT(DISTINCT so.id), 0) as order_count,
                COALESCE(COUNT(DISTINCT sd.product_variant_id), 0) as product_count,
                COALESCE(SUM(sd.quantity), 0) as total_quantity,
                COALESCE(SUM(sd.sale_price * sd.quantity), 0) as total_revenue,
                COALESCE(SUM(pv.unit_cost * sd.quantity), 0) as total_cost,
                COALESCE((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)), 0) as total_profit,
                CASE 
                    WHEN SUM(sd.sale_price * sd.quantity) > 0 THEN ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2)
                    ELSE 0
                END as profit_margin_percent,
                0 as sort_order
            FROM categories c
            LEFT JOIN product_categories pc ON c.id = pc.category_id
            LEFT JOIN products p ON pc.product_id = p.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            LEFT JOIN sales_orders so ON sd.sales_order_id = so.id AND {$whereClause}
            GROUP BY c.id, c.name
            
            UNION ALL
            
            SELECT 
                0 as id,
                'Không có danh mục' as category_name,
                COALESCE(COUNT(DISTINCT so.id), 0) as order_count,
                COALESCE(COUNT(DISTINCT sd.product_variant_id), 0) as product_count,
                COALESCE(SUM(sd.quantity), 0) as total_quantity,
                COALESCE(SUM(sd.sale_price * sd.quantity), 0) as total_revenue,
                COALESCE(SUM(pv.unit_cost * sd.quantity), 0) as total_cost,
                COALESCE((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)), 0) as total_profit,
                CASE 
                    WHEN SUM(sd.sale_price * sd.quantity) > 0 THEN ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2)
                    ELSE 0
                END as profit_margin_percent,
                1 as sort_order
            FROM product_variants pv
            LEFT JOIN products p ON pv.product_id = p.id
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            LEFT JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE pc.category_id IS NULL AND 1=1
            
            ORDER BY sort_order ASC, total_revenue DESC
        ";

        return $this->query($sql, $params);
    }

    /**
     * Lấy danh sách danh mục CHA với doanh thu chi tiết (với filter danh mục)
     * Ghi chú: Dùng khi người dùng click vào danh mục nào đó
     */
    public function getCategorySalesDetailsFiltered(int $categoryId, ?string $startDate = null, ?string $endDate = null): array
    {
        $where = ['cat.id = ?'];
        $params = [$categoryId];

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
                COALESCE(cat.id, 0) as id,
                COALESCE(cat.name, 'Không có danh mục') as category_name,
                COALESCE(COUNT(DISTINCT so.id), 0) as order_count,
                COALESCE(COUNT(DISTINCT sd.product_variant_id), 0) as product_count,
                COALESCE(SUM(sd.quantity), 0) as total_quantity,
                COALESCE(SUM(sd.sale_price * sd.quantity), 0) as total_revenue,
                COALESCE(SUM(pv.unit_cost * sd.quantity), 0) as total_cost,
                COALESCE((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)), 0) as total_profit,
                CASE 
                    WHEN SUM(sd.sale_price * sd.quantity) > 0 THEN ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2)
                    ELSE 0
                END as profit_margin_percent,
                CASE WHEN cat.id = 0 THEN 1 ELSE 0 END as sort_order
            FROM (
                -- Lấy tất cả danh mục cha
                SELECT id, name FROM categories WHERE parent_id IS NULL
                UNION ALL
                -- Thêm nhóm 'Không có danh mục'
                SELECT 0, 'Không có danh mục'
            ) cat
            LEFT JOIN product_categories pc ON cat.id = pc.category_id
            LEFT JOIN products p ON pc.product_id = p.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            LEFT JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY cat.id, cat.name
            ORDER BY sort_order ASC, total_revenue DESC
        ";

        return $this->query($sql, $params);
    }
    public function getBrandSalesDetails(?string $startDate = null, ?string $endDate = null): array
    {
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
                COALESCE(b.id, 0) as brand_id,
                COALESCE(b.name, 'Không có thương hiệu') as brand_name,
                COUNT(DISTINCT so.id) as order_count,
                COUNT(DISTINCT pv.id) as product_count,
                SUM(sd.quantity) as total_quantity,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                SUM(pv.unit_cost * sd.quantity) as total_cost,
                (SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) as total_profit,
                ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2) as profit_margin_percent,
                CASE WHEN COALESCE(b.id, 0) = 0 THEN 1 ELSE 0 END as sort_order
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN brands b ON p.brand_id = b.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY COALESCE(b.id, 0), COALESCE(b.name, 'Không có thương hiệu'), CASE WHEN COALESCE(b.id, 0) = 0 THEN 1 ELSE 0 END
            ORDER BY sort_order ASC, total_revenue DESC
        ";

        return $this->query($sql, $params);
    }

    /**
     * Lấy top sản phẩm bán chạy nhất (theo doanh thu thực tế > 0)
     */
    public function getTopSellingProducts(int $topN = 20, ?string $startDate = null, ?string $endDate = null): array
    {
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
                pv.sku as variant_sku,
                SUM(sd.quantity) as total_quantity_sold,
                SUM(sd.sale_price * sd.quantity) as total_revenue,
                SUM(pv.unit_cost * sd.quantity) as total_cost,
                (SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) as gross_profit,
                ROUND(((SUM(sd.sale_price * sd.quantity) - SUM(pv.unit_cost * sd.quantity)) / SUM(sd.sale_price * sd.quantity) * 100), 2) as profit_margin_percent,
                COUNT(DISTINCT so.id) as order_count
            FROM sales_details sd
            INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
            INNER JOIN products p ON pv.product_id = p.id
            INNER JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY p.id, p.name, p.sku, pv.id, pv.sku
            ORDER BY total_revenue DESC
            LIMIT ?
        ";

        $params[] = $topN;
        return $this->query($sql, $params);
    }

    /**
     * Lấy sản phẩm tồn kho lâu, ít bán (dựa trên tồn kho hiện tại và lịch sử bán)
     * Logic: Tồn kho cao, doanh thu thấp
     */
    public function getSlowMovingProducts(int $limit = 20, ?string $startDate = null, ?string $endDate = null): array
    {
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
                pv.sku as variant_sku,
                COALESCE(i.quantity, 0) as current_stock,
                COALESCE(SUM(sd.quantity), 0) as total_quantity_sold,
                COALESCE(SUM(sd.sale_price * sd.quantity), 0) as total_revenue,
                COALESCE(MAX(so.created_at), pv.created_at) as last_sale_date,
                DATEDIFF(NOW(), COALESCE(MAX(so.created_at), pv.created_at)) as days_since_last_sale
            FROM product_variants pv
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN inventory i ON pv.id = i.product_variant_id AND i.warehouse = 'default'
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            LEFT JOIN sales_orders so ON sd.sales_order_id = so.id
            WHERE {$whereClause}
            GROUP BY pv.id, p.id, p.name, p.sku, pv.sku, i.quantity, pv.created_at
            HAVING COALESCE(i.quantity, 0) > 0 AND COALESCE(SUM(sd.quantity), 0) <= 5
            ORDER BY 
                COALESCE(i.quantity, 0) DESC,
                days_since_last_sale DESC
            LIMIT ?
        ";

        $params[] = $limit;
        return $this->query($sql, $params);
    }

    /**
     * Lấy cấu trúc cây danh mục với doanh thu
     */
    public function getCategoryTreeWithRevenue(): array
    {
        $sql = "
            SELECT 
                c.id,
                c.name,
                c.parent_id,
                COALESCE(SUM(sd.sale_price * sd.quantity), 0) as total_revenue
            FROM categories c
            LEFT JOIN product_categories pc ON c.id = pc.category_id
            LEFT JOIN products p ON pc.product_id = p.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            LEFT JOIN sales_details sd ON pv.id = sd.product_variant_id
            GROUP BY c.id, c.name, c.parent_id
            ORDER BY c.parent_id ASC, c.name ASC
        ";
        return $this->query($sql);
    }
}
