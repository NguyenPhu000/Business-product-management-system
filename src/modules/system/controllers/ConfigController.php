<?php

namespace Modules\System\Controllers;

use Core\Controller;
use Modules\System\Services\ConfigService;
use Helpers\AuthHelper;
use Exception;

/**
 * ConfigController - Routing layer cho quản lý cấu hình hệ thống
 * 
 * Chỉ xử lý request/response, logic nằm trong ConfigService
 */
class ConfigController extends Controller
{
    private ConfigService $configService;

    public function __construct()
    {
        $this->configService = new ConfigService();
    }

    /**
     * Hiển thị danh sách cấu hình
     */
    public function index(): void
    {
        $configs = $this->configService->getAllConfigs();

        $data = [
            'title' => 'Quản lý Cấu hình Hệ thống',
            'configs' => $configs,
            'isAdmin' => $this->configService->isAdmin()
        ];

        $this->view('admin/config/index', $data);
    }

    /**
     * Cập nhật cấu hình
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/config');
            return;
        }

        try {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            $key = $jsonData['key'] ?? null;
            $value = $jsonData['value'] ?? null;

            if (empty($key)) {
                $this->error('Key không được để trống', 400);
                return;
            }

            $this->configService->updateConfig($key, $value);
            $this->success(null, 'Cập nhật cấu hình thành công');
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }

    /**
     * Thêm cấu hình mới
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/config');
            return;
        }

        try {
            $key = $this->input('key');
            $value = $this->input('value');

            if (empty($key)) {
                AuthHelper::setFlash('error', 'Key không được để trống');
                $this->redirect('/admin/config');
                return;
            }

            $this->configService->createConfig($key, $value);
            AuthHelper::setFlash('success', 'Thêm cấu hình thành công');
            $this->redirect('/admin/config');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/config');
        }
    }

    /**
     * Xóa cấu hình
     */
    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/config');
            return;
        }

        try {
            $jsonData = json_decode(file_get_contents('php://input'), true);
            $key = $jsonData['key'] ?? null;

            if (empty($key)) {
                $this->error('Key không được để trống', 400);
                return;
            }

            $this->configService->deleteConfig($key);
            $this->success(null, 'Xóa cấu hình thành công');
        } catch (Exception $e) {
            $this->error($e->getMessage(), 400);
        }
    }
}
