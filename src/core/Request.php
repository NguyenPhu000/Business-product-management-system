<?php

namespace Core;

/**
 * Request - Xử lý HTTP request an toàn
 * 
 * Chức năng:
 * - Wrapper cho $_GET, $_POST, $_SERVER để tránh truy cập trực tiếp
 * - Validation và sanitization input
 * - Lấy thông tin request: method, URI, IP, user agent
 */
class Request
{
    /**
     * Lấy giá trị từ $_GET
     */
    public static function get(string $key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Lấy giá trị từ $_POST
     */
    public static function post(string $key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Lấy giá trị từ $_GET hoặc $_POST
     */
    public static function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Lấy tất cả POST data
     */
    public static function all(): array
    {
        return $_POST;
    }

    /**
     * Lấy HTTP method
     */
    public static function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    /**
     * Kiểm tra có phải POST request không
     */
    public static function isPost(): bool
    {
        return self::method() === 'POST';
    }

    /**
     * Kiểm tra có phải GET request không
     */
    public static function isGet(): bool
    {
        return self::method() === 'GET';
    }

    /**
     * Lấy URI hiện tại
     */
    public static function uri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }

    /**
     * Lấy IP address của client
     */
    public static function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Lấy User Agent
     */
    public static function userAgent(): ?string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * Kiểm tra request có key không
     */
    public static function has(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    /**
     * Sanitize input string
     */
    public static function sanitize(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Validate email
     */
    public static function isEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Lấy nhiều giá trị cùng lúc
     */
    public static function only(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::input($key);
        }
        return $result;
    }

    /**
     * Lấy uploaded files
     */
    public static function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }

    /**
     * Kiểm tra có file upload không
     */
    public static function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }
}
