<?php

namespace Helpers;

use Modules\System\Models\UserLogModel;

/**
 * LogHelper - Ghi log hoạt động
 */
class LogHelper
{
    private static ?UserLogModel $logModel = null;

    /**
     * Khởi tạo log model
     */
    private static function getModel(): UserLogModel
    {
        if (self::$logModel === null) {
            self::$logModel = new UserLogModel();
        }
        return self::$logModel;
    }

    /**
     * Ghi log hoạt động
     */
    public static function log(string $action, ?string $objectType = null, ?int $objectId = null, ?array $meta = null): void
    {
        $userId = AuthHelper::id();
        if (!$userId) {
            return; // Không ghi log nếu chưa đăng nhập
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        try {
            self::getModel()->log($userId, $action, $objectType, $objectId, $meta, $ip);
        } catch (\Exception $e) {
            error_log("Log Error: " . $e->getMessage());
        }
    }

    /**
     * Ghi log đăng nhập
     */
    public static function logLogin(int $userId): void
    {
        $meta = [
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ];

        try {
            $model = self::getModel();
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $model->log($userId, 'login', 'user', $userId, $meta, $ip);
        } catch (\Exception $e) {
            error_log("Log Error: " . $e->getMessage());
        }
    }

    /**
     * Ghi log đăng xuất
     */
    public static function logLogout(int $userId): void
    {
        try {
            $model = self::getModel();
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            $model->log($userId, 'logout', 'user', $userId, null, $ip);
        } catch (\Exception $e) {
            error_log("Log Error: " . $e->getMessage());
        }
    }

    /**
     * Ghi log tạo mới
     */
    public static function logCreate(string $objectType, int $objectId, ?array $data = null): void
    {
        self::log('create', $objectType, $objectId, $data);
    }

    /**
     * Ghi log cập nhật
     */
    public static function logUpdate(string $objectType, int $objectId, ?array $changes = null): void
    {
        self::log('update', $objectType, $objectId, $changes);
    }

    /**
     * Ghi log xóa
     */
    public static function logDelete(string $objectType, int $objectId, ?array $data = null): void
    {
        self::log('delete', $objectType, $objectId, $data);
    }

    /**
     * Ghi log vào file
     */
    public static function logToFile(string $message, string $level = 'info'): void
    {
        $logDir = __DIR__ . '/../../storage/logs/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
}
