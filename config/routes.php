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
$router->get('/admin/logs', 'Modules\System\Controllers\LogsController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/cleanup', 'Modules\System\Controllers\LogsController@cleanup', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/update/{id}', 'Modules\System\Controllers\LogsController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/delete/{id}', 'Modules\System\Controllers\LogsController@delete', [AuthMiddleware::class, RoleMiddleware::class]);

// System Config (CHỈ ADMIN - Chủ tiệm KHÔNG được vào)
$router->get('/admin/config', 'Modules\System\Controllers\ConfigController@index', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/config/store', 'Modules\System\Controllers\ConfigController@store', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/config/update', 'Modules\System\Controllers\ConfigController@update', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/config/delete', 'Modules\System\Controllers\ConfigController@delete', [AuthMiddleware::class, AdminOnlyMiddleware::class]);

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

// ============ CATEGORY ROUTES (Categories, Brands, Suppliers) ============

// Categories Management
$router->get('/admin/categories', 'Modules\Category\Controllers\CategoryController@index', [AuthMiddleware::class]);
$router->get('/admin/categories/create', 'Modules\Category\Controllers\CategoryController@create', [AuthMiddleware::class]);
$router->post('/admin/categories/store', 'Modules\Category\Controllers\CategoryController@store', [AuthMiddleware::class]);
$router->get('/admin/categories/edit/{id}', 'Modules\Category\Controllers\CategoryController@edit', [AuthMiddleware::class]);
$router->post('/admin/categories/update/{id}', 'Modules\Category\Controllers\CategoryController@update', [AuthMiddleware::class]);
$router->post('/admin/categories/delete/{id}', 'Modules\Category\Controllers\CategoryController@destroy', [AuthMiddleware::class]);
$router->post('/admin/categories/toggle/{id}', 'Modules\Category\Controllers\CategoryController@toggle', [AuthMiddleware::class]);

// Brands Management
$router->get('/admin/brands', 'Modules\Category\Controllers\BrandController@index', [AuthMiddleware::class]);
$router->get('/admin/brands/create', 'Modules\Category\Controllers\BrandController@create', [AuthMiddleware::class]);
$router->post('/admin/brands/store', 'Modules\Category\Controllers\BrandController@store', [AuthMiddleware::class]);
$router->get('/admin/brands/edit/{id}', 'Modules\Category\Controllers\BrandController@edit', [AuthMiddleware::class]);
$router->post('/admin/brands/update/{id}', 'Modules\Category\Controllers\BrandController@update', [AuthMiddleware::class]);
$router->post('/admin/brands/delete/{id}', 'Modules\Category\Controllers\BrandController@destroy', [AuthMiddleware::class]);
$router->post('/admin/brands/toggle/{id}', 'Modules\Category\Controllers\BrandController@toggle', [AuthMiddleware::class]);

// Suppliers Management
$router->get('/admin/suppliers', 'Modules\Category\Controllers\SupplierController@index', [AuthMiddleware::class]);
$router->get('/admin/suppliers/create', 'Modules\Category\Controllers\SupplierController@create', [AuthMiddleware::class]);
$router->post('/admin/suppliers/store', 'Modules\Category\Controllers\SupplierController@store', [AuthMiddleware::class]);
$router->get('/admin/suppliers/detail/{id}', 'Modules\Category\Controllers\SupplierController@detail', [AuthMiddleware::class]);
$router->get('/admin/suppliers/edit/{id}', 'Modules\Category\Controllers\SupplierController@edit', [AuthMiddleware::class]);
$router->post('/admin/suppliers/update/{id}', 'Modules\Category\Controllers\SupplierController@update', [AuthMiddleware::class]);
$router->post('/admin/suppliers/delete/{id}', 'Modules\Category\Controllers\SupplierController@destroy', [AuthMiddleware::class]);
$router->post('/admin/suppliers/toggle/{id}', 'Modules\Category\Controllers\SupplierController@toggle', [AuthMiddleware::class]);
