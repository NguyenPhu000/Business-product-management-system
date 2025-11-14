<?php

use Helpers\AuthHelper;

$old = AuthHelper::getFlash('old') ?? [];
?>

<link rel="stylesheet" href="/assets/css/roles.css">

<div class="edit-role-container">
    <?php if ($error = AuthHelper::getFlash('error')): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= $error ?></span>
            <button type="button" class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-card-header">
            <h3 class="form-card-title">
                <i class="fas fa-info-circle"></i> Thông tin vai trò
            </h3>
            <a href="/admin/roles" class="btn-back-header">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
        <div class="form-card-body">
            <form action="/admin/roles/update/<?= $role['id'] ?>" method="POST" class="role-form">
                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group-modern">
                            <label for="name" class="form-label-modern">
                                <i class="fas fa-tag"></i> Tên vai trò
                                <span class="required-mark">*</span>
                            </label>
                            <input type="text" id="name" name="name" class="form-input-modern"
                                value="<?= \Core\View::e($old['name'] ?? $role['name'] ?? '') ?>" required
                                maxlength="50" placeholder="Ví dụ: Admin, Chủ tiệm, Nhân viên...">
                            <small class="form-hint">
                                <i class="fas fa-info-circle"></i> Tên vai trò từ 3-50 ký tự
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col-full">
                        <div class="form-group-modern">
                            <label for="description" class="form-label-modern">
                                <i class="fas fa-align-left"></i> Mô tả vai trò
                                <span class="required-mark">*</span>
                            </label>
                            <textarea id="description" name="description" class="form-textarea-modern" rows="5" required
                                placeholder="Mô tả chi tiết về quyền hạn và trách nhiệm của vai trò này..."><?= \Core\View::e($old['description'] ?? $role['description'] ?? '') ?></textarea>
                            <small class="form-hint">
                                <i class="fas fa-info-circle"></i> Mô tả chi tiết về vai trò này và các quyền liên quan
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i> Cập nhật vai trò
                    </button>
                    <a href="/admin/roles" class="btn-cancel">
                        <i class="fas fa-times"></i> Hủy bỏ
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>