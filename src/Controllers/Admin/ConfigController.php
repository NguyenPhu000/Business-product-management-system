<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\SystemConfigModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * ConfigController - Quản lý cấu hình hệ thống
 */
class ConfigController extends Controller
{
    private SystemConfigModel $configModel;

    public function __construct()
    {
        $this->configModel = new SystemConfigModel();
    }

    /**
     * Danh sách cấu hình
     */
    public function index(): void
    {
        // Chỉ Admin mới được xem
        if (!AuthHelper::isAdmin()) {
            AuthHelper::setFlash('error', 'Bạn không có quyền truy cập chức năng này');
            $this->redirect('/admin/dashboard');
            return;
        }

        $configs = $this->configModel->getAllConfigs();

        $data = [
            'title' => 'Cấu hình hệ thống',
            'configs' => $configs
        ];

        $this->view('admin/config/index', $data);
    }

    /**
     * Cập nhật cấu hình
     */
    public function update(): void
    {
        // Chỉ Admin mới được sửa
        if (!AuthHelper::isAdmin()) {
            $this->error('Bạn không có quyền thực hiện thao tác này', 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/config');
            return;
        }

        // Lấy dữ liệu từ JSON body
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $key = $jsonData['key'] ?? null;
        $value = $jsonData['value'] ?? null;

        if (empty($key)) {
            $this->error('Key không được để trống', 400);
            return;
        }

        $userId = AuthHelper::id();
        $this->configModel->setValue($key, $value, $userId);

        // Ghi log
        LogHelper::log('update_config', 'system_config', 0, [
            'key' => $key,
            'value' => $value
        ]);

        $this->success(null, 'Cập nhật cấu hình thành công');
    }

    /**
     * Thêm cấu hình mới
     */
    public function store(): void
    {
        // Chỉ Admin mới được thêm
        if (!AuthHelper::isAdmin()) {
            AuthHelper::setFlash('error', 'Bạn không có quyền thực hiện thao tác này');
            $this->redirect('/admin/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/config');
            return;
        }

        $key = $this->input('key');
        $value = $this->input('value');

        if (empty($key)) {
            AuthHelper::setFlash('error', 'Key không được để trống');
            $this->redirect('/admin/config');
            return;
        }

        // Kiểm tra key đã tồn tại chưa
        $existing = $this->configModel->findBy(['key' => $key]);
        if ($existing) {
            AuthHelper::setFlash('error', 'Key đã tồn tại trong hệ thống');
            $this->redirect('/admin/config');
            return;
        }

        $userId = AuthHelper::id();
        $this->configModel->setValue($key, $value, $userId);

        // Ghi log
        LogHelper::logCreate('system_config', 0, ['key' => $key, 'value' => $value]);

        AuthHelper::setFlash('success', 'Thêm cấu hình thành công');
        $this->redirect('/admin/config');
    }

    /**
     * Xóa cấu hình
     */
    public function delete(): void
    {
        // Chỉ Admin mới được xóa
        if (!AuthHelper::isAdmin()) {
            $this->error('Bạn không có quyền thực hiện thao tác này', 403);
            return;
        }

        // Lấy dữ liệu từ JSON body
        $jsonData = json_decode(file_get_contents('php://input'), true);
        $key = $jsonData['key'] ?? null;

        if (empty($key)) {
            $this->error('Key không được để trống', 400);
            return;
        }

        $config = $this->configModel->findBy(['key' => $key]);
        if (!$config) {
            $this->error('Không tìm thấy cấu hình', 404);
            return;
        }

        $this->configModel->deleteByKey($key);

        // Ghi log
        LogHelper::logDelete('system_config', 0, ['key' => $key]);

        $this->success(null, 'Xóa cấu hình thành công');
    }
}
