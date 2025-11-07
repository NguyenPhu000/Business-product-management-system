<?php
/**
 * View: Thêm thương hiệu mới
 */
$pageTitle = $pageTitle ?? 'Thêm thương hiệu mới';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus-circle"></i> <?= $pageTitle ?></h2>
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
            <form method="POST" action="/admin/brands/store" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag me-1"></i>Tên thương hiệu <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Mô tả
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="logo_image" class="form-label">
                                <i class="fas fa-image me-1"></i>Logo thương hiệu
                            </label>
                            <input type="file" class="form-control" id="logo_image" name="logo_image" accept="image/*">
                            <div class="form-text">Chọn ảnh logo từ máy tính (JPG, PNG, GIF - Max 5MB)</div>

                            <!-- Preview ảnh -->
                            <div id="logoPreview" class="mt-3" style="display: none;">
                                <img id="previewImg" src="" alt="Preview"
                                    style="max-width: 200px; max-height: 200px; border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-toggle-on me-1"></i>Trạng thái
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                    value="1" checked>
                                <label class="form-check-label" for="is_active">Hoạt động</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu thương hiệu
                    </button>
                    <a href="/admin/brands" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/assets/css/brand-style.css">

<script>
// Preview ảnh khi chọn file
document.getElementById('logo_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        // Kiểm tra kích thước file (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Kích thước file không được vượt quá 5MB!');
            this.value = '';
            document.getElementById('logoPreview').style.display = 'none';
            return;
        }

        // Kiểm tra định dạng file
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!validTypes.includes(file.type)) {
            alert('Chỉ chấp nhận file ảnh JPG, PNG, GIF!');
            this.value = '';
            document.getElementById('logoPreview').style.display = 'none';
            return;
        }

        // Preview ảnh
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('logoPreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        document.getElementById('logoPreview').style.display = 'none';
    }
});
</script>