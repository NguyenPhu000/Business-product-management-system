<?php
$user = \Helpers\AuthHelper::user();
?>
<header class="admin-header">
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
    function handleLogout(event) {
        // Xóa storage
        sessionStorage.clear();
        localStorage.clear();

        // Cho phép link chạy bình thường
        return true;
    }
</script>