<?php

namespace Modules\Auth\Controllers;

use Core\Controller;
use Modules\Auth\Services\PasswordResetService;
use Helpers\AuthHelper;

/**
 * PasswordResetController - Xử lý routing cho quản lý yêu cầu reset password
 * 
 * Chức năng: Nhận request, gọi PasswordResetService, trả về view/response
 * Note: Tất cả business logic đã được di chuyển sang PasswordResetService
 */
class PasswordResetController extends Controller
{
    private PasswordResetService $passwordResetService;

    public function __construct()
    {
        $this->passwordResetService = new PasswordResetService();
    }

    /**
     * Hiển thị danh sách yêu cầu reset password
     */
    public function index(): void
    {
        // Kiểm tra quyền admin
        if (!$this->passwordResetService->isAdmin()) {
            AuthHelper::setFlash('error', 'Bạn không có quyền truy cập!');
            $this->redirect('/admin/dashboard');
            return;
        }

        // Lấy số trang từ query string
        $page = (int)$this->input('page', 1);
        if ($page < 1) $page = 1;

        $perPage = 10;

        // Lấy dữ liệu từ service
        $result = $this->passwordResetService->getAllRequests($page, $perPage);

        $this->view('admin/password-reset/index', $result);
    }

    /**
     * Phê duyệt yêu cầu
     */
    public function approve($id = null): void
    {
        try {
            $requestId = (int)$id;

            // Gọi service approve
            $this->passwordResetService->approveRequest($requestId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Đã phê duyệt yêu cầu! Người dùng có thể đăng nhập vào trang quên mật khẩu để đổi mật khẩu mới.'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Từ chối yêu cầu
     */
    public function reject($id = null): void
    {
        try {
            $requestId = (int)$id;

            // Gọi service reject
            $this->passwordResetService->rejectRequest($requestId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Đã từ chối yêu cầu!'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * API: Lấy danh sách yêu cầu mới (để polling)
     */
    public function checkNew(): void
    {
        // Kiểm tra quyền admin
        if (!$this->passwordResetService->isAdmin()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập!'
            ]);
            return;
        }

        $pendingCount = $this->passwordResetService->getPendingCount();
        $requests = $this->passwordResetService->getPendingRequests();

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
        try {
            $requestId = (int)$id;

            // Gọi service delete
            $this->passwordResetService->deleteRequest($requestId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Đã xóa yêu cầu thành công!'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Kiểm tra các request bị cancelled (hủy bởi user)
     */
    public function checkCancelled(): void
    {
        if (!$this->passwordResetService->isAdmin()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
            return;
        }

        // Lấy các request có status = 'cancelled'
        $cancelledRequests = $this->passwordResetService->getCancelledRequests();
        $cancelledIds = array_column($cancelledRequests, 'id');

        $this->jsonResponse([
            'success' => true,
            'cancelledIds' => $cancelledIds,
            'count' => count($cancelledIds)
        ]);
    }

    /**
     * Đánh dấu request đã hoàn tất (sau khi approve)
     */
    public function markCompleted($id = null): void
    {
        try {
            $requestId = (int)$id;

            // Gọi service để đánh dấu hoàn tất
            $this->passwordResetService->markCompleted($requestId);

            $this->jsonResponse([
                'success' => true,
                'message' => 'Đã cập nhật trạng thái thành công!'
            ]);
        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
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
