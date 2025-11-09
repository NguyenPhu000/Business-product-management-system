<?php

/**
 * routes.php - Định tuyến URL -> Controller/Action
 * 
 * $router được truyền vào từ Bootstrap
 * @var \Core\Router $router
 */

use Middlewares\AuthMiddleware;
use Middlewares\RoleMiddleware;
use Middlewares\AdminOnlyMiddleware;

// Redirect root to admin login
$router->get('/', function () {
    header('Location: /admin/login');
    exit;
});

// ============ AUTH ROUTES ============
$router->get('/admin/login', 'Admin\AuthController@showLogin');
$router->post('/admin/login', 'Admin\AuthController@login');
$router->get('/admin/logout', 'Admin\AuthController@logout');
$router->get('/forgot-password', 'Admin\AuthController@showForgotPassword');
$router->post('/forgot-password', 'Admin\AuthController@forgotPassword');
$router->post('/forgot-password/cancel-request', 'Admin\AuthController@cancelRequest');
$router->get('/reset-password-form', 'Admin\AuthController@showResetPasswordForm');
$router->get('/forgot-password/check-approval/{userId}', 'Admin\AuthController@checkApproval');
$router->post('/check-request-status', 'Admin\AuthController@checkRequestStatus');

// ============ ADMIN ROUTES (Protected) ============

// Dashboard
$router->get('/admin/dashboard', 'Admin\HomeController@index', [AuthMiddleware::class]);

// Users Management
$router->get('/admin/users', 'Admin\UsersController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/users/create', 'Admin\UsersController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/users/store', 'Admin\UsersController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/users/edit/{id}', 'Admin\UsersController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/users/update/{id}', 'Admin\UsersController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/users/delete/{id}', 'Admin\UsersController@delete', [AuthMiddleware::class, RoleMiddleware::class]);

// Roles Management
$router->get('/admin/roles', 'Admin\RolesController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/roles/edit/{id}', 'Admin\RolesController@edit', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/roles/update/{id}', 'Admin\RolesController@update', [AuthMiddleware::class, AdminOnlyMiddleware::class]);

// Logs Management
$router->get('/admin/logs', 'Admin\LogsController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/cleanup', 'Admin\LogsController@cleanup', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/update/{id}', 'Admin\LogsController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/delete/{id}', 'Admin\LogsController@delete', [AuthMiddleware::class, RoleMiddleware::class]);

// System Config (CHỈ ADMIN - Chủ tiệm KHÔNG được vào)
$router->get('/admin/config', 'Admin\ConfigController@index', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/config/store', 'Admin\ConfigController@store', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/config/update', 'Admin\ConfigController@update', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/config/delete', 'Admin\ConfigController@delete', [AuthMiddleware::class, AdminOnlyMiddleware::class]);

// Password Reset Management (CHỈ ADMIN - Chủ tiệm KHÔNG được vào)
$router->get('/admin/password-reset', 'Admin\PasswordResetController@index', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/password-reset/check-new', 'Admin\PasswordResetController@checkNew', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/password-reset/check-cancelled', 'Admin\PasswordResetController@checkCancelled', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/password-reset/approve/{id}', 'Admin\PasswordResetController@approve', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/password-reset/reject/{id}', 'Admin\PasswordResetController@reject', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/password-reset/delete/{id}', 'Admin\PasswordResetController@delete', [AuthMiddleware::class, AdminOnlyMiddleware::class]);

// ============ PRODUCT ROUTES (Refactored - Using Modules Structure) ============
$router->get('/admin/products', 'Modules\Product\Controllers\ProductController@index', [AuthMiddleware::class]);
$router->get('/admin/products/create', 'Modules\Product\Controllers\ProductController@create', [AuthMiddleware::class]);
$router->post('/admin/products/store', 'Modules\Product\Controllers\ProductController@store', [AuthMiddleware::class]);
$router->get('/admin/products/edit/{id}', 'Modules\Product\Controllers\ProductController@edit', [AuthMiddleware::class]);
$router->post('/admin/products/update/{id}', 'Modules\Product\Controllers\ProductController@update', [AuthMiddleware::class]);
$router->post('/admin/products/delete/{id}', 'Modules\Product\Controllers\ProductController@destroy', [AuthMiddleware::class]);
$router->post('/admin/products/toggle/{id}', 'Modules\Product\Controllers\ProductController@toggle', [AuthMiddleware::class]);
$router->post('/admin/products/delete-image', 'Modules\Product\Controllers\ProductController@deleteImage', [AuthMiddleware::class]);
$router->post('/admin/products/set-primary-image', 'Modules\Product\Controllers\ProductController@setPrimaryImage', [AuthMiddleware::class]);
