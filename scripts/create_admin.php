<?php

/**
 * create_admin.php - Script tạo tài khoản admin
 * 
 * Chạy file này để tạo tài khoản admin mới:
 * php create_admin.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Modules\Auth\Models\UserModel;

echo "=== TẠO TÀI KHOẢN ADMIN ===\n\n";

// Nhập thông tin
echo "Username: ";
$username = trim(fgets(STDIN));

echo "Email: ";
$email = trim(fgets(STDIN));

echo "Họ tên: ";
$fullName = trim(fgets(STDIN));

echo "Mật khẩu: ";
$password = trim(fgets(STDIN));

echo "Số điện thoại (có thể bỏ qua): ";
$phone = trim(fgets(STDIN));

try {
    $userModel = new UserModel();

    // Kiểm tra email đã tồn tại
    if ($userModel->emailExists($email)) {
        die("❌ Lỗi: Email đã tồn tại!\n");
    }

    // Kiểm tra username đã tồn tại
    if ($userModel->usernameExists($username)) {
        die("❌ Lỗi: Username đã tồn tại!\n");
    }

    // Tạo user
    $userId = $userModel->createUser([
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'full_name' => $fullName,
        'phone' => $phone ?: null,
        'id_role' => 1, // Admin role
        'status' => 1   // Active
    ]);

    echo "\n✅ Tạo tài khoản admin thành công!\n";
    echo "User ID: {$userId}\n";
    echo "Username: {$username}\n";
    echo "Email: {$email}\n";
    echo "\nBạn có thể đăng nhập bằng email hoặc username với mật khẩu đã tạo.\n";
} catch (Exception $e) {
    echo "\n❌ Lỗi: " . $e->getMessage() . "\n";
}
