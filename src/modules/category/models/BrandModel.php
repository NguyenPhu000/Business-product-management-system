<?php

namespace Modules\Category\Models;

use Core\BaseModel;

/**
 * BrandModel - Quản lý thương hiệu sản phẩm
 * 
 * Chức năng:
 * - CRUD thương hiệu
 * - Quản lý logo thương hiệu
 * - Kiểm tra tên trùng lặp
 * - Thống kê số sản phẩm theo thương hiệu
 */
class BrandModel extends BaseModel
{
    protected string $table = 'brands';
    protected string $primaryKey = 'id';

    /**
     * Lấy tất cả thương hiệu kèm số lượng sản phẩm
     */
    public function getAllWithProductCount(): array
    {
        $sql = "SELECT b.*, COUNT(p.id) as product_count 
                FROM {$this->table} b 
                LEFT JOIN products p ON b.id = p.brand_id 
                GROUP BY b.id 
                ORDER BY b.name ASC";

        return $this->query($sql);
    }

    /**
     * Lấy thương hiệu với phân trang
     * 
     * @param int $page Trang hiện tại (bắt đầu từ 1)
     * @param int $perPage Số lượng/trang
     * @return array ['data' => [], 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
     */
    public function getAllWithPagination(int $page = 1, int $perPage = 8): array
    {
        // Đảm bảo page >= 1
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Đếm tổng số thương hiệu
        $countSql = "SELECT COUNT(DISTINCT b.id) as total FROM {$this->table} b";
        $countResult = $this->queryOne($countSql);
        $total = (int) ($countResult['total'] ?? 0);

        // Lấy dữ liệu phân trang
        $sql = "SELECT b.*, COUNT(p.id) as product_count 
                FROM {$this->table} b 
                LEFT JOIN products p ON b.id = p.brand_id 
                GROUP BY b.id 
                ORDER BY b.id ASC
                LIMIT {$perPage} OFFSET {$offset}";

        $data = $this->query($sql);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Lấy thương hiệu kèm số lượng sản phẩm
     */
    public function findWithProductCount(int $id): ?array
    {
        $sql = "SELECT b.*, COUNT(p.id) as product_count 
                FROM {$this->table} b 
                LEFT JOIN products p ON b.id = p.brand_id 
                WHERE b.id = ? 
                GROUP BY b.id";

        return $this->queryOne($sql, [$id]);
    }

    /**
     * Kiểm tra tên thương hiệu đã tồn tại chưa
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE name = ?";
        $params = [$name];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->queryOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Lấy tất cả thương hiệu đang active
     */
    public function getActiveBrands(): array
    {
        return $this->where(['is_active' => 1], 'name', 'ASC');
    }

    /**
     * Đếm số lượng sản phẩm của thương hiệu
     */
    public function countProducts(int $brandId): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM products 
                WHERE brand_id = ?";

        $result = $this->queryOne($sql, [$brandId]);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Kiểm tra có thể xóa thương hiệu không (không có sản phẩm)
     */
    public function canDelete(int $brandId): array
    {
        $hasProducts = $this->countProducts($brandId) > 0;

        return [
            'can_delete' => !$hasProducts,
            'has_products' => $hasProducts,
            'product_count' => $hasProducts ? $this->countProducts($brandId) : 0
        ];
    }

    /**
     * Cập nhật logo thương hiệu
     */
    public function updateLogo(int $id, string $logoUrl): bool
    {
        return $this->update($id, ['logo_url' => $logoUrl]);
    }

    /**
     * Xóa logo thương hiệu
     */
    public function removeLogo(int $id): bool
    {
        return $this->update($id, ['logo_url' => null]);
    }

    /**
     * Toggle trạng thái active
     */
    public function toggleActive(int $id): bool
    {
        $brand = $this->find($id);

        if (!$brand) {
            return false;
        }

        $newStatus = $brand['is_active'] ? 0 : 1;
        return $this->update($id, ['is_active' => $newStatus]);
    }

    /**
     * Tìm kiếm thương hiệu theo tên
     */
    public function search(string $keyword): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? OR description LIKE ? 
                ORDER BY name ASC";

        $searchTerm = "%{$keyword}%";
        return $this->query($sql, [$searchTerm, $searchTerm]);
    }

    /**
     * Lấy top thương hiệu có nhiều sản phẩm nhất
     */
    public function getTopBrands(int $limit = 10): array
    {
        $sql = "SELECT b.*, COUNT(p.id) as product_count 
                FROM {$this->table} b 
                LEFT JOIN products p ON b.id = p.brand_id 
                WHERE b.is_active = 1 
                GROUP BY b.id 
                ORDER BY product_count DESC 
                LIMIT {$limit}";

        return $this->query($sql);
    }
}
