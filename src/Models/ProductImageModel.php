<?php

namespace Models;

/**
 * ProductImageModel - Quản lý hình ảnh sản phẩm
 */
class ProductImageModel extends BaseModel
{
    protected string $table = 'product_images';
    protected string $primaryKey = 'id';

    /**
     * Lấy tất cả hình ảnh của sản phẩm
     */
    public function getByProduct(int $productId): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY is_primary DESC, sort_order ASC",
            [$productId]
        );
    }

    /**
     * Lấy ảnh chính của sản phẩm
     */
    public function getPrimaryImage(int $productId): ?array
    {
        return $this->queryOne(
            "SELECT * FROM {$this->table} WHERE product_id = ? AND is_primary = 1 LIMIT 1",
            [$productId]
        );
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
