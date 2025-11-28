<?php

namespace Modules\Report\Services;

use Modules\Report\Models\InventoryReportModel;
use Modules\Report\Models\SalesReportModel;
use Modules\Report\Models\TopProductsReportModel;
use Exception;

/**
 * ReportService - Business logic cho báo cáo
 * 
 * Chức năng:
 * - Tổng hợp dữ liệu báo cáo từ các model
 * - Format dữ liệu cho views
 * - Tính toán KPI
 */
class ReportService
{
    private InventoryReportModel $inventoryModel;
    private SalesReportModel $salesModel;
    private TopProductsReportModel $topProductsModel;

    public function __construct()
    {
        $this->inventoryModel = new InventoryReportModel();
        $this->salesModel = new SalesReportModel();
        $this->topProductsModel = new TopProductsReportModel();
    }

    /**
     * Báo cáo tồn kho theo thời gian (daily closing balance + chart data)
     */
    public function getInventoryOverTimeReport(
        ?string $startDate = null,
        ?string $endDate = null,
        ?string $productSku = null,
        string $transactionType = 'all'
    ): array {
        // Lấy lịch sử giao dịch theo filter
        $transactions = $this->inventoryModel->getTransactionHistory(
            $transactionType,
            $startDate,
            $endDate,
            1000, // lấy tối đa 1000 giao dịch cho báo cáo
            0,
            $productSku
        );

        // Tính tồn đầu kỳ (trước ngày startDate)
        $openingStock = $this->inventoryModel->getOpeningStock($startDate, $productSku);

        // Tính daily closing balance theo ngày
        $totalImport = 0;
        $totalExport = 0;
        $running = $openingStock;
        $dailyBalances = []; // key: date, value: {opening, import, export, closing}

        foreach ($transactions as $tr) {
            $date = date('Y-m-d', strtotime($tr['created_at']));
            if (!isset($dailyBalances[$date])) {
                $dailyBalances[$date] = [
                    'date' => $date,
                    'opening_balance' => $running, // tồn đầu ngày = tồn cuối ngày trước
                    'total_import' => 0,
                    'total_export' => 0,
                    'closing_balance' => $running
                ];
            }

            // Cộng dồn quantity_change vào daily balance
            if ($tr['type'] === 'import') {
                $dailyBalances[$date]['total_import'] += $tr['quantity_change'];
                $totalImport += $tr['quantity_change'];
            } elseif ($tr['type'] === 'export') {
                $dailyBalances[$date]['total_export'] += abs($tr['quantity_change']);
                $totalExport += abs($tr['quantity_change']);
            } elseif ($tr['type'] === 'adjust') {
                if ($tr['quantity_change'] > 0) {
                    $dailyBalances[$date]['total_import'] += $tr['quantity_change'];
                    $totalImport += $tr['quantity_change'];
                } else {
                    $dailyBalances[$date]['total_export'] += abs($tr['quantity_change']);
                    $totalExport += abs($tr['quantity_change']);
                }
            }

            $running += $tr['quantity_change'];
            $dailyBalances[$date]['closing_balance'] = $running;
        }

        // Tính tồn cuối kỳ
        $closingStock = $running;

        // Nếu có lọc theo sản phẩm hoặc không có filter, tính chi tiết từng sản phẩm theo ngày
        $productDailyBalances = [];
        if (!empty($productSku) || empty($productSku)) {
            // Lấy chi tiết giao dịch theo sản phẩm + ngày
            $productDailyBalances = $this->getProductDailyBalances($transactions);
        }

        return [
            'daily_balances' => array_values($dailyBalances), // mảng {date, opening, import, export, closing}
            'product_daily_balances' => $productDailyBalances, // mảng chi tiết {date, product_name, product_sku, import, export, closing}
            'chart_data' => $this->formatChartData($dailyBalances), // dữ liệu cho Chart.js
            'opening_stock' => $openingStock,
            'total_import' => $totalImport,
            'total_export' => $totalExport,
            'closing_stock' => $closingStock
        ];
    }

