<?php
/**
 * View: Sửa nhà cung cấp
 */
$pageTitle = $pageTitle ?? 'Sửa nhà cung cấp';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-pen"></i> <?= $pageTitle ?></h2>
        <a href="/admin/suppliers" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="/admin/suppliers/update/<?= $supplier['id'] ?>">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Tên nhà cung cấp <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($supplier['name']) ?>" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="contact" class="form-label">Người liên hệ</label>
                            <input type="text" class="form-control" id="contact" name="contact"
                                   value="<?= htmlspecialchars($supplier['contact'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= htmlspecialchars($supplier['phone'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($supplier['email'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= htmlspecialchars($supplier['address'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" <?= $supplier['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Hoạt động</label>
                            </div>
                        </div>

                        <?php if (isset($supplier['order_count'])): ?>
                            <div class="alert alert-info">
                                <strong>Thống kê:</strong><br>
                                Số đơn hàng: <?= $supplier['order_count'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                    <a href="/admin/suppliers" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                    <button type="button" class="btn btn-danger float-end" 
                            onclick="deleteSupplier(<?= $supplier['id'] ?>, '<?= htmlspecialchars($supplier['name']) ?>')">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;"></form>

<link rel="stylesheet" href="/assets/css/supplier-style.css">

<script>
function deleteSupplier(id, name) {
    if (confirm('Bạn có chắc chắn muốn xóa nhà cung cấp "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = '/admin/suppliers/delete/' + id;
        form.submit();
    }
}
</script>
