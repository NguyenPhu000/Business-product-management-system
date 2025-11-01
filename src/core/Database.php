<?php

/**
 * Database.php
 * 
 * Chức năng:
 * - Quản lý kết nối cơ sở dữ liệu sử dụng PDO
 * - Singleton pattern để đảm bảo chỉ có 1 kết nối
 * - Cung cấp các phương thức query, execute, transaction
 * - Xử lý lỗi database và logging
 */

namespace Core;

use PDO;
use PDOException;
use Exception;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config;

    /**
     * Private constructor - Singleton pattern
     */
    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/database.php';
        $this->connect();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Kết nối database với PDO
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                "%s:host=%s;port=%d;dbname=%s;charset=%s",
                $this->config['driver'],
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . $this->config['charset'] . " COLLATE " . $this->config['collation']
            ];

            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $options
            );
        } catch (PDOException $e) {
            $this->handleException($e, 'Kết nối database thất bại');
        }
    }

    /**
     * Get PDO connection
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Execute SELECT query - trả về nhiều rows
     */
    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->prepare($sql, $params);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            $this->handleException($e, 'Query failed', $sql, $params);
            return [];
        }
    }

    /**
     * Execute SELECT query - trả về 1 row
     */
    public function queryOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->prepare($sql, $params);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            $this->handleException($e, 'QueryOne failed', $sql, $params);
            return null;
        }
    }

    /**
     * Execute INSERT/UPDATE/DELETE - trả về số rows bị ảnh hưởng
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->prepare($sql, $params);
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->handleException($e, 'Execute failed', $sql, $params);
            return 0;
        }
    }

    /**
     * Execute INSERT và trả về ID vừa insert
     */
    public function insert(string $sql, array $params = []): int
    {
        try {
            $stmt = $this->prepare($sql, $params);
            $stmt->execute();
            return (int) $this->connection->lastInsertId();
        } catch (PDOException $e) {
            $this->handleException($e, 'Insert failed', $sql, $params);
            return 0;
        }
    }

    /**
     * Prepare statement với parameters
     */
    private function prepare(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->connection->prepare($sql);

        foreach ($params as $key => $value) {
            $paramKey = is_int($key) ? $key + 1 : $key;
            $paramType = $this->getParamType($value);
            $stmt->bindValue($paramKey, $value, $paramType);
        }

        return $stmt;
    }

    /**
     * Xác định kiểu dữ liệu cho PDO param
     */
    private function getParamType($value): int
    {
        return match (gettype($value)) {
            'boolean' => PDO::PARAM_BOOL,
            'integer' => PDO::PARAM_INT,
            'NULL' => PDO::PARAM_NULL,
            default => PDO::PARAM_STR,
        };
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        try {
            return $this->connection->beginTransaction();
        } catch (PDOException $e) {
            $this->handleException($e, 'Begin transaction failed');
            return false;
        }
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        try {
            return $this->connection->commit();
        } catch (PDOException $e) {
            $this->handleException($e, 'Commit transaction failed');
            return false;
        }
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        try {
            return $this->connection->rollBack();
        } catch (PDOException $e) {
            $this->handleException($e, 'Rollback transaction failed');
            return false;
        }
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * Xử lý exception
     */
    private function handleException(PDOException $e, string $message, string $sql = '', array $params = []): void
    {
        // Log error
        error_log(sprintf(
            "[Database Error] %s: %s | SQL: %s | Params: %s",
            $message,
            $e->getMessage(),
            $sql,
            json_encode($params)
        ));

        // Trong môi trường development, throw exception
        if (defined('APP_DEBUG') && APP_DEBUG) {
            throw new Exception($message . ': ' . $e->getMessage());
        }
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialize
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Close connection
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->disconnect();
    }
}
