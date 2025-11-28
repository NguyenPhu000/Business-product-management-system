<?php

namespace Modules\Report\Services;

use Modules\Report\Models\SalesAnalyticsModel;

/**
 * SalesAnalyticsService - Xử lý logic phân tích doanh thu
 */
class SalesAnalyticsService
{
    private SalesAnalyticsModel $model;

    public function __construct()
    {
        $this->model = new SalesAnalyticsModel();
    }

    /**
     * Lấy toàn bộ dữ liệu dashboard doanh thu
     */
    public function getSalesDashboard(?string $startDate = null, ?string $endDate = null, int $page = 1): array
    {
        // KPI tóm tắt
        $kpis = $this->model->getSummaryKPIs($startDate, $endDate);
        $grossProfit = $this->model->getGrossProfitKPI($startDate, $endDate);
        
        // Xu hướng doanh thu theo ngày
        $dailyTrend = $this->model->getDailyRevenuesTrend($startDate, $endDate);
        
        // Top 10 sản phẩm
        $topProducts = $this->model->getTopProductsByRevenue(10, $startDate, $endDate);
        
        // Doanh thu theo danh mục
        $categoryRevenue = $this->model->getRevenueByCategory($startDate, $endDate);
        
        // Chi tiết sản phẩm (phân trang)
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $productDetails = $this->model->getProductSalesDetails($startDate, $endDate, $perPage, $offset);
        $totalProducts = $this->model->countProductsSold($startDate, $endDate);
        
        // Chi tiết danh mục
        $categoryDetails = $this->model->getCategorySalesDetails($startDate, $endDate);
        
        // Chi tiết nhà sản xuất (Brand)
        $brandDetails = $this->model->getBrandSalesDetails($startDate, $endDate);
        
        // Chuẩn bị dữ liệu cho charts
        $chartDataDaily = $this->formatDailyTrendChart($dailyTrend);
        $chartDataCategory = $this->formatCategoryChart($categoryRevenue);
        
        return [
            'kpis' => [
                'total_revenue' => $kpis['total_revenue'],
                'gross_profit' => $grossProfit,
                'total_orders' => $kpis['total_orders'],
                'average_order_value' => $kpis['average_order_value'],
                'unique_customers' => $kpis['unique_customers'],
                'profit_margin_percent' => $kpis['total_revenue'] > 0 ? round(($grossProfit / $kpis['total_revenue']) * 100, 2) : 0
            ],
            'daily_trend' => $dailyTrend,
            'chart_daily' => $chartDataDaily,
            'chart_category' => $chartDataCategory,
            'top_products' => $topProducts,
            'category_revenue' => $categoryRevenue,
            'category_details' => $categoryDetails,
            'brand_details' => $brandDetails,
            'product_details' => $productDetails,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalProducts,
                'total_pages' => ceil($totalProducts / $perPage)
            ]
        ];
    }

    /**
     * Format dữ liệu xu hướng hàng ngày cho Chart.js
     */
    private function formatDailyTrendChart(array $dailyTrend): array
    {
        $labels = [];
        $revenueData = [];
        $profitData = [];
        
        foreach ($dailyTrend as $row) {
            $date = new \DateTime($row['sale_date']);
            $labels[] = $date->format('d/m/Y');
            $revenueData[] = (float) $row['daily_revenue'];
            $profit = (float) $row['daily_revenue'] - (float) $row['daily_cost'];
            $profitData[] = $profit;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doanh thu',
                    'data' => $revenueData,
                    'borderColor' => '#0d6efd',
                    'backgroundColor' => 'rgba(13, 110, 253, 0.1)',
                    'tension' => 0.4,
                    'fill' => true
                ],
                [
                    'label' => 'Lợi nhuận gộp',
                    'data' => $profitData,
                    'borderColor' => '#198754',
                    'backgroundColor' => 'rgba(25, 135, 84, 0.1)',
                    'tension' => 0.4,
                    'fill' => true
                ]
            ]
        ];
    }

    /**
     * Format dữ liệu danh mục cho Horizontal Bar Chart
     * Nếu <= 5 danh mục: hiển thị tất cả
     * Nếu > 5 danh mục: hiển thị Top 5 + nhóm "Khác" (Others)
     */
    private function formatCategoryChart(array $categories): array
    {
        $colors = ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14', '#e83e8c'];
        
        // Nếu <= 5 danh mục: hiển thị tất cả
        if (count($categories) <= 5) {
            $labels = [];
            $data = [];
            $backgroundColors = [];
            
            foreach ($categories as $index => $cat) {
                $labels[] = $cat['category_name'];
                $data[] = (float) $cat['category_revenue'];
                $backgroundColors[] = $colors[$index % count($colors)];
            }
        } else {
            // Nếu > 5: Top 5 + Others
            $labels = [];
            $data = [];
            $backgroundColors = [];
            $othersRevenue = 0;
            
            foreach ($categories as $index => $cat) {
                if ($index < 5) {
                    $labels[] = $cat['category_name'];
                    $data[] = (float) $cat['category_revenue'];
                    $backgroundColors[] = $colors[$index % count($colors)];
                } else {
                    $othersRevenue += (float) $cat['category_revenue'];
                }
            }
            
            // Thêm "Khác (Others)"
            if ($othersRevenue > 0) {
                $labels[] = 'Khác (Others)';
                $data[] = $othersRevenue;
                $backgroundColors[] = '#6c757d';
            }
        }
        
        return [
            'type' => 'bar',
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Doanh thu',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => '#fff',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    /**
     * Lấy dữ liệu dashboard được lọc theo danh mục
     */
    public function getSalesDashboardByCategory(int $categoryId, ?string $startDate = null, ?string $endDate = null, int $page = 1): array
    {
        // KPI tóm tắt (vẫn lấy toàn bộ)
        $kpis = $this->model->getSummaryKPIs($startDate, $endDate);
        $grossProfit = $this->model->getGrossProfitKPI($startDate, $endDate);
        
        // Doanh thu theo danh mục (lọc danh mục)
        $categoryRevenue = $this->model->getRevenueByCategoryFiltered($categoryId, $startDate, $endDate);
        
        // Chi tiết sản phẩm theo danh mục (phân trang)
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $productDetails = $this->model->getProductSalesDetailsByCategory($categoryId, $startDate, $endDate, $perPage, $offset);
        
        // Chi tiết danh mục (lọc danh mục)
        $categoryDetails = $this->model->getCategorySalesDetailsFiltered($categoryId, $startDate, $endDate);
        
        // Chi tiết nhà sản xuất theo danh mục
        $brandDetails = $this->model->getBrandSalesDetailsByCategory($categoryId, $startDate, $endDate);
        
        // Chuẩn bị dữ liệu cho charts
        $chartDataCategory = $this->formatCategoryChart($categoryRevenue);
        
        return [
            'kpis' => [
                'total_revenue' => $kpis['total_revenue'],
                'gross_profit' => $grossProfit,
                'total_orders' => $kpis['total_orders'],
                'average_order_value' => $kpis['average_order_value'],
                'unique_customers' => $kpis['unique_customers'],
                'profit_margin_percent' => $kpis['total_revenue'] > 0 ? round(($grossProfit / $kpis['total_revenue']) * 100, 2) : 0
            ],
            'chart_category' => $chartDataCategory,
            'category_revenue' => $categoryRevenue,
            'category_details' => $categoryDetails,
            'brand_details' => $brandDetails,
            'product_details' => $productDetails,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => count($productDetails),
                'total_pages' => ceil(count($productDetails) / $perPage)
            ],
            'selected_category_id' => $categoryId
        ];
    }

    /**
     * Lấy dữ liệu thương hiệu theo danh mục (cho AJAX)
     */
    public function getBrandsByCategory(int $categoryId, ?string $startDate = null, ?string $endDate = null): array
    {
        return $this->model->getBrandSalesDetailsByCategory($categoryId, $startDate, $endDate);
    }

    /**
     * Lấy dữ liệu sản phẩm theo danh mục (cho AJAX) - Có phân trang
     */
    public function getProductsByCategory(int $categoryId, ?string $startDate = null, ?string $endDate = null, int $page = 1): array
    {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $products = $this->model->getProductSalesDetailsByCategory($categoryId, $startDate, $endDate, $perPage, $offset);
        
        // Đếm tổng sản phẩm
        $totalProducts = count($products);
        
        return [
            'products' => $products,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $totalProducts,
                'total_pages' => ceil($totalProducts / $perPage)
            ]
        ];
    }

    /**
     * Lấy cấu trúc cây danh mục (parent-child)
     */
    public function getCategoryTree(): array
    {
        $results = $this->model->getCategoryTreeWithRevenue();
        return $this->buildCategoryTree($results);
    }

    /**
     * Build recursive category tree
     */
    private function buildCategoryTree(array $categories, ?int $parentId = null): array
    {
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category['parent_id'] == $parentId) {
                $category['children'] = $this->buildCategoryTree($categories, $category['id']);
                $tree[] = $category;
            }
        }
        
        return $tree;
    }
}