    /**
     * Tính toán chi tiết tồn kho từng sản phẩm theo ngày
     * 
     * @param array $transactions Lịch sử giao dịch
     * @return array Mảng [{date, product_name, product_sku, variant_sku, total_import, total_export, closing_balance}, ...]
     */
    private function getProductDailyBalances(array $transactions): array
    {
        // Cấu trúc: {date} => {variant_id} => {details}
        $productDaily = [];
        $productRunning = []; // running balance per variant

        foreach ($transactions as $tr) {
            $date = date('Y-m-d', strtotime($tr['created_at']));
            $productId = $tr['product_id'] ?? $tr['id'] ?? 0; // Fallback if product_id doesn't exist
            $variantId = $tr['product_variant_id'] ?? 'unknown_' . uniqid(); // Safe access with fallback
            $productName = $tr['product_name'] ?? 'N/A';
            $productSku = $tr['product_sku'] ?? '';
            $variantSku = $tr['variant_sku'] ?? '';
            
            // Initialize daily record for product
            if (!isset($productDaily[$date])) {
                $productDaily[$date] = [];
            }

            $key = $variantId;
            if (!isset($productDaily[$date][$key])) {
                $productDaily[$date][$key] = [
                    'date' => $date,
                    'product_id' => $productId,
                    'product_name' => $productName,
                    'product_sku' => $productSku,
                    'variant_sku' => $variantSku,
                    'total_import' => 0,
                    'total_export' => 0,
                    'closing_balance' => $productRunning[$key] ?? 0
                ];
            }

            // Update balances
            if ($tr['type'] === 'import') {
                $productDaily[$date][$key]['total_import'] += $tr['quantity_change'];
                $productRunning[$key] = ($productRunning[$key] ?? 0) + $tr['quantity_change'];
            } elseif ($tr['type'] === 'export') {
                $productDaily[$date][$key]['total_export'] += abs($tr['quantity_change']);
                $productRunning[$key] = ($productRunning[$key] ?? 0) - abs($tr['quantity_change']);
            } elseif ($tr['type'] === 'adjust') {
                if ($tr['quantity_change'] > 0) {
                    $productDaily[$date][$key]['total_import'] += $tr['quantity_change'];
                    $productRunning[$key] = ($productRunning[$key] ?? 0) + $tr['quantity_change'];
                } else {
                    $productDaily[$date][$key]['total_export'] += abs($tr['quantity_change']);
                    $productRunning[$key] = ($productRunning[$key] ?? 0) - abs($tr['quantity_change']);
                }
            }

            $productDaily[$date][$key]['closing_balance'] = $productRunning[$key] ?? 0;
        }

        // Flatten to array
        $result = [];
        foreach ($productDaily as $date => $products) {
            foreach ($products as $product) {
                $result[] = $product;
            }
        }

        return $result;
    }

    /**
     * Format dữ liệu daily balances để dùng với Chart.js (Line chart)
     * Trả về {labels: [...dates...], datasets: [{label, data: [...closing_balances...]}]}
     */
    private function formatChartData(array $dailyBalances): array
    {
        $labels = [];
        $closingData = [];

        foreach ($dailyBalances as $balance) {
            $labels[] = $balance['date'];
            $closingData[] = $balance['closing_balance'];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Tồn kho cuối ngày',
                    'data' => $closingData,
                    'borderColor' => '#0d6efd',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ];
    }

    /**
     * ==================== INVENTORY REPORTS ====================
     */

    /**
     * Lấy báo cáo tồn kho tổng quát
     * 
     * @return array
     */
    public function getInventorySummary(): array
    {
        $stats = $this->inventoryModel->getStockStatistics();

        return [
            'in_stock' => $stats['in_stock'],
            'low_stock' => $stats['low_stock'],
            'out_of_stock' => $stats['out_of_stock'],
            'total_variants' => $stats['total_variants'],
            'in_stock_percent' => $stats['total_variants'] > 0 
                ? round(($stats['in_stock'] / $stats['total_variants']) * 100, 2)
                : 0,
            'low_stock_percent' => $stats['total_variants'] > 0
                ? round(($stats['low_stock'] / $stats['total_variants']) * 100, 2)
                : 0
        ];
    }

