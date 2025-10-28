<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\PasswordResetRequestModel;
use Models\UserModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * PasswordResetController - Quản lý yêu cầu đặt lại mật khẩu
 */
class PasswordResetController extends Controller
{
    private PasswordResetRequestModel $resetRequestModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->resetRequestModel = new PasswordResetRequestModel();
        $this->userModel = new UserModel();
    }

    /**
     * Hiển thị danh sách yêu cầu reset password
     */
    public function index(): void
    {
        // Kiểm tra quyền admin
        if (!AuthHelper::isAdmin()) {
            AuthHelper::setFlash('error', 'Bạn không có quyền truy cập!');
            $this->redirect('/admin/dashboard');
            return;
        }

        // Lấy danh sách yêu cầu
        $requests = $this->resetRequestModel->getAllRequests();
        $pendingCount = $this->resetRequestModel->countPendingRequests();

        $this->view('admin/password-reset/index', [
            'requests' => $requests,
            'pendingCount' => $pendingCount
        ]);
    }

    /**
     * Phê duyệt yêu cầu
     */
    public function approve($id = null): void
    {
        // Kiểm tra quyền admin
        if (!AuthHelper::isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền thực hiện!']);
            return;
        }

        $id = (int)$id;
        $request = $this->resetRequestModel->getRequestById($id);

        if (!$request) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy yêu cầu!']);
            return;
        }

        if ($request['status'] !== 'pending') {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu đã được xử lý!']);
            return;
        }

        // Phê duyệt yêu cầu (KHÔNG tự động tạo mật khẩu, để user tự đổi)
        $adminId = AuthHelper::user()['id'] ?? 0;
        $this->resetRequestModel->approveRequest($id, $adminId);

        // Ghi log
        LogHelper::log('approve_reset_password', 'password_reset_request', $id, [
            'user_id' => $request['user_id'],
            'email' => $request['email'],
            'admin_id' => $adminId
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Đã phê duyệt yêu cầu! Người dùng có thể đăng nhập vào trang quên mật khẩu để đổi mật khẩu mới.'
        ]);
    }

    /**
     * Từ chối yêu cầu
     */
    public function reject($id = null): void
    {
        // Kiểm tra quyền admin
        if (!AuthHelper::isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền thực hiện!']);
            return;
        }

        $id = (int)$id;
        $request = $this->resetRequestModel->getRequestById($id);

        if (!$request) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy yêu cầu!']);
            return;
        }

        if ($request['status'] !== 'pending') {
            $this->jsonResponse(['success' => false, 'message' => 'Yêu cầu đã được xử lý!']);
            return;
        }

        // Từ chối yêu cầu
        $adminId = AuthHelper::user()['id'] ?? 0;
        $this->resetRequestModel->rejectRequest($id, $adminId);

        // Ghi log
        LogHelper::log('reject_reset_password', 'password_reset_request', $id, [
            'user_id' => $request['user_id'],
            'email' => $request['email'],
            'admin_id' => $adminId
        ]);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Đã từ chối yêu cầu!'
        ]);
    }

    /**
     * API: Lấy danh sách yêu cầu mới (để polling)
     */
    public function checkNew(): void
    {
        // Kiểm tra quyền admin
        if (!AuthHelper::isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền truy cập!']);
            return;
        }

        $pendingCount = $this->resetRequestModel->countPendingRequests();
        $requests = $this->resetRequestModel->getPendingRequests();

        $this->jsonResponse([
            'success' => true,
            'pendingCount' => $pendingCount,
            'requests' => $requests
        ]);
    }

    /**
     * Xóa request (chỉ admin)
     */
    public function delete($id = null): void
    {
        // Kiểm tra quyền admin
        if (!AuthHelper::isAdmin()) {
            $this->jsonResponse(['success' => false, 'message' => 'Bạn không có quyền thực hiện!']);
            return;
        }

        $id = (int)$id;
        $request = $this->resetRequestModel->getRequestById($id);

        if (!$request) {
            $this->jsonResponse(['success' => false, 'message' => 'Không tìm thấy yêu cầu!']);
            return;
        }

        // Xóa request
        $adminId = AuthHelper::user()['id'] ?? 0;
        $result = $this->resetRequestModel->deleteRequest($id);

        if ($result) {
            // Ghi log
            LogHelper::log('delete', 'password_reset_request', $id, [
                'user_id' => $request['user_id'],
                'email' => $request['email'],
                'status' => $request['status'],
                'admin_id' => $adminId
            ]);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Đã xóa yêu cầu thành công!'
            ]);
        } else {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Không thể xóa yêu cầu!'
            ]);
        }
    }

    /**
     * Tạo mật khẩu ngẫu nhiên
     */
    private function generateRandomPassword(int $length = 8): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%';
        $password = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, $charactersLength - 1)];
        }

        return $password;
    }

    /**
     * Trả về JSON response
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
