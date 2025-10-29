<?php

/**
 * constants.php - Hằng số hệ thống
 */

// User roles
define('ROLE_ADMIN', 1);
define('ROLE_MANAGER', 2);
define('ROLE_STAFF', 3);

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
