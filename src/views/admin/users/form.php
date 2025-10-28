<div class="page-header">
    <h2 class="page-title"><?= isset($user) ? 'Sửa người dùng' : 'Thêm người dùng mới' ?></h2>
    <a href="/admin/users" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Quay lại
    </a>
</div>

<?php
$old = \Helpers\AuthHelper::getFlash('old') ?? [];
$formAction = isset($user) ? "/admin/users/update/{$user['id']}" : "/admin/users/store";
?>

<div class="card">
    <div class="card-body">
        <form action="<?= $formAction ?>" method="POST">
            <div class="row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <div class="form-group">
                        <label for="username" class="form-label">Username *</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-control"
                            value="<?= \Core\View::e($old['username'] ?? $user['username'] ?? '') ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email *</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            value="<?= \Core\View::e($old['email'] ?? $user['email'] ?? '') ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="full_name" class="form-label">Họ tên *</label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            class="form-control"
                            value="<?= \Core\View::e($old['full_name'] ?? $user['full_name'] ?? '') ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input
                            type="text"
                            id="phone"
                            name="phone"
                            class="form-control"
                            value="<?= \Core\View::e($old['phone'] ?? $user['phone'] ?? '') ?>">
                    </div>
                </div>

                <div>
                    <div class="form-group">
                        <label for="role_id" class="form-label">Vai trò *</label>
                        <select id="role_id" name="role_id" class="form-control" required>
                            <option value="">-- Chọn vai trò --</option>
                            <?php if (isset($roles)): ?>
                                <?php foreach ($roles as $role): ?>
                                    <option
                                        value="<?= $role['id'] ?>"
                                        <?= ($old['role_id'] ?? $user['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                                        <?= \Core\View::e($role['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status" class="form-label">Trạng thái *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="1" <?= ($old['status'] ?? $user['status'] ?? 1) == 1 ? 'selected' : '' ?>>Hoạt động</option>
                            <option value="0" <?= ($old['status'] ?? $user['status'] ?? 1) == 0 ? 'selected' : '' ?>>Không hoạt động</option>
                        </select>
                    </div>

                    <?php if (!isset($user)): ?>
                        <div class="form-group">
                            <label for="password" class="form-label">Mật khẩu *</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                required
                                minlength="8">
                            <small style="color: #666;">Tối thiểu 8 ký tự</small>
                        </div>
                    <?php else: ?>
                        <div class="form-group">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-control"
                                minlength="8">
                            <small style="color: #666;">Để trống nếu không đổi mật khẩu</small>
                        </div>
                    <?php endif; ?>

                    <div class="form-group" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= isset($user) ? 'Cập nhật' : 'Thêm mới' ?>
                        </button>
                        <a href="/admin/users" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>