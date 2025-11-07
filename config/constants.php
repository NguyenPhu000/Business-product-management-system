<?php

/**
 * constants.php - Hằng số hệ thống
 */

// User roles (khớp với database: roles table)
if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 1);           // Admin - Quản trị hệ thống toàn quyền
    define('ROLE_SALES_STAFF', 2);     // Sales Staff - Nhân viên bán hàng
    define('ROLE_WAREHOUSE_MANAGER', 3); // Warehouse Manager - Quản lý kho
    define('ROLE_OWNER', 5);           // Owner - Chủ tiệm/Chủ cửa hàng
}

// User status
define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 0);

// Order status
define('ORDER_PENDING', 'pending');
define('ORDER_PROCESSING', 'processing');
define('ORDER_COMPLETED', 'completed');
define('ORDER_CANCELLED', 'cancelled');

// App config
define('APP_DEBUG', true);
define('APP_ENV', 'development');

// Pagination
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// TODO: Thêm các hằng số khác
