<?php

namespace Modules\User\Services;

use Modules\Auth\Models\UserModel;
use Modules\Auth\Models\RoleModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * UserService - Business logic cho quản lý người dùng
 * 
 * Chức năng:
 * - CRUD users với validation
 * - Kiểm tra quyền hạn
 * - Quản lý role và permissions
 */
class UserService
{
    private UserModel $userModel;
    private RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Lấy danh sách users với phân trang
     * 
     * @param int $page Trang hiện tại
     * @param int $perPage Số bản ghi mỗi trang
     * @return array ['users' => array, 'pagination' => array]
     */
    public function getAllUsers(int $page = 1, int $perPage = 10): array
    {
        $result = $this->userModel->paginate($page, $perPage);
        $users = [];

        foreach ($result['data'] as $user) {
            $users[] = $this->userModel->findWithRole($user['id']);
        }

        return [
            'users' => $users,
            'pagination' => [
                'page' => $result['page'],
                'totalPages' => $result['totalPages'],
                'total' => $result['total']
            ]
        ];
    }

    /**
     * Lấy thông tin user theo ID
     * 
     * @param int $userId
     * @return array|null
     */
    public function getUserById(int $userId): ?array
    {
        return $this->userModel->findWithRole($userId);
    }

    /**
     * Lấy tất cả roles mà user hiện tại có thể gán cho người khác
     * Quy tắc: Chỉ được gán role có level THẤP HƠN hoặc BẰNG mình
     * 
     * @return array
     */
    public function getAllRoles(): array
    {
        $allRoles = $this->roleModel->all();
        $currentRoleLevel = AuthHelper::getRoleLevel(AuthHelper::user()['role_id'] ?? 0);

        // Lọc các role mà user hiện tại có thể gán
        return array_filter($allRoles, function ($role) use ($currentRoleLevel) {
            $targetRoleLevel = AuthHelper::getRoleLevel($role['id']);
            // Chỉ hiển thị các role có level thấp hơn hoặc bằng mình
            return $targetRoleLevel <= $currentRoleLevel;
        });
    }

    /**
     * Tạo user mới
     * 
     * @param array $data Dữ liệu user từ form
     * @return int ID user vừa tạo
     * @throws Exception Nếu validation fail hoặc lỗi DB
     */
    public function createUser(array $data): int
    {
        // Validate
        $errors = $this->validateUserData($data, true);
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }

        // Kiểm tra quyền tạo user với role được chọn
        // Quy tắc: Chỉ được tạo user có role level thấp hơn hoặc bằng mình
        $currentRoleLevel = AuthHelper::getRoleLevel(AuthHelper::user()['role_id'] ?? 0);
        $targetRoleLevel = AuthHelper::getRoleLevel((int)$data['role_id']);

        if ($targetRoleLevel > $currentRoleLevel) {
            throw new Exception('Bạn không có quyền tạo người dùng với vai trò này. Chỉ được tạo người dùng có quyền ngang hoặc thấp hơn bạn.');
        }

        // Kiểm tra email đã tồn tại
        if ($this->userModel->emailExists($data['email'])) {
            throw new Exception('Email đã tồn tại trong hệ thống');
        }

        // Kiểm tra username đã tồn tại
        if ($this->userModel->usernameExists($data['username'])) {
            throw new Exception('Username đã tồn tại trong hệ thống');
        }

