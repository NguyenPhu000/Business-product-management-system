<?php

namespace Modules\Dashboard\Services;

use Models\UserModel;
use Models\UserLogModel;
use Models\RoleModel;
use Models\SystemConfigModel;

/**
 * DashboardService - Business logic cho Dashboard
 * 
 * Chức năng:
 * - Lấy thống kê tổng quan hệ thống
 * - Lấy dữ liệu logs gần đây
 * - Lấy users mới nhất
 */
class DashboardService
{
    private UserModel $userModel;
    private UserLogModel $logModel;
    private RoleModel $roleModel;
    private SystemConfigModel $configModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->logModel = new UserLogModel();
        $this->roleModel = new RoleModel();
        $this->configModel = new SystemConfigModel();
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
            'recentLogs' => $this->getRecentLogs(10),
            'recentUsers' => $this->getRecentUsers(5)
        ];
    }
}
