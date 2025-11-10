<?php

namespace Modules\Product\Models;

use Core\BaseModel;

/**
 * ProductCategoryModel - Quản lý quan hệ nhiều-nhiều giữa Product và Category
 * 
 * Chức năng:
 * - Gán sản phẩm vào nhiều danh mục
 * - Lấy danh sách danh mục của sản phẩm
 * - Lấy danh sách sản phẩm theo danh mục
 */
class ProductCategoryModel extends BaseModel
{
    protected string $table = 'product_categories';
    // Bảng này không có primary key đơn, có composite key (product_id, category_id)
    
    /**
     * Gán sản phẩm vào nhiều danh mục
     * 
     * @param int $productId ID sản phẩm
     * @param array $categoryIds Mảng ID danh mục
     * @return bool
     */
    public function assignCategories(int $productId, array $categoryIds): bool
    {
        try {
            // Xóa tất cả danh mục cũ
            $this->removeAllCategories($productId);
            
            // Thêm danh mục mới
            foreach ($categoryIds as $categoryId) {
                $sql = "INSERT INTO {$this->table} (product_id, category_id) VALUES (?, ?)";
                $this->execute($sql, [$productId, $categoryId]);
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Error assigning categories: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thêm sản phẩm vào 1 danh mục
     */
    public function addCategory(int $productId, int $categoryId): bool
    {
        // Kiểm tra đã tồn tại chưa
        if ($this->existsRelation($productId, $categoryId)) {
            return true;
        }
        
        $sql = "INSERT INTO {$this->table} (product_id, category_id) VALUES (?, ?)";
        return $this->execute($sql, [$productId, $categoryId]);
    }

    /**
     * Xóa sản phẩm khỏi 1 danh mục
     */
    public function removeCategory(int $productId, int $categoryId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE product_id = ? AND category_id = ?";
        return $this->execute($sql, [$productId, $categoryId]);
    }

    /**
     * Xóa tất cả danh mục của sản phẩm
     */
    public function removeAllCategories(int $productId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE product_id = ?";
        return $this->execute($sql, [$productId]);
    }

    /**
     * Lấy danh sách danh mục của sản phẩm
     */
    public function getCategoriesByProduct(int $productId): array
    {
        $sql = "SELECT c.* 
                FROM categories c 
                INNER JOIN {$this->table} pc ON c.id = pc.category_id 
                WHERE pc.product_id = ? 
                ORDER BY c.name ASC";
        
        return $this->query($sql, [$productId]);
    }

    /**
     * Lấy danh sách ID danh mục của sản phẩm
     */
    public function getCategoryIdsByProduct(int $productId): array
    {
        $sql = "SELECT category_id FROM {$this->table} WHERE product_id = ?";
        $results = $this->query($sql, [$productId]);
        
        return array_column($results, 'category_id');
    }

    /**
     * Lấy danh sách sản phẩm theo danh mục
     */
    public function getProductsByCategory(int $categoryId, int $limit = 0): array
    {
        $sql = "SELECT p.* 
                FROM products p 
                INNER JOIN {$this->table} pc ON p.id = pc.product_id 
                WHERE pc.category_id = ? 
                ORDER BY p.created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit}";
        }
        
        return $this->query($sql, [$categoryId]);
    }

    /**
     * Đếm số sản phẩm trong danh mục
     */
    public function countProductsByCategory(int $categoryId): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE category_id = ?";
        
        $result = $this->queryOne($sql, [$categoryId]);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Kiểm tra sản phẩm đã thuộc danh mục chưa
     */
    public function existsRelation(int $productId, int $categoryId): bool
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} 
                WHERE product_id = ? AND category_id = ?";
        
        $result = $this->queryOne($sql, [$productId, $categoryId]);
        return $result['total'] > 0;
    }

    /**
     * Lấy danh sách sản phẩm theo nhiều danh mục (OR)
     */
    public function getProductsByCategories(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
        
        $sql = "SELECT DISTINCT p.* 
                FROM products p 
                INNER JOIN {$this->table} pc ON p.id = pc.product_id 
                WHERE pc.category_id IN ({$placeholders}) 
                ORDER BY p.created_at DESC";
        
        return $this->query($sql, $categoryIds);
    }

    /**
     * Copy danh mục từ sản phẩm này sang sản phẩm khác
     */
    public function copyCategories(int $fromProductId, int $toProductId): bool
    {
        $categoryIds = $this->getCategoryIdsByProduct($fromProductId);
        return $this->assignCategories($toProductId, $categoryIds);
    }

    /**
     * Lấy số lượng sản phẩm theo từng danh mục (thống kê)
     */
    public function getProductCountByCategories(): array
    {
        $sql = "SELECT c.id, c.name, COUNT(pc.product_id) as product_count 
                FROM categories c 
                LEFT JOIN {$this->table} pc ON c.id = pc.category_id 
                GROUP BY c.id, c.name 
                ORDER BY product_count DESC";
        
        return $this->query($sql);
    }

    /**
     * Tìm sản phẩm thuộc tất cả các danh mục (AND)
     */
    public function getProductsInAllCategories(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }
        
        $count = count($categoryIds);
        $placeholders = implode(',', array_fill(0, $count, '?'));
        
        $sql = "SELECT p.* 
                FROM products p 
                WHERE (
                    SELECT COUNT(DISTINCT category_id) 
                    FROM {$this->table} 
                    WHERE product_id = p.id 
                    AND category_id IN ({$placeholders})
                ) = {$count}";
        
        return $this->query($sql, $categoryIds);
    }
}
