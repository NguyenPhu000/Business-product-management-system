<?php

namespace Modules\Category\Services;

use Modules\Category\Models\BrandModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * BrandService - Business logic cho quản lý thương hiệu
 * 
 * Chức năng:
 * - CRUD thương hiệu
 * - Upload và quản lý logo
 * - Tìm kiếm và filter
 */
class BrandService
{
    private BrandModel $brandModel;

    public function __construct()
    {
        $this->brandModel = new BrandModel();
    }

    /**
     * Lấy tất cả thương hiệu với số lượng sản phẩm
     * 
     * @return array
     */
    public function getAllBrands(): array
    {
        return $this->brandModel->getAllWithProductCount();
    }

    /**
     * Tìm kiếm thương hiệu
     * 
     * @param string $keyword
     * @return array
     */
    public function searchBrands(string $keyword): array
    {
        return $this->brandModel->search($keyword);
    }

    /**
     * Lấy thương hiệu theo ID với số lượng sản phẩm
     * 
     * @param int $id
     * @return array|null
     */
    public function getBrandWithProductCount(int $id): ?array
    {
        return $this->brandModel->findWithProductCount($id);
    }

    /**
     * Lấy thương hiệu theo ID
     * 
     * @param int $id
     * @return array|null
     */
    public function getBrand(int $id): ?array
    {
        return $this->brandModel->find($id);
    }

    /**
     * Tạo thương hiệu mới
     * 
     * @param array $data
     * @return int Brand ID
     * @throws Exception
     */
    public function createBrand(array $data): int
    {
        // Validate
        if (empty($data['name'])) {
            throw new Exception('Tên thương hiệu không được để trống');
        }

        // Kiểm tra tên đã tồn tại
        if ($this->brandModel->nameExists($data['name'])) {
            throw new Exception('Tên thương hiệu đã tồn tại');
        }

        // Xử lý upload logo nếu có
        $logoUrl = null;
        if (!empty($_FILES['logo_image']['name'])) {
            $logoUrl = $this->handleLogoUpload();
            if (!$logoUrl && !empty($_FILES['logo_image']['tmp_name'])) {
                throw new Exception('Có lỗi xảy ra khi tải lên logo');
            }
        }

        // Chuẩn bị dữ liệu
        $brandData = [
            'name' => trim($data['name']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'logo_url' => $logoUrl,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0
        ];

        $brandId = $this->brandModel->create($brandData);

        if (!$brandId) {
            throw new Exception('Có lỗi xảy ra khi tạo thương hiệu');
        }

        // Ghi log
        LogHelper::log('create', 'brand', $brandId, $brandData);

        return $brandId;
    }

    /**
     * Cập nhật thương hiệu
     * 
     * @param int $id
     * @param array $data
     * @return bool
     * @throws Exception
     */
    public function updateBrand(int $id, array $data): bool
    {
        $brand = $this->brandModel->find($id);

        if (!$brand) {
            throw new Exception('Thương hiệu không tồn tại');
        }

        // Validate
        if (empty($data['name'])) {
            throw new Exception('Tên thương hiệu không được để trống');
        }

        // Kiểm tra tên trùng lặp
        if ($this->brandModel->nameExists($data['name'], $id)) {
            throw new Exception('Tên thương hiệu đã tồn tại');
        }

        // Xử lý upload logo mới (base64)
        $logoUrl = $brand['logo_url']; // Giữ logo cũ
        if (!empty($_FILES['logo_image']['name'])) {
            $newLogoBase64 = $this->handleLogoUpload();
            if ($newLogoBase64) {
                // Logo mới được lưu dưới dạng base64, không cần xóa file cũ
                $logoUrl = $newLogoBase64;
            }
        }

        // Chuẩn bị dữ liệu
        $brandData = [
            'name' => trim($data['name']),
            'description' => !empty($data['description']) ? trim($data['description']) : null,
            'logo_url' => $logoUrl,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0
        ];

        $success = $this->brandModel->update($id, $brandData);

        if (!$success) {
            throw new Exception('Có lỗi xảy ra khi cập nhật thương hiệu');
        }

        // Ghi log
        LogHelper::log('update', 'brand', $id, $brandData);

        return true;
    }

    /**
     * Xóa thương hiệu
     * 
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteBrand(int $id): bool
    {
        $brand = $this->brandModel->find($id);

        if (!$brand) {
            throw new Exception('Thương hiệu không tồn tại');
        }

        // Kiểm tra có thể xóa không
        $canDelete = $this->brandModel->canDelete($id);

        if (!$canDelete['can_delete']) {
            throw new Exception('Không thể xóa thương hiệu này vì đang có ' . $canDelete['product_count'] . ' sản phẩm');
        }

        // Xóa thương hiệu
        $success = $this->brandModel->delete($id);

        if (!$success) {
            throw new Exception('Có lỗi xảy ra khi xóa thương hiệu');
        }

        // Logo được lưu dưới dạng base64, không cần xóa file

        // Ghi log
        LogHelper::log('delete', 'brand', $id, $brand);

        return true;
    }

    /**
     * Toggle trạng thái active
     * 
     * @param int $id
     * @return array ['success' => bool, 'is_active' => int]
     * @throws Exception
     */
    public function toggleActive(int $id): array
    {
        $success = $this->brandModel->toggleActive($id);

        if (!$success) {
            throw new Exception('Có lỗi xảy ra khi thay đổi trạng thái');
        }

        $brand = $this->brandModel->find($id);

        // Ghi log
        LogHelper::log('toggle_active', 'brand', $id, ['is_active' => $brand['is_active']]);

        return [
            'success' => true,
            'is_active' => $brand['is_active']
        ];
    }

    /**
     * Xử lý upload logo thương hiệu và convert sang base64
     * 
     * @return string|null Base64 của logo hoặc null nếu thất bại
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
            throw new Exception('Kích thước file quá lớn (tối đa 5MB)');
        }

        // Kiểm tra định dạng file
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception('Định dạng file không hợp lệ. Chỉ chấp nhận: JPG, PNG, GIF, WEBP');
        }

        // Đọc nội dung file và convert sang base64
        $imageData = file_get_contents($file['tmp_name']);
        if ($imageData === false) {
            return null;
        }

        // Tạo base64 string với data URI scheme
        $base64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

        return $base64;
    }
}
