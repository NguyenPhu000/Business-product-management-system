<?php

namespace Modules\User\Controllers;

use Core\Controller;
use Modules\User\Services\UserService;
use Helpers\AuthHelper;

/**
 * UsersController - Xử lý routing cho quản lý người dùng
 * 
 * Chức năng: Nhận request, gọi UserService, trả về view/response
 * Note: Tất cả business logic đã được di chuyển sang UserService
 */
class UsersController extends Controller
{
    private UserService $userService;

    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Danh sách người dùng
     */
    public function index(): void
    {
        $page = (int) $this->input('page', 1);
        $perPage = 10;

        // Gọi service lấy danh sách users
        $result = $this->userService->getAllUsers($page, $perPage);

        $data = [
            'title' => 'Quản lý người dùng',
            'users' => $result['users'],
            'pagination' => $result['pagination']
        ];

        $this->view('admin/users/index', $data);
    }

    /**
     * Form tạo user mới
     */
    public function create(): void
    {
        $roles = $this->userService->getAllRoles();

        $data = [
            'title' => 'Thêm người dùng mới',
            'roles' => $roles,
            'user' => null
        ];

        $this->view('admin/users/form', $data);
    }

    /**
     * Lưu user mới
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        try {
            // Thu thập dữ liệu từ form
            $data = [
                'username' => $this->input('username'),
                'email' => $this->input('email'),
                'password' => $this->input('password'),
                'full_name' => $this->input('full_name'),
                'phone' => $this->input('phone'),
                'role_id' => $this->input('role_id'),
                'status' => $this->input('status', STATUS_ACTIVE)
            ];

            // Gọi service tạo user
            $this->userService->createUser($data);

            AuthHelper::setFlash('success', 'Thêm người dùng thành công');
            $this->redirect('/admin/users');
        } catch (\Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            AuthHelper::setFlash('old', $this->all());
            $this->redirect('/admin/users/create');
        }
    }

    /**
     * Form sửa user
     */
    public function edit(string $id): void
    {
        try {
            $userId = (int) $id;
            $currentUserId = AuthHelper::id();

            // Kiểm tra quyền quản lý user
            if (!$this->userService->canManageUser($userId, $currentUserId)) {
                AuthHelper::setFlash('error', 'Bạn không có quyền sửa người dùng này. Chỉ quyền cao hơn mới được quản lý quyền thấp hơn hoặc bằng mình.');
                $this->redirect('/admin/users');
                return;
            }

            $user = $this->userService->getUserById($userId);

            if (!$user) {
                AuthHelper::setFlash('error', 'Không tìm thấy người dùng');
                $this->redirect('/admin/users');
                return;
            }

            $roles = $this->userService->getAllRoles();

            $data = [
                'title' => 'Sửa người dùng',
                'user' => $user,
                'roles' => $roles
            ];

            $this->view('admin/users/form', $data);
        } catch (\Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/users');
        }
    }

    /**
     * Cập nhật user
     */
    public function update(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/users');
            return;
        }

        try {
            $userId = (int) $id;

            // Thu thập dữ liệu từ form
            $data = [
                'username' => $this->input('username'),
                'email' => $this->input('email'),
                'full_name' => $this->input('full_name'),
                'phone' => $this->input('phone'),
                'role_id' => $this->input('role_id'),
                'new_password' => $this->input('new_password')
            ];

            // Gọi service cập nhật user
            $this->userService->updateUser($userId, $data);

            AuthHelper::setFlash('success', 'Cập nhật người dùng thành công');
            $this->redirect('/admin/users');
        } catch (\Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            AuthHelper::setFlash('old', $this->all());
            $this->redirect("/admin/users/edit/{$id}");
        }
    }

    /**
     * Xóa user
     */
    public function delete(string $id): void
    {
        try {
            $userId = (int) $id;

            // Gọi service xóa user
            $this->userService->deleteUser($userId);

            $this->success(null, 'Xóa người dùng thành công');
        } catch (\Exception $e) {
            $code = ($e->getMessage() === 'Không tìm thấy người dùng') ? 404 : (($e->getMessage() === 'Không thể xóa tài khoản của chính mình đang đăng nhập') ? 400 : 403);

            $this->error($e->getMessage(), $code);
        }
    }
}
