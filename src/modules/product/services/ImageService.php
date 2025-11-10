<?php

namespace Modules\Product\Services;

use Modules\Product\Models\ProductImageModel;
use Exception;

/**
 * ImageService - Xử lý upload và quản lý hình ảnh sản phẩm
 */
class ImageService
{
    private ProductImageModel $imageModel;

    public function __construct()
    {
        $this->imageModel = new ProductImageModel();
    }

    /**
     * Lấy tất cả hình ảnh của sản phẩm
     * 
     * @param int $productId ID sản phẩm
     * @return array
     */
    public function getProductImages(int $productId): array
    {
        return $this->imageModel->getByProduct($productId);
    }

    /**
     * Upload nhiều hình ảnh cho sản phẩm (lưu base64)
     * 
     * @param int $productId ID sản phẩm
     * @param array $files Mảng $_FILES['images']
     * @return array Mảng các data URL ảnh đã upload
     */
    public function uploadImages(int $productId, array $files): array
    {
        $uploadedImages = [];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            // Kiểm tra lỗi upload
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }

            $fileTmpName = $files['tmp_name'][$i];
            $fileSize = $files['size'][$i];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($fileTmpName);

            if (!in_array($fileType, $allowedTypes)) {
                continue;
            }

            // Validate file size (max 5MB)
            if ($fileSize > 5 * 1024 * 1024) {
                continue;
            }

            // Đọc file và convert sang base64
            $imageData = file_get_contents($fileTmpName);
            $base64Data = base64_encode($imageData);

            // Ảnh đầu tiên là ảnh chính
            $isPrimary = ($i === 0 && empty($uploadedImages)) ? 1 : 0;

            // Lưu vào database (base64 + mime type)
            $imageId = $this->imageModel->create([
                'product_id' => $productId,
                'url' => null, // Không cần lưu URL
                'image_data' => $base64Data,
                'mime_type' => $fileType,
                'is_primary' => $isPrimary,
                'sort_order' => $i
            ]);

            if ($imageId) {
                $uploadedImages[] = "data:{$fileType};base64,{$base64Data}";
            }
        }

        return $uploadedImages;
    }

    /**
     * Xóa hình ảnh
     * 
     * @param int $imageId ID hình ảnh
     * @return bool
     * @throws Exception
     */
    public function deleteImage(int $imageId): bool
    {
        $image = $this->imageModel->find($imageId);
        if (!$image) {
            throw new Exception('Không tìm thấy hình ảnh');
        }

        // Xóa file nếu có URL (ảnh cũ lưu dạng file)
        if (!empty($image['url'])) {
            $this->deleteImageFile($image['url']);
        }

        // Xóa record trong DB
        return $this->imageModel->delete($imageId);
    }

    /**
     * Xóa tất cả hình ảnh của sản phẩm
     * 
     * @param int $productId ID sản phẩm
     * @return void
     */
    public function deleteAllProductImages(int $productId): void
    {
        $images = $this->imageModel->getByProduct($productId);

        foreach ($images as $image) {
            // Chỉ xóa file nếu có URL (ảnh cũ), không xóa ảnh base64
            if (!empty($image['url'])) {
                $this->deleteImageFile($image['url']);
            }
        }

        // DB cascade sẽ tự động xóa khi xóa product
    }

    /**
     * Đặt ảnh chính cho sản phẩm
     * 
     * @param int $imageId ID hình ảnh
     * @param int $productId ID sản phẩm
     * @return bool
     */
    public function setPrimaryImage(int $imageId, int $productId): bool
    {
        // Bỏ primary của tất cả ảnh khác
        $this->imageModel->removePrimary($productId);

        // Đặt ảnh này là primary
        return $this->imageModel->update($imageId, ['is_primary' => 1]);
    }

    /**
     * Xóa file hình ảnh trên server
     * 
     * @param string $imageUrl Đường dẫn URL của ảnh
     * @return bool
     */
    private function deleteImageFile(string $imageUrl): bool
    {
        $filePath = __DIR__ . '/../../../../public' . $imageUrl;

        if (file_exists($filePath)) {
            return unlink($filePath);
        }

        return false;
    }
}
