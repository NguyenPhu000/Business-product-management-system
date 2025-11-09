<?php

namespace Modules\Auth\Controllers;

use Core\Controller;
use Models\UserModel;
use Models\PasswordResetRequestModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * AuthController - Xử lý đăng nhập/đăng xuất (Refactored)
 * 
 * Note: AuthService hiện tại rỗng, logic chủ yếu nằm trong UserModel và AuthHelper
 */
class AuthController extends Controller
{
    private UserModel $userModel;
    private PasswordResetRequestModel $resetRequestModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->resetRequestModel = new PasswordResetRequestModel();
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

            // Validate
            if (empty($email) || empty($password)) {
                throw new \Exception('Vui lòng nhập đầy đủ thông tin');
            }

            // Xác thực
            $user = $this->userModel->authenticate($email, $password);

            if (!$user) {
                throw new \Exception('Email hoặc mật khẩu không đúng');
            }

            // Kiểm tra trạng thái tài khoản
            if ($user['status'] != STATUS_ACTIVE) {
                throw new \Exception('Tài khoản của bạn đã bị vô hiệu hóa');
            }

            // Đăng nhập thành công
            AuthHelper::login($user);

            // Ghi log
            LogHelper::logLogin($user['id']);

            // Set cookie remember me nếu cần
            if ($remember) {
                // TODO: Implement remember me token
            }

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

        if ($userId) {
            LogHelper::logLogout($userId);
        }

