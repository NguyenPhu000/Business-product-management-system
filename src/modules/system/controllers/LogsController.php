<?php

namespace Modules\System\Controllers;

use Core\Controller;
use Modules\System\Services\LogsService;
use Helpers\AuthHelper;
use Exception;

/**
 * LogsController - Routing layer cho quản lý logs
 * 
 * Chỉ xử lý request/response, logic nằm trong LogsService
 */
class LogsController extends Controller
{
    private LogsService $logsService;

    public function __construct()
    {
        $this->logsService = new LogsService();
    }

    /**
     * Hiển thị danh sách logs
     */
    public function index(): void
    {
        $page = (int)($this->input('page') ?? 1);
        $userId = $this->input('user_id') ? (int)$this->input('user_id') : null;
        $action = $this->input('action') ?: null;

        $result = $this->logsService->getLogsWithFilter($page, 12, $userId, $action);
        $users = $this->logsService->getUsersForFilter();
        $actions = $this->logsService->getActions();

        $data = [
            'title' => 'Quản lý Logs',
            'logs' => $result['logs'],
            'pagination' => $result['pagination'],
            'users' => $users,
            'actions' => $actions,
            'currentUserId' => $userId,
            'currentAction' => $action
        ];

        $this->view('admin/logs/index', $data);
    }

    /**
     * Xóa logs cũ
     */
    public function cleanup(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/logs');
            return;
        }

        try {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            $days = (int)($jsonData['days'] ?? 30);

            $this->logsService->cleanupOldLogs($days);
            $this->success(null, 'Xóa log cũ thành công');
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Cập nhật log
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/logs');
            return;
        }

        try {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            $logId = (int)($jsonData['id'] ?? 0);
            $action = $jsonData['action'] ?? '';

            $this->logsService->updateLog($logId, $action);
            $this->success(null, 'Cập nhật log thành công');
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Xóa log
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/logs');
            return;
        }

        try {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            $logId = (int)($jsonData['id'] ?? 0);

            $this->logsService->deleteLog($logId);
            $this->success(null, 'Xóa log thành công');
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }
}
