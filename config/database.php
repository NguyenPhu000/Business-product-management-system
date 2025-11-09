<?php

/**
 * database.php - Cấu hình kết nối database
 *
 * File này sẽ ưu tiên đọc biến từ environment (.env được load trong Bootstrap)
 * Nếu không có biến môi trường tương ứng sẽ dùng giá trị mặc định phù hợp với Laragon.
 */

return [
    'driver' => $_ENV['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: 'mysql',
    'host' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '100.106.99.41',
    'port' => (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306),
    'database' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'business_product_management_system',
    'username' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'dev',
    'password' => $_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '',
    'charset' => $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4',
    'collation' => $_ENV['DB_COLLATION'] ?? getenv('DB_COLLATION') ?: 'utf8mb4_unicode_ci',

    // Ghi chú: Laragon mặc định chạy MySQL trên 127.0.0.1:3306, user root không có password.
    // Nếu Laragon của bạn dùng port khác (ví dụ 3307), hãy chỉnh DB_PORT trong file .env.
];
