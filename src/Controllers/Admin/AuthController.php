<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\UserModel;
use Models\PasswordResetRequestModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * AuthController - Xử lý đăng nhập/đăng xuất
 */
class AuthController extends Controller
{
    private UserModel $userModel;
    private PasswordResetRequestModel $resetRequestModel;

    public function __construct()
    {
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/login');
            return;
        }

        $email = $this->input('email', '');
        $password = $this->input('password', '');
        $remember = $this->input('remember', false);

        // Validate
        if (empty($email) || empty($password)) {
            AuthHelper::setFlash('error', 'Vui lòng nhập đầy đủ thông tin');
            $this->redirect('/admin/login');
            return;
        }

        // Xác thực
        $user = $this->userModel->authenticate($email, $password);

        if (!$user) {
            AuthHelper::setFlash('error', 'Email hoặc mật khẩu không đúng');
            $this->redirect('/admin/login');
            return;
        }

        // Kiểm tra trạng thái tài khoản
        if ($user['status'] != STATUS_ACTIVE) {
            AuthHelper::setFlash('error', 'Tài khoản của bạn đã bị vô hiệu hóa');
            $this->redirect('/admin/login');
            return;
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

        // Kiểm tra xem có session waiting không, nếu có thì check xem đã approved chưa
        if (isset($_SESSION['waiting_approval_user_id'])) {
            $userId = (int)$_SESSION['waiting_approval_user_id'];
            $email = $_SESSION['waiting_approval_email'] ?? '';

            // Kiểm tra có request approved không
            $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($userId);
            if ($approvedRequest) {
                // Đã được approve! Chuyển sang form reset password
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = $userId;
                $_SESSION['reset_request_id'] = $approvedRequest['id'];

                // Xóa session waiting
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
        // Nếu là GET request, check xem có parameter email không (từ polling redirect)
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $email = $this->input('email', '');

            if (!empty($email)) {
                // Kiểm tra email có tồn tại không
                $user = $this->userModel->findByEmail($email);

                if ($user) {
                    // Kiểm tra user có yêu cầu đã được approve chưa?
                    $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($user['id']);
                    if ($approvedRequest) {
                        // Có yêu cầu đã được approve, cho phép đổi mật khẩu
                        $_SESSION['reset_email'] = $email;
                        $_SESSION['reset_user_id'] = $user['id'];
                        $_SESSION['reset_request_id'] = $approvedRequest['id'];

                        // Xóa session waiting
                        unset($_SESSION['waiting_approval_email']);
                        unset($_SESSION['waiting_approval_user_id']);

                        $this->redirect('/reset-password-form');
                        return;
                    }
                }
            }

            // Nếu không có email hoặc không có approved request, hiển thị form
            $this->redirect('/forgot-password');
            return;
        }

        // POST request - xử lý form submit
        $email = $this->input('email', '');
        $newPassword = $this->input('new_password', '');
        $confirmPassword = $this->input('confirm_password', '');

        // Validate email
        if (empty($email)) {
            AuthHelper::setFlash('error', 'Vui lòng nhập email');
            $this->redirect('/forgot-password');
            return;
        }

        // Kiểm tra email có tồn tại không
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            AuthHelper::setFlash('error', 'Email không tồn tại trong hệ thống');
            $this->redirect('/forgot-password');
            return;
        }

        // Kiểm tra user có yêu cầu đã được approve chưa?
        $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($user['id']);
        if ($approvedRequest && empty($newPassword)) {
            // Có yêu cầu đã được approve, cho phép đổi mật khẩu
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_request_id'] = $approvedRequest['id'];

            // Xóa session waiting
            unset($_SESSION['waiting_approval_email']);
            unset($_SESSION['waiting_approval_user_id']);

            $this->redirect('/reset-password-form');
            return;
        }

        // Kiểm tra role của user
        $userWithRole = $this->userModel->findWithRole($user['id']);

        // Nếu là admin - cho phép tự đổi mật khẩu
        if ($userWithRole && isset($userWithRole['name']) && strtolower($userWithRole['name']) === 'admin') {

            // Nếu chưa nhập mật khẩu mới, lưu email vào session và chuyển đến form nhập mật khẩu
            if (empty($newPassword)) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = $user['id'];
                $_SESSION['is_admin'] = true;
                $this->redirect('/reset-password-form');
                return;
            }

            // Validate mật khẩu mới
            if (strlen($newPassword) < 6) {
                AuthHelper::setFlash('error', 'Mật khẩu phải có ít nhất 6 ký tự');
                $this->redirect('/reset-password-form');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                AuthHelper::setFlash('error', 'Mật khẩu xác nhận không khớp');
                $this->redirect('/reset-password-form');
                return;
            }

            // Cập nhật mật khẩu mới
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->userModel->updateUser($user['id'], [
                'password_hash' => $hashedPassword
            ]);

            // Ghi log
            LogHelper::log('reset_password', 'user', $user['id'], [
                'email' => $email,
                'type' => 'admin_self_reset',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Xóa session
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['is_admin']);

            // Hiển thị thông báo thành công
            AuthHelper::setFlash('success', 'Đổi mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
            $this->redirect('/admin/login');
            return;
        }

        // Nếu user thường và đã có mật khẩu mới (từ form sau khi được approve)
        if (!empty($newPassword)) {
            // Validate mật khẩu mới
            if (strlen($newPassword) < 6) {
                AuthHelper::setFlash('error', 'Mật khẩu phải có ít nhất 6 ký tự');
                $this->redirect('/reset-password-form');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                AuthHelper::setFlash('error', 'Mật khẩu xác nhận không khớp');
                $this->redirect('/reset-password-form');
                return;
            }

            // Cập nhật mật khẩu mới
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->userModel->updateUser($user['id'], [
                'password_hash' => $hashedPassword
            ]);

            // Đánh dấu request là đã đổi mật khẩu
            if (isset($_SESSION['reset_request_id'])) {
                $this->resetRequestModel->markPasswordChanged($_SESSION['reset_request_id']);
            }

            // Ghi log
            LogHelper::log('reset_password', 'user', $user['id'], [
                'email' => $email,
                'type' => 'user_reset_after_approval',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            // Xóa session
            unset($_SESSION['reset_email']);
            unset($_SESSION['reset_user_id']);
            unset($_SESSION['reset_request_id']);

            // Hiển thị thông báo thành công
            AuthHelper::setFlash('success', 'Đổi mật khẩu thành công! Vui lòng đăng nhập với mật khẩu mới.');
            $this->redirect('/admin/login');
            return;
        }

        // Nếu không phải admin - tạo yêu cầu chờ admin phê duyệt
        // Xóa các request approved đã hết hạn (quá 10 phút chưa đổi MK)
        $this->resetRequestModel->deleteExpiredApprovedRequests($user['id']);

        // Kiểm tra đã có yêu cầu pending chưa
        if ($this->resetRequestModel->hasPendingRequest($user['id'])) {
            AuthHelper::setFlash('warning', 'Bạn đã có yêu cầu đang chờ xét duyệt!');
            $this->redirect('/forgot-password');
            return;
        }

        // Xóa tất cả yêu cầu rejected cũ trước khi tạo yêu cầu mới
        $this->resetRequestModel->deleteRejectedRequests($user['id']);

        // Tạo yêu cầu mới
        $requestId = $this->resetRequestModel->createRequest($user['id'], $email);

        if ($requestId) {
            // Ghi log
            LogHelper::log('request_reset_password', 'user', $user['id'], [
                'email' => $email,
                'request_id' => $requestId,
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);

            AuthHelper::setFlash('success', 'Yêu cầu đã được gửi thành công! Đang chờ admin phê duyệt...');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra. Vui lòng thử lại!');
        }

        // Lưu email vào session để hiển thị trạng thái chờ
        $_SESSION['waiting_approval_email'] = $email;
        $_SESSION['waiting_approval_user_id'] = $user['id'];

        $this->redirect('/forgot-password');
    }

    /**
     * Hiển thị form nhập mật khẩu mới (chỉ cho admin)
     */
    public function showResetPasswordForm(): void
    {
        // Kiểm tra có email trong session không
        if (!isset($_SESSION['reset_email']) || !isset($_SESSION['reset_user_id'])) {
            AuthHelper::setFlash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại!');
            $this->redirect('/forgot-password');
            return;
        }

        $email = $_SESSION['reset_email'];
        $this->view('auth/reset-password-form', ['email' => $email], null);
    }

    /**
     * API: Kiểm tra trạng thái yêu cầu (approved/rejected/pending)
     */
    public function checkApproval($userId = null): void
    {
        header('Content-Type: application/json');

        if (!$userId) {
            echo json_encode(['status' => 'pending']);
            exit;
        }

        $userId = (int)$userId;

        // Kiểm tra xem có yêu cầu approved không
        $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($userId);
        if ($approvedRequest) {
            echo json_encode(['status' => 'approved', 'request_id' => $approvedRequest['id']]);
            exit;
        }

        // Kiểm tra xem có yêu cầu rejected không
        $rejectedRequest = $this->resetRequestModel->getRejectedRequestByUserId($userId);
        if ($rejectedRequest) {
            echo json_encode(['status' => 'rejected', 'request_id' => $rejectedRequest['id']]);
            exit;
        }

        // Vẫn pending
        echo json_encode(['status' => 'pending']);
        exit;
    }

    /**
     * Kiểm tra trạng thái yêu cầu reset password
     */
    public function checkRequestStatus(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/login');
            return;
        }

        $email = $this->input('email', '');

        if (empty($email)) {
            AuthHelper::setFlash('error', 'Vui lòng nhập email');
            $this->redirect('/admin/login');
            return;
        }

        // Tìm user theo email
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            AuthHelper::setFlash('error', 'Email không tồn tại trong hệ thống');
            $this->redirect('/admin/login');
            return;
        }

        // Kiểm tra xem có yêu cầu pending không
        if ($this->resetRequestModel->hasPendingRequest($user['id_user'])) {
            $this->view('auth/check-request-status', ['status' => 'pending', 'email' => $email], null);
            return;
        }

        // Kiểm tra xem có yêu cầu approved không
        $approvedRequest = $this->resetRequestModel->getApprovedRequestByUserId($user['id_user']);
        if ($approvedRequest) {
            $this->view('auth/check-request-status', ['status' => 'approved', 'email' => $email], null);
            return;
        }

        // Kiểm tra xem có yêu cầu rejected không
        $rejectedRequest = $this->resetRequestModel->getRejectedRequestByUserId($user['id_user']);
        if ($rejectedRequest) {
            $this->view('auth/check-request-status', ['status' => 'rejected', 'email' => $email], null);
            return;
        }

        // Không có yêu cầu nào
        $this->view('auth/check-request-status', ['status' => 'none', 'email' => $email], null);
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
}
