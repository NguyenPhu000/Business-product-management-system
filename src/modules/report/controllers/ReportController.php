<?php

namespace Modules\Report\Controllers;

use Core\Controller;
use Modules\Report\Services\ReportService;
use Modules\Report\Services\SalesAnalyticsService;
use Helpers\AuthHelper;
use Exception;

/**
 * ReportController - Routing layer cho báo cáo
 * 
 * Chức năng: Nhận request, gọi ReportService, trả về view
 */
class ReportController extends Controller
{
    private ReportService $reportService;
    private SalesAnalyticsService $salesAnalyticsService;

    public function __construct()
    {
        $this->reportService = new ReportService();
        $this->salesAnalyticsService = new SalesAnalyticsService();
    }

    /**
     * Dashboard báo cáo - Redirect to main company dashboard
     */
    public function dashboard(): void
    {
        $this->redirect('/admin/dashboard');
    }

    /**
     * ==================== INVENTORY REPORTS ====================
     */

    /**
     * Báo cáo tồn kho chung
     */
    public function inventoryReport(): void
    {
        // Danh sách tồn kho đã bị bỏ khỏi module Reports (đã có trong Inventory module).
        // Redirect đến báo cáo tóm tắt theo thời gian để tránh trùng lặp.
        $this->redirect('/admin/reports/inventory-over-time');
    }

    /**
     * Lịch sử nhập - xuất hàng
     */
    public function transactionHistory(): void
    {
        // Transaction history is handled by Inventory module.
        // Redirect to inventory-over-time report in Reports module which summarizes import/export per day.
        $this->redirect('/admin/reports/inventory-over-time');
    }

    /**
     * Báo cáo: Tồn kho theo thời gian (tổng nhập/xuất theo ngày)
     */
    public function inventoryOverTime(): void
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $productSku = $this->input('product_sku');
        $transactionType = $this->input('transaction_type', 'all');

