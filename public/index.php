<?php

/**
 * index.php - Entry point của ứng dụng
 * 
 * Chức năng:
 * - Load Composer autoload
 * - Khởi tạo Bootstrap
 * - Chạy ứng dụng
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Load Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

// Khởi tạo và chạy ứng dụng
try {
    $app = new \Core\Bootstrap();
    $app->run();
} catch (\Throwable $e) {
    echo "<h1>Error</h1>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    http_response_code(500);
}

