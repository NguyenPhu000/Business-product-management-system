<?php
/**
 * VariantModel.php - Tương tác với bảng product_variants
 */
namespace Modules\Product\Models;

use Core\BaseModel;

class VariantModel extends BaseModel
{
    protected string $table = 'product_variants';
    protected string $primaryKey = 'id';

    /**
     * Lấy tất cả variants của một sản phẩm
     */
    public function getByProductId(int $productId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE product_id = ? ORDER BY id ASC";
        return $this->query($sql, [$productId]);
    }

    /**
     * Lấy variant kèm thông tin sản phẩm
     */
    public function getWithProduct(int $variantId): ?array
    {
        $sql = "
            SELECT 
                pv.*,
                p.name as product_name,
                p.sku as product_sku
            FROM {$this->table} pv
            INNER JOIN products p ON pv.product_id = p.id
            WHERE pv.id = ?
        ";
        return $this->queryOne($sql, [$variantId]);
    }

    /**
     * Kiểm tra SKU variant đã tồn tại chưa
     */
    public function skuExists(string $sku, int $productId, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE sku = ? AND product_id = ? AND id != ?";
            $result = $this->queryOne($sql, [$sku, $productId, $excludeId]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE sku = ? AND product_id = ?";
            $result = $this->queryOne($sql, [$sku, $productId]);
        }

        return (int) ($result['count'] ?? 0) > 0;
    }

    /**
     * Tạo variant mới
     */
    public function createVariant(array $data): int
    {
        $fields = ['product_id', 'sku', 'attributes', 'price', 'unit_cost', 'barcode', 'is_active'];
        $filteredData = array_intersect_key($data, array_flip($fields));
        
        return $this->create($filteredData);
    }

    /**
     * Cập nhật variant
     */
    public function updateVariant(int $id, array $data): bool
    {
        $fields = ['sku', 'attributes', 'price', 'unit_cost', 'barcode', 'is_active'];
        $filteredData = array_intersect_key($data, array_flip($fields));
        
        return $this->update($id, $filteredData);
    }

    /**
     * Xóa variant
     */
    public function deleteVariant(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Đếm số variants của sản phẩm
     */
    public function countByProduct(int $productId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE product_id = ?";
        $result = $this->queryOne($sql, [$productId]);
        return (int) ($result['count'] ?? 0);
    }
}
