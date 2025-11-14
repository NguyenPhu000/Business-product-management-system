<?php

namespace Modules\System\Services;

use Modules\System\Models\UserLogModel;
use Modules\Auth\Models\UserModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * LogsService - Business logic cho quản lý logs
 * 
 * Chức năng:
 * - Lấy danh sách logs với filter và phân trang
 * - Xóa logs cũ (cleanup)
 * - Cập nhật và xóa log (chỉ Admin)
 */
class LogsService
{
    private UserLogModel $logModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->logModel = new UserLogModel();
        $this->userModel = new UserModel();
    }

    /**
     * Lấy danh sách logs với filter
     * 
     * @param int $page Trang hiện tại
     * @param int $perPage Số bản ghi mỗi trang
     * @param int|null $userId Filter theo user
     * @param string|null $action Filter theo action
     * @return array
     */
    public function getLogsWithFilter(int $page = 1, int $perPage = 12, ?int $userId = null, ?string $action = null): array
    {
        $result = $this->logModel->getLogsWithFilter($page, $perPage, $userId, $action);

        return [
            'logs' => $result['data'],
            'pagination' => [
                'page' => $result['page'],
                'totalPages' => $result['totalPages'],
                'total' => $result['total']
            ]
        ];
    }

    /**
     * Lấy danh sách users cho filter
     * 
     * @return array
     */
    public function getUsersForFilter(): array
    {
        return $this->userModel->all('username', 'ASC');
    }

    /**
     * Lấy danh sách actions
     * 
     * @return array
     */
    public function getActions(): array
    {
        return [
            'login' => 'Login',
            'logout' => 'Logout',
            'create' => 'Create',
            'update' => 'Update',
            'delete' => 'Delete',
            'approve_reset_password' => 'Approve Reset Password',
            'reject_reset_password' => 'Reject Reset Password',
            'request_reset_password' => 'Request Reset Password',
            'reset_password' => 'Reset Password',
        ];
    }

    /**
     * Xóa logs cũ
     * 
     * @param int $days Số ngày
     * @return bool
     * @throws Exception
     */
    public function cleanupOldLogs(int $days): bool
    {
        if ($days < 30) {
            throw new Exception('Chỉ có thể xóa log cũ hơn 30 ngày');
        }

        $result = $this->logModel->deleteOldLogs($days);

        // Ghi log
        LogHelper::log('cleanup_logs', 'system', null, ['days' => $days]);

        return $result;
    }

    /**
     * Cập nhật log
     * 
     * @param int $logId
     * @param string $action
     * @return bool
     * @throws Exception
     */
    public function updateLog(int $logId, string $action): bool
    {
        // Chỉ Admin mới được sửa
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Bạn không có quyền thực hiện thao tác này');
        }

        if (empty($action)) {
            throw new Exception('Action không được để trống');
        }

        $log = $this->logModel->find($logId);
        if (!$log) {
            throw new Exception('Không tìm thấy log');
        }

        $result = $this->logModel->update($logId, ['action' => $action]);

        // Ghi log cho việc sửa log
        if ($result) {
            LogHelper::log('update_log', 'user_log', $logId, [
                'old_action' => $log['action'],
                'new_action' => $action
            ]);
        }

        return $result;
    }

    /**
     * Xóa log
     * 
     * @param int $logId
     * @return bool
     * @throws Exception
     */
    public function deleteLog(int $logId): bool
    {
        // Chỉ Admin mới được xóa
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Bạn không có quyền thực hiện thao tác này');
        }

        $log = $this->logModel->find($logId);
        if (!$log) {
            throw new Exception('Không tìm thấy log');
        }

        $result = $this->logModel->delete($logId);

        // Ghi log cho việc xóa log
        if ($result) {
            LogHelper::log('delete_log', 'user_log', $logId, [
                'action' => $log['action'],
                'user_id' => $log['user_id']
            ]);
        }

        return $result;
    }

    /**
     * Kiểm tra quyền admin
     * 
     * @return bool
     */
    public function isAdmin(): bool
    {
        return AuthHelper::isAdmin();
    }
}
