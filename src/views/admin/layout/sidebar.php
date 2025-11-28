<?php
$user = \Helpers\AuthHelper::user();
$currentPath = $_SERVER['REQUEST_URI'] ?? '';

// Active menu detection
$isDashboardActive = str_starts_with($currentPath, '/admin/dashboard');
$isSalesMenuActive = str_starts_with($currentPath, '/admin/sales');
$isPurchaseMenuActive = str_starts_with($currentPath, '/admin/purchase');
$isInventoryMenuActive = str_starts_with($currentPath, '/admin/inventory');
$isProductMenuActive = str_starts_with($currentPath, '/admin/products');
$isCategoryMenuActive = str_starts_with($currentPath, '/admin/categories') ||
    str_starts_with($currentPath, '/admin/brands') ||
    str_starts_with($currentPath, '/admin/suppliers');
$isReportMenuActive = str_starts_with($currentPath, '/admin/reports') || $isDashboardActive;
$isCompanyMenuActive = str_starts_with($currentPath, '/admin/users') ||
    str_starts_with($currentPath, '/admin/roles') ||
    str_starts_with($currentPath, '/admin/logs') ||
    str_starts_with($currentPath, '/admin/config') ||
    str_starts_with($currentPath, '/admin/password-reset');

// Get notification counts (TODO: Tích hợp từ database)
$lowStockCount = 0; // TODO: Lấy từ InventoryService
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <i class="fas fa-store"></i>
        <div class="brand-text">
            <h2>BPM System</h2>
            <p class="brand-subtitle">Business Management</p>
        </div>
    </div>

    <ul class="sidebar-menu">
        <?php if (\Helpers\AuthHelper::isAdminOrOwner()): ?>

            <!-- SECTION: REPORTING & ANALYTICS -->
            <li class="menu-section-divider">
                <span>BÁO CÁO & PHÂN TÍCH</span>
            </li>

            <!-- Dashboard -->
            <li>
                <a href="/admin/dashboard" class="<?= $isDashboardActive ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Reports Menu -->
            <li class="menu-item-has-children <?= $isReportMenuActive ? 'active' : '' ?>">
                <input type="checkbox" id="report-menu-toggle" class="menu-toggle"
                    <?= $isReportMenuActive ? 'checked' : '' ?>>
                <label for="report-menu-toggle" class="menu-label">
                    <i class="fas fa-chart-line"></i>
                    <span>Báo cáo chi tiết</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </label>
                <ul class="submenu">
                    <!-- Inventory Reports -->
                    <li class="submenu-section-title">
                        <i class="fas fa-warehouse"></i>
                        <span>Báo cáo kho</span>
                    </li>
                    <li>
                        <a href="/admin/reports/inventory-over-time"
                            class="<?= str_starts_with($currentPath, '/admin/reports/inventory-over-time') ? 'active' : '' ?>">
                            <i class="fas fa-chart-area"></i>
                            <span>Tồn theo thời gian</span>
                        </a>
                    </li>

                    <!-- Sales & Profit Reports -->
                    <li class="submenu-section-title">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Doanh thu & Lợi nhuận</span>
                    </li>
                    <li>
                        <a href="/admin/reports/sales"
                            class="<?= str_starts_with($currentPath, '/admin/reports/sales') ? 'active' : '' ?>">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Doanh thu bán hàng</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/reports/profit"
                            class="<?= str_starts_with($currentPath, '/admin/reports/profit') ? 'active' : '' ?>">
                            <i class="fas fa-chart-pie"></i>
                            <span>Phân tích lợi nhuận</span>
                        </a>
                    </li>

                    <!-- Product Performance -->
                    <li class="submenu-section-title">
                        <i class="fas fa-star"></i>
                        <span>Hiệu suất sản phẩm</span>
                    </li>
                    <li>
                        <a href="/admin/reports/top-products"
                            class="<?= str_starts_with($currentPath, '/admin/reports/top-products') ? 'active' : '' ?>">
                            <i class="fas fa-trophy"></i>
                            <span>Top sản phẩm</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/reports/top-selling"
                            class="<?= str_starts_with($currentPath, '/admin/reports/top-selling') ? 'active' : '' ?>">
                            <i class="fas fa-fire"></i>
                            <span>Bán chạy nhất</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/reports/slow-moving"
                            class="<?= str_starts_with($currentPath, '/admin/reports/slow-moving') ? 'active' : '' ?>">
                            <i class="fas fa-turtle"></i>
                            <span>Bán chậm</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/reports/dead-stock"
                            class="<?= str_starts_with($currentPath, '/admin/reports/dead-stock') ? 'active' : '' ?>">
                            <i class="fas fa-times-circle"></i>
                            <span>Hàng tồn đọng</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- SECTION: BUSINESS OPERATIONS -->
            <li class="menu-section-divider">
                <span>NGHIỆP VỤ KINH DOANH</span>
            </li>

            <!-- Purchase Menu (Nhập hàng) -->
            <li>
                <a href="/admin/purchase/create" class="<?= $isPurchaseMenuActive ? 'active' : '' ?>">
                    <i class="fas fa-cart-plus"></i>
                    <span>Nhập hàng</span>
                </a>
            </li>

            <!-- Sales Menu (Xuất hàng) -->
            <li>
                <a href="/admin/sales/create" class="<?= $isSalesMenuActive ? 'active' : '' ?>">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Xuất hàng</span>
                </a>
            </li>

            <!-- SECTION: INVENTORY MANAGEMENT -->
            <li class="menu-section-divider">
                <span>QUẢN LÝ KHO</span>
            </li>

            <!-- Inventory Menu -->
            <li class="menu-item-has-children <?= $isInventoryMenuActive ? 'active' : '' ?>">
                <input type="checkbox" id="inventory-menu-toggle" class="menu-toggle"
                    <?= $isInventoryMenuActive ? 'checked' : '' ?>>
                <label for="inventory-menu-toggle" class="menu-label">
                    <i class="fas fa-warehouse"></i>
                    <span>Tồn kho</span>
                    <?php if ($lowStockCount > 0): ?>
                        <span class="menu-badge badge-warning"><?= $lowStockCount ?></span>
                    <?php endif; ?>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </label>
                <ul class="submenu">
                    <li>
                        <a href="/admin/inventory"
                            class="<?= $currentPath === '/admin/inventory' || str_starts_with($currentPath, '/admin/inventory/detail') || str_starts_with($currentPath, '/admin/inventory/adjust') ? 'active' : '' ?>">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Kiểm kho</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/inventory/low-stock"
                            class="<?= str_starts_with($currentPath, '/admin/inventory/low-stock') ? 'active' : '' ?>">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Cảnh báo tồn kho</span>
                            <?php if ($lowStockCount > 0): ?>
                                <span class="submenu-badge badge-warning"><?= $lowStockCount ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/inventory/history"
                            class="<?= str_starts_with($currentPath, '/admin/inventory/history') ? 'active' : '' ?>">
                            <i class="fas fa-history"></i>
                            <span>Lịch sử giao dịch</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- SECTION: PRODUCT MANAGEMENT -->
            <li class="menu-section-divider">
                <span>QUẢN LÝ SẢN PHẨM</span>
            </li>

            <!-- Products Menu -->
            <li class="menu-item-has-children <?= $isProductMenuActive ? 'active' : '' ?>">
                <input type="checkbox" id="product-menu-toggle" class="menu-toggle"
                    <?= $isProductMenuActive ? 'checked' : '' ?>>
                <label for="product-menu-toggle" class="menu-label">
                    <i class="fas fa-box-open"></i>
                    <span>Sản phẩm</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </label>
                <ul class="submenu">
                    <li>
                        <a href="/admin/products"
                            class="<?= str_starts_with($currentPath, '/admin/products') ? 'active' : '' ?>">
                            <i class="fas fa-list"></i>
                            <span>Danh sách sản phẩm</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/products/create"
                            class="<?= str_starts_with($currentPath, '/admin/products/create') ? 'active' : '' ?>">
                            <i class="fas fa-plus-circle"></i>
                            <span>Thêm sản phẩm mới</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- Categories Menu -->
            <li class="menu-item-has-children <?= $isCategoryMenuActive ? 'active' : '' ?>">
                <input type="checkbox" id="category-menu-toggle" class="menu-toggle"
                    <?= $isCategoryMenuActive ? 'checked' : '' ?>>
                <label for="category-menu-toggle" class="menu-label">
                    <i class="fas fa-tags"></i>
                    <span>Danh mục & Thương hiệu</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </label>
                <ul class="submenu">
                    <li>
                        <a href="/admin/categories"
                            class="<?= str_starts_with($currentPath, '/admin/categories') ? 'active' : '' ?>">
                            <i class="fas fa-folder-open"></i>
                            <span>Danh mục sản phẩm</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/brands"
                            class="<?= str_starts_with($currentPath, '/admin/brands') ? 'active' : '' ?>">
                            <i class="fas fa-certificate"></i>
                            <span>Thương hiệu</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/suppliers"
                            class="<?= str_starts_with($currentPath, '/admin/suppliers') ? 'active' : '' ?>">
                            <i class="fas fa-truck"></i>
                            <span>Nhà cung cấp</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- SECTION: SYSTEM MANAGEMENT -->
            <li class="menu-section-divider">
                <span>QUẢN TRỊ HỆ THỐNG</span>
            </li>

            <!-- Company Management Menu -->
            <li class="menu-item-has-children <?= $isCompanyMenuActive ? 'active' : '' ?>">
                <input type="checkbox" id="company-menu-toggle" class="menu-toggle"
                    <?= $isCompanyMenuActive ? 'checked' : '' ?>>
                <label for="company-menu-toggle" class="menu-label">
                    <i class="fas fa-building"></i>
                    <span>Quản lý công ty</span>
                    <i class="fas fa-chevron-down toggle-icon"></i>
                </label>
                <ul class="submenu">
                    <li>
                        <a href="/admin/users" class="<?= str_starts_with($currentPath, '/admin/users') ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>
                            <span>Người dùng</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/roles" class="<?= str_starts_with($currentPath, '/admin/roles') ? 'active' : '' ?>">
                            <i class="fas fa-user-tag"></i>
                            <span>Vai trò</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/logs" class="<?= str_starts_with($currentPath, '/admin/logs') ? 'active' : '' ?>">
                            <i class="fas fa-history"></i>
                            <span>Log hoạt động</span>
                        </a>
                    </li>

                    <?php if (\Helpers\AuthHelper::isAdmin()): ?>
                        <!-- CHỈ ADMIN -->
                        <li class="submenu-section-title">
                            <i class="fas fa-shield-alt"></i>
                            <span>Admin Only</span>
                        </li>
                        <li>
                            <a href="/admin/password-reset"
                                class="<?= str_starts_with($currentPath, '/admin/password-reset') ? 'active' : '' ?>">
                                <i class="fas fa-key"></i>
                                <span>Đặt lại mật khẩu</span>
                            </a>
                        </li>
                        <li>
                            <a href="/admin/config"
                                class="<?= str_starts_with($currentPath, '/admin/config') ? 'active' : '' ?>">
                                <i class="fas fa-cog"></i>
                                <span>Cấu hình hệ thống</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </li>

        <?php endif; ?>
    </ul>
</aside>