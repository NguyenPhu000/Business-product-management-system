<?php

namespace Modules\Auth\Controllers;

use Core\Controller;
use Modules\Auth\Services\AuthService;
use Helpers\AuthHelper;

/**
 * AuthController - Xử lý routing cho Authentication
 * 
 * Chức năng: Nhận request, gọi service, trả về view/response
 * Note: Tất cả business logic đã được di chuyển sang AuthService
 */
class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function showLogin(): void
    {
        // Nếu đã đăng nhập thì redirect về dashboard
        if (AuthHelper::check()) {
            $this->redirect('/admin/dashboard');
        }

        $this->view('auth/login', [], null);
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(): void
    {
        try {
            $email = $this->input('email', '');
            $password = $this->input('password', '');
            $remember = $this->input('remember', false);

            // Gọi service xử lý login
            $this->authService->authenticate($email, $password, $remember);

            AuthHelper::setFlash('success', 'Đăng nhập thành công!');
            $this->redirect('/admin/dashboard');
        } catch (\Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/login');
        }
    }

    /**
     * Xử lý đăng xuất
     */
    public function logout(): void
    {
        $userId = AuthHelper::id();

        // Gọi service xử lý logout
        $this->authService->logout($userId);

        // Thêm cache control headers để ngăn back button
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        AuthHelper::setFlash('success', 'Đăng xuất thành công!');
        $this->redirect('/admin/login?from=logout');
    }

    /**
     * Hiển thị form quên mật khẩu
     */
    public function showForgotPassword(): void
    {
        // Nếu đã đăng nhập thì redirect về dashboard
        if (AuthHelper::check()) {
            $this->redirect('/admin/dashboard');
        }

        // Nếu URL có ?approved=1&email=... thì xử lý ngay
        $approvedParam = $this->input('approved', '');
        $emailParam = $this->input('email', '');

        if (!empty($approvedParam) && (string)$approvedParam === '1' && !empty($emailParam)) {
            $result = $this->authService->checkResetPasswordApproval($emailParam);
            if ($result) {
                $_SESSION['reset_email'] = $emailParam;
                $_SESSION['reset_user_id'] = $result['user']['id'];
                $_SESSION['reset_request_id'] = $result['request']['id'];

                unset($_SESSION['waiting_approval_email']);
                unset($_SESSION['waiting_approval_user_id']);

                $this->redirect('/reset-password-form');
                return;
            }
        }

        // Kiểm tra xem có session waiting không
        if (isset($_SESSION['waiting_approval_user_id'])) {
            $userId = (int)$_SESSION['waiting_approval_user_id'];
            $email = $_SESSION['waiting_approval_email'] ?? '';

            $status = $this->authService->checkRequestStatus($userId);
            if ($status['status'] === 'approved') {
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = $userId;
                $_SESSION['reset_request_id'] = $status['request']['id'];

                unset($_SESSION['waiting_approval_email']);
                unset($_SESSION['waiting_approval_user_id']);

                $this->redirect('/reset-password-form');
                return;
            }
        }

        $this->view('auth/forgot-password', [], null);
    }

    /**
     * Xử lý quên mật khẩu - Bước 1: Kiểm tra email
     */
    public function forgotPassword(): void
    {
        try {
            // GET request - check email từ polling
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $email = $this->input('email', '');

                if (!empty($email)) {
                    $result = $this->authService->checkResetPasswordApproval($email);
                    if ($result) {
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['reset_user_id'] = $result['user']['id'];
                        $_SESSION['reset_request_id'] = $result['request']['id'];

                        unset($_SESSION['waiting_approval_email']);
                        unset($_SESSION['waiting_approval_user_id']);

                        $this->redirect('/reset-password-form');
                        return;
                    }
                }

                $this->redirect('/forgot-password');
                return;
            }

            // POST request - xử lý form submit
            $email = $this->input('email', '');
            $newPassword = $this->input('new_password', '');
            $confirmPassword = $this->input('confirm_password', '');

            // Gọi service xử lý forgot password
            $result = $this->authService->handleForgotPassword($email, $newPassword, $confirmPassword);

            // Xử lý kết quả dựa theo action
            switch ($result['action']) {
                case 'redirect_to_form':
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_user_id'] = $result['user']['id'];
                    if (isset($result['request'])) {
                        $_SESSION['reset_request_id'] = $result['request']['id'];
                    }
                    if (isset($result['is_admin'])) {
                        $_SESSION['is_admin'] = true;
                    }

                    unset($_SESSION['waiting_approval_email']);
                    unset($_SESSION['waiting_approval_user_id']);

                    $this->redirect('/reset-password-form');
                    break;

                case 'password_changed':
                    unset($_SESSION['reset_email']);
                    unset($_SESSION['reset_user_id']);
                    unset($_SESSION['reset_request_id']);
                    unset($_SESSION['is_admin']);

                    AuthHelper::setFlash('success', 'Đổi mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
                    $this->redirect('/admin/login');
                    break;

                case 'create_request':
                    $_SESSION['waiting_approval_email'] = $email;
                    $_SESSION['waiting_approval_user_id'] = $result['user']['id'];

                    AuthHelper::setFlash('success', 'Yêu cầu đã được gửi thành công! Đang chờ admin phê duyệt...');
                    $this->redirect('/forgot-password');
                    break;

                default:
                    throw new \Exception('Hành động không hợp lệ');
            }
        } catch (\Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Hiển thị form nhập mật khẩu mới
     */
    public function showResetPasswordForm(): void
    {
        $requestId = (int)$this->input('request_id', 0);
        if ($requestId > 0) {
            $approvedRequest = $this->authService->getApprovedRequest($requestId);
            if ($approvedRequest) {
                $_SESSION['reset_email'] = $approvedRequest['email'];
                $_SESSION['reset_user_id'] = $approvedRequest['user_id'];
                $_SESSION['reset_request_id'] = $approvedRequest['id'];

                $this->view('auth/reset-password-form', ['email' => $approvedRequest['email']], null);
                return;
            }

            AuthHelper::setFlash('error', 'Liên kết đổi mật khẩu không hợp lệ hoặc đã hết hạn. Vui lòng gửi lại yêu cầu.');
            $this->redirect('/forgot-password');
            return;
        }

        if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_user_id'])) {
            AuthHelper::setFlash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại!');
            $this->redirect('/forgot-password');
            return;
        }

        $email = $_SESSION['reset_email'];
        $this->view('auth/reset-password-form', ['email' => $email], null);
    }

    /**
     * API: Kiểm tra trạng thái yêu cầu (AJAX)
     */
    public function checkApproval($userId = null): void
    {
        header('Content-Type: application/json');

        if (!$userId) {
            echo json_encode(['status' => 'pending']);
            exit;
        }

        $userId = (int)$userId;

        $status = $this->authService->checkRequestStatus($userId);

        if ($status['status'] === 'approved') {
            echo json_encode(['status' => 'approved', 'request_id' => $status['request']['id']]);
            exit;
        }

        if ($status['status'] === 'rejected') {
            echo json_encode(['status' => 'rejected', 'request_id' => $status['request']['id']]);
            exit;
        }

        echo json_encode(['status' => 'pending']);
        exit;
    }

    /**
     * Kiểm tra trạng thái yêu cầu reset password
     */
    public function checkRequestStatus(): void
    {
        try {
            $email = $this->input('email', '');

            if (empty($email)) {
                throw new \Exception('Vui lòng nhập email');
            }

            $user = $this->authService->findUserByEmail($email);

            if (!$user) {
                throw new \Exception('Email không tồn tại trong hệ thống');
            }

            $userId = $user['id'] ?? $user['id_user'] ?? null;

            if (!$userId) {
                throw new \Exception('Không tìm thấy thông tin user');
            }

            // Gọi service kiểm tra status
            $status = $this->authService->checkRequestStatus($userId);

            // Render view theo status
            $this->view('auth/check-request-status', [
                'status' => $status['status'],
                'email' => $email,
                'userId' => $userId
            ], null);
        } catch (\Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/login');
        }
    }

    /**
     * Hủy yêu cầu đặt lại mật khẩu (AJAX)
     */
    public function cancelRequest(): void
    {
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $input['user_id'] ?? null;
            $silent = isset($input['silent']) ? (bool)$input['silent'] : false;

            if (!$userId) {
                throw new \Exception('User ID is required');
            }

            $result = $this->authService->cancelResetRequest((int)$userId, $silent);

            if ($result) {
                $message = $silent ? 'Yêu cầu đã được hủy (silent)' : 'Yêu cầu đã được hủy thành công';
                $this->json(['success' => true, 'message' => $message]);
            } else {
                throw new \Exception('Không tìm thấy yêu cầu pending hoặc không thể hủy');
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
