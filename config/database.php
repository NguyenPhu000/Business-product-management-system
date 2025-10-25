<?php

/**
 * database.php - Cấu hình kết nối database
 */

return [
    'driver' => 'mysql',
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'business_product_management_system',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',

    // TODO: Sử dụng biến môi trường từ .env thay vì hardcode
];