    /**
     * Lấy tồn kho tại một ngày cụ thể
     * 
     * @param string $date Y-m-d
     * @param string $productSku
     * @return int
     */
    public function getStockAtDate(string $date, string $productSku): int
    {
        return $this->inventoryModel->getStockAtDate($date, $productSku);
    }

    /**
     * Lấy chi tiết tồn kho từng sản phẩm tại một ngày cụ thể
     * 
     * @param string $date Y-m-d
     * @return array Mảng [{product_name, product_sku, variant_sku, stock_at_date}, ...]
     */
    public function getProductStockDetailsAtDate(string $date): array
    {
        return $this->inventoryModel->getProductStockDetailsAtDate($date);
    }

    /**
     * Lấy lịch sử tồn kho của một sản phẩm (tất cả các ngày)
     * 
     * @param string $productSku
     * @return array Mảng [{date, opening_balance, total_import, total_export, closing_balance}, ...]
     */
    public function getProductStockHistory(string $productSku): array
    {
        return $this->inventoryModel->getProductStockHistory($productSku);
    }

    /**
     * Lấy danh sách sản phẩm theo trạng thái tồn kho
     * 
     * @param string $status 'in_stock' | 'low_stock' | 'out_of_stock'
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getProductsByStockStatus(string $status, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $products = $this->inventoryModel->getProductsByStockStatus($status, $perPage, $offset);
        $total = $this->inventoryModel->countByStockStatus($status);

        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
    }

    /**
     * Lấy lịch sử nhập - xuất hàng
     * 
     * @param string $type 'import' | 'export' | 'adjust' | 'all'
     * @param string|null $startDate Y-m-d
     * @param string|null $endDate Y-m-d
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getTransactionHistory(
        string $type = 'all',
        ?string $startDate = null,
        ?string $endDate = null,
        int $page = 1,
        int $perPage = 20
    ): array {
        $offset = ($page - 1) * $perPage;
        $transactions = $this->inventoryModel->getTransactionHistory($type, $startDate, $endDate, $perPage, $offset);
        $total = $this->inventoryModel->countTransactions($type, $startDate, $endDate);

        return [
            'transactions' => $transactions,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => ceil($total / $perPage)
            ]
        ];
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
        return $this->inventoryModel->getDailyTransactionSummary($startDate, $endDate);
    }

    /**
     * ==================== SALES & PROFIT REPORTS ====================
     */

    /**
     * Lấy tổng quan doanh thu
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getSalesSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $stats = $this->salesModel->getTotalSalesStatistics($startDate, $endDate);

        return [
            'total_orders' => $stats['total_orders'],
            'unique_products' => $stats['unique_products'],
            'total_quantity_sold' => $stats['total_quantity_sold'],
            'total_revenue' => $stats['total_revenue'],
            'average_order_value' => $stats['average_order_value'],
            'formatted_revenue' => $this->formatCurrency($stats['total_revenue']),
            'formatted_avg_order' => $this->formatCurrency($stats['average_order_value'])
        ];
    }

    /**
     * Lấy doanh thu theo sản phẩm
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getSalesRevenueByProduct(
        ?string $startDate = null,
        ?string $endDate = null,
        int $page = 1,
        int $perPage = 20
    ): array {
        $offset = ($page - 1) * $perPage;
        $products = $this->salesModel->getSalesRevenueByProduct($startDate, $endDate, $perPage, $offset);

        // Format currency
        foreach ($products as &$product) {
            $product['formatted_revenue'] = $this->formatCurrency($product['total_revenue']);
            $product['formatted_price'] = $this->formatCurrency($product['average_price']);
        }

        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil(count($products) / $perPage)
            ]
        ];
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
        $categories = $this->salesModel->getSalesRevenueByCategory($startDate, $endDate);

        // Format currency
        foreach ($categories as &$cat) {
            $cat['formatted_revenue'] = $this->formatCurrency($cat['total_revenue']);
        }

        return $categories;
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
        $data = $this->salesModel->getDailySalesRevenue($startDate, $endDate);

        foreach ($data as &$day) {
            $day['formatted_revenue'] = $this->formatCurrency($day['daily_revenue']);
        }

        return $data;
    }

    /**
     * Lấy tổng quan lợi nhuận
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getProfitSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $stats = $this->salesModel->getTotalProfitStatistics($startDate, $endDate);

        return [
            'total_revenue' => $stats['total_revenue'],
            'total_cogs' => $stats['total_cogs'],
            'total_gross_profit' => $stats['total_gross_profit'],
            'profit_margin_percent' => $stats['profit_margin_percent'],
            'formatted_revenue' => $this->formatCurrency($stats['total_revenue']),
            'formatted_cogs' => $this->formatCurrency($stats['total_cogs']),
            'formatted_profit' => $this->formatCurrency($stats['total_gross_profit'])
        ];
    }

    /**
     * Lấy lợi nhuận gộp theo sản phẩm
     * 
     * @param string|null $startDate
     * @param string|null $endDate
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getGrossProfitByProduct(
        ?string $startDate = null,
        ?string $endDate = null,
        int $page = 1,
        int $perPage = 20
    ): array {
        $offset = ($page - 1) * $perPage;
        $products = $this->salesModel->getGrossProfitByProduct($startDate, $endDate, $perPage, $offset);

        // Format currency
        foreach ($products as &$product) {
            $product['formatted_revenue'] = $this->formatCurrency($product['total_revenue']);
            $product['formatted_cogs'] = $this->formatCurrency($product['total_cost_of_goods_sold']);
            $product['formatted_profit'] = $this->formatCurrency($product['gross_profit']);
        }

        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil(count($products) / $perPage)
            ]
        ];
    }

    /**
     * ==================== TOP PRODUCTS REPORTS ====================
     */

