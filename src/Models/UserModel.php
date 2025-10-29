<?php

namespace Models;

/**
 * UserModel - Quản lý người dùng
 */
class UserModel extends BaseModel
{
    protected string $table = 'users';
    protected string $primaryKey = 'id';

    /**
     * Tìm user theo email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findBy(['email' => $email]);
    }

    /**
     * Tìm user theo username
     */
    public function findByUsername(string $username): ?array
    {
        return $this->findBy(['username' => $username]);
    }

    /**
     * Xác thực đăng nhập
     */
    public function authenticate(string $emailOrUsername, string $password): ?array
    {
        // Tìm user theo email hoặc username
        $sql = "SELECT * FROM {$this->table} WHERE email = ? OR username = ? LIMIT 1";
        $user = $this->queryOne($sql, [$emailOrUsername, $emailOrUsername]);

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return null;
    }

    /**
     * Tạo user mới với mật khẩu đã hash
     */
    public function createUser(array $data): int
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->create($data);
    }

    /**
     * Cập nhật thông tin user
     */
    public function updateUser(int $id, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->update($id, $data);
    }

    /**
     * Lấy danh sách user với thông tin role
     */
    public function getAllWithRole(): array
    {
        $sql = "SELECT u.*, r.name, r.description 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                ORDER BY u.created_at DESC";

        return $this->query($sql);
    }

    /**
     * Lấy user với thông tin role
     */
    public function findWithRole(int $id): ?array
    {
        $sql = "SELECT u.*, r.name, r.description 
                FROM {$this->table} u 
                LEFT JOIN roles r ON u.role_id = r.id 
                WHERE u.{$this->primaryKey} = ? 
                LIMIT 1";

        return $this->queryOne($sql, [$id]);
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword(int $id, string $newPassword): bool
    {
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password_hash' => $passwordHash]);
    }

    /**
     * Kiểm tra email đã tồn tại chưa
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->queryOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Kiểm tra username đã tồn tại chưa
     */
    public function usernameExists(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE username = ?";
        $params = [$username];

        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != ?";
            $params[] = $excludeId;
        }

        $result = $this->queryOne($sql, $params);
        return $result['total'] > 0;
    }
}
