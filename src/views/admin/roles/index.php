<div class="page-header">
    <h2 class="page-title">Quản lý vai trò</h2>
    <a href="/admin/roles/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm vai trò
    </a>
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
                                <a href="/admin/roles/edit/<?= $role['id'] ?>" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i> Sửa
                                </a>
                                <?php if ($role['id'] != 1): // Không cho xóa Admin role 
                                ?>
                                    <button onclick="deleteRole(<?= $role['id'] ?>)" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
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

<script>
    function deleteRole(roleId) {
        if (!confirm('Bạn có chắc chắn muốn xóa vai trò này?')) {
            return;
        }

        fetch('/admin/roles/delete/' + roleId, {
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