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

        // Lọc theo thương hiệu
        if (!empty($filters['brand_id'])) {
            $where[] = "p.brand_id = ?";
            $params[] = $filters['brand_id'];
        }

        // Lọc theo từ khóa
        if (!empty($filters['keyword'])) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        // Lọc theo trạng thái
        if (isset($filters['status']) && $filters['status'] !== '') {
            $where[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = implode(' AND ', $where);

        // Xử lý ORDER BY
        $orderBy = 'p.created_at DESC'; // Mặc định
        if (!empty($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'created_at_asc':
                    $orderBy = 'p.created_at ASC';
                    break;
                case 'created_at_desc':
                    $orderBy = 'p.created_at DESC';
                    break;
                case 'price_asc':
                    // Ưu tiên giá khuyến mãi (sale_price), nếu không có mới lấy giá bán (price)
                    $orderBy = 'COALESCE(NULLIF(p.sale_price, 0), p.price, 0) ASC';
                    break;
                case 'price_desc':
                    // Ưu tiên giá khuyến mãi (sale_price), nếu không có mới lấy giá bán (price)
                    $orderBy = 'COALESCE(NULLIF(p.sale_price, 0), p.price, 0) DESC';
                    break;
                case 'name_asc':
                    $orderBy = 'p.name ASC';
                    break;
                case 'name_desc':
                    $orderBy = 'p.name DESC';
                    break;
            }
        }

        $query = "
            SELECT p.*,
                   GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') AS category_names,
                   GROUP_CONCAT(DISTINCT c.id) AS category_ids,
                   (SELECT 
                       CASE 
                           WHEN pi2.image_data IS NOT NULL THEN CONCAT('data:', pi2.mime_type, ';base64,', pi2.image_data)
                           ELSE pi2.url
                       END
                   FROM product_images pi2 
                   WHERE pi2.product_id = p.id AND pi2.is_primary = 1 
                   LIMIT 1) AS primary_image
            FROM {$this->table} p
            LEFT JOIN product_categories pc ON p.id = pc.product_id
            LEFT JOIN categories c ON pc.category_id = c.id
            WHERE {$whereClause}
            GROUP BY p.id
            ORDER BY {$orderBy}
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

        if (!empty($filters['brand_id'])) {
            $where[] = "p.brand_id = ?";
            $params[] = $filters['brand_id'];
        }

        if (!empty($filters['keyword'])) {
            $where[] = "(p.name LIKE ? OR p.sku LIKE ?)";
            $keyword = '%' . $filters['keyword'] . '%';
            $params[] = $keyword;
            $params[] = $keyword;
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
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