<?php

namespace Modules\System\Models;

use Core\BaseModel;

/**
 * SystemConfigModel - Quản lý cấu hình hệ thống
 */
class SystemConfigModel extends BaseModel
{
    protected string $table = 'system_config';
    protected string $primaryKey = 'key';

    /**
     * Lấy giá trị cấu hình theo key
     */
    public function getValue(string $key, $default = null)
    {
        $config = $this->findBy(['key' => $key]);
        return $config ? $config['value'] : $default;
    }

    /**
     * Đặt giá trị cấu hình
     */
    public function setValue(string $key, $value, ?int $userId = null): bool
    {
        $existing = $this->findBy(['key' => $key]);

        $data = [
            'value' => $value,
            'user_id' => $userId,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($existing) {
            // Update
            $sql = "UPDATE {$this->table} 
                    SET value = ?, user_id = ?, updated_at = ? 
                    WHERE `key` = ?";
            return $this->execute($sql, [$value, $userId, $data['updated_at'], $key]);
        } else {
            // Insert
            $data['key'] = $key;
            $sql = "INSERT INTO {$this->table} (`key`, value, user_id, updated_at) 
                    VALUES (?, ?, ?, ?)";
            return $this->execute($sql, [$key, $value, $userId, $data['updated_at']]);
        }
    }

    /**
     * Lấy tất cả cấu hình
     */
    public function getAllConfigs(): array
    {
        $sql = "SELECT sc.*, u.username, u.full_name 
                FROM {$this->table} sc 
                LEFT JOIN users u ON sc.user_id = u.id 
                ORDER BY sc.`key` ASC";

        return $this->query($sql);
    }

    /**
     * Xóa cấu hình theo key
     */
    public function deleteByKey(string $key): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE `key` = ?";
        return $this->execute($sql, [$key]);
    }

    /**
     * Lấy nhiều cấu hình cùng lúc
     */
    public function getMultiple(array $keys): array
    {
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $sql = "SELECT `key`, value FROM {$this->table} WHERE `key` IN ({$placeholders})";

        $results = $this->query($sql, $keys);

        // Chuyển thành mảng key => value
        $configs = [];
        foreach ($results as $row) {
            $configs[$row['key']] = $row['value'];
        }

        return $configs;
    }
}