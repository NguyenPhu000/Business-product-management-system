<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\UserLogModel;
use Models\UserModel;
use Helpers\AuthHelper;

/**
 * LogsController - Quản lý log hoạt động
 */
class LogsController extends Controller
{
    private UserLogModel $logModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->logModel = new UserLogModel();
        $this->userModel = new UserModel();
    }

    /**
     * Danh sách log
     */
    public function index(): void
    {
        $page = (int) $this->input('page', 1);
        $perPage = 20;
        $userId = $this->input('user_id') ? (int) $this->input('user_id') : null;
        $action = $this->input('action', null);

        // Lấy danh sách log với filter
        $result = $this->logModel->getLogsWithFilter($page, $perPage, $userId, $action);

        // Lấy danh sách users cho filter
        $users = $this->userModel->all('username', 'ASC');

        // Các loại action (tiếng Anh)
        $actions = [
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

        $data = [
            'title' => 'Log hoạt động',
            'logs' => $result['data'],
            'pagination' => [
                'page' => $result['page'],
                'totalPages' => $result['totalPages'],
                'total' => $result['total']
            ],
            'users' => $users,
            'actions' => $actions,
            'currentUserId' => $userId,
            'currentAction' => $action
        ];

        $this->view('admin/logs/index', $data);
    }

    /**
     * Xóa log cũ
     */
    public function cleanup(): void
    {
        $days = (int) $this->input('days', 90);

        if ($days < 30) {
            $this->error('Chỉ có thể xóa log cũ hơn 30 ngày', 400);
            return;
        }

        $this->logModel->deleteOldLogs($days);

        // Ghi log
        \Helpers\LogHelper::log('cleanup_logs', 'system', null, ['days' => $days]);

        $this->success(null, "Đã xóa log cũ hơn {$days} ngày");
    }

    /**
     * Cập nhật log (chỉ Admin)
     */
    public function update($id = null): void
    {
        // Chỉ Admin mới được sửa
        if (!AuthHelper::isAdmin()) {
            $this->error('Bạn không có quyền thực hiện thao tác này', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/logs');
            return;
        }

        // Lấy action từ JSON body
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $action = $jsonData['action'] ?? null;

        if (empty($id) || empty($action)) {
            $this->error('Dữ liệu không hợp lệ', 400);
            return;
        }

        $log = $this->logModel->find($id);
        if (!$log) {
            $this->error('Không tìm thấy log', 404);
            return;
        }

        $this->logModel->update($id, ['action' => $action]);

        // Ghi log cho việc sửa log
        \Helpers\LogHelper::log('update_log', 'user_log', (int)$id, [
            'old_action' => $log['action'],
            'new_action' => $action
        ]);

        $this->success(null, 'Cập nhật log thành công');
    }

    /**
     * Xóa log (chỉ Admin)
     */
    public function delete($id = null): void
    {
        // Chỉ Admin mới được xóa
        if (!AuthHelper::isAdmin()) {
            $this->error('Bạn không có quyền thực hiện thao tác này', 403);
            return;
        }

        if (empty($id)) {
            $this->error('ID log không hợp lệ', 400);
            return;
        }

        $log = $this->logModel->find($id);
        if (!$log) {
            $this->error('Không tìm thấy log', 404);
            return;
        }

        $this->logModel->delete($id);

        // Ghi log cho việc xóa log
        \Helpers\LogHelper::log('delete_log', 'user_log', (int)$id, [
            'action' => $log['action'],
            'user_id' => $log['user_id']
        ]);

        $this->success(null, 'Xóa log thành công');
    }
}
