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

        // Xử lý upload logo
        $logoUrl = null;
        if (!empty($_FILES['logo_image']['name'])) {
            $logoUrl = $this->handleLogoUpload();
            if (!$logoUrl) {
                AuthHelper::setFlash('error', 'Có lỗi xảy ra khi tải lên logo');
                $this->redirect('/admin/brands/create');
                return;
            }
        }

        // Tạo thương hiệu
        $data = [
            'name' => $name,
            'description' => $description ?: null,
            'logo_url' => $logoUrl,
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

        // Xử lý upload logo mới
        $logoUrl = $brand['logo_url']; // Giữ logo cũ
        if (!empty($_FILES['logo_image']['name'])) {
            $newLogoUrl = $this->handleLogoUpload();
            if ($newLogoUrl) {
                // Xóa logo cũ nếu có
                if ($logoUrl && file_exists(getcwd() . $logoUrl)) {
                    @unlink(getcwd() . $logoUrl);
                }
                $logoUrl = $newLogoUrl;
            }
        }

        // Cập nhật thương hiệu
        $data = [
            'name' => $name,
            'description' => $description ?: null,
            'logo_url' => $logoUrl,
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

    /**
     * Xử lý upload logo thương hiệu
     */
    private function handleLogoUpload(): ?string
    {
        if (empty($_FILES['logo_image']['name'])) {
            return null;
        }

        $file = $_FILES['logo_image'];
        
        // Kiểm tra lỗi upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Kiểm tra kích thước file (5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return null;
        }

        // Kiểm tra định dạng file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            return null;
        }

        // Tạo tên file unique
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'brand_' . time() . '_' . uniqid() . '.' . $extension;
        
        // Đường dẫn lưu file
        $uploadDir = getcwd() . '/assets/images/brands/';
        
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadPath = $uploadDir . $filename;

        // Di chuyển file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Trả về đường dẫn tương đối
            return '/assets/images/brands/' . $filename;
        }

        return null;
    }
}
