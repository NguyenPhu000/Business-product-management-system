<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\UserModel;
use Models\UserLogModel;
use Models\RoleModel;
use Models\SystemConfigModel;
use Helpers\AuthHelper;

/**
 * HomeController - Trang dashboard admin
 */
class HomeController extends Controller
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
     * Trang dashboard
     */
    public function index(): void
    {
        // Thống kê
        $stats = [
            'totalUsers' => $this->userModel->count(),
            'activeUsers' => $this->userModel->count(['status' => STATUS_ACTIVE]),
            'totalRoles' => $this->roleModel->count(),
            'totalLogs' => $this->logModel->count(),
        ];
        
        // Log gần đây
        $recentLogs = $this->logModel->getAllWithUser(10);
        
        // User mới nhất
        $recentUsers = $this->userModel->getAllWithRole();
        $recentUsers = array_slice($recentUsers, 0, 5);
        
        $data = [
            'title' => 'Dashboard',
            'stats' => $stats,
            'recentLogs' => $recentLogs,
            'recentUsers' => $recentUsers,
        ];
        
        $this->view('admin/dashboard', $data);
    }
}
