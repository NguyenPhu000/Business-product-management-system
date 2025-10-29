<?php
/**
 * View: Sửa thương hiệu
 */
$pageTitle = $pageTitle ?? 'Sửa thương hiệu';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-pen"></i> <?= $pageTitle ?></h2>
        <a href="/admin/brands" class="btn btn-secondary">
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
            <form method="POST" action="/admin/brands/update/<?= $brand['id'] ?>">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                Tên thương hiệu <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= htmlspecialchars($brand['name']) ?>" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?= htmlspecialchars($brand['description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="logo_url" class="form-label">URL Logo</label>
                            <input type="url" class="form-control" id="logo_url" name="logo_url"
                                   value="<?= htmlspecialchars($brand['logo_url'] ?? '') ?>">
                            <?php if ($brand['logo_url']): ?>
                                <div class="mt-2">
                                    <img src="<?= htmlspecialchars($brand['logo_url']) ?>" 
                                         alt="Logo" class="brand-logo img-thumbnail">
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Trạng thái</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" <?= $brand['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">Hoạt động</label>
                            </div>
                        </div>

                        <?php if (isset($brand['product_count'])): ?>
                            <div class="alert alert-info">
                                <strong>Thống kê:</strong><br>
                                Số sản phẩm: <?= $brand['product_count'] ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                    <a href="/admin/brands" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                    <button type="button" class="btn btn-danger float-end" 
                            onclick="deleteBrand(<?= $brand['id'] ?>, '<?= htmlspecialchars($brand['name']) ?>')">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;"></form>

<link rel="stylesheet" href="/assets/css/brand-style.css">

<script>
function deleteBrand(id, name) {
    if (confirm('Bạn có chắc chắn muốn xóa thương hiệu "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = '/admin/brands/delete/' + id;
        form.submit();
    }
}
</script>
