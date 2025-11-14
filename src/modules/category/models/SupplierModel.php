<?php

namespace Modules\Category\Models;

use Core\BaseModel;

/**
 * SupplierModel - Quản lý nhà cung cấp
 * 
 * Chức năng:
 * - CRUD nhà cung cấp
 * - Kiểm tra thông tin trùng lặp
 * - Thống kê đơn hàng theo nhà cung cấp
 * - Tìm kiếm nhà cung cấp
 */
class SupplierModel extends BaseModel
{
    protected string $table = 'suppliers';
    protected string $primaryKey = 'id';

    /**
     * Lấy tất cả nhà cung cấp kèm số lượng đơn hàng
     */
    public function getAllWithOrderCount(): array
    {
        $sql = "SELECT s.*, COUNT(po.id) as order_count 
                FROM {$this->table} s 
                LEFT JOIN purchase_orders po ON s.id = po.supplier_id 
                GROUP BY s.id 
                ORDER BY s.name ASC";
        
        return $this->query($sql);
    }

    /**
     * Lấy nhà cung cấp với phân trang
     * 
     * @param int $page Trang hiện tại (bắt đầu từ 1)
     * @param int $perPage Số lượng/trang
     * @return array ['data' => [], 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
     */
    public function getAllWithPagination(int $page = 1, int $perPage = 8): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Đếm tổng số nhà cung cấp
        $countSql = "SELECT COUNT(DISTINCT s.id) as total FROM {$this->table} s";
        $countResult = $this->queryOne($countSql);
        $total = (int) ($countResult['total'] ?? 0);

        // Lấy dữ liệu phân trang
        $sql = "SELECT s.*, COUNT(po.id) as order_count 
                FROM {$this->table} s 
                LEFT JOIN purchase_orders po ON s.id = po.supplier_id 
                GROUP BY s.id 
                ORDER BY s.id ASC
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
     * Lấy nhà cung cấp kèm số lượng đơn hàng
     */
    public function findWithOrderCount(int $id): ?array
    {
        $sql = "SELECT s.*, COUNT(po.id) as order_count 
                FROM {$this->table} s 
                LEFT JOIN purchase_orders po ON s.id = po.supplier_id 
                WHERE s.id = ? 
                GROUP BY s.id";
        
        return $this->queryOne($sql, [$id]);
    }

    /**
     * Kiểm tra email đã tồn tại chưa
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Kiểm tra số điện thoại đã tồn tại chưa
     */
    public function phoneExists(string $phone, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE phone = ?";
        $params = [$phone];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Lấy tất cả nhà cung cấp đang active
     */
    public function getActiveSuppliers(): array
    {
        return $this->where(['is_active' => 1], 'name', 'ASC');
    }

    /**
     * Đếm số lượng đơn hàng của nhà cung cấp
     */
    public function countOrders(int $supplierId): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM purchase_orders 
                WHERE supplier_id = ?";
        
        $result = $this->queryOne($sql, [$supplierId]);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Kiểm tra có thể xóa nhà cung cấp không (không có đơn hàng)
     */
    public function canDelete(int $supplierId): array
    {
        $hasOrders = $this->countOrders($supplierId) > 0;
        
        return [
            'can_delete' => !$hasOrders,
            'has_orders' => $hasOrders,
            'order_count' => $hasOrders ? $this->countOrders($supplierId) : 0
        ];
    }

    /**
     * Toggle trạng thái active
     */
    public function toggleActive(int $id): bool
    {
        $supplier = $this->find($id);
        
        if (!$supplier) {
            return false;
        }
        
        $newStatus = $supplier['is_active'] ? 0 : 1;
        return $this->update($id, ['is_active' => $newStatus]);
    }

    /**
     * Tìm kiếm nhà cung cấp
     */
    public function search(string $keyword): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE name LIKE ? 
                   OR contact LIKE ? 
                   OR phone LIKE ? 
                   OR email LIKE ? 
                ORDER BY name ASC";
        
        $searchTerm = "%{$keyword}%";
        return $this->query($sql, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    /**
     * Lấy top nhà cung cấp có nhiều đơn hàng nhất
     */
    public function getTopSuppliers(int $limit = 10): array
    {
        $sql = "SELECT s.*, COUNT(po.id) as order_count,
                       SUM(po.total_amount) as total_amount 
                FROM {$this->table} s 
                LEFT JOIN purchase_orders po ON s.id = po.supplier_id 
                WHERE s.is_active = 1 
                GROUP BY s.id 
                ORDER BY order_count DESC 
                LIMIT {$limit}";
        
        return $this->query($sql);
    }

    /**
     * Lấy lịch sử đơn hàng của nhà cung cấp
     */
    public function getOrderHistory(int $supplierId, int $limit = 20): array
    {
        $sql = "SELECT * FROM purchase_orders 
                WHERE supplier_id = ? 
                ORDER BY created_at DESC 
                LIMIT {$limit}";
        
        return $this->query($sql, [$supplierId]);
    }

    /**
     * Thống kê tổng giá trị đơn hàng
     */
    public function getTotalOrderValue(int $supplierId): float
    {
        $sql = "SELECT SUM(total_amount) as total 
                FROM purchase_orders 
                WHERE supplier_id = ? AND status = 'completed'";
        
        $result = $this->queryOne($sql, [$supplierId]);
        return (float) ($result['total'] ?? 0);
    }
}
