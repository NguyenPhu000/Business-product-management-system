<?php

/**
 * database.php - Cấu hình kết nối database
 */

return [
    'driver' => 'mysql',
    'host' => '100.106.99.41',
    'port' => 3306,
    'database' => 'business_product_management_system',
    'username' => 'dev',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',

    // TODO: Sử dụng biến môi trường từ .env thay vì hardcode
];
