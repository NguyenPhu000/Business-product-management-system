<?php

/**
 * index.php - Entry point của ứng dụng
 * 
 * Chức năng:
 * - Load Composer autoload
 * - Load cấu hình
 * - Khởi tạo Application
 * - Load routes
 * - Chạy ứng dụng
 */

// Load Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Load cấu hình
$config = require __DIR__ . '/../config/app.php';

// Khởi tạo Application
// $app = new \Core\Application($config);

// Load routes
// require __DIR__ . '/../config/routes.php';

// Chạy ứng dụng
// $app->run();

// TODO: Uncomment code above khi đã implement Core\Application

echo "Business Product Management System";
echo "<br>Entry point is working!";
