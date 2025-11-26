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
$router->post('/admin/password-reset/mark-completed/{id}', 'Modules\Auth\Controllers\PasswordResetController@markCompleted', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
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

// Product Variants Management (Tích hợp với Inventory)
$router->get('/admin/products/{id}/variants', 'Modules\Product\Controllers\VariantController@index', [AuthMiddleware::class]);
$router->get('/admin/products/{id}/variants/create', 'Modules\Product\Controllers\VariantController@create', [AuthMiddleware::class]);
$router->post('/admin/products/{id}/variants/store', 'Modules\Product\Controllers\VariantController@store', [AuthMiddleware::class]);
$router->get('/admin/products/{id}/variants/{variantId}/edit', 'Modules\Product\Controllers\VariantController@edit', [AuthMiddleware::class]);
$router->post('/admin/products/{id}/variants/{variantId}/update', 'Modules\Product\Controllers\VariantController@update', [AuthMiddleware::class]);
$router->post('/admin/products/{id}/variants/{variantId}/delete', 'Modules\Product\Controllers\VariantController@delete', [AuthMiddleware::class]);
$router->post('/admin/products/{id}/variants/{variantId}/toggle', 'Modules\Product\Controllers\VariantController@toggle', [AuthMiddleware::class]);

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

// Purchase (Import) - tạo phiếu nhập
$router->get('/admin/purchase/create', 'Modules\Purchase\Controllers\PurchaseController@create', [AuthMiddleware::class]);
$router->post('/admin/purchase/store', 'Modules\Purchase\Controllers\PurchaseController@store', [AuthMiddleware::class]);

// Sales (Export) - tạo phiếu xuất
$router->get('/admin/sales/create', 'Modules\Sales\Controllers\SalesController@create', [AuthMiddleware::class]);
$router->post('/admin/sales/store', 'Modules\Sales\Controllers\SalesController@store', [AuthMiddleware::class]);

// ============ INVENTORY ROUTES (Quản lý kho hàng) ============

// Inventory List & Low Stock Alerts
$router->get('/admin/inventory', 'Modules\Inventory\Controllers\InventoryController@index', [AuthMiddleware::class]);
$router->get('/admin/inventory/low-stock', 'Modules\Inventory\Controllers\InventoryController@lowStock', [AuthMiddleware::class]);

// Stock Detail & Adjustment
$router->get('/admin/inventory/detail/{id}', 'Modules\Inventory\Controllers\InventoryController@detail', [AuthMiddleware::class]);
$router->get('/admin/inventory/adjust/{id}', 'Modules\Inventory\Controllers\InventoryController@adjustForm', [AuthMiddleware::class]);
$router->post('/admin/inventory/adjust', 'Modules\Inventory\Controllers\InventoryController@adjust', [AuthMiddleware::class]);

// Transaction History
$router->get('/admin/inventory/history', 'Modules\Inventory\Controllers\InventoryController@history', [AuthMiddleware::class]);

// Stock Operations
$router->post('/admin/inventory/import', 'Modules\Inventory\Controllers\InventoryController@import', [AuthMiddleware::class]);
$router->post('/admin/inventory/export', 'Modules\Inventory\Controllers\InventoryController@export', [AuthMiddleware::class]);
$router->post('/admin/inventory/transfer', 'Modules\Inventory\Controllers\InventoryController@transfer', [AuthMiddleware::class]);

// Threshold Update
$router->post('/admin/inventory/threshold/{id}', 'Modules\Inventory\Controllers\InventoryController@updateThreshold', [AuthMiddleware::class]);

// Reports
$router->get('/admin/inventory/report', 'Modules\Inventory\Controllers\InventoryController@exportReport', [AuthMiddleware::class]);

// ============ REPORT ROUTES (Báo Cáo & Thống Kê) ============

// Report Dashboard - Redirect to main company dashboard
$router->get('/admin/reports', 'Modules\Report\Controllers\ReportController@dashboard', [AuthMiddleware::class, RoleMiddleware::class]);

// Inventory Reports (5.1 - Báo Cáo Tồn Kho)
$router->get('/admin/reports/inventory-over-time', 'Modules\Report\Controllers\ReportController@inventoryOverTime', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/reports/stock-at-date', 'Modules\Report\Controllers\ReportController@stockAtDate', [AuthMiddleware::class, RoleMiddleware::class]);
$router->post('/admin/reports/product-stock-history', 'Modules\Report\Controllers\ReportController@productStockHistory', [AuthMiddleware::class, RoleMiddleware::class]);

// Sales & Profit Reports (5.2 - Báo Cáo Doanh Thu & Lợi Nhuận)
$router->get('/admin/reports/sales', 'Modules\Report\Controllers\ReportController@salesReport', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/reports/profit', 'Modules\Report\Controllers\ReportController@profitReport', [AuthMiddleware::class, RoleMiddleware::class]);

// Top Products Reports (5.3 - Báo Cáo Top Sản Phẩm)
$router->get('/admin/reports/top-selling', 'Modules\Report\Controllers\ReportController@topSellingProducts', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/reports/slow-moving', 'Modules\Report\Controllers\ReportController@slowMovingInventory', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/reports/dead-stock', 'Modules\Report\Controllers\ReportController@deadStock', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/reports/high-value', 'Modules\Report\Controllers\ReportController@highValueProducts', [AuthMiddleware::class, RoleMiddleware::class]);
$router->get('/admin/reports/top-profit', 'Modules\Report\Controllers\ReportController@topProfitProducts', [AuthMiddleware::class, RoleMiddleware::class]);
