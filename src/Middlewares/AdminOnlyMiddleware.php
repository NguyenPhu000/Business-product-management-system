<?php

namespace Middlewares;

use Helpers\AuthHelper;

/**
 * AdminOnlyMiddleware - Chỉ cho phép Admin truy cập
 * Sử dụng cho các chức năng nhạy cảm như Cấu hình hệ thống
 */
class AdminOnlyMiddleware
{
    public function handle(): bool
    {
        // Kiểm tra đã đăng nhập
        if (!AuthHelper::check()) {
            AuthHelper::setFlash('error', 'Vui lòng đăng nhập để tiếp tục');
            header('Location: /admin/login');
            exit;
        }

        // Chỉ cho phép Admin (không cho Chủ tiệm)
        if (!AuthHelper::isAdmin()) {
            http_response_code(403);
            echo "
            <!DOCTYPE html>
            <html lang='vi'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>403 - Forbidden</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                    h1 { font-size: 48px; color: #dc3545; }
                    p { font-size: 18px; color: #666; }
                    a { color: #007bff; text-decoration: none; }
                </style>
            </head>
            <body>
                <h1>403</h1>
                <p>Chỉ Admin mới có quyền truy cập trang này</p>
                <p style='color: #999; font-size: 14px;'>Chức năng Cấu hình hệ thống chỉ dành cho Administrator</p>
                <a href='/admin/dashboard'>← Quay lại Dashboard</a>
            </body>
            </html>
            ";
            exit;
        }

        return true;
    }
}