        // Chuẩn bị dữ liệu
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'role_id' => (int) $data['role_id'],
            'status' => (int) ($data['status'] ?? STATUS_ACTIVE)
        ];

        // Tạo user
        $userId = $this->userModel->createUser($userData);

        // Ghi log
        LogHelper::logCreate('user', $userId, $userData);

        return $userId;
    }

    /**
     * Cập nhật user
     * 
     * @param int $userId
     * @param array $data Dữ liệu user từ form
     * @return bool
     * @throws Exception
     */
    public function updateUser(int $userId, array $data): bool
    {
        // Kiểm tra user tồn tại
        $user = $this->userModel->find($userId);
        if (!$user) {
            throw new Exception('Không tìm thấy người dùng');
        }

        // Kiểm tra quyền hạn
        if (!AuthHelper::canManageRole($user['role_id']) && $user['id'] != AuthHelper::id()) {
            throw new Exception('Bạn không có quyền sửa người dùng này. Chỉ quyền cao hơn mới được quản lý quyền thấp hơn hoặc bằng mình.');
        }

        // Kiểm tra quyền thay đổi role
        // Quy tắc: Chỉ được đổi sang role có level thấp hơn hoặc bằng mình
        $currentRoleLevel = AuthHelper::getRoleLevel(AuthHelper::user()['role_id'] ?? 0);
        $targetRoleLevel = AuthHelper::getRoleLevel((int)$data['role_id']);

        if ($targetRoleLevel > $currentRoleLevel) {
            throw new Exception('Bạn không có quyền thay đổi vai trò này. Chỉ được đổi sang vai trò ngang hoặc thấp hơn bạn.');
        }

        // Validate
        $errors = $this->validateUserData($data, false);
        if (!empty($errors)) {
            throw new Exception(implode('<br>', $errors));
        }

        // Kiểm tra email đã tồn tại (trừ user hiện tại)
        if ($this->userModel->emailExists($data['email'], $userId)) {
            throw new Exception('Email đã tồn tại trong hệ thống');
        }

        // Kiểm tra username đã tồn tại (trừ user hiện tại)
        if ($this->userModel->usernameExists($data['username'], $userId)) {
            throw new Exception('Username đã tồn tại trong hệ thống');
        }

        // Chuẩn bị dữ liệu update
        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null,
            'role_id' => (int) $data['role_id']
        ];

        // Nếu có đổi mật khẩu
        if (!empty($data['new_password'])) {
            if (strlen($data['new_password']) < 8) {
                throw new Exception('Mật khẩu mới phải có ít nhất 8 ký tự');
            }
            $userData['password'] = $data['new_password'];
        }

        // Update user
        $result = $this->userModel->updateUser($userId, $userData);

        // Ghi log
        LogHelper::logUpdate('user', $userId, $userData);

        return $result;
    }

    /**
     * Xóa user
     * 
     * @param int $userId
     * @return bool
     * @throws Exception
     */
    public function deleteUser(int $userId): bool
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            throw new Exception('Không tìm thấy người dùng');
        }

        // Không cho xóa chính mình đang đăng nhập
        if ($userId == AuthHelper::id()) {
            throw new Exception('Không thể xóa tài khoản của chính mình đang đăng nhập');
        }

        // Kiểm tra quyền hạn
        if (!AuthHelper::canManageRole($user['role_id'])) {
            throw new Exception('Bạn không có quyền xóa người dùng này. Chỉ quyền cao hơn mới được xóa quyền thấp hơn hoặc bằng mình.');
        }

        // Xóa user
        $result = $this->userModel->delete($userId);

        // Ghi log
        LogHelper::logDelete('user', $userId, $user);

        return $result;
    }

    /**
     * Validate dữ liệu user
     * 
     * @param array $data
     * @param bool $isCreate Có phải tạo mới không (để validate password)
     * @return array Mảng lỗi
     */
    private function validateUserData(array $data, bool $isCreate): array
    {
        $errors = [];

        // Username
        if (empty($data['username'])) {
            $errors[] = 'Username là bắt buộc';
        } elseif (strlen($data['username']) < 3 || strlen($data['username']) > 50) {
            $errors[] = 'Username phải từ 3-50 ký tự';
        }

        // Email
        if (empty($data['email'])) {
            $errors[] = 'Email là bắt buộc';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email không hợp lệ';
        }

        // Full name
        if (empty($data['full_name'])) {
            $errors[] = 'Họ tên là bắt buộc';
        }

        // Role ID
        if (empty($data['role_id']) || !is_numeric($data['role_id'])) {
            $errors[] = 'Vui lòng chọn quyền hạn';
        }

        // Password (chỉ khi tạo mới)
        if ($isCreate) {
            if (empty($data['password'])) {
                $errors[] = 'Mật khẩu là bắt buộc';
            } elseif (strlen($data['password']) < 8) {
                $errors[] = 'Mật khẩu phải có ít nhất 8 ký tự';
            }
        }

        return $errors;
    }

    /**
     * Kiểm tra user có quyền quản lý user khác không
     * 
     * @param int $targetUserId ID user cần kiểm tra
     * @param int $currentUserId ID user hiện tại
     * @return bool
     */
    public function canManageUser(int $targetUserId, int $currentUserId): bool
    {
        $targetUser = $this->userModel->find($targetUserId);

        if (!$targetUser) {
            return false;
        }

        // Có thể sửa chính mình
        if ($targetUserId === $currentUserId) {
            return true;
        }

        // Kiểm tra quyền hạn dựa trên role
        return AuthHelper::canManageRole($targetUser['role_id']);
    }
}
