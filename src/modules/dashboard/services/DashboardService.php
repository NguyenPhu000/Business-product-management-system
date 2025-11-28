<?php

namespace Modules\Dashboard\Services;

use Modules\Auth\Models\UserModel;
use Modules\System\Models\UserLogModel;
use Modules\Auth\Models\RoleModel;
use Modules\System\Models\SystemConfigModel;
use Core\Database;

/**
 * DashboardService - Business logic cho Dashboard
 * 
 * Chức năng:
 * - Lấy thống kê tổng quan hệ thống
 * - Lấy dữ liệu logs gần đây  
 * - Lấy users mới nhất
 * - Lấy thống kê sản phẩm, tồn kho, doanh thu
 */
class DashboardService
{
    private UserModel $userModel;
    private UserLogModel $logModel;
    private RoleModel $roleModel;
    private SystemConfigModel $configModel;
    private Database $db;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logModel = new UserLogModel();
        $this->roleModel = new RoleModel();
        $this->configModel = new SystemConfigModel();
        $this->db = Database::getInstance();
    }

    /**
     * Lấy thống kê tổng quan
     * 
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'totalUsers' => $this->userModel->count(),
            'activeUsers' => $this->userModel->count(['status' => STATUS_ACTIVE]),
            'totalRoles' => $this->roleModel->count(),
            'totalLogs' => $this->logModel->count(),
        ];
    }

    /**
     * Lấy logs gần đây
     * 
     * @param int $limit Số lượng logs
     * @return array
     */
    public function getRecentLogs(int $limit = 10): array
    {
        return $this->logModel->getAllWithUser($limit);
    }

    /**
     * Lấy users mới nhất
     * 
     * @param int $limit Số lượng users
     * @return array
     */
    public function getRecentUsers(int $limit = 5): array
    {
        $users = $this->userModel->getAllWithRole();
        return array_slice($users, 0, $limit);
    }

    /**
     * Lấy toàn bộ dữ liệu dashboard
     * 
     * @return array
     */
    public function getDashboardData(): array
    {
        return [
            'stats' => $this->getStatistics(),
            'businessStats' => $this->getBusinessStatistics(),
            'inventoryStats' => $this->getInventoryStatistics(),
            'recentLogs' => $this->getRecentLogs(10),
            'recentUsers' => $this->getRecentUsers(5),
            'lowStockProducts' => $this->getLowStockProducts(5),
            'topSellingProducts' => $this->getTopSellingProducts(5),
            'revenueChart' => $this->getRevenueChartData(7)
        ];
    }

    /**
     * Lấy thống kê kinh doanh (products, sales, purchase)
     */
    private function getBusinessStatistics(): array
    {
        $pdo = $this->db->getConnection();

        // Tổng sản phẩm
        $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 1");
        $totalProducts = $stmt->fetchColumn();

        // Tổng variants
        $stmt = $pdo->query("SELECT COUNT(*) FROM product_variants WHERE is_active = 1");
        $totalVariants = $stmt->fetchColumn();

        // Tổng danh mục
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1");
        $totalCategories = $stmt->fetchColumn();

        // Tổng thương hiệu
        $stmt = $pdo->query("SELECT COUNT(*) FROM brands WHERE is_active = 1");
        $totalBrands = $stmt->fetchColumn();

        // Tổng nhà cung cấp
        $stmt = $pdo->query("SELECT COUNT(*) FROM suppliers WHERE is_active = 1");
        $totalSuppliers = $stmt->fetchColumn();

        return [
            'totalProducts' => $totalProducts,
            'totalVariants' => $totalVariants,
            'totalCategories' => $totalCategories,
            'totalBrands' => $totalBrands,
            'totalSuppliers' => $totalSuppliers
        ];
    }

    /**
     * Lấy thống kê tồn kho
     */
    private function getInventoryStatistics(): array
    {
        $pdo = $this->db->getConnection();

        // Tổng giá trị và số lượng tồn kho (từ inventory_transactions)
        $stmt = $pdo->query("
            SELECT 
                SUM(
                    CASE 
                        WHEN it.quantity_change > 0 THEN it.quantity_change * pv.unit_cost
                        ELSE 0
                    END
                ) as total_value,
                SUM(it.quantity_change) as total_quantity
            FROM inventory_transactions it
            INNER JOIN product_variants pv ON it.product_variant_id = pv.id
            WHERE pv.is_active = 1
        ");
        $inventory = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Tính tồn kho hiện tại cho mỗi variant (dùng threshold cố định là 10)
        $stmt = $pdo->query("
            SELECT 
                pv.id,
                COALESCE(SUM(it.quantity_change), 0) as current_stock
            FROM product_variants pv
            LEFT JOIN inventory_transactions it ON pv.id = it.product_variant_id
            WHERE pv.is_active = 1
            GROUP BY pv.id
        ");
        $variants = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $lowStock = 0;
        $outOfStock = 0;
        $totalValue = 0;
        $lowStockThreshold = 10; // Ngưỡng mặc định

        foreach ($variants as $variant) {
            $stock = (int)$variant['current_stock'];

            if ($stock == 0) {
                $outOfStock++;
            } elseif ($stock > 0 && $stock <= $lowStockThreshold) {
                $lowStock++;
            }
        }

        return [
            'totalValue' => $inventory['total_value'] ?? 0,
            'totalQuantity' => abs($inventory['total_quantity'] ?? 0),
            'lowStock' => $lowStock,
            'outOfStock' => $outOfStock
        ];
    }

    /**
     * Lấy sản phẩm sắp hết hàng
     */
    private function getLowStockProducts(int $limit = 5): array
    {
        $pdo = $this->db->getConnection();
        $lowStockThreshold = 10; // Ngưỡng mặc định

        $stmt = $pdo->prepare("
            SELECT 
                p.name as product_name,
                pv.sku,
                COALESCE(SUM(it.quantity_change), 0) as current_stock,
                pv.unit_cost,
                pv.price as sale_price
            FROM product_variants pv
            INNER JOIN products p ON pv.product_id = p.id
            LEFT JOIN inventory_transactions it ON pv.id = it.product_variant_id
            WHERE pv.is_active = 1
            GROUP BY pv.id, p.id
            HAVING current_stock > 0 AND current_stock <= ?
            ORDER BY current_stock ASC
            LIMIT ?
        ");
        $stmt->execute([$lowStockThreshold, $limit]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Lấy top sản phẩm bán chạy (7 ngày gần nhất)
     */
    private function getTopSellingProducts(int $limit = 5): array
    {
        try {
            $pdo = $this->db->getConnection();

            // Kiểm tra xem bảng sales_orders có tồn tại không
            $stmt = $pdo->query("SHOW TABLES LIKE 'sales_orders'");
            if ($stmt->rowCount() === 0) {
                return []; // Bảng chưa tồn tại
            }

            $stmt = $pdo->prepare("
                SELECT 
                    p.name as product_name,
                    pv.sku,
                    SUM(sd.quantity) as total_sold,
                    SUM(sd.sale_price * sd.quantity) as total_revenue
                FROM sales_details sd
                INNER JOIN product_variants pv ON sd.product_variant_id = pv.id
                INNER JOIN products p ON pv.product_id = p.id
                INNER JOIN sales_orders so ON sd.sales_order_id = so.id
                WHERE so.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                GROUP BY p.id, pv.id
                ORDER BY total_sold DESC
                LIMIT ?
            ");
            $stmt->execute([$limit]);

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Dashboard getTopSellingProducts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy dữ liệu biểu đồ doanh thu (7 ngày gần nhất)
     */
    private function getRevenueChartData(int $days = 7): array
    {
        try {
            $pdo = $this->db->getConnection();

            // Kiểm tra xem bảng sales_orders có tồn tại không
            $stmt = $pdo->query("SHOW TABLES LIKE 'sales_orders'");
            if ($stmt->rowCount() === 0) {
                return ['labels' => [], 'revenues' => [], 'orders' => []]; // Bảng chưa tồn tại
            }

            $stmt = $pdo->prepare("
                SELECT 
                    DATE(so.created_at) as date,
                    SUM(sd.sale_price * sd.quantity) as revenue,
                    COUNT(DISTINCT so.id) as orders
                FROM sales_orders so
                INNER JOIN sales_details sd ON so.id = sd.sales_order_id
                WHERE so.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                GROUP BY DATE(so.created_at)
                ORDER BY date ASC
            ");
            $stmt->execute([$days]);

            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $labels = [];
            $revenues = [];
            $orders = [];

            foreach ($data as $row) {
                $labels[] = date('d/m', strtotime($row['date']));
                $revenues[] = $row['revenue'];
                $orders[] = $row['orders'];
            }

            return [
                'labels' => $labels,
                'revenues' => $revenues,
                'orders' => $orders
            ];
        } catch (\Exception $e) {
            error_log('Dashboard getRevenueChartData error: ' . $e->getMessage());
            return ['labels' => [], 'revenues' => [], 'orders' => []];
        }
    }
}
