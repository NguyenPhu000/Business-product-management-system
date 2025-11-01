<?php

/**
 * Script tạo user test với role "Chủ tiệm"
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

    // Thông tin user test
    $username = 'chutiem';
    $email = 'chutiem@example.com';
    $password = '123456';
    $fullName = 'Chủ Tiệm Test';
    $phone = '0123456789';
    $roleId = 5; // Chủ tiệm

    // Kiểm tra user đã tồn tại chưa
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        echo "User đã tồn tại:\n";
        echo "ID: {$existing['id']}\n";
        echo "Username: {$existing['username']}\n";
        echo "Email: {$existing['email']}\n";
        echo "Role ID: {$existing['role_id']}\n";

        // Cập nhật role nếu cần
        if ($existing['role_id'] != $roleId) {
            $stmt = $pdo->prepare("UPDATE users SET role_id = ? WHERE id = ?");
            $stmt->execute([$roleId, $existing['id']]);
            echo "\n✅ Đã cập nhật role thành Chủ tiệm (ID: 5)\n";
        }
    } else {
        // Tạo user mới
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            INSERT INTO users (username, email, password_hash, role_id, full_name, phone, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, 1, NOW(), NOW())
        ");
        $stmt->execute([$username, $email, $passwordHash, $roleId, $fullName, $phone]);

        echo "✅ Đã tạo user Chủ tiệm thành công!\n\n";
        echo "Thông tin đăng nhập:\n";
        echo "Username: $username\n";
        echo "Email: $email\n";
        echo "Password: $password\n";
        echo "Role: Chủ tiệm (ID: 5)\n";
    }

    echo "\n--- Danh sách tất cả users ---\n";
    $stmt = $pdo->query("
        SELECT u.*, r.name as role_name 
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        ORDER BY u.id
    ");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($users as $user) {
        echo "\nID: {$user['id']}\n";
        echo "Username: {$user['username']}\n";
        echo "Email: {$user['email']}\n";
        echo "Full Name: {$user['full_name']}\n";
        echo "Role: {$user['role_name']} (ID: {$user['role_id']})\n";
        echo str_repeat('-', 50) . "\n";
    }
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
