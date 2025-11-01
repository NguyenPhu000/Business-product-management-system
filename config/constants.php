<?php

/**
 * constants.php - Hằng số hệ thống
 */

// User roles
define('ROLE_ADMIN', 1);
define('ROLE_SALES_STAFF', 2);
define('ROLE_WAREHOUSE_MANAGER', 3);
define('ROLE_OWNER', 5); // Chủ tiệm - quyền cao hơn Staff nhưng thấp hơn Admin

// Legacy role constants (backward compatibility)
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

// TODO: Thêm các hằng số khác
