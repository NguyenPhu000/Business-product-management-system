<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\BrandModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * BrandController - Quản lý thương hiệu sản phẩm
 */
class BrandController extends Controller
{
    private BrandModel $brandModel;

    public function __construct()
    {
        $this->brandModel = new BrandModel();
    }

    /**
     * Hiển thị danh sách thương hiệu
     */
    public function index(): void
    {
        $keyword = $this->input('keyword', '');
        
        if ($keyword) {
            $brands = $this->brandModel->search($keyword);
        } else {
            $brands = $this->brandModel->getAllWithProductCount();
        }
        
        $this->view('admin/brands/index', [
            'brands' => $brands,
            'keyword' => $keyword,
            'pageTitle' => 'Quản lý thương hiệu'
        ]);
    }

    /**
     * Hiển thị form tạo thương hiệu mới
     */
    public function create(): void
    {
        $this->view('admin/brands/create', [
            'pageTitle' => 'Thêm thương hiệu mới'
        ]);
    }

    /**
     * Xử lý tạo thương hiệu mới
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/brands');
            return;
        }

        $name = trim($this->input('name', ''));
        $description = trim($this->input('description', ''));
        $logoUrl = trim($this->input('logo_url', ''));
        $isActive = $this->input('is_active', 0);

        // Validate
        if (empty($name)) {
            AuthHelper::setFlash('error', 'Tên thương hiệu không được để trống');
            $this->redirect('/admin/brands/create');
            return;
        }

        // Kiểm tra tên đã tồn tại
        if ($this->brandModel->nameExists($name)) {
            AuthHelper::setFlash('error', 'Tên thương hiệu đã tồn tại');
            $this->redirect('/admin/brands/create');
            return;
        }

        // Tạo thương hiệu
        $data = [
            'name' => $name,
            'description' => $description ?: null,
            'logo_url' => $logoUrl ?: null,
            'is_active' => $isActive ? 1 : 0
        ];

        $brandId = $this->brandModel->create($data);

        if ($brandId) {
            LogHelper::log('create', 'brand', $brandId, $data);
            AuthHelper::setFlash('success', 'Thêm thương hiệu thành công!');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/brands');
    }

    /**
     * Hiển thị form sửa thương hiệu
     */
    public function edit(int $id): void
    {
        $brand = $this->brandModel->findWithProductCount($id);

        if (!$brand) {
            AuthHelper::setFlash('error', 'Thương hiệu không tồn tại');
            $this->redirect('/admin/brands');
            return;
        }

        $this->view('admin/brands/edit', [
            'brand' => $brand,
            'pageTitle' => 'Sửa thương hiệu: ' . $brand['name']
        ]);
    }

    /**
     * Xử lý cập nhật thương hiệu
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/brands');
            return;
        }

        $brand = $this->brandModel->find($id);

        if (!$brand) {
            AuthHelper::setFlash('error', 'Thương hiệu không tồn tại');
            $this->redirect('/admin/brands');
            return;
        }

        $name = trim($this->input('name', ''));
        $description = trim($this->input('description', ''));
        $logoUrl = trim($this->input('logo_url', ''));
        $isActive = $this->input('is_active', 0);

        // Validate
        if (empty($name)) {
            AuthHelper::setFlash('error', 'Tên thương hiệu không được để trống');
            $this->redirect('/admin/brands/edit/' . $id);
            return;
        }

        // Kiểm tra tên trùng lặp
        if ($this->brandModel->nameExists($name, $id)) {
            AuthHelper::setFlash('error', 'Tên thương hiệu đã tồn tại');
            $this->redirect('/admin/brands/edit/' . $id);
            return;
        }

        // Cập nhật thương hiệu
        $data = [
            'name' => $name,
            'description' => $description ?: null,
            'logo_url' => $logoUrl ?: null,
            'is_active' => $isActive ? 1 : 0
        ];

        $success = $this->brandModel->update($id, $data);

        if ($success) {
            LogHelper::log('update', 'brand', $id, $data);
            AuthHelper::setFlash('success', 'Cập nhật thương hiệu thành công!');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/brands');
    }

    /**
     * Xóa thương hiệu
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/brands');
            return;
        }

        $brand = $this->brandModel->find($id);

        if (!$brand) {
            AuthHelper::setFlash('error', 'Thương hiệu không tồn tại');
            $this->redirect('/admin/brands');
            return;
        }

        // Kiểm tra có thể xóa không
        $canDelete = $this->brandModel->canDelete($id);

        if (!$canDelete['can_delete']) {
            AuthHelper::setFlash('error', 'Không thể xóa thương hiệu này vì đang có ' . $canDelete['product_count'] . ' sản phẩm');
            $this->redirect('/admin/brands');
            return;
        }

        // Xóa thương hiệu
        $success = $this->brandModel->delete($id);

        if ($success) {
            LogHelper::log('delete', 'brand', $id, $brand);
            AuthHelper::setFlash('success', 'Xóa thương hiệu thành công!');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/brands');
    }

    /**
     * Toggle trạng thái active
     */
    public function toggleActive(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $success = $this->brandModel->toggleActive($id);

        if ($success) {
            $brand = $this->brandModel->find($id);
            LogHelper::log('toggle_active', 'brand', $id, ['is_active' => $brand['is_active']]);
            $this->json(['success' => true, 'is_active' => $brand['is_active']]);
        } else {
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }
}
