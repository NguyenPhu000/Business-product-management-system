<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\UserModel;
use Models\RoleModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * UsersController - Quản lý người dùng
 */
class UsersController extends Controller
{
    private UserModel $userModel;
    private RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    /**
     * Danh sách người dùng
     */
    public function index(): void
    {
        $page = (int) $this->input('page', 1);
        $perPage = 10;

        $result = $this->userModel->paginate($page, $perPage);
        $users = [];

        foreach ($result['data'] as $user) {
            $users[] = $this->userModel->findWithRole($user['id']);
        }

        $data = [
            'title' => 'Quản lý người dùng',
            'users' => $users,
            'pagination' => [
                'page' => $result['page'],
                'totalPages' => $result['totalPages'],
                'total' => $result['total']
            ]
        ];

        $this->view('admin/users/index', $data);
    }

    /**
     * Form tạo user mới
     */
    public function create(): void
    {
        $roles = $this->roleModel->all();

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

        // Validate
        $errors = $this->validate([
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'full_name' => 'required',
            'role_id' => 'required|numeric'
        ]);

        if (!empty($errors)) {
            AuthHelper::setFlash('error', implode('<br>', $errors));
            AuthHelper::setFlash('old', $this->all());
            $this->redirect('/admin/users/create');
            return;
        }

        // Kiểm tra email đã tồn tại
        if ($this->userModel->emailExists($this->input('email'))) {
            AuthHelper::setFlash('error', 'Email đã tồn tại trong hệ thống');
            AuthHelper::setFlash('old', $this->all());
            $this->redirect('/admin/users/create');
            return;
        }

        // Kiểm tra username đã tồn tại
        if ($this->userModel->usernameExists($this->input('username'))) {
            AuthHelper::setFlash('error', 'Username đã tồn tại trong hệ thống');
            AuthHelper::setFlash('old', $this->all());
            $this->redirect('/admin/users/create');
            return;
        }

        // Tạo user
        $data = [
            'username' => $this->input('username'),
            'email' => $this->input('email'),
            'password' => $this->input('password'),
            'full_name' => $this->input('full_name'),
            'phone' => $this->input('phone'),
            'role_id' => (int) $this->input('role_id'),
            'status' => (int) $this->input('status', STATUS_ACTIVE)
        ];

        $userId = $this->userModel->createUser($data);

        // Ghi log
        LogHelper::logCreate('user', $userId, $data);

        AuthHelper::setFlash('success', 'Thêm người dùng thành công');
        $this->redirect('/admin/users');
    }

