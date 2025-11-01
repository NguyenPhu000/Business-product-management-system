<div class="page-header">
    <h2 class="page-title">Log hoạt động</h2>
</div>

<!-- Filter -->
<div class="card">
    <div class="card-body">
        <form action="/admin/logs" method="GET" style="display: flex; gap: 15px; align-items: end;">
            <div class="form-group" style="flex: 1; margin: 0;">
                <label class="form-label">Người dùng</label>
                <select name="user_id" class="form-control">
                    <option value="">-- Tất cả --</option>
                    <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <option value="<?= $user['id'] ?>" <?= ($currentUserId == $user['id']) ? 'selected' : '' ?>>
                        <?= \Core\View::e($user['username']) ?> - <?= \Core\View::e($user['full_name']) ?>
                    </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <div class="form-group" style="flex: 1; margin: 0;">
                <label class="form-label">Hành động</label>
                <select name="action" class="form-control">
                    <option value="">-- Tất cả --</option>
                    <?php if (!empty($actions)): ?>
                    <?php foreach ($actions as $key => $label): ?>
                    <option value="<?= $key ?>" <?= ($currentAction == $key) ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-filter"></i> Lọc
            </button>
            <a href="/admin/logs" class="btn btn-secondary">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

<!-- Log table -->
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vai trò</th>
                    <th>Người dùng</th>
                    <th>Hành động</th>
                    <th>Đối tượng</th>
                    <th>IP</th>
                    <th>Thời gian</th>
                    <?php if (\Helpers\AuthHelper::isAdmin()): ?>
                    <th>Thao tác</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= $log['id'] ?></td>
                    <td>
                        <?php if (!empty($log['role_name'])): ?>
                        <span class="badge badge-primary"><?= \Core\View::e($log['role_name']) ?></span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= \Core\View::e($log['username'] ?? '-') ?></td>
                    <td>
                        <?php
                                $badgeClass = 'badge-info';
                                $action = strtolower($log['action']);

                                if (strpos($action, 'delete') !== false) {
                                    $badgeClass = 'badge-danger';
                                } elseif (strpos($action, 'create') !== false) {
                                    $badgeClass = 'badge-success';
                                } elseif (strpos($action, 'update') !== false || strpos($action, 'edit') !== false) {
                                    $badgeClass = 'badge-warning';
                                } elseif (strpos($action, 'login') !== false) {
                                    $badgeClass = 'badge-info';
                                } elseif (strpos($action, 'logout') !== false) {
                                    $badgeClass = 'badge-secondary';
                                }
                                ?>
                        <span class="badge <?= $badgeClass ?>"><?= \Core\View::e($log['action']) ?></span>
                    </td>
                    <td>
                        <?php if ($log['object_type'] === 'user' && !empty($log['target_username'])): ?>
                        <?= \Core\View::e($log['target_username']) ?>
                        <?php elseif ($log['object_type'] === 'role'): ?>
                        <span class="text-muted">-</span>
                        <?php elseif ($log['object_type']): ?>
                        <span class="text-muted">
                            <?= \Core\View::e($log['object_type']) ?>
                            <?php if ($log['object_id']): ?>
                            #<?= $log['object_id'] ?>
                            <?php endif; ?>
                        </span>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($log['ip']): ?>
                        <code style="font-size: 13px; background: #f3f4f6; padding: 4px 8px; border-radius: 4px;">
                                        <?= \Core\View::e($log['ip']) ?>
                                    </code>
                        <?php else: ?>
                        <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= \Helpers\FormatHelper::datetime($log['created_at']) ?></td>
                    <?php if (\Helpers\AuthHelper::isAdmin()): ?>
                    <td>
                        <button onclick="editLog(<?= $log['id'] ?>, '<?= \Core\View::e($log['action']) ?>')"
                            class="btn btn-warning btn-sm" title="Sửa">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deleteLog(<?= $log['id'] ?>)" class="btn btn-danger btn-sm" title="Xóa">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="<?= \Helpers\AuthHelper::isAdmin() ? '7' : '6' ?>" style="text-align: center;">Chưa có
                        log nào</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if (isset($pagination) && $pagination['totalPages'] > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
            <a href="/admin/logs?page=<?= $i ?>&user_id=<?= $currentUserId ?? '' ?>&action=<?= $currentAction ?? '' ?>"
                class="<?= $i == $pagination['page'] ? 'active' : '' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function editLog(logId, currentAction) {
    const newAction = prompt('Nhập hành động mới:', currentAction);
    if (!newAction || newAction === currentAction) {
        return;
    }

    fetch('/admin/logs/update/' + logId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: newAction
            })
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

function deleteLog(logId) {
    if (!confirm('Bạn có chắc chắn muốn xóa log này?')) {
        return;
    }

    fetch('/admin/logs/delete/' + logId, {
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