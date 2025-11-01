<?php

namespace Models;

use PDO;
use PDOException;
use Exception;

/**
 * DatabaseModel - Kết nối PDO trung tâm
 * 
 * Chức năng:
 * - Quản lý kết nối database bằng PDO (Singleton pattern)
 * - Tự động load cấu hình từ file .env
 * - Cung cấp các phương thức query, execute, transaction
 * - Xử lý lỗi database và logging
 */
class DatabaseModel
{
    protected static ?PDO $connection = null;
    protected string $table = '';
    
    /**
     * Khởi tạo kết nối database (Singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                // Load cấu hình database
                $config = require __DIR__ . '/../../config/database.php';
                
                $dsn = sprintf(
                    "%s:host=%s;port=%s;dbname=%s;charset=%s",
                    $config['driver'],
                    $config['host'],
                    $config['port'],
                    $config['database'],
                    $config['charset']
                );
                
                self::$connection = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                );
                
            } catch (PDOException $e) {
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Không thể kết nối database: " . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
    
    /**
     * Thực thi câu lệnh SELECT với prepared statement
     */
    protected function query(string $sql, array $params = []): array
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Lỗi truy vấn: " . $e->getMessage());
        }
    }
    
    /**
     * Thực thi câu lệnh SELECT và trả về 1 bản ghi
     */
    protected function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Query Error: " . $e->getMessage());
            throw new Exception("Lỗi truy vấn: " . $e->getMessage());
        }
    }
    
    /**
     * Thực thi câu lệnh INSERT, UPDATE, DELETE
     */
    protected function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = self::getConnection()->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Execute Error: " . $e->getMessage());
            throw new Exception("Lỗi thực thi: " . $e->getMessage());
        }
    }
    
    /**
     * Lấy ID của bản ghi vừa insert
     */
    protected function lastInsertId(): string
    {
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Bắt đầu transaction
     */
    public function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return self::getConnection()->rollBack();
    }
    
    /**
     * Đóng kết nối
     */
    public static function closeConnection(): void
    {
        self::$connection = null;
    }
}
