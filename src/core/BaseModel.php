<?php

namespace Core;

use Exception;

/**
 * BaseModel - Lớp cha cho tất cả Model
 * 
 * Chức năng:
 * - Kế thừa DatabaseModel để có kết nối PDO
 * - Cung cấp các phương thức CRUD cơ bản
 * - Hỗ trợ query builder đơn giản
 */
abstract class BaseModel extends DatabaseModel
{
    protected string $table = '';
    protected string $primaryKey = 'id';

    /**
     * Lấy tất cả bản ghi
     */
    public function all(string $orderBy = '', string $order = 'ASC'): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy} {$order}";
        }

        return $this->query($sql);
    }

    /**
     * Tìm bản ghi theo ID
     */
    public function find(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->queryOne($sql, [$id]);
    }

    /**
     * Tìm bản ghi theo điều kiện
     */
    public function findBy(array $conditions): ?array
    {
        $where = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $where[] = "`{$key}` = ?";
            $params[] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where) . " LIMIT 1";
        return $this->queryOne($sql, $params);
    }

    /**
     * Lấy nhiều bản ghi theo điều kiện
     */
    public function where(array $conditions, string $orderBy = '', string $order = 'ASC'): array
    {
        $where = [];
        $params = [];

        foreach ($conditions as $key => $value) {
            $where[] = "`{$key}` = ?";
            $params[] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where);

        if ($orderBy) {
            $sql .= " ORDER BY `{$orderBy}` {$order}";
        }

        return $this->query($sql, $params);
    }

    /**
     * Thêm bản ghi mới
     */
    public function create(array $data): int
    {
        $fields = array_keys($data);
        $placeholders = array_fill(0, count($fields), '?');

        $sql = sprintf(
            "INSERT INTO %s (%s) VALUES (%s)",
            $this->table,
            implode(', ', $fields),
            implode(', ', $placeholders)
        );

        $this->execute($sql, array_values($data));
        return (int) $this->lastInsertId();
    }

    /**
     * Cập nhật bản ghi
     */
    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = ?";
            $params[] = $value;
        }

        $params[] = $id;

        $sql = sprintf(
            "UPDATE %s SET %s WHERE %s = ?",
            $this->table,
            implode(', ', $fields),
            $this->primaryKey
        );

        return $this->execute($sql, $params);
    }

    /**
     * Xóa bản ghi
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->execute($sql, [$id]);
    }

    /**
     * Đếm số bản ghi
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        if (!empty($conditions)) {
            $where = [];
            $params = [];

            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }

            $sql .= " WHERE " . implode(' AND ', $where);
            $result = $this->queryOne($sql, $params);
        } else {
            $result = $this->queryOne($sql);
        }

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Kiểm tra bản ghi có tồn tại không
     */
    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }

    /**
     * Lấy danh sách có phân trang
     */
    public function paginate(int $page = 1, int $perPage = 10, array $conditions = []): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "{$key} = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }

        $sql .= " LIMIT {$perPage} OFFSET {$offset}";

        $data = $this->query($sql, $params);
        $total = $this->count($conditions);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => ceil($total / $perPage)
        ];
    }
}
