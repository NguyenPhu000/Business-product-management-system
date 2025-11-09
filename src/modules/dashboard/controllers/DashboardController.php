<?php

namespace Modules\Dashboard\Controllers;

use Core\Controller;
use Modules\Dashboard\Services\DashboardService;

/**
 * DashboardController - Xử lý routing cho Dashboard
 * 
 * Chức năng: Nhận request, gọi DashboardService, trả về view
 * Note: Tất cả business logic đã được di chuyển sang DashboardService
 */
class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * Trang dashboard chính
     */
    public function index(): void
    {
        // Lấy dữ liệu từ service
        $dashboardData = $this->dashboardService->getDashboardData();

        $data = [
            'title' => 'Dashboard',
            'stats' => $dashboardData['stats'],
            'recentLogs' => $dashboardData['recentLogs'],
            'recentUsers' => $dashboardData['recentUsers']
        ];

        $this->view('admin/dashboard', $data);
    }
}