        AuthHelper::logout();

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
            $user = $this->userModel->findByEmail($emailParam);
            if ($user) {
                $userId = $user['id'];
                $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($userId);
                if ($approvedRequest) {
                    $_SESSION['reset_email'] = $emailParam;
                    $_SESSION['reset_user_id'] = $userId;
                    $_SESSION['reset_request_id'] = $approvedRequest['id'];

                    unset($_SESSION['waiting_approval_email']);
                    unset($_SESSION['waiting_approval_user_id']);

                    $this->redirect('/reset-password-form');
                    return;
                }
            }
        }

        // Kiểm tra xem có session waiting không
        if (isset($_SESSION['waiting_approval_user_id'])) {
            $userId = (int)$_SESSION['waiting_approval_user_id'];
            $email = $_SESSION['waiting_approval_email'] ?? '';

            $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($userId);
            if ($approvedRequest) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = $userId;
                $_SESSION['reset_request_id'] = $approvedRequest['id'];

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
                    $user = $this->userModel->findByEmail($email);

                    if ($user) {
                        $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($user['id']);
                        if ($approvedRequest) {
                            $_SESSION['reset_email'] = $email;
                            $_SESSION['reset_user_id'] = $user['id'];
                            $_SESSION['reset_request_id'] = $approvedRequest['id'];

                            unset($_SESSION['waiting_approval_email']);
                            unset($_SESSION['waiting_approval_user_id']);

                            $this->redirect('/reset-password-form');
                            return;
                        }
                    }
                }

                $this->redirect('/forgot-password');
                return;
            }

            // POST request - xử lý form submit
            $email = $this->input('email', '');
            $newPassword = $this->input('new_password', '');
            $confirmPassword = $this->input('confirm_password', '');

            if (empty($email)) {
                throw new \Exception('Vui lòng nhập email');
            }

            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                throw new \Exception('Email không tồn tại trong hệ thống');
            }

            // Kiểm tra đã được approve chưa
            $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($user['id']);
            if ($approvedRequest && empty($newPassword)) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['reset_request_id'] = $approvedRequest['id'];

                unset($_SESSION['waiting_approval_email']);
                unset($_SESSION['waiting_approval_user_id']);

                $this->redirect('/reset-password-form');
                return;
            }

            // Kiểm tra role
            $userWithRole = $this->userModel->findWithRole($user['id']);

            // Admin tự đổi
            if ($userWithRole && isset($userWithRole['name']) && strtolower($userWithRole['name']) === 'admin') {
                if (empty($newPassword)) {
                    $_SESSION['reset_email'] = $email;
                    $_SESSION['reset_user_id'] = $user['id'];
                    $_SESSION['is_admin'] = true;
                    $this->redirect('/reset-password-form');
                    return;
                }

                // Validate password
                if (strlen($newPassword) < 6) {
                    throw new \Exception('Mật khẩu phải có ít nhất 6 ký tự');
                }

                if ($newPassword !== $confirmPassword) {
                    throw new \Exception('Mật khẩu xác nhận không khớp');
                }

                // Update password
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $this->userModel->updateUser($user['id'], [
                    'password_hash' => $hashedPassword
                ]);

                LogHelper::log('reset_password', 'user', $user['id'], [
                    'email' => $email,
                    'type' => 'admin_self_reset',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['is_admin']);

                AuthHelper::setFlash('success', 'Đổi mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
                $this->redirect('/admin/login');
                return;
            }

            // User thường đã approve
            if (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    throw new \Exception('Mật khẩu phải có ít nhất 6 ký tự');
                }

                if ($newPassword !== $confirmPassword) {
                    throw new \Exception('Mật khẩu xác nhận không khớp');
                }

                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $this->userModel->updateUser($user['id'], [
                    'password_hash' => $hashedPassword
                ]);

                if (isset($_SESSION['reset_request_id'])) {
                    $this->resetRequestModel->markPasswordChanged($_SESSION['reset_request_id']);
                }

                LogHelper::log('reset_password', 'user', $user['id'], [
                    'email' => $email,
                    'type' => 'user_reset_after_approval',
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_user_id']);
                unset($_SESSION['reset_request_id']);

                AuthHelper::setFlash('success', 'Đổi mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
                $this->redirect('/admin/login');
                return;
            }

            // User thường tạo request
            $this->resetRequestModel->deleteExpiredApprovedRequests($user['id']);

            if ($this->resetRequestModel->hasPendingRequest($user['id'])) {
                AuthHelper::setFlash('warning', 'Bạn đã có yêu cầu đang chờ xét duyệt!');
                $this->redirect('/forgot-password');
                return;
            }

            $this->resetRequestModel->deleteRejectedRequests($user['id']);

            $requestId = $this->resetRequestModel->createRequest($user['id'], $email);

            if ($requestId) {
                LogHelper::log('request_reset_password', 'user', $user['id'], [
                    'email' => $email,
                    'request_id' => $requestId,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);

                AuthHelper::setFlash('success', 'Yêu cầu đã được gửi thành công! Đang chờ admin phê duyệt...');
            } else {
                throw new \Exception('Có lỗi xảy ra. Vui lòng thử lại!');
            }

            $_SESSION['waiting_approval_email'] = $email;
            $_SESSION['waiting_approval_user_id'] = $user['id'];

            $this->redirect('/forgot-password');

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
            $approvedRequest = $this->resetRequestModel->getApprovedRequestById($requestId);
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

        $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($userId);
        if ($approvedRequest) {
            echo json_encode(['status' => 'approved', 'request_id' => $approvedRequest['id']]);
            exit;
        }

        $rejectedRequest = $this->resetRequestModel->getRejectedRequestByUserId($userId);
        if ($rejectedRequest) {
            echo json_encode(['status' => 'rejected', 'request_id' => $rejectedRequest['id']]);
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

            $user = $this->userModel->findByEmail($email);

            if (!$user) {
                throw new \Exception('Email không tồn tại trong hệ thống');
            }

            $userId = $user['id'] ?? $user['id_user'] ?? null;

            if (!$userId) {
                throw new \Exception('Không tìm thấy thông tin user');
            }

            // Kiểm tra pending
            if ($this->resetRequestModel->hasPendingRequest($userId)) {
                $this->view('auth/check-request-status', [
                    'status' => 'pending',
                    'email' => $email,
                    'userId' => $userId
                ], null);
                return;
            }

            // Kiểm tra approved
            $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($userId);
            if ($approvedRequest) {
                $this->view('auth/check-request-status', [
                    'status' => 'approved',
                    'email' => $email,
                    'userId' => $userId
                ], null);
                return;
            }

            // Kiểm tra rejected
            $rejectedRequest = $this->resetRequestModel->getRejectedRequestByUserId($userId);
            if ($rejectedRequest) {
                $this->view('auth/check-request-status', [
                    'status' => 'rejected',
                    'email' => $email,
                    'userId' => $userId
                ], null);
                return;
            }

            // Không có yêu cầu nào
            $this->view('auth/check-request-status', [
                'status' => 'none',
                'email' => $email,
                'userId' => null
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

            if ($silent) {
                $deleted = $this->resetRequestModel->deletePendingRequestByUser((int)$userId);
                if ($deleted) {
                    $this->json(['success' => true, 'message' => 'Yêu cầu đã được hủy (silent)']);
                } else {
                    throw new \Exception('Không tìm thấy yêu cầu pending hoặc không thể xóa');
                }
                return;
            }

            $result = $this->resetRequestModel->cancelPendingRequest($userId);

            if ($result) {
                $this->json(['success' => true, 'message' => 'Yêu cầu đã được hủy thành công']);
            } else {
                throw new \Exception('Không tìm thấy yêu cầu pending hoặc không thể hủy');
            }

        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
