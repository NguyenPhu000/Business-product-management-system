<?php

namespace Models;

/**
 * ProductModel - Quản lý sản phẩm
 */
class ProductModel extends BaseModel
{
    protected string $table = 'products';
    protected string $primaryKey = 'id';

    /**
     * Lấy sản phẩm với thông tin danh mục
     */
    public function getWithCategories(int $id): ?array
    {
        $query = "
            SELECT p.*,
                   GROUP_CONCAT(c.name SEPARATOR ', ') AS category_names,
                   GROUP_CONCAT(c.id) AS category_ids
            FROM {$this->table} p
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE p.id = ?
            GROUP BY p.id
        ";

        return $this->queryOne($query, [$id]);
    }

    /**
     * Lấy danh sách sản phẩm với phân trang và lọc theo danh mục
     */
    public function getProductsList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $where = ['1=1'];
        $params = [];

        // Lọc theo danh mục
        if (!empty($filters['category_id'])) {
            $where[] = "pc.category_id = ?";
            $params[] = $filters['category_id'];
        }

        // Lọc theo từ khóa
        if (!empty($filters['keyword'])) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        // Lọc theo trạng thái
        if (isset($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);

        $query = "
            SELECT p.*,
                   GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS category_names,
                   GROUP_CONCAT(DISTINCT c.id) AS category_ids
            FROM {$this->table} p
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE {$whereClause}
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $perPage;
        $params[] = $offset;

        return $this->query($query, $params);
    }

    /**
     * Đếm tổng số sản phẩm theo bộ lọc
     */
    public function countProducts(array $filters = []): int
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['category_id'])) {
            $where[] = "pc.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if (!empty($filters['keyword'])) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (isset($filters['status'])) {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);

        $query = "
            SELECT COUNT(DISTINCT p.id) AS total
            FROM {$this->table} p
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            WHERE {$whereClause}
        ";

        $result = $this->queryOne($query, $params);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Cập nhật số lượng sản phẩm
     */
    public function updateStock(int $id, int $quantity): bool
    {
        return $this->update($id, ['stock_quantity' => $quantity]);
    }

    /**
     * Kiểm tra SKU đã tồn tại chưa
     */
    public function skuExists(string $sku, ?int $excludeId = null): bool
    {
        if ($excludeId) {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE sku = ? AND id != ?";
            $result = $this->queryOne($query, [$sku, $excludeId]);
        } else {
            $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE sku = ?";
            $result = $this->queryOne($query, [$sku]);
        }

        return (int) ($result['count'] ?? 0) > 0;
    }
}
