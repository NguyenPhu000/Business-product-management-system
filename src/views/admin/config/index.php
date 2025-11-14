<div class="page-header">
    <h2 class="page-title">Cấu hình hệ thống</h2>
    <button onclick="showAddForm()" class="btn btn-primary">
        <i class="fas fa-plus"></i> Thêm cấu hình
    </button>
</div>

<!-- Form thêm cấu hình -->
<div class="card" id="addForm" style="display: none;">
    <div class="card-header">
        <h3>Thêm cấu hình mới</h3>
    </div>
    <div class="card-body">
        <form action="/admin/config/store" method="POST">
            <div class="row" style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 15px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Key *</label>
                    <input type="text" name="key" class="form-control" required>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label class="form-label">Value *</label>
                    <input type="text" name="value" class="form-control" required>
                </div>
                <div>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Lưu
                    </button>
                    <button type="button" onclick="hideAddForm()" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Danh sách cấu hình -->
<div class="card">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Key</th>
                    <th>Value</th>
                    <th>Người cập nhật</th>
                    <th>Thời gian</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($configs)): ?>
                    <?php foreach ($configs as $config): ?>
                        <tr id="config-<?= \Core\View::e($config['key']) ?>">
                            <td><strong><?= \Core\View::e($config['key']) ?></strong></td>
                            <td>
                                <span class="config-value"><?= \Core\View::e($config['value']) ?></span>
                                <input type="text" class="form-control config-edit" style="display: none;"
                                    value="<?= \Core\View::e($config['value']) ?>">
                            </td>
                            <td><?= \Core\View::e($config['username'] ?? 'N/A') ?></td>
                            <td><?= \Helpers\FormatHelper::datetime($config['updated_at']) ?></td>
                            <td>
                                <button onclick="editConfig('<?= \Core\View::e($config['key']) ?>')"
                                    class="btn btn-warning btn-sm btn-edit">
                                    <i class="fas fa-edit"></i> Sửa
                                </button>
                                <button onclick="saveConfig('<?= \Core\View::e($config['key']) ?>')"
                                    class="btn btn-success btn-sm btn-save" style="display: none;">
                                    <i class="fas fa-save"></i> Lưu
                                </button>
                                <button onclick="cancelEdit('<?= \Core\View::e($config['key']) ?>')"
                                    class="btn btn-secondary btn-sm btn-cancel" style="display: none;">
                                    <i class="fas fa-times"></i> Hủy
                                </button>
                                <button onclick="deleteConfig('<?= \Core\View::e($config['key']) ?>')"
                                    class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align: center;">Chưa có cấu hình nào</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function showAddForm() {
        document.getElementById('addForm').style.display = 'block';
    }

    function hideAddForm() {
        document.getElementById('addForm').style.display = 'none';
    }

    function editConfig(key) {
        const row = document.getElementById('config-' + key);
        row.querySelector('.config-value').style.display = 'none';
        row.querySelector('.config-edit').style.display = 'block';
        row.querySelector('.btn-edit').style.display = 'none';
        row.querySelector('.btn-save').style.display = 'inline-block';
        row.querySelector('.btn-cancel').style.display = 'inline-block';
    }

    function cancelEdit(key) {
        const row = document.getElementById('config-' + key);
        row.querySelector('.config-value').style.display = 'inline';
        row.querySelector('.config-edit').style.display = 'none';
        row.querySelector('.btn-edit').style.display = 'inline-block';
        row.querySelector('.btn-save').style.display = 'none';
        row.querySelector('.btn-cancel').style.display = 'none';
    }

    function saveConfig(key) {
        const row = document.getElementById('config-' + key);
        const newValue = row.querySelector('.config-edit').value;

        fetch('/admin/config/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    key: key,
                    value: newValue
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

    function deleteConfig(key) {
        if (!confirm('Bạn có chắc chắn muốn xóa cấu hình này?')) {
            return;
        }

        fetch('/admin/config/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    key: key
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
</script>