    /**
     * Form sửa user
     */
    public function edit(string $id): void
    {
        $user = $this->userModel->findWithRole((int) $id);

        if (!$user) {
            AuthHelper::setFlash('error', 'Không tìm thấy người dùng');
            $this->redirect('/admin/users');
            return;
        }

        // Kiểm tra quyền hạn: 
        // - Chỉ quyền CAO HƠN mới được sửa quyền THẤP HƠN
        // - Quyền BẰNG NHAU không được sửa lẫn nhau (trừ sửa chính mình)
        if (!AuthHelper::canManageRole($user['role_id']) && $user['id'] != AuthHelper::id()) {
            AuthHelper::setFlash('error', 'Bạn không có quyền sửa người dùng này. Chỉ quyền cao hơn mới được quản lý quyền thấp hơn hoặc bằng mình.');
            $this->redirect('/admin/users');
            return;
        }

        $roles = $this->roleModel->all();

        $data = [
            'title' => 'Sửa người dùng',
            'user' => $user,
            'roles' => $roles
        ];

        $this->view('admin/users/form', $data);
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

        $userId = (int) $id;
        $user = $this->userModel->find($userId);

        if (!$user) {
            AuthHelper::setFlash('error', 'Không tìm thấy người dùng');
            $this->redirect('/admin/users');
            return;
        }

        // Kiểm tra quyền hạn: 
        // - Chỉ quyền CAO HƠN mới được sửa quyền THẤP HƠN
        // - Quyền BẰNG NHAU không được sửa lẫn nhau (trừ sửa chính mình)
        if (!AuthHelper::canManageRole($user['role_id']) && $user['id'] != AuthHelper::id()) {
            AuthHelper::setFlash('error', 'Bạn không có quyền sửa người dùng này. Chỉ quyền cao hơn mới được quản lý quyền thấp hơn hoặc bằng mình.');
            $this->redirect('/admin/users');
            return;
        }

        // Validate
        $errors = $this->validate([
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'full_name' => 'required',
            'role_id' => 'required|numeric'
        ]);

        if (!empty($errors)) {
            AuthHelper::setFlash('error', implode('<br>', $errors));
            AuthHelper::setFlash('old', $this->all());
            $this->redirect("/admin/users/edit/{$id}");
            return;
        }

        // Kiểm tra email đã tồn tại (trừ user hiện tại)
        if ($this->userModel->emailExists($this->input('email'), $userId)) {
            AuthHelper::setFlash('error', 'Email đã tồn tại trong hệ thống');
            AuthHelper::setFlash('old', $this->all());
            $this->redirect("/admin/users/edit/{$id}");
            return;
        }

        // Kiểm tra username đã tồn tại (trừ user hiện tại)
        if ($this->userModel->usernameExists($this->input('username'), $userId)) {
            AuthHelper::setFlash('error', 'Username đã tồn tại trong hệ thống');
            AuthHelper::setFlash('old', $this->all());
            $this->redirect("/admin/users/edit/{$id}");
            return;
        }

        // Cập nhật user
        $data = [
            'username' => $this->input('username'),
            'email' => $this->input('email'),
            'full_name' => $this->input('full_name'),
            'phone' => $this->input('phone'),
            'role_id' => (int) $this->input('role_id'),
            'status' => (int) $this->input('status', STATUS_ACTIVE)
        ];

        // Nếu có đổi mật khẩu
        $newPassword = $this->input('new_password');
        if (!empty($newPassword)) {
            if (strlen($newPassword) < 8) {
                AuthHelper::setFlash('error', 'Mật khẩu mới phải có ít nhất 8 ký tự');
                AuthHelper::setFlash('old', $this->all());
                $this->redirect("/admin/users/edit/{$id}");
                return;
            }
            $data['password'] = $newPassword;
        }

        $this->userModel->updateUser($userId, $data);

        // Ghi log
        LogHelper::logUpdate('user', $userId, $data);

        AuthHelper::setFlash('success', 'Cập nhật người dùng thành công');
        $this->redirect('/admin/users');
    }

    /**
     * Xóa user
     */
    public function delete(string $id): void
    {
        $userId = (int) $id;
        $user = $this->userModel->find($userId);

        if (!$user) {
            $this->error('Không tìm thấy người dùng', 404);
            return;
        }

        // Không cho xóa chính mình đang đăng nhập
        if ($userId == AuthHelper::id()) {
            $this->error('Không thể xóa tài khoản của chính mình đang đăng nhập', 400);
            return;
        }

        // Kiểm tra quyền hạn: 
        // - Chỉ quyền CAO HƠN mới được xóa quyền THẤP HƠN
        // - Quyền BẰNG NHAU không được xóa lẫn nhau
        // Quy tắc: Admin (1) > Chủ tiệm (5) > Sales Staff (2) = Warehouse Manager (3)
        if (!AuthHelper::canManageRole($user['role_id'])) {
            $this->error('Bạn không có quyền xóa người dùng này. Chỉ quyền cao hơn mới được xóa quyền thấp hơn hoặc bằng mình.', 403);
            return;
        }

        // Xóa user
        $this->userModel->delete($userId);

        // Ghi log
        LogHelper::logDelete('user', $userId, $user);

        $this->success(null, 'Xóa người dùng thành công');
    }
}
