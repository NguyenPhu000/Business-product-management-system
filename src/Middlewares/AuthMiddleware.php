<?php

namespace Middlewares;

use Helpers\AuthHelper;

/**
 * AuthMiddleware - Kiểm tra đăng nhập
 */
class AuthMiddleware
{
    public function handle(): bool
    {
        // Thêm headers ngăn cache
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
        header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

        // Kiểm tra session timeout
        if (AuthHelper::checkTimeout()) {
            AuthHelper::setFlash('error', 'Phiên đăng nhập đã hết hạn');
            header('Location: /admin/login');
            exit;
        }

        // Kiểm tra đã đăng nhập chưa
        if (!AuthHelper::check()) {
            AuthHelper::setFlash('error', 'Vui lòng đăng nhập để tiếp tục');
            header('Location: /admin/login');
            exit;
        }

        return true;
    }
}
