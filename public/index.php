<?php

/**
 * index.php - Entry point của ứng dụng
 * 
 * Chức năng:
 * - Load Composer autoload
 * - Khởi tạo Bootstrap
 * - Chạy ứng dụng
 */

// Load Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Khởi tạo và chạy ứng dụng
$app = new \Core\Bootstrap();
$app->run();

