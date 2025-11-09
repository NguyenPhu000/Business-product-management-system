<?php

namespace Modules\Auth\Services;

use Models\UserModel;
use Models\PasswordResetRequestModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * AuthService - Xử lý business logic cho Authentication
 * 
 * Chức năng:
 * - Xác thực đăng nhập
 * - Quên mật khẩu và reset password
 * - Quản lý session và remember me
 */
class AuthService
{
    private UserModel $userModel;
    private PasswordResetRequestModel $resetRequestModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->resetRequestModel = new PasswordResetRequestModel();
    }

    /**
     * Xác thực đăng nhập
     * 
     * @param string $email Email hoặc username
     * @param string $password Mật khẩu
     * @param bool $remember Có remember me không
     * @return array Thông tin user
     * @throws Exception Nếu đăng nhập thất bại
     */
    public function authenticate(string $email, string $password, bool $remember = false): array
    {
        // Validate
        if (empty($email) || empty($password)) {
            throw new Exception('Vui lòng nhập đầy đủ thông tin');
        }

        // Xác thực
        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            throw new Exception('Email hoặc mật khẩu không đúng');
        }

        // Kiểm tra trạng thái tài khoản
        if ($user['status'] != STATUS_ACTIVE) {
            throw new Exception('Tài khoản của bạn đã bị vô hiệu hóa');
        }

        // Đăng nhập thành công
        AuthHelper::login($user);

        // Ghi log
        LogHelper::logLogin($user['id']);

        // Set cookie remember me nếu cần
        if ($remember) {
            // TODO: Implement remember me token
        }

        return $user;
    }

    /**
     * Đăng xuất
     * 
     * @param int|null $userId ID user đang đăng xuất
     * @return void
     */
    public function logout(?int $userId = null): void
    {
        if ($userId) {
            LogHelper::logLogout($userId);
        }

        AuthHelper::logout();
    }

    /**
     * Kiểm tra yêu cầu reset password đã được approve chưa
     * 
     * @param string $email
     * @return array|null ['approved' => true, 'user' => array, 'request' => array] hoặc null
     */
    public function checkResetPasswordApproval(string $email): ?array
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return null;
        }

        $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($user['id']);

        if ($approvedRequest) {
            return [
                'approved' => true,
                'user' => $user,
                'request' => $approvedRequest
            ];
        }

        return null;
    }

    /**
     * Xử lý quên mật khẩu
     * 
     * @param string $email
     * @param string|null $newPassword Mật khẩu mới (nếu có)
     * @param string|null $confirmPassword Xác nhận mật khẩu
     * @return array ['action' => 'redirect_to_form|create_request|password_changed', 'user' => array, ...]
     * @throws Exception
     */
    public function handleForgotPassword(string $email, ?string $newPassword = null, ?string $confirmPassword = null): array
    {
        if (empty($email)) {
            throw new Exception('Vui lòng nhập email');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            throw new Exception('Email không tồn tại trong hệ thống');
        }

        // Kiểm tra đã được approve chưa
        $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($user['id']);
        if ($approvedRequest && empty($newPassword)) {
            return [
                'action' => 'redirect_to_form',
                'user' => $user,
                'request' => $approvedRequest
            ];
        }

        // Kiểm tra role
        $userWithRole = $this->userModel->findWithRole($user['id']);

        // Admin tự đổi
        if ($userWithRole && isset($userWithRole['name']) && strtolower($userWithRole['name']) === 'admin') {
            if (empty($newPassword)) {
                return [
                    'action' => 'redirect_to_form',
                    'user' => $user,
                    'is_admin' => true
                ];
            }

            // Validate password
            $this->validatePassword($newPassword, $confirmPassword);

            // Update password
            $this->updatePassword($user['id'], $newPassword, $email, 'admin_self_reset');

            return [
                'action' => 'password_changed',
                'user' => $user
            ];
        }

        // User thường đã approve
        if (!empty($newPassword)) {
            $this->validatePassword($newPassword, $confirmPassword);

            // Update password
            $this->updatePassword($user['id'], $newPassword, $email, 'user_reset_after_approval');

            // Mark request as password changed
            if ($approvedRequest) {
                $this->resetRequestModel->markPasswordChanged($approvedRequest['id']);
            }

            return [
                'action' => 'password_changed',
                'user' => $user
            ];
        }

        // User thường tạo request
        $this->resetRequestModel->deleteExpiredApprovedRequests($user['id']);

        if ($this->resetRequestModel->hasPendingRequest($user['id'])) {
            throw new Exception('Bạn đã có yêu cầu đang chờ xét duyệt!');
        }

        $this->resetRequestModel->deleteRejectedRequests($user['id']);

        $requestId = $this->resetRequestModel->createRequest($user['id'], $email);

        if (!$requestId) {
            throw new Exception('Có lỗi xảy ra. Vui lòng thử lại!');
        }

        LogHelper::log('request_reset_password', 'user', $user['id'], [
            'email' => $email,
            'request_id' => $requestId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        return [
            'action' => 'create_request',
            'user' => $user,
            'request_id' => $requestId
        ];
    }

    /**
     * Validate mật khẩu mới
     * 
     * @param string $password
     * @param string $confirmPassword
     * @throws Exception
     */
    private function validatePassword(string $password, string $confirmPassword): void
    {
        if (strlen($password) < 6) {
            throw new Exception('Mật khẩu phải có ít nhất 6 ký tự');
        }

        if ($password !== $confirmPassword) {
            throw new Exception('Mật khẩu xác nhận không khớp');
        }
    }

    /**
     * Cập nhật mật khẩu
     * 
     * @param int $userId
     * @param string $newPassword
     * @param string $email
     * @param string $resetType
     * @return void
     */
    private function updatePassword(int $userId, string $newPassword, string $email, string $resetType): void
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $this->userModel->updateUser($userId, [
            'password_hash' => $hashedPassword
        ]);

        LogHelper::log('reset_password', 'user', $userId, [
            'email' => $email,
            'type' => $resetType,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
    }

    /**
     * Lấy thông tin yêu cầu reset password đã approve
     * 
     * @param int $requestId
     * @return array|null
     */
    public function getApprovedRequest(int $requestId): ?array
    {
        return $this->resetRequestModel->getApprovedRequestById($requestId);
    }

    /**
     * Kiểm tra trạng thái yêu cầu reset password
     * 
     * @param int $userId
     * @return array ['status' => 'pending|approved|rejected', 'request' => array|null]
     */
    public function checkRequestStatus(int $userId): array
    {
        $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($userId);
        if ($approvedRequest) {
            return [
                'status' => 'approved',
                'request' => $approvedRequest
            ];
        }

        $rejectedRequest = $this->resetRequestModel->getRejectedRequestByUserId($userId);
        if ($rejectedRequest) {
            return [
                'status' => 'rejected',
                'request' => $rejectedRequest
            ];
        }

        if ($this->resetRequestModel->hasPendingRequest($userId)) {
            return [
                'status' => 'pending',
                'request' => null
            ];
        }

        return [
            'status' => 'none',
            'request' => null
        ];
    }

    /**
     * Hủy yêu cầu reset password
     * 
     * @param int $userId
     * @param bool $silent Hủy mà không cập nhật status (chỉ xóa)
     * @return bool
     * @throws Exception
     */
    public function cancelResetRequest(int $userId, bool $silent = false): bool
    {
        if ($silent) {
            return $this->resetRequestModel->deletePendingRequestByUser($userId);
        }

        return $this->resetRequestModel->cancelPendingRequest($userId);
    }

    /**
     * Tìm user theo email
     * 
     * @param string $email
     * @return array|null
     */
    public function findUserByEmail(string $email): ?array
    {
        return $this->userModel->findByEmail($email);
    }
}
