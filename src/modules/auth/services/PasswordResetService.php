<?php

namespace Modules\Auth\Services;

use Modules\Auth\Models\PasswordResetRequestModel;
use Modules\Auth\Models\UserModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * PasswordResetService - Business logic cho quản lý yêu cầu reset password
 * 
 * Chức năng:
 * - Quản lý các request reset password từ users
 * - Approve/Reject requests (chỉ Admin)
 * - Tracking và logging
 */
class PasswordResetService
{
    private PasswordResetRequestModel $resetRequestModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->resetRequestModel = new PasswordResetRequestModel();
        $this->userModel = new UserModel();
    }

    /**
     * Lấy danh sách requests với phân trang
     * 
     * @param int $page Trang hiện tại
     * @param int $perPage Số bản ghi mỗi trang
     * @return array
     */
    public function getAllRequests(int $page = 1, int $perPage = 10): array
    {
        $requests = $this->resetRequestModel->getAllRequests($page, $perPage);
        $totalRequests = $this->resetRequestModel->countRequests();
        $totalPages = ceil($totalRequests / $perPage);
        $pendingCount = $this->resetRequestModel->countPendingRequests();

        return [
            'requests' => $requests,
            'pendingCount' => $pendingCount,
            'pagination' => [
                'page' => $page,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'perPage' => $perPage,
                'totalRecords' => $totalRequests
            ]
        ];
    }

    /**
     * Phê duyệt yêu cầu reset password
     * 
     * @param int $requestId
     * @return bool
     * @throws Exception
     */
    public function approveRequest(int $requestId): bool
    {
        // Kiểm tra quyền admin
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Bạn không có quyền thực hiện!');
        }

        $request = $this->resetRequestModel->getRequestById($requestId);

        if (!$request) {
            throw new Exception('Không tìm thấy yêu cầu!');
        }

        if ($request['status'] !== 'pending') {
            throw new Exception('Yêu cầu đã được xử lý!');
        }

        // Phê duyệt yêu cầu
        $adminId = AuthHelper::user()['id'] ?? 0;
        $result = $this->resetRequestModel->approveRequest($requestId, $adminId);

        // Ghi log
        if ($result) {
            LogHelper::log('approve_reset_password', 'password_reset_request', $requestId, [
                'user_id' => $request['user_id'],
                'email' => $request['email'],
                'admin_id' => $adminId
            ]);
        }

        return $result;
    }

    /**
     * Từ chối yêu cầu reset password
     * 
     * @param int $requestId
     * @return bool
     * @throws Exception
     */
    public function rejectRequest(int $requestId): bool
    {
        // Kiểm tra quyền admin
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Bạn không có quyền thực hiện!');
        }

        $request = $this->resetRequestModel->getRequestById($requestId);

        if (!$request) {
            throw new Exception('Không tìm thấy yêu cầu!');
        }

        if ($request['status'] !== 'pending') {
            throw new Exception('Yêu cầu đã được xử lý!');
        }

        // Từ chối yêu cầu
        $adminId = AuthHelper::user()['id'] ?? 0;
        $result = $this->resetRequestModel->rejectRequest($requestId, $adminId);

        // Ghi log
        if ($result) {
            LogHelper::log('reject_reset_password', 'password_reset_request', $requestId, [
                'user_id' => $request['user_id'],
                'email' => $request['email'],
                'admin_id' => $adminId
            ]);
        }

        return $result;
    }

    /**
     * Xóa request
     * 
     * @param int $requestId
     * @return bool
     * @throws Exception
     */
    public function deleteRequest(int $requestId): bool
    {
        // Kiểm tra quyền admin
        if (!AuthHelper::isAdmin()) {
            throw new Exception('Bạn không có quyền thực hiện!');
        }

        $request = $this->resetRequestModel->getRequestById($requestId);

        if (!$request) {
            throw new Exception('Không tìm thấy yêu cầu!');
        }

        // Xóa request
        $adminId = AuthHelper::user()['id'] ?? 0;
        $result = $this->resetRequestModel->deleteRequest($requestId);

        // Ghi log
        if ($result) {
            LogHelper::log('delete', 'password_reset_request', $requestId, [
                'user_id' => $request['user_id'],
                'email' => $request['email'],
                'status' => $request['status'],
                'admin_id' => $adminId
            ]);
        }

        return $result;
    }

    /**
     * Lấy số lượng pending requests
     * 
     * @return int
     */
    public function getPendingCount(): int
    {
        return $this->resetRequestModel->countPendingRequests();
    }

    /**
     * Lấy danh sách pending requests
     * 
     * @return array
     */
    public function getPendingRequests(): array
    {
        return $this->resetRequestModel->getPendingRequests();
    }

    /**
     * Lấy danh sách cancelled requests
     * 
     * @return array
     */
    public function getCancelledRequests(): array
    {
        return $this->resetRequestModel->getCancelledRequests();
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
