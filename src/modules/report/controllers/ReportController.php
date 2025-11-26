<?php

namespace Modules\Report\Controllers;

use Core\Controller;
use Modules\Report\Services\ReportService;
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

    public function __construct()
    {
        $this->reportService = new ReportService();
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
     * Báo cáo doanh thu
     */
    public function salesReport(): void
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');

        try {
            $summary = $this->reportService->getSalesSummary($startDate, $endDate);
            $byCategory = $this->reportService->getSalesRevenueByCategory($startDate, $endDate);
            $dailyRevenue = $this->reportService->getDailySalesRevenue($startDate, $endDate);

            $data = [
                'title' => 'Báo Cáo Doanh Thu',
                'summary' => $summary,
                'by_category' => $byCategory,
                'daily_revenue' => $dailyRevenue,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'activeTab' => 'sales'
            ];

            $this->view('admin/reports/sales_report', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Báo cáo lợi nhuận
     */
    public function profitReport(): void
    {
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');
        $page = (int) ($this->input('page') ?? 1);

        try {
            $summary = $this->reportService->getProfitSummary($startDate, $endDate);
            $byProduct = $this->reportService->getGrossProfitByProduct($startDate, $endDate, $page);

            $data = [
                'title' => 'Báo Cáo Lợi Nhuận',
                'summary' => $summary,
                'products' => $byProduct['products'],
                'pagination' => $byProduct['pagination'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'activeTab' => 'sales'
            ];

            $this->view('admin/reports/profit_report', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * ==================== TOP PRODUCTS REPORTS ====================
     */

    /**
     * Báo cáo top sản phẩm bán chạy
     */
    public function topSellingProducts(): void
    {
        $topN = (int) ($this->input('top', 10));
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');

        try {
            $products = $this->reportService->getTopSellingProducts($topN, $startDate, $endDate);

            $data = [
                'title' => 'Top Sản Phẩm Bán Chạy',
                'products' => $products,
                'topN' => $topN,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'activeTab' => 'top_products'
            ];

            $this->view('admin/reports/top_selling_products', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Báo cáo sản phẩm tồn kho lâu
     */
    public function slowMovingInventory(): void
    {
        $topN = (int) ($this->input('top', 10));
        $daysThreshold = (int) ($this->input('days', 30));

        try {
            $products = $this->reportService->getSlowMovingInventory($topN, $daysThreshold);

            $data = [
                'title' => 'Sản Phẩm Tồn Kho Lâu',
                'products' => $products,
                'topN' => $topN,
                'daysThreshold' => $daysThreshold,
                'activeTab' => 'top_products'
            ];

            $this->view('admin/reports/slow_moving_inventory', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Báo cáo dead stock
     */
    public function deadStock(): void
    {
        $topN = (int) ($this->input('top', 10));

        try {
            $products = $this->reportService->getDeadStock($topN);

            $data = [
                'title' => 'Dead Stock (Chưa Bao Giờ Bán)',
                'products' => $products,
                'topN' => $topN,
                'activeTab' => 'top_products'
            ];

            $this->view('admin/reports/dead_stock', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Báo cáo high value products
     */
    public function highValueProducts(): void
    {
        $topN = (int) ($this->input('top', 10));

        try {
            $products = $this->reportService->getHighValueProducts($topN);

            $data = [
                'title' => 'Top Sản Phẩm Giá Trị Cao',
                'products' => $products,
                'topN' => $topN,
                'activeTab' => 'top_products'
            ];

            $this->view('admin/reports/high_value_products', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }

    /**
     * Báo cáo top sản phẩm lợi nhuận
     */
    public function topProfitProducts(): void
    {
        $topN = (int) ($this->input('top', 10));
        $startDate = $this->input('start_date');
        $endDate = $this->input('end_date');

        try {
            $products = $this->reportService->getTopProfitProducts($topN, $startDate, $endDate);

            $data = [
                'title' => 'Top Sản Phẩm Lợi Nhuận Cao',
                'products' => $products,
                'topN' => $topN,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'activeTab' => 'top_products'
            ];

            $this->view('admin/reports/top_profit_products', $data);
        } catch (Exception $e) {
            AuthHelper::setFlash('error', 'Lỗi: ' . $e->getMessage());
            $this->redirect('/admin/dashboard');
        }
    }
}

