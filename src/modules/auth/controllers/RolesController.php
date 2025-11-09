<?php

namespace Modules\Auth\Controllers;

use Core\Controller;
use Modules\Auth\Services\RoleService;
use Helpers\AuthHelper;

/**
 * RolesController - Xử lý routing cho quản lý vai trò
 * 
 * Chức năng: Nhận request, gọi RoleService, trả về view/response
 * Note: Tất cả business logic đã được di chuyển sang RoleService
 */
class RolesController extends Controller
{
    private RoleService $roleService;

    public function __construct()
    {
        $this->roleService = new RoleService();
    }

    /**
     * Danh sách vai trò
     */
    public function index(): void
    {
        $roles = $this->roleService->getAllRolesWithUserCount();

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
        try {
            // Kiểm tra quyền admin
            if (!$this->roleService->isAdmin()) {
                AuthHelper::setFlash('error', 'Chỉ Admin mới có quyền sửa vai trò');
                $this->redirect('/admin/roles');
                return;
            }

            $role = $this->roleService->getRoleById((int) $id);

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
        } catch (\Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/roles');
        }
    }

    /**
     * Cập nhật role
     */
    public function update(string $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/roles');
            return;
        }

        try {
            $roleId = (int) $id;

            // Thu thập dữ liệu từ form
            $data = [
                'name' => $this->input('name'),
                'description' => $this->input('description')
            ];

            // Gọi service cập nhật role
            $this->roleService->updateRole($roleId, $data);

            AuthHelper::setFlash('success', 'Cập nhật vai trò thành công');
            $this->redirect('/admin/roles');
        } catch (\Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            AuthHelper::setFlash('old', $this->all());
            $this->redirect("/admin/roles/edit/{$id}");
        }
    }
}
