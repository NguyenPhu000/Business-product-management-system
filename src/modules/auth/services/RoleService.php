<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\RoleModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * RoleService - Business logic cho quản lý vai trò
 * 
 * Chức năng:
 * - Quản lý roles (CRUD)
 * - Kiểm tra quyền hạn
 * - Đếm số user trong mỗi role
 */
class RoleService
{
    private RoleModel $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    /**
     * Lấy tất cả roles với số lượng users
     * 
     * @param string $orderBy Cột sắp xếp
     * @param string $order ASC hoặc DESC
     * @return array
     */
    public function getAllRolesWithUserCount(string $orderBy = 'name', string $order = 'ASC'): array
    {
        $roles = $this->roleModel->all($orderBy, $order);

        // Đếm số user cho mỗi role
        foreach ($roles as &$role) {
            $role['user_count'] = $this->roleModel->countUsers($role['id']);
        }

        return $roles;
    }

    /**
     * Lấy thông tin role theo ID
     * 
     * @param int $roleId
     * @return array|null
     */
    public function getRoleById(int $roleId): ?array
    {
        return $this->roleModel->find($roleId);
    }

    /**
     * Cập nhật role
     * 
     * @param int $roleId
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateRole(int $roleId, array $data): bool
    {
        // Chỉ Admin mới được sửa vai trò
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Chỉ Admin mới có quyền sửa vai trò');
        }

        // Kiểm tra role tồn tại
        $role = $this->roleModel->find($roleId);
        if (!$role) {
            throw new Exception('Không tìm thấy vai trò');
        }

        // Validate
        $errors = $this->validateRoleData($data);
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }

        // Kiểm tra tên role đã tồn tại (trừ role hiện tại)
        if ($this->roleModel->nameExists($data['name'], $roleId)) {
            throw new Exception('Tên vai trò đã tồn tại');
        }

        // Chuẩn bị dữ liệu
        $roleData = [
            'name' => $data['name'],
            'description' => $data['description']
        ];

        // Cập nhật
        $result = $this->roleModel->update($roleId, $roleData);

        // Ghi log
        if ($result) {
            LogHelper::logUpdate('role', $roleId, $roleData);
        }

        return $result;
    }

    /**
     * Validate dữ liệu role
     * 
     * @param array $data
     * @return array Mảng lỗi
     */
    private function validateRoleData(array $data): array
    {
        $errors = [];

        // Name
        if (empty($data['name'])) {
            $errors[] = 'Tên vai trò là bắt buộc';
        } elseif (strlen($data['name']) < 3 || strlen($data['name']) > 50) {
            $errors[] = 'Tên vai trò phải từ 3-50 ký tự';
        }

        // Description
        if (empty($data['description'])) {
            $errors[] = 'Mô tả vai trò là bắt buộc';
        }

        return $errors;
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
