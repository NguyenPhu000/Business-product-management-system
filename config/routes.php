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
$router->get('/admin/login', 'Modules\Auth\Controllers\AuthController@showLogin');
$router->post('/admin/login', 'Modules\Auth\Controllers\AuthController@login');
$router->get('/admin/logout', 'Modules\Auth\Controllers\AuthController@logout');
$router->get('/forgot-password', 'Modules\Auth\Controllers\AuthController@showForgotPassword');
$router->post('/forgot-password', 'Modules\Auth\Controllers\AuthController@forgotPassword');
$router->post('/forgot-password/cancel-request', 'Modules\Auth\Controllers\AuthController@cancelRequest');
$router->get('/reset-password-form', 'Modules\Auth\Controllers\AuthController@showResetPasswordForm');
$router->get('/forgot-password/check-approval/{userId}', 'Modules\Auth\Controllers\AuthController@checkApproval');
$router->post('/check-request-status', 'Modules\Auth\Controllers\AuthController@checkRequestStatus');

// ============ ADMIN ROUTES (Protected) ============

// Dashboard
$router->get('/admin/dashboard', 'Modules\Dashboard\Controllers\DashboardController@index', [AuthMiddleware::class]);

// Users Management
$router->get('/admin/users', 'Modules\User\Controllers\UsersController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/users/create', 'Modules\User\Controllers\UsersController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/users/store', 'Modules\User\Controllers\UsersController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/users/edit/{id}', 'Modules\User\Controllers\UsersController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/users/update/{id}', 'Modules\User\Controllers\UsersController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/users/delete/{id}', 'Modules\User\Controllers\UsersController@delete', [AuthMiddleware::class, RoleMiddleware::class]);

// Roles Management
$router->get('/admin/roles', 'Modules\Auth\Controllers\RolesController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/roles/edit/{id}', 'Modules\Auth\Controllers\RolesController@edit', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/roles/update/{id}', 'Modules\Auth\Controllers\RolesController@update', [AuthMiddleware::class, AdminOnlyMiddleware::class]);

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
$router->get('/admin/password-reset', 'Modules\Auth\Controllers\PasswordResetController@index', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/password-reset/check-new', 'Modules\Auth\Controllers\PasswordResetController@checkNew', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/password-reset/check-cancelled', 'Modules\Auth\Controllers\PasswordResetController@checkCancelled', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/password-reset/approve/{id}', 'Modules\Auth\Controllers\PasswordResetController@approve', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/password-reset/reject/{id}', 'Modules\Auth\Controllers\PasswordResetController@reject', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/password-reset/delete/{id}', 'Modules\Auth\Controllers\PasswordResetController@delete', [AuthMiddleware::class, AdminOnlyMiddleware::class]);

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
