<?php
$user = \Helpers\AuthHelper::user();
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isCompanyMenuActive = str_starts_with($currentPath, '/admin/dashboard') ||
    str_starts_with($currentPath, '/admin/users') ||
    str_starts_with($currentPath, '/admin/roles') ||
    str_starts_with($currentPath, '/admin/logs') ||
    str_starts_with($currentPath, '/admin/config') ||
    str_starts_with($currentPath, '/admin/password-reset');
$isProductMenuActive = str_starts_with($currentPath, '/admin/products');
$isCategoryMenuActive = str_starts_with($currentPath, '/admin/categories') ||
    str_starts_with($currentPath, '/admin/brands') ||
    str_starts_with($currentPath, '/admin/suppliers');
$isInventoryMenuActive = str_starts_with($currentPath, '/admin/inventory');
$isReportMenuActive = str_starts_with($currentPath, '/admin/reports');
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand">
        <h2><i class="fas fa-box"></i> BPM System</h2>
    </div>

    <ul class="sidebar-menu">
        <?php if (\Helpers\AuthHelper::isAdminOrOwner()): ?>
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
                        <a href="/admin/dashboard"
                            class="<?= str_starts_with($currentPath, '/admin/dashboard') ? 'active' : '' ?>">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="/admin/users" class="<?= str_starts_with($currentPath, '/admin/users') ? 'active' : '' ?>">
                            <i class="fas fa-users"></i>
                            <span>Quản lý người dùng</span>
                        </a>
                    </li>

                    <li>
                        <a href="/admin/roles" class="<?= str_starts_with($currentPath, '/admin/roles') ? 'active' : '' ?>">
                            <i class="fas fa-user-tag"></i>
                            <span>Quản lý vai trò</span>
                        </a>
                    </li>

                    <li>
                        <a href="/admin/logs" class="<?= str_starts_with($currentPath, '/admin/logs') ? 'active' : '' ?>">
                            <i class="fas fa-history"></i>
                            <span>Log hoạt động</span>
                        </a>
                    </li>

                    <?php if (\Helpers\AuthHelper::isAdmin()): ?>
                        <!-- CHỈ ADMIN - Chủ tiệm KHÔNG hiển thị menu này -->
                        <li>
                            <a href="/admin/password-reset"
                                class="<?= str_starts_with($currentPath, '/admin/password-reset') ? 'active' : '' ?>">
                                <i class="fas fa-key"></i>
                                <span>Yêu cầu đặt lại MK</span>
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

        <!-- Products Menu -->
        <li class="menu-item-has-children <?= $isProductMenuActive ? 'active' : '' ?>">
            <input type="checkbox" id="product-menu-toggle" class="menu-toggle"
                <?= $isProductMenuActive ? 'checked' : '' ?>>
            <label for="product-menu-toggle" class="menu-label">
                <i class="fas fa-box"></i>
                <span>Quản lý sản phẩm</span>
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
            </ul>
        </li>

        <!-- Categories Menu (Categories, Brands, Suppliers) -->
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
                        <i class="fas fa-folder"></i>
                        <span>Danh mục sản phẩm</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/brands"
                        class="<?= str_starts_with($currentPath, '/admin/brands') ? 'active' : '' ?>">
                        <i class="fas fa-star"></i>
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

        <!-- Inventory Menu (Quản lý kho) -->
        <li class="menu-item-has-children <?= $isInventoryMenuActive ? 'active' : '' ?>">
            <input type="checkbox" id="inventory-menu-toggle" class="menu-toggle"
                <?= $isInventoryMenuActive ? 'checked' : '' ?>>
            <label for="inventory-menu-toggle" class="menu-label">
                <i class="fas fa-warehouse"></i>
                <span>Quản lý kho hàng</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </label>
            <ul class="submenu">
                <li>
                    <a href="/admin/inventory"
                        class="<?= $currentPath === '/admin/inventory' || str_starts_with($currentPath, '/admin/inventory/detail') || str_starts_with($currentPath, '/admin/inventory/adjust') ? 'active' : '' ?>">
                        <i class="fas fa-boxes"></i>
                        <span>Tồn kho</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/inventory/low-stock"
                        class="<?= str_starts_with($currentPath, '/admin/inventory/low-stock') ? 'active' : '' ?>">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Cảnh báo tồn kho</span>
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

        <!-- Reports Menu (Báo Cáo & Thống Kê) -->
        <li class="menu-item-has-children <?= $isReportMenuActive ? 'active' : '' ?>">
            <input type="checkbox" id="report-menu-toggle" class="menu-toggle"
                <?= $isReportMenuActive ? 'checked' : '' ?>>
            <label for="report-menu-toggle" class="menu-label">
                <i class="fas fa-chart-line"></i>
                <span>Báo cáo & Thống kê</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </label>
            <ul class="submenu">
                <li>
                    <a href="/admin/reports/inventory-over-time"
                        class="<?= str_starts_with($currentPath, '/admin/reports/inventory-over-time') ? 'active' : '' ?>">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Tồn theo thời gian</span>
                    </a>
                </li>

                <li>
                    <a href="/admin/reports/sales"
                        class="<?= str_starts_with($currentPath, '/admin/reports/sales') ? 'active' : '' ?>">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Doanh thu</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/reports/profit"
                        class="<?= str_starts_with($currentPath, '/admin/reports/profit') ? 'active' : '' ?>">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Lợi nhuận gộp</span>
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
                        <span>Tồn kho lâu</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/reports/dead-stock"
                        class="<?= str_starts_with($currentPath, '/admin/reports/dead-stock') ? 'active' : '' ?>">
                        <i class="fas fa-skull-crossbones"></i>
                        <span>Dead Stock</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/reports/high-value"
                        class="<?= str_starts_with($currentPath, '/admin/reports/high-value') ? 'active' : '' ?>">
                        <i class="fas fa-gem"></i>
                        <span>Giá trị cao</span>
                    </a>
                </li>
                <li>
                    <a href="/admin/reports/top-profit"
                        class="<?= str_starts_with($currentPath, '/admin/reports/top-profit') ? 'active' : '' ?>">
                        <i class="fas fa-award"></i>
                        <span>Lợi nhuận cao</span>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</aside>