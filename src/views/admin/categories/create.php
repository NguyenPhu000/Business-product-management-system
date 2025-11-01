<?php
/**
 * View: Thêm danh mục mới
 */
$pageTitle = $pageTitle ?? 'Thêm danh mục mới';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-plus-circle"></i> <?= $pageTitle ?></h2>
        <a href="/admin/categories" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Flash messages -->
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>
            <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="/admin/categories/store" id="categoryForm">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Tên danh mục -->
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag me-1"></i>Tên danh mục <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   placeholder="Nhập tên danh mục" required autofocus
                                   onchange="generateSlug()">
                            <div class="form-text">Tên hiển thị của danh mục</div>
                        </div>

                        <!-- Slug -->
                        <div class="mb-3">
                            <label for="slug" class="form-label">
                                <i class="fas fa-link me-1"></i>Slug (URL thân thiện)
                            </label>
                            <input type="text" class="form-control" id="slug" name="slug" 
                                   placeholder="auto-generate-from-name">
                            <div class="form-text">Để trống để tự động tạo từ tên danh mục</div>
                        </div>

                        <!-- Danh mục cha -->
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">
                                <i class="fas fa-folder-tree me-1"></i>Danh mục cha
                            </label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">-- Không có (Danh mục gốc) --</option>
                                <?php foreach ($parentCategories as $parent): ?>
                                    <option value="<?= $parent['id'] ?>">
                                        <?= htmlspecialchars($parent['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Chọn danh mục cha nếu đây là danh mục con</div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Thứ tự sắp xếp -->
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">
                                <i class="fas fa-sort-numeric-down me-1"></i>Thứ tự sắp xếp
                            </label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" 
                                   value="0" min="0">
                            <div class="form-text">Số càng nhỏ càng ưu tiên hiển thị trước</div>
                        </div>

                        <!-- Trạng thái -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-toggle-on me-1"></i>Trạng thái
                            </label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" 
                                       name="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">
                                    Hoạt động
                                </label>
                            </div>
                            <div class="form-text">Cho phép hiển thị danh mục này</div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="border-top pt-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Lưu danh mục
                    </button>
                    <a href="/admin/categories" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="/assets/css/category-style.css">

<script>
function generateSlug() {
    const name = document.getElementById('name').value;
    const slug = document.getElementById('slug');
    
    if (!slug.value || slug.value === '') {
        // Chuyển tiếng Việt sang không dấu và tạo slug
        let str = name.toLowerCase();
        str = str.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        str = str.replace(/đ/g, 'd').replace(/Đ/g, 'D');
        str = str.replace(/[^a-z0-9\s-]/g, '');
        str = str.replace(/\s+/g, '-');
        str = str.replace(/-+/g, '-');
        str = str.trim().replace(/^-+|-+$/g, '');
        
        slug.value = str;
    }
}

// Form validation
document.getElementById('categoryForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    
    if (!name) {
        e.preventDefault();
        alert('Vui lòng nhập tên danh mục');
        document.getElementById('name').focus();
        return false;
    }
});
</script>
