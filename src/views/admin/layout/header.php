<?php
$user = \Helpers\AuthHelper::user();
?>
<header class="admin-header">
    <button type="button" class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    <div class="header-title">
        <h1><?= \Core\View::e($title ?? 'Dashboard') ?></h1>
    </div>

    <div class="header-user">
        <div class="user-info">
            <div class="user-name"><?= \Core\View::e($user['full_name'] ?? 'Admin') ?></div>
            <div class="user-role">
                <?php if (\Helpers\AuthHelper::isAdmin()): ?>
                    Administrator
                <?php else: ?>
                    User
                <?php endif; ?>
            </div>
        </div>
        <a href="/admin/logout" class="btn btn-secondary btn-sm" onclick="handleLogout(event)">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</header>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            // Save state
            const isCollapsed = sidebar.classList.contains('collapsed');
            try {
                localStorage.setItem('sidebarCollapsed', isCollapsed);
            } catch (e) {
                document.cookie = 'sidebarCollapsed=' + isCollapsed + ';path=/;max-age=31536000';
            }
            console.log('Sidebar toggled:', isCollapsed ? 'collapsed' : 'expanded');
        }
    }

    function handleLogout(event) {
        // Xóa storage
        sessionStorage.clear();
        try {
            localStorage.clear();
        } catch (e) {}
        // Cho phép link chạy bình thường
        return true;
    }
</script>