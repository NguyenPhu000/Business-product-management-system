<?php

namespace Modules\System\Services;

use Modules\System\Models\SystemConfigModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * ConfigService - Business logic cho quản lý cấu hình hệ thống
 * 
 * Chức năng:
 * - CRUD system config
 * - Kiểm tra quyền admin
 * - Ghi log các thao tác
 */
class ConfigService
{
    private SystemConfigModel $configModel;

    public function __construct()
    {
        $this->configModel = new SystemConfigModel();
    }

    /**
     * Lấy tất cả cấu hình
     * 
     * @return array
     */
    public function getAllConfigs(): array
    {
        return $this->configModel->getAllConfigs();
    }

    /**
     * Lấy cấu hình theo key
     * 
     * @param string $key
     * @return array|null
     */
    public function getConfigByKey(string $key): ?array
    {
        return $this->configModel->findBy(['key' => $key]);
    }

    /**
     * Tạo cấu hình mới
     * 
     * @param string $key
     * @param string $value
     * @param string|null $description
     * @return bool
     * @throws Exception
     */
    public function createConfig(string $key, string $value, ?string $description = null): bool
    {
        // Chỉ Admin mới được tạo
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Bạn không có quyền thực hiện thao tác này');
        }

        // Validate
        if (empty($key)) {
            throw new Exception('Config key không được để trống');
        }

        // Kiểm tra key đã tồn tại chưa
        $existingConfig = $this->configModel->findBy(['key' => $key]);
        if ($existingConfig) {
            throw new Exception('Config key đã tồn tại');
        }

        $userId = AuthHelper::id();
        $result = $this->configModel->setValue($key, $value, $userId);

        // Ghi log
        if ($result) {
            LogHelper::log('create_config', 'system_config', 0, [
                'key' => $key,
                'value' => $value
            ]);
        }

        return $result;
    }

    /**
     * Cập nhật cấu hình
     * 
     * @param string $key
     * @param string $value
     * @return bool
     * @throws Exception
     */
    public function updateConfig(string $key, string $value): bool
    {
        // Chỉ Admin mới được sửa
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Bạn không có quyền thực hiện thao tác này');
        }

        // Validate
        if (empty($key)) {
            throw new Exception('Config key không được để trống');
        }

        $userId = AuthHelper::id();
        $result = $this->configModel->setValue($key, $value, $userId);

        // Ghi log
        if ($result) {
            LogHelper::log('update_config', 'system_config', 0, [
                'key' => $key,
                'value' => $value
            ]);
        }

        return $result;
    }

    /**
     * Xóa cấu hình
     * 
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public function deleteConfig(string $key): bool
    {
        // Chỉ Admin mới được xóa
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Bạn không có quyền thực hiện thao tác này');
        }

        // Kiểm tra config có tồn tại không
        $config = $this->configModel->findBy(['key' => $key]);
        if (!$config) {
            throw new Exception('Không tìm thấy cấu hình');
        }

        // Xóa
        $result = $this->configModel->deleteByKey($key);

        // Ghi log
        if ($result) {
            LogHelper::log('delete_config', 'system_config', 0, [
                'key' => $key
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

    /**
     * Validate dữ liệu config
     * 
     * @param string $key
     * @param string $value
     * @return array Mảng lỗi (rỗng nếu hợp lệ)
     */
    public function validateConfigData(string $key, string $value): array
    {
        $errors = [];

        if (empty($key)) {
            $errors['config_key'] = 'Config key không được để trống';
        }

        if (empty($value)) {
            $errors['config_value'] = 'Config value không được để trống';
        }

        return $errors;
    }
}
