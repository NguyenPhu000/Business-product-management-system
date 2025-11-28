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

        // Truyền toàn bộ dữ liệu vào view
        $data = array_merge(['title' => 'Dashboard'], $dashboardData);

        $this->view('admin/dashboard', $data);
    }
}
