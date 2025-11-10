<link rel="stylesheet" href="/assets/css/logs.css">

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
            <!-- Phân trang hiện đại -->
            <div class="modern-pagination-wrapper">
                <div class="pagination-controls">
                    <!-- Nút First và Previous -->
                    <div class="pagination-nav-buttons">
                        <a href="/admin/logs?page=1&user_id=<?= $currentUserId ?? '' ?>&action=<?= $currentAction ?? '' ?>"
                            class="pagination-btn <?= $pagination['page'] == 1 ? 'disabled' : '' ?>" title="Trang đầu">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="/admin/logs?page=<?= max(1, $pagination['page'] - 1) ?>&user_id=<?= $currentUserId ?? '' ?>&action=<?= $currentAction ?? '' ?>"
                            class="pagination-btn <?= $pagination['page'] == 1 ? 'disabled' : '' ?>" title="Trang trước">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </div>

                    <!-- Hiển thị số trang -->
                    <div class="pagination-numbers">
                        <?php
                        // Hiển thị trang đầu
                        if ($pagination['page'] > 3): ?>
                            <a href="/admin/logs?page=1&user_id=<?= $currentUserId ?? '' ?>&action=<?= $currentAction ?? '' ?>"
                                class="page-number">1</a>
                            <?php if ($pagination['page'] > 4): ?>
                                <span class="page-dots">...</span>
                            <?php endif;
                        endif;

                        // Hiển thị các trang gần current
                        $start = max(1, $pagination['page'] - 2);
                        $end = min($pagination['totalPages'], $pagination['page'] + 2);

                        for ($i = $start; $i <= $end; $i++): ?>
                            <a href="/admin/logs?page=<?= $i ?>&user_id=<?= $currentUserId ?? '' ?>&action=<?= $currentAction ?? '' ?>"
                                class="page-number <?= $i == $pagination['page'] ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor;

                        // Hiển thị trang cuối
                        if ($pagination['page'] < $pagination['totalPages'] - 2): ?>
                            <?php if ($pagination['page'] < $pagination['totalPages'] - 3): ?>
                                <span class="page-dots">...</span>
                            <?php endif; ?>
                            <a href="/admin/logs?page=<?= $pagination['totalPages'] ?>&user_id=<?= $currentUserId ?? '' ?>&action=<?= $currentAction ?? '' ?>"
                                class="page-number">
                                <?= $pagination['totalPages'] ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Nút Next và Last -->
                    <div class="pagination-nav-buttons">
                        <a href="/admin/logs?page=<?= min($pagination['totalPages'], $pagination['page'] + 1) ?>&user_id=<?= $currentUserId ?? '' ?>&action=<?= $currentAction ?? '' ?>"
                            class="pagination-btn <?= $pagination['page'] == $pagination['totalPages'] ? 'disabled' : '' ?>"
                            title="Trang sau">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="/admin/logs?page=<?= $pagination['totalPages'] ?>&user_id=<?= $currentUserId ?? '' ?>&action=<?= $currentAction ?? '' ?>"
                            class="pagination-btn <?= $pagination['page'] == $pagination['totalPages'] ? 'disabled' : '' ?>"
                            title="Trang cuối">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </div>

                    <!-- Ô nhập trang -->
                    <div class="pagination-jump">
                        <span class="jump-label">Đến trang:</span>
                        <input type="number" id="jumpToPageLogs" class="jump-input" min="1"
                            max="<?= $pagination['totalPages'] ?>" value="<?= $pagination['page'] ?>"
                            placeholder="<?= $pagination['page'] ?>">
                        <button onclick="jumpToPageLogs()" class="jump-btn" title="Chuyển trang">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Thông tin phân trang -->
                <div class="pagination-info-modern">
                    <span class="info-text">
                        Trang <strong><?= $pagination['page'] ?></strong> /
                        <strong><?= $pagination['totalPages'] ?></strong>
                    </span>
                </div>
            </div>

            <script>
                function jumpToPageLogs() {
                    const page = document.getElementById('jumpToPageLogs').value;
                    const maxPage = <?= $pagination['totalPages'] ?>;
                    const userId = '<?= $currentUserId ?? '' ?>';
                    const action = '<?= $currentAction ?? '' ?>';

                    if (page && page >= 1 && page <= maxPage) {
                        window.location.href = `/admin/logs?page=${page}&user_id=${userId}&action=${action}`;
                    } else {
                        alert('Vui lòng nhập số trang hợp lệ (1 - ' + maxPage + ')');
                    }
                }

                // Enter để jump
                document.getElementById('jumpToPageLogs')?.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        jumpToPageLogs();
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</div>

<script>
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