<?php

/**
 * routes.php - Định tuyến URL -> Controller/Action
 * 
 * $router được truyền vào từ Bootstrap
 * @var \Core\Router $router
 */

use Middlewares\AuthMiddleware;
use Middlewares\RoleMiddleware;

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
$router->get('/admin/roles/create', 'Admin\RolesController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/roles/store', 'Admin\RolesController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/roles/edit/{id}', 'Admin\RolesController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/roles/update/{id}', 'Admin\RolesController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/roles/delete/{id}', 'Admin\RolesController@delete', [AuthMiddleware::class, RoleMiddleware::class]);

// Logs Management
$router->get('/admin/logs', 'Admin\LogsController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/cleanup', 'Admin\LogsController@cleanup', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/update/{id}', 'Admin\LogsController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/logs/delete/{id}', 'Admin\LogsController@delete', [AuthMiddleware::class, RoleMiddleware::class]);

// System Config
$router->get('/admin/config', 'Admin\ConfigController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/config/store', 'Admin\ConfigController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/config/update', 'Admin\ConfigController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/config/delete', 'Admin\ConfigController@delete', [AuthMiddleware::class, RoleMiddleware::class]);

// Password Reset Management
$router->get('/admin/password-reset', 'Admin\PasswordResetController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/password-reset/check-new', 'Admin\PasswordResetController@checkNew', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/password-reset/approve/{id}', 'Admin\PasswordResetController@approve', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/password-reset/reject/{id}', 'Admin\PasswordResetController@reject', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/password-reset/delete/{id}', 'Admin\PasswordResetController@delete', [AuthMiddleware::class, RoleMiddleware::class]);

// ============ CATEGORY MANAGEMENT ROUTES ============

// Categories
$router->get('/admin/categories', 'Admin\CategoryController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/categories/create', 'Admin\CategoryController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/categories/store', 'Admin\CategoryController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/categories/edit/{id}', 'Admin\CategoryController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/categories/update/{id}', 'Admin\CategoryController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/categories/delete/{id}', 'Admin\CategoryController@delete', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/categories/toggle-active/{id}', 'Admin\CategoryController@toggleActive', [AuthMiddleware::class, RoleMiddleware::class]);

// Brands
$router->get('/admin/brands', 'Admin\BrandController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/brands/create', 'Admin\BrandController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/brands/store', 'Admin\BrandController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/brands/edit/{id}', 'Admin\BrandController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/brands/update/{id}', 'Admin\BrandController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/brands/delete/{id}', 'Admin\BrandController@delete', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/brands/toggle-active/{id}', 'Admin\BrandController@toggleActive', [AuthMiddleware::class, RoleMiddleware::class]);

// Suppliers
$router->get('/admin/suppliers', 'Admin\SupplierController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/suppliers/create', 'Admin\SupplierController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/suppliers/store', 'Admin\SupplierController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/suppliers/edit/{id}', 'Admin\SupplierController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/suppliers/update/{id}', 'Admin\SupplierController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/suppliers/delete/{id}', 'Admin\SupplierController@delete', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/suppliers/detail/{id}', 'Admin\SupplierController@detail', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/suppliers/toggle-active/{id}', 'Admin\SupplierController@toggleActive', [AuthMiddleware::class, RoleMiddleware::class]);

// ============ CATEGORY MANAGEMENT (Protected) ============

// Categories - Quản lý danh mục sản phẩm
$router->get('/admin/categories', 'Admin\CategoryController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/categories/create', 'Admin\CategoryController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/categories/store', 'Admin\CategoryController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/categories/edit/{id}', 'Admin\CategoryController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/categories/update/{id}', 'Admin\CategoryController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/categories/delete/{id}', 'Admin\CategoryController@delete', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/categories/toggle-active/{id}', 'Admin\CategoryController@toggleActive', [AuthMiddleware::class, RoleMiddleware::class]);

// ============ BRAND MANAGEMENT (Protected) ============

// Brands - Quản lý thương hiệu
$router->get('/admin/brands', 'Admin\BrandController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/brands/create', 'Admin\BrandController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/brands/store', 'Admin\BrandController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/brands/edit/{id}', 'Admin\BrandController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/brands/update/{id}', 'Admin\BrandController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/brands/delete/{id}', 'Admin\BrandController@delete', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/brands/toggle-active/{id}', 'Admin\BrandController@toggleActive', [AuthMiddleware::class, RoleMiddleware::class]);

// ============ SUPPLIER MANAGEMENT (Protected) ============

// Suppliers - Quản lý nhà cung cấp
$router->get('/admin/suppliers', 'Admin\SupplierController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/suppliers/create', 'Admin\SupplierController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/suppliers/store', 'Admin\SupplierController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/suppliers/edit/{id}', 'Admin\SupplierController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/suppliers/update/{id}', 'Admin\SupplierController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/suppliers/delete/{id}', 'Admin\SupplierController@delete', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/suppliers/detail/{id}', 'Admin\SupplierController@detail', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/suppliers/toggle-active/{id}', 'Admin\SupplierController@toggleActive', [AuthMiddleware::class, RoleMiddleware::class]);

// ============ PRODUCT MANAGEMENT (Protected) ============

// Product CRUD
$router->get('/admin/products', 'Admin\ProductController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/products/create', 'Admin\ProductController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/store', 'Admin\ProductController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/products/{id}/edit', 'Admin\ProductController@edit', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/{id}/update', 'Admin\ProductController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/{id}/delete', 'Admin\ProductController@destroy', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/{id}/toggle', 'Admin\ProductController@toggle', [AuthMiddleware::class, RoleMiddleware::class]);

// Product Images
$router->post('/admin/products/delete-image', 'Admin\ProductController@deleteImage', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/set-primary-image', 'Admin\ProductController@setPrimaryImage', [AuthMiddleware::class, RoleMiddleware::class]);

// Product Variants - Quản lý biến thể
$router->get('/admin/products/{id}/variants', 'Admin\ProductVariantController@index', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/products/{id}/variants/create', 'Admin\ProductVariantController@create', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/{id}/variants/store', 'Admin\ProductVariantController@store', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/{productId}/variants/{variantId}/delete', 'Admin\ProductVariantController@destroy', [AuthMiddleware::class, RoleMiddleware::class]);

// ============ PRODUCT-CATEGORY MANAGEMENT (Protected) ============

// Product-Category Relations - Gán sản phẩm vào nhiều danh mục
$router->get('/admin/products/manage-categories/{productId}', 'Admin\ProductCategoryController@manage', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/manage-categories/{productId}', 'Admin\ProductCategoryController@update', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/add-to-category', 'Admin\ProductCategoryController@addToCategory', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/products/remove-from-category', 'Admin\ProductCategoryController@removeFromCategory', [AuthMiddleware::class, RoleMiddleware::class]);
