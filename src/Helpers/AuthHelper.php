<?php

namespace Helpers;

/**
 * AuthHelper - Xử lý authentication và session
 */
class AuthHelper
{
    /**
     * Bắt đầu session
     */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../../config/auth.php';
            session_name($config['session_name']);
            session_start();
        }
    }

    /**
     * Đăng nhập user
     */
    public static function login(array $user): void
    {
        self::startSession();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
        $_SESSION['user_role'] = $user['role_id'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
    }

    /**
     * Đăng xuất user
     */
    public static function logout(): void
    {
        self::startSession();
        session_unset();
        session_destroy();
    }

    /**
     * Kiểm tra đã đăng nhập chưa
     */
    public static function check(): bool
    {
        self::startSession();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public static function user(): ?array
    {
        self::startSession();
        if (!self::check()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'full_name' => $_SESSION['user_name'] ?? null,
            'role_id' => $_SESSION['user_role'] ?? null,
        ];
    }

    /**
     * Lấy ID user hiện tại
     */
    public static function id(): ?int
    {
        $user = self::user();
        return $user['id'] ?? null;
    }

    /**
     * Kiểm tra quyền admin
     */
    public static function isAdmin(): bool
    {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == ROLE_ADMIN;
    }

    /**
     * Kiểm tra quyền Chủ tiệm
     */
    public static function isOwner(): bool
    {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == ROLE_OWNER;
    }

    /**
     * Kiểm tra quyền Admin hoặc Chủ tiệm (quyền quản lý cao)
     */
    public static function isAdminOrOwner(): bool
    {
        self::startSession();
        $role = $_SESSION['user_role'] ?? null;
        return $role == ROLE_ADMIN || $role == ROLE_OWNER;
    }

    /**
     * Kiểm tra có role cụ thể
     */
    public static function hasRole(int $roleId): bool
    {
        self::startSession();
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] == $roleId;
    }

    /**
     * Set flash message
     */
    public static function setFlash(string $key, $value): void
    {
        self::startSession();
        $_SESSION['flash'][$key] = $value;
    }

    /**
     * Get flash message
     */
    public static function getFlash(string $key, $default = null)
    {
        self::startSession();
        $value = $_SESSION['flash'][$key] ?? $default;
        unset($_SESSION['flash'][$key]);
        return $value;
    }

    /**
     * Kiểm tra session timeout
     */
    public static function checkTimeout(): bool
    {
        self::startSession();
        $config = require __DIR__ . '/../../config/auth.php';
        $lifetime = $config['session_lifetime'] * 60; // Convert to seconds

        if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $lifetime)) {
            self::logout();
            return true;
        }

        return false;
    }

    /**
     * Lấy level quyền của role
     * Quy tắc: Admin (1) > Chủ tiệm (5) > Sales Staff (2) = Warehouse Manager (3)
     * 
     * @param int $roleId ID của role
     * @return int Level của role (càng cao càng nhiều quyền)
     */
    public static function getRoleLevel(int $roleId): int
    {
        $roleLevel = [
            ROLE_ADMIN => 3,              // Admin có level cao nhất (3)
            ROLE_OWNER => 2,              // Chủ tiệm có level 2
            ROLE_SALES_STAFF => 1,        // Sales Staff có level 1
            ROLE_WAREHOUSE_MANAGER => 1   // Warehouse Manager có level 1
        ];

        return $roleLevel[$roleId] ?? 0;
    }

    /**
     * Kiểm tra user hiện tại có quyền cao hơn role được chỉ định không
     * 
     * Quy tắc:
     * - Level cao hơn = có quyền cao hơn
     * - Level bằng nhau = không có quyền cao hơn
     * 
     * @param int $targetRoleId Role ID cần so sánh
     * @return bool True nếu user hiện tại có quyền CAO HƠN (không bao gồm bằng)
     */
    public static function hasHigherRoleThan(int $targetRoleId): bool
    {
        self::startSession();
        $currentRoleId = $_SESSION['user_role'] ?? 0;

        return self::getRoleLevel($currentRoleId) > self::getRoleLevel($targetRoleId);
    }

    /**
     * Kiểm tra user hiện tại có thể quản lý (edit/delete) user có role ID được chỉ định không
     * 
     * Quy tắc:
     * - Chỉ quyền CAO HƠN mới được quản lý quyền THẤP HƠN
     * - Quyền BẰNG NHAU không được quản lý lẫn nhau
     * - Không thể xóa tài khoản đang đăng nhập (check riêng ở controller)
     * 
     * @param int $targetRoleId Role ID của user cần quản lý
     * @return bool True nếu có quyền quản lý (level cao hơn)
     */
    public static function canManageRole(int $targetRoleId): bool
    {
        return self::hasHigherRoleThan($targetRoleId);
    }
}
