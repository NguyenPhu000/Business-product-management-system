<link rel="stylesheet" href="/assets/css/roles.css">

<div class="page-header">
    <h2 class="page-title">Quản lý vai trò</h2>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên vai trò</th>
                    <th>Mô tả</th>
                    <th>Số người dùng</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($roles)): ?>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td><?= $role['id'] ?></td>
                            <td><strong><?= \Core\View::e($role['name']) ?></strong></td>
                            <td><?= \Core\View::e($role['description']) ?></td>
                            <td><?= $role['user_count'] ?? 0 ?> người</td>
                            <td>
                                <?php if (\Helpers\AuthHelper::isAdmin()): ?>
                                    <a href="/admin/roles/edit/<?= $role['id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">Không có quyền</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Chưa có vai trò nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>