        try {
            $report = $this->reportService->getInventoryOverTimeReport($startDate, $endDate, $productSku, $transactionType);

            $data = [
                'title' => 'Tồn Theo Thời Gian (Nhập - Xuất theo ngày)',
                'daily_balances' => $report['daily_balances'] ?? [],
                'product_daily_balances' => $report['product_daily_balances'] ?? [],
                'chart_data' => $report['chart_data'] ?? [],
                'opening_stock' => $report['opening_stock'],
                'total_import' => $report['total_import'],
                'total_export' => $report['total_export'],
                'closing_stock' => $report['closing_stock'],
                'product_sku' => $productSku,
                'transaction_type' => $transactionType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'activeTab' => 'inventory'
            ];

            $this->view('admin/reports/inventory_over_time', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * API endpoint: Lấy tồn kho tại một ngày cụ thể (cho date picker)
     */
    public function stockAtDate(): void
    {
        $date = $this->input('date'); // Y-m-d
        $productSku = $this->input('product_sku', ''); // Optional: empty string means all products

        try {
            if (!$date) {
                $this->json(['error' => 'Thiếu tham số date'], 400);
                return;
            }

            $stock = $this->reportService->getStockAtDate($date, $productSku);
            
            $message = "Vào lúc 23:59 ngày " . date('d/m/Y', strtotime($date));
            
            // Nếu không có SKU (query tất cả sản phẩm), lấy chi tiết từng sản phẩm
            if (empty($productSku)) {
                $productDetails = $this->reportService->getProductStockDetailsAtDate($date);
                $message = "Vào lúc 23:59 ngày " . date('d/m/Y', strtotime($date)) . ", Tổng tồn kho toàn hệ thống: $stock cái";
                
                $this->json([
                    'success' => true,
                    'date' => $date,
                    'product_sku' => '',
                    'stock_at_date' => $stock,
                    'message' => $message,
                    'product_details' => $productDetails
                ]);
            } else {
                // Nếu có SKU, chỉ trả về tổng số
                $message .= " (SKU: $productSku), tồn kho là: $stock cái";
                
                $this->json([
                    'success' => true,
                    'date' => $date,
                    'product_sku' => $productSku,
                    'stock_at_date' => $stock,
                    'message' => $message
                ]);
            }
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API endpoint: Lấy lịch sử tồn kho của một sản phẩm (tất cả các ngày)
     */
    public function productStockHistory(): void
    {
        $productSku = $this->input('product_sku');

        try {
            if (!$productSku) {
                $this->json(['error' => 'Thiếu tham số product_sku'], 400);
                return;
            }

            $history = $this->reportService->getProductStockHistory($productSku);
            
            // Lấy tên sản phẩm từ SKU (để hiển thị trên giao diện)
            $productName = $this->reportService->getProductNameBySku($productSku);
            
            $this->json([
                'success' => true,
                'product_sku' => $productSku,
                'product_name' => $productName,
                'history' => $history,
                'message' => "Lịch sử tồn kho của sản phẩm: $productSku"
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * ==================== SALES & PROFIT REPORTS ====================
     */

    /**
     * Báo cáo doanh thu toàn diện (KPIs, Charts, Tables)
     */
    public function salesReport(): void
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $categoryId = $this->input('category_id') ? (int) $this->input('category_id') : null;
        $page = (int) ($this->input('page') ?? 1);

        try {
            // Nếu có filter danh mục, lấy dữ liệu lọc
            if ($categoryId !== null) {
                $dashboardData = $this->salesAnalyticsService->getSalesDashboardByCategory($categoryId, $startDate, $endDate, $page);
            } else {
                $dashboardData = $this->salesAnalyticsService->getSalesDashboard($startDate, $endDate, $page);
            }

            // Lấy cấu trúc cây danh mục
            $categoryTree = $this->salesAnalyticsService->getCategoryTree();

            $data = [
                'title' => 'Báo Cáo Doanh Thu',
                'kpis' => $dashboardData['kpis'],
                'daily_trend' => $dashboardData['daily_trend'] ?? null,
                'chart_daily' => $dashboardData['chart_daily'] ?? null,
                'chart_category' => $dashboardData['chart_category'],
                'top_products' => $dashboardData['top_products'] ?? [],
                'category_revenue' => $dashboardData['category_revenue'],
                'category_details' => $dashboardData['category_details'],
                'category_tree' => $categoryTree,
                'supplier_details' => $dashboardData['brand_details'],
                'product_details' => $dashboardData['product_details'],
                'pagination' => $dashboardData['pagination'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'category_id' => $categoryId,
                'activeTab' => 'sales'
            ];

            $this->view('admin/reports/sales_dashboard', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Báo cáo lợi nhuận (DEPRECATED - Redirect to sales_dashboard)
     */
    public function profitReport(): void
    {
        $this->redirect('/admin/reports/sales');
    }

    /**
     * Báo cáo bán chạy nhất (DEPRECATED - Redirect to sales_dashboard)
     */
    public function topSellingProducts(): void
    {
        $this->redirect('/admin/reports/sales');
    }

    /**
     * Báo cáo sản phẩm tồn kho lâu (DEPRECATED - Redirect to sales_dashboard)
     */
    public function slowMovingInventory(): void
    {
        $this->redirect('/admin/reports/sales');
    }

    /**
     * Báo cáo dead stock (DEPRECATED - Redirect to sales_dashboard)
     */
    public function deadStock(): void
    {
        $this->redirect('/admin/reports/sales');
    }

    /**
     * Báo cáo high value products (DEPRECATED - Redirect to sales_dashboard)
     */
    public function highValueProducts(): void
    {
        $this->redirect('/admin/reports/sales');
    }

    /**
     * Báo cáo top sản phẩm lợi nhuận (DEPRECATED - Redirect to sales_dashboard)
     */
    public function topProfitProducts(): void
    {
        $this->redirect('/admin/reports/sales');
    }

    /**
     * Báo cáo Top Sản Phẩm - Bán chạy nhất & Tồn kho lâu, ít bán
     */
    public function topProducts(): void
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');

        try {
            $model = new \Modules\Report\Models\SalesAnalyticsModel();
            
            // Lấy dữ liệu
            $topSellingProducts = $model->getTopSellingProducts(20, $startDate, $endDate);
            $slowMovingProducts = $model->getSlowMovingProducts(20, $startDate, $endDate);

            $data = [
                'title' => 'Top Sản Phẩm',
                'top_selling_products' => $topSellingProducts,
                'slow_moving_products' => $slowMovingProducts,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'activeTab' => 'sales'
            ];

            $this->view('admin/reports/top_products', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * API: Lấy dữ liệu thương hiệu theo danh mục (AJAX)
     */
    public function getSalesDataBrands(): void
    {
        $categoryId = $this->input('category_id') ? (int) $this->input('category_id') : null;
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');

        try {
            if ($categoryId === null) {
                $this->json(['error' => 'Thiếu tham số category_id'], 400);
                return;
            }

            // Lấy dữ liệu thương hiệu theo danh mục
            $brands = $this->salesAnalyticsService->getBrandsByCategory($categoryId, $startDate, $endDate);

            $this->json([
                'success' => true,
                'category_id' => $categoryId,
                'brands' => $brands,
                'count' => count($brands)
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Lấy dữ liệu sản phẩm theo danh mục (AJAX)
     */
    public function getSalesDataProducts(): void
    {
        $categoryId = $this->input('category_id') ? (int) $this->input('category_id') : null;
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $page = (int) ($this->input('page') ?? 1);

        try {
            if ($categoryId === null) {
                $this->json(['error' => 'Thiếu tham số category_id'], 400);
                return;
            }

            // Lấy dữ liệu sản phẩm theo danh mục
            $result = $this->salesAnalyticsService->getProductsByCategory($categoryId, $startDate, $endDate, $page);

            $this->json([
                'success' => true,
                'category_id' => $categoryId,
                'products' => $result['products'],
                'pagination' => $result['pagination'],
                'count' => count($result['products'])
            ]);
        } catch (Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}


