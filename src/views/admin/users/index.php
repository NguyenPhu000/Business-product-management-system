<div class="page-header">
    <h2 class="page-title">Quản lý người dùng</h2>
    <a href="/admin/users/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm người dùng
    </a>
</div>

<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= \Core\View::e($user['username']) ?></td>
                            <td><?= \Core\View::e($user['full_name']) ?></td>
                            <td><?= \Core\View::e($user['email']) ?></td>
                            <td><?= \Core\View::e($user['name'] ?? 'N/A') ?></td>
                            <td><?= \Helpers\FormatHelper::statusBadge($user['status']) ?></td>
                            <td><?= \Helpers\FormatHelper::datetime($user['created_at']) ?></td>
                            <td>
                                <?php
                                // Kiểm tra quyền:
                                // - Có quyền cao hơn HOẶC là chính mình -> Được sửa
                                // - Có quyền cao hơn VÀ không phải chính mình -> Được xóa
                                $canManage = \Helpers\AuthHelper::canManageRole($user['role_id']);
                                $isCurrentUser = ($user['id'] == \Helpers\AuthHelper::id());
                                $canEdit = $canManage || $isCurrentUser;
                                $canDelete = $canManage && !$isCurrentUser;
                                ?>

                                <?php if ($canEdit): ?>
                                    <a href="/admin/users/edit/<?= $user['id'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                <?php endif; ?>

                                <?php if ($canDelete): ?>
                                    <button onclick="deleteUser(<?= $user['id'] ?>)" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                <?php endif; ?>

                                <?php if (!$canEdit && !$canDelete): ?>
                                    <span class="text-muted"><i class="fas fa-lock"></i> Không có quyền</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center;">Chưa có người dùng nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                    <a href="/admin/users?page=<?= $i ?>" class="<?= $i == $pagination['page'] ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function deleteUser(userId) {
        if (!confirm('Bạn có chắc chắn muốn xóa người dùng này?')) {
            return;
        }

        fetch('/admin/users/delete/' + userId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                alert('Có lỗi xảy ra: ' + error);
            });
    }
</script>