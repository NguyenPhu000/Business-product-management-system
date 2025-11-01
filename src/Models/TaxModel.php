<?php

namespace Models;

/**
 * TaxModel - Quản lý thuế VAT
 */
class TaxModel extends BaseModel
{
    protected string $table = 'tax';

    /**
     * Lấy danh sách thuế theo loại
     */
    public function getByType(string $type = 'product'): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE type = ? AND is_active = 1 ORDER BY rate ASC";
        return $this->query($sql, [$type]);
    }

    /**
     * Lấy tất cả thuế đang hoạt động
     * Tự động detect xem bảng có cột is_active không
     */
    public function getAllActive(): array
    {
        // Thử query với is_active trước
        try {
            $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY rate ASC";
            return $this->query($sql);
        } catch (\Exception $e) {
            // Nếu lỗi (do không có cột is_active), query tất cả
            $sql = "SELECT * FROM {$this->table} ORDER BY rate ASC";
            return $this->query($sql);
        }
    }
}
