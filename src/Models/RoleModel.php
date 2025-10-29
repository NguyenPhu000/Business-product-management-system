<?php

namespace Models;

/**
 * RoleModel - Quản lý vai trò người dùng
 */
class RoleModel extends BaseModel
{
    protected string $table = 'roles';
    protected string $primaryKey = 'id';

    /**
     * Tìm role theo tên
     */
    public function findByName(string $name): ?array
    {
        return $this->findBy(['name' => $name]);
    }

    /**
     * Lấy tất cả roles cho dropdown
     */
    public function getAllForSelect(): array
    {
        $sql = "SELECT id, name FROM {$this->table} ORDER BY name ASC";
        return $this->query($sql);
    }

    /**
     * Đếm số user theo role
     */
    public function countUsers(int $roleId): int
    {
        $sql = "SELECT COUNT(*) as total FROM users WHERE role_id = ?";
        $result = $this->queryOne($sql, [$roleId]);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Kiểm tra role name đã tồn tại chưa
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE name = ?";
        $params = [$name];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->queryOne($sql, $params);
        return $result['total'] > 0;
    }
}
