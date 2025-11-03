<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\RoleModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * RolesController - Quản lý vai trò
 */
class RolesController extends Controller
{
    private RoleModel $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    /**
     * Danh sách vai trò
     */
    public function index(): void
    {
        $roles = $this->roleModel->all('name', 'ASC');

        // Đếm số user cho mỗi role
        foreach ($roles as &$role) {
            $role['user_count'] = $this->roleModel->countUsers($role['id']);
        }

        $data = [
            'title' => 'Quản lý vai trò',
            'roles' => $roles
        ];

        $this->view('admin/roles/index', $data);
    }

    /**
     * Form sửa role
     */
    public function edit(string $id): void
    {
        // Chỉ Admin mới được sửa vai trò
        if (!AuthHelper::isAdmin()) {
            AuthHelper::setFlash('error', 'Chỉ Admin mới có quyền sửa vai trò');
            $this->redirect('/admin/roles');
            return;
        }

        $role = $this->roleModel->find((int) $id);

        if (!$role) {
            AuthHelper::setFlash('error', 'Không tìm thấy vai trò');
            $this->redirect('/admin/roles');
            return;
        }

        $data = [
            'title' => 'Sửa vai trò',
            'role' => $role
        ];

        $this->view('admin/roles/edit', $data);
    }

    /**
     * Cập nhật role
     */
    public function update(string $id): void
    {
        // Chỉ Admin mới được sửa vai trò
        if (!AuthHelper::isAdmin()) {
            AuthHelper::setFlash('error', 'Chỉ Admin mới có quyền sửa vai trò');
            $this->redirect('/admin/roles');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/roles');
            return;
        }

        $roleId = (int) $id;
        $role = $this->roleModel->find($roleId);

        if (!$role) {
            AuthHelper::setFlash('error', 'Không tìm thấy vai trò');
            $this->redirect('/admin/roles');
            return;
        }

        // Validate
        $errors = $this->validate([
            'name' => 'required|min:3|max:50',
            'description' => 'required'
        ]);

        if (!empty($errors)) {
            AuthHelper::setFlash('error', implode('<br>', $errors));
            AuthHelper::setFlash('old', $this->all());
            $this->redirect("/admin/roles/edit/{$id}");
            return;
        }

        // Kiểm tra tên role đã tồn tại (trừ role hiện tại)
        if ($this->roleModel->nameExists($this->input('name'), $roleId)) {
            AuthHelper::setFlash('error', 'Tên vai trò đã tồn tại');
            AuthHelper::setFlash('old', $this->all());
            $this->redirect("/admin/roles/edit/{$id}");
            return;
        }

        // Cập nhật role
        $data = [
            'name' => $this->input('name'),
            'description' => $this->input('description')
        ];

        $this->roleModel->update($roleId, $data);

        // Ghi log
        LogHelper::logUpdate('role', $roleId, $data);

        AuthHelper::setFlash('success', 'Cập nhật vai trò thành công');
        $this->redirect('/admin/roles');
    }
}