    /**
     * Lấy top sản phẩm bán chạy nhất
     * 
     * @param int $topN Số lượng top
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTopSellingProducts(
        int $topN = 10,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $products = $this->topProductsModel->getTopSellingProducts($topN, $startDate, $endDate);

        foreach ($products as &$product) {
            $product['formatted_revenue'] = $this->formatCurrency($product['total_revenue']);
            $product['formatted_price'] = $this->formatCurrency($product['average_price']);
        }

        return $products;
    }

    /**
     * Lấy sản phẩm tồn kho lâu, ít bán (slow moving)
     * 
     * @param int $topN
     * @param int $daysThreshold
     * @return array
     */
    public function getSlowMovingInventory(
        int $topN = 10,
        int $daysThreshold = 30
    ): array {
        return $this->topProductsModel->getSlowMovingInventory($topN, $daysThreshold);
    }

    /**
     * Lấy dead stock (không bao giờ bán)
     * 
     * @param int $topN
     * @return array
     */
    public function getDeadStock(int $topN = 10): array
    {
        return $this->topProductsModel->getDeadStock($topN);
    }

    /**
     * Lấy high value products
     * 
     * @param int $topN
     * @return array
     */
    public function getHighValueProducts(int $topN = 10): array
    {
        $products = $this->topProductsModel->getHighValueProducts($topN);

        foreach ($products as &$product) {
            $product['formatted_unit_cost'] = $this->formatCurrency($product['unit_cost']);
            $product['formatted_price'] = $this->formatCurrency($product['price']);
            $product['formatted_stock_value'] = $this->formatCurrency($product['stock_value']);
        }

        return $products;
    }

    /**
     * Lấy top sản phẩm lợi nhuận cao
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
        $products = $this->topProductsModel->getTopProfitProducts($topN, $startDate, $endDate);

        foreach ($products as &$product) {
            $product['formatted_revenue'] = $this->formatCurrency($product['total_revenue']);
            $product['formatted_cogs'] = $this->formatCurrency($product['average_unit_cost']);
            $product['formatted_profit'] = $this->formatCurrency($product['gross_profit']);
        }

        return $products;
    }

    /**
     * ==================== HELPER METHODS ====================
     */

    /**
     * Lấy tên sản phẩm từ SKU
     * 
     * @param string $productSku
     * @return string|null
     */
    public function getProductNameBySku(string $productSku): ?string
    {
        return $this->inventoryModel->getProductNameBySku($productSku);
    }

    /**
     * Format tiền tệ VND
     * 
     * @param float|int $amount
     * @return string
     */
    private function formatCurrency($amount): string
    {
        return number_format((float) $amount, 0, ',', '.') . ' ₫';
    }
}
