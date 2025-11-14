<div class="page-header">
    <h2 class="page-title"><?= isset($role) ? 'Sửa vai trò' : 'Thêm vai trò mới' ?></h2>
    <a href="/admin/roles" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<?php
$old = \Helpers\AuthHelper::getFlash('old') ?? [];
$formAction = isset($role) ? "/admin/roles/update/{$role['id']}" : "/admin/roles/store";
?>

<div class="card">
    <div class="card-body">
        <form action="<?= $formAction ?>" method="POST">
            <div class="form-group">
                <label for="name" class="form-label">Tên vai trò *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="<?= \Core\View::e($old['name'] ?? $role['name'] ?? '') ?>"
                    required>
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Mô tả *</label>
                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    rows="4"
                    required><?= \Core\View::e($old['description'] ?? $role['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?= isset($role) ? 'Cập nhật' : 'Thêm mới' ?>
                </button>
                <a href="/admin/roles" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Hủy
                </a>
            </div>
        </form>
    </div>
</div>