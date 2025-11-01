<?php

namespace Models;

/**
 * UserLogModel - Quản lý log hoạt động người dùng
 */
class UserLogModel extends BaseModel
{
    protected string $table = 'user_logs';
    protected string $primaryKey = 'id';

    /**
     * Ghi log hoạt động
     */
    public function log(int $userId, string $action, ?string $objectType = null, ?int $objectId = null, ?array $meta = null, ?string $ip = null): int
    {
        $data = [
            'user_id' => $userId,
            'action' => $action,
            'object_type' => $objectType,
            'object_id' => $objectId,
            'meta' => $meta ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
            'ip' => $ip ?? $_SERVER['REMOTE_ADDR'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];

        return $this->create($data);
    }

    /**
     * Lấy log với thông tin user
     */
    public function getAllWithUser(int $limit = 100): array
    {
        $sql = "SELECT ul.*, u.username, u.full_name, u.email 
                FROM {$this->table} ul 
                LEFT JOIN users u ON ul.user_id = u.id 
                ORDER BY ul.created_at DESC 
                LIMIT ?";

        return $this->query($sql, [$limit]);
    }

    /**
     * Lấy log theo user
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";

        return $this->query($sql, [$userId, $limit]);
    }

    /**
     * Lấy log theo action
     */
    public function getByAction(string $action, int $limit = 50): array
    {
        $sql = "SELECT ul.*, u.username, u.full_name 
                FROM {$this->table} ul 
                LEFT JOIN users u ON ul.user_id = u.id 
                WHERE ul.action = ? 
                ORDER BY ul.created_at DESC 
                LIMIT ?";

        return $this->query($sql, [$action, $limit]);
    }

    /**
     * Lấy log với phân trang và filter
     */
    public function getLogsWithFilter(int $page = 1, int $perPage = 20, ?int $userId = null, ?string $action = null): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];

        $sql = "SELECT ul.*, 
                u.username, u.full_name, u.email, u.role_id,
                r.name as role_name,
                target_user.username as target_username, 
                target_user.full_name as target_full_name
                FROM {$this->table} ul 
                LEFT JOIN users u ON ul.user_id = u.id
                LEFT JOIN roles r ON u.role_id = r.id
                LEFT JOIN users target_user ON ul.object_type = 'user' AND ul.object_id = target_user.id
                WHERE 1=1";

        if ($userId) {
            $sql .= " AND ul.user_id = ?";
            $params[] = $userId;
        }

        if ($action) {
            $sql .= " AND ul.action = ?";
            $params[] = $action;
        }

        // Đếm tổng
        $countSql = "SELECT COUNT(*) as total FROM ({$sql}) as sub";
        $totalResult = $this->queryOne($countSql, $params);
        $total = (int) ($totalResult['total'] ?? 0);

        // Lấy dữ liệu với phân trang
        $sql .= " ORDER BY ul.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $data = $this->query($sql, $params);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }

    /**
     * Xóa log cũ (cleanup)
     */
    public function deleteOldLogs(int $days = 90): bool
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        $sql = "DELETE FROM {$this->table} WHERE created_at < ?";
        return $this->execute($sql, [$date]);
    }
}
