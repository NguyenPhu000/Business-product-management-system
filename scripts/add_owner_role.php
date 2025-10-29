<?php

/**
 * Script thêm role "Chủ tiệm" vào database
 */

require __DIR__ . '/../vendor/autoload.php';

// Load .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
            putenv("{$name}={$value}");
        }
    }
}

$config = require __DIR__ . '/../config/database.php';
$dsn = sprintf(
    "%s:host=%s;port=%s;dbname=%s;charset=%s",
    $config['driver'],
    $config['host'],
    $config['port'],
    $config['database'],
    $config['charset']
);

try {
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    echo "Connected to database successfully.\n\n";

    // Kiểm tra role đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT * FROM roles WHERE id = 4 OR name = 'Chủ tiệm'");
    $stmt->execute();
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo "Role 'Chủ tiệm' đã tồn tại:\n";
        echo "ID: {$existing['id']}\n";
        echo "Name: {$existing['name']}\n";
        echo "Description: {$existing['description']}\n";
    } else {
        // Thêm role mới
        $stmt = $pdo->prepare("
            INSERT INTO roles (id, name, description) 
            VALUES (4, 'Chủ tiệm', 'Quản lý toàn bộ hệ thống trừ cấu hình hệ thống')
        ");
        $stmt->execute();

        echo "✅ Đã thêm role 'Chủ tiệm' thành công!\n";
        echo "ID: 4\n";
        echo "Name: Chủ tiệm\n";
        echo "Description: Quản lý toàn bộ hệ thống trừ cấu hình hệ thống\n";
    }

    echo "\n--- Danh sách tất cả roles ---\n";
    $stmt = $pdo->query("SELECT * FROM roles ORDER BY id");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($roles as $role) {
        echo "\nID: {$role['id']}\n";
        echo "Name: {$role['name']}\n";
        echo "Description: {$role['description']}\n";
        echo str_repeat('-', 50) . "\n";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
