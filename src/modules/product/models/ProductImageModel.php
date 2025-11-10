<?php

namespace Modules\Product\Models;

use Core\BaseModel;

/**
 * ProductImageModel - Quản lý hình ảnh sản phẩm
 */
class ProductImageModel extends BaseModel
{
    protected string $table = 'product_images';
    protected string $primaryKey = 'id';

    /**
     * Lấy tất cả hình ảnh của sản phẩm
     * Trả về base64 data nếu có, nếu không có thì dùng URL
     */
    public function getByProduct(int $productId): array
    {
        $images = $this->query(
            "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC",
            [$productId]
        );
        
        // Convert base64 to data URL nếu có
        foreach ($images as &$image) {
            if (!empty($image['image_data'])) {
                $mimeType = $image['mime_type'] ?? 'image/jpeg';
                $image['display_url'] = "data:{$mimeType};base64,{$image['image_data']}";
            } else {
                $image['display_url'] = $image['url'];
            }
        }
        
        return $images;
    }

    /**
     * Lấy ảnh chính của sản phẩm
     */
    public function getPrimaryImage(int $productId): ?array
    {
        $image = $this->queryOne(
            "SELECT * FROM {$this->table} WHERE product_id = ? AND is_primary = 1 LIMIT 1",
            [$productId]
        );
        
        if ($image && !empty($image['image_data'])) {
            $mimeType = $image['mime_type'] ?? 'image/jpeg';
            $image['display_url'] = "data:{$mimeType};base64,{$image['image_data']}";
        } elseif ($image) {
            $image['display_url'] = $image['url'];
        }
        
        return $image;
    }

    /**
     * Bỏ primary của tất cả ảnh của sản phẩm
     */
    public function removePrimary(int $productId): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET is_primary = 0 WHERE product_id = ?",
            [$productId]
        );
    }

    /**
     * Xóa tất cả ảnh của sản phẩm
     */
    public function deleteByProduct(int $productId): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE product_id = ?",
            [$productId]
        );
    }

    /**
     * Đếm số lượng ảnh của sản phẩm
     */
    public function countByProduct(int $productId): int
    {
        $result = $this->queryOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE product_id = ?",
            [$productId]
        );

        return (int) ($result['total'] ?? 0);
    }
}