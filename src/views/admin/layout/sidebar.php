<?php
$user = \Helpers\AuthHelper::user();
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$isCompanyMenuActive = str_starts_with($currentPath, '/admin/dashboard') ||
    str_starts_with($currentPath, '/admin/users') ||
    str_starts_with($currentPath, '/admin/roles') ||
    str_starts_with($currentPath, '/admin/logs') ||
    str_starts_with($currentPath, '/admin/config') ||
    str_starts_with($currentPath, '/admin/password-reset');

$isCategoryMenuActive = str_starts_with($currentPath, '/admin/categories') ||
    str_starts_with($currentPath, '/admin/brands') ||
    str_starts_with($currentPath, '/admin/suppliers');

$isProductMenuActive = str_starts_with($currentPath, '/admin/products');
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

        <!-- Menu Danh mục sản phẩm -->
        <?php if (\Helpers\AuthHelper::isAdminOrOwner()): ?>
        <li class="menu-item-has-children <?= $isCategoryMenuActive ? 'active' : '' ?>">
            <input type="checkbox" id="category-menu-toggle" class="menu-toggle"
                <?= $isCategoryMenuActive ? 'checked' : '' ?>>
            <label for="category-menu-toggle" class="menu-label">
                <i class="fas fa-folder-tree"></i>
                <span>Danh mục sản phẩm</span>
                <i class="fas fa-chevron-down toggle-icon"></i>
            </label>
            <ul class="submenu">
                <li>
                    <a href="/admin/categories"
                        class="<?= str_starts_with($currentPath, '/admin/categories') ? 'active' : '' ?>">
                        <i class="fas fa-folder"></i>
                        <span>Danh mục</span>
                    </a>
                </li>

                <li>
                    <a href="/admin/brands"
                        class="<?= str_starts_with($currentPath, '/admin/brands') ? 'active' : '' ?>">
                        <i class="fas fa-tag"></i>
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
        <?php endif; ?>

        <!-- Menu Sản phẩm -->
        <?php if (\Helpers\AuthHelper::isAdminOrOwner()): ?>
        <li>
            <a href="/admin/products" class="<?= $isProductMenuActive ? 'active' : '' ?>">
                <i class="fas fa-box"></i>
                <span>Sản phẩm</span>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</aside>