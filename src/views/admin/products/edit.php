<?php

/**
 * View: Chỉnh sửa sản phẩm
 * Path: src/views/admin/products/edit.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-pencil-square"></i> Chỉnh sửa sản phẩm
        </h1>
        <a href="/admin/products" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i>
        <?= $flash ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($flash = \Helpers\AuthHelper::getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i>
        <?= $flash ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-info-circle"></i> Thông tin sản phẩm
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/products/update/<?= $product['id'] ?>" enctype="multipart/form-data"
                id="productForm">

                <!-- Row 1: SKU và Tên sản phẩm -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="sku" class="form-label">
                            Mã sản phẩm (SKU) <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="sku" name="sku"
                            value="<?= htmlspecialchars($product['sku']) ?>" required>
                        <small class="text-muted">Mã định danh duy nhất cho sản phẩm</small>
                    </div>

                    <div class="col-md-8">
                        <label for="name" class="form-label">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="<?= htmlspecialchars($product['name']) ?>" placeholder="Nhập tên sản phẩm" required>
                    </div>
                </div>

                <!-- Row 2: Danh mục và Thương hiệu -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Danh mục <span class="text-danger">*</span>
                        </label>
                        <div class="category-selector border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            <?php if (!empty($categories)): ?>
                            <?php foreach ($categories as $category): ?>
                            <?php
                                    $level = $category['level'] ?? 0;
                                    $indent = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $level);
                                    $disabled = $category['is_active'] == 0 ? 'disabled' : '';
                                    $badge = $category['is_active'] == 0 ? '<span class="badge bg-secondary ms-2">Ẩn</span>' : '';
                                    $checked = in_array($category['id'], $assignedCategoryIds) ? 'checked' : '';
                                    ?>
                            <div class="form-check category-level-<?= $level ?>">
                                <input class="form-check-input category-checkbox" type="checkbox" name="category_ids[]"
                                    value="<?= $category['id'] ?>" id="cat_<?= $category['id'] ?>" <?= $disabled ?>
                                    <?= $checked ?>>
                                <label class="form-check-label" for="cat_<?= $category['id'] ?>">
                                    <?= $indent ?><?= htmlspecialchars($category['name']) ?> <?= $badge ?>
                                </label>
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                            <p class="text-muted">Chưa có danh mục nào. <a href="/admin/categories/create">Tạo danh mục
                                    mới</a></p>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">Có thể chọn nhiều danh mục</small>
                        <div id="categorySelectionInfo" class="alert alert-info mt-2" style="display: none;">
                            <i class="bi bi-info-circle"></i>
                            <strong>Danh mục cha đã chọn:</strong> <span id="selectedParentName"></span>
                            <br>
                            <small>Bạn chỉ có thể chọn thêm các danh mục con của danh mục này.</small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="brand_id" class="form-label">
                            Thương hiệu <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="brand_id" name="brand_id" required>
                            <option value="">-- Chọn thương hiệu --</option>
                            <?php foreach ($brands as $brand): ?>
                            <?php if ($brand['is_active'] == 1): ?>
                            <option value="<?= $brand['id'] ?>"
                                <?= $brand['id'] == $product['brand_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($brand['name']) ?>
                            </option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">
                            Chưa có thương hiệu? <a href="/admin/brands/create" target="_blank">Tạo mới</a>
                        </small>
                    </div>
                </div>

                <!-- Row 3: Đơn vị tính và Thuế VAT -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="unit" class="form-label">
                            Đơn vị tính <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="unit" name="unit" required>
                            <?php
                            $units = ['cái', 'hộp', 'kg', 'gram', 'lít', 'ml', 'thùng', 'bộ', 'chiếc', 'gói', 'chai', 'lon'];
                            foreach ($units as $unit):
                                $selected = ($product['unit'] ?? 'cái') == $unit ? 'selected' : '';
                            ?>
                            <option value="<?= $unit ?>" <?= $selected ?>><?= ucfirst($unit) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="tax_rate" class="form-label">Thuế VAT (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" min="0" max="100"
                                step="0.01" value="<?= htmlspecialchars($product['tax_rate'] ?? 0) ?>"
                                placeholder="VD: 10 (cho VAT 10%)">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Nhập % thuế VAT (0-100)</small>
                    </div>
                </div>

                <!-- Row 4: Giá nhập - Giá bán - Giá khuyến mãi -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="unit_cost" class="form-label">
                            Giá nhập <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="unit_cost" name="unit_cost" min="0"
                                step="0.01" value="<?= $product['unit_cost'] ?? 0 ?>" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-muted">Giá nhập từ nhà cung cấp</small>
                    </div>

                    <div class="col-md-4">
                        <label for="price" class="form-label">
                            Giá bán <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01"
                                value="<?= $product['price'] ?? 0 ?>" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-muted">Giá bán cho khách hàng</small>
                    </div>

                    <div class="col-md-4">
                        <label for="sale_price" class="form-label">Giá khuyến mãi</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="sale_price" name="sale_price" min="0"
                                step="0.01" value="<?= $product['sale_price'] ?? '' ?>"
                                placeholder="Để trống nếu không KM">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-muted">Giá ưu đãi (nếu có)</small>
                    </div>
                </div>

                <!-- Row 5: Mô tả ngắn -->
                <div class="mb-3">
                    <label for="short_desc" class="form-label">Mô tả ngắn / Chi tiết</label>
                    <textarea class="form-control" id="short_desc" name="short_desc" rows="3"
                        placeholder="Mô tả ngắn gọn về sản phẩm (tối đa 500 ký tự)"
                        maxlength="500"><?= htmlspecialchars($product['short_desc'] ?? '') ?></textarea>
                    <small class="text-muted">Hiển thị trong danh sách sản phẩm</small>
                </div>

                <!-- Row 6: Mô tả dài -->
                <div class="mb-3">
                    <label for="long_desc" class="form-label">Mô tả chi tiết</label>
                    <textarea class="form-control" id="long_desc" name="long_desc" rows="6"
                        placeholder="Mô tả đầy đủ về sản phẩm, tính năng, ưu điểm..."><?= htmlspecialchars($product['long_desc'] ?? '') ?></textarea>
                    <small class="text-muted">Hiển thị trong trang chi tiết sản phẩm</small>
                </div>

                <!-- Row 7: Hình ảnh hiện tại -->
                <?php if (!empty($images)): ?>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Hình ảnh hiện tại</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <?php
                            // Tìm ảnh chính và ảnh phụ
                            $primaryImage = null;
                            $otherImages = [];

                            // Sắp xếp theo sort_order trước
                            usort($images, function ($a, $b) {
                                return ($a['sort_order'] ?? 0) - ($b['sort_order'] ?? 0);
                            });

                            foreach ($images as $image) {
                                if ($image['is_primary'] == 1 && $primaryImage === null) {
                                    // Chỉ lấy ảnh primary đầu tiên
                                    $primaryImage = $image;
                                } else {
                                    // Các ảnh còn lại là ảnh phụ
                                    $otherImages[] = $image;
                                }
                            }

                            // Nếu không có ảnh chính, lấy ảnh đầu tiên làm ảnh chính
                            if (!$primaryImage && !empty($images)) {
                                $primaryImage = $images[0];
                                // Loại bỏ ảnh đầu tiên khỏi danh sách ảnh phụ
                                $otherImages = array_slice($images, 1);
                            }
                            ?>

                        <!-- Ảnh chính -->
                        <?php if ($primaryImage): ?>
                        <div style="flex: 0 0 200px;">
                            <div class="card shadow-sm h-100" id="image-<?= $primaryImage['id'] ?>">
                                <div class="card-header bg-success text-white text-center py-2"
                                    style="height: 45px; display: flex; align-items: center; justify-content: center;">
                                    <strong><i class="bi bi-star-fill"></i> ẢNH CHÍNH</strong>
                                </div>
                                <img src="<?= htmlspecialchars($primaryImage['display_url'] ?? $primaryImage['url']) ?>"
                                    class="card-img" alt="Ảnh chính"
                                    style="height: 200px; width: 200px; object-fit: cover;">
                                <div class="card-body p-2" style="height: 80px;">
                                    <button type="button" class="btn btn-sm btn-danger w-100 delete-image-btn"
                                        data-image-id="<?= $primaryImage['id'] ?>">
                                        <i class="bi bi-trash"></i> Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Ảnh phụ (ngang hàng) -->
                        <?php if (!empty($otherImages)): ?>
                        <?php foreach ($otherImages as $image): ?>
                        <div style="flex: 0 0 200px;" id="image-<?= $image['id'] ?>">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-light text-center py-2"
                                    style="height: 45px; display: flex; align-items: center; justify-content: center;">
                                    <small class="text-muted">Ảnh phụ</small>
                                </div>
                                <img src="<?= htmlspecialchars($image['display_url'] ?? $image['url']) ?>"
                                    class="card-img" alt="Ảnh phụ"
                                    style="height: 200px; width: 200px; object-fit: cover;">
                                <div class="card-body p-2" style="height: 80px;">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary w-100 mb-1 set-primary-btn"
                                        data-image-id="<?= $image['id'] ?>"
                                        style="font-size: 0.7rem; padding: 0.25rem;">
                                        Đặt làm ảnh chính
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger w-100 delete-image-btn"
                                        data-image-id="<?= $image['id'] ?>"
                                        style="font-size: 0.7rem; padding: 0.25rem;">
                                        Xóa
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Row 8: Upload hình ảnh mới -->
                <div class="mb-3">
                    <label for="images" class="form-label">
                        Thêm hình ảnh mới (đa ảnh)
                    </label>
                    <input type="file" class="form-control" id="images" name="images[]" accept="image/*" multiple>
                    <small class="text-muted">
                        Chọn nhiều ảnh (jpg, png, gif, webp). Tối đa 5MB/ảnh.
                    </small>

                    <!-- Preview container -->
                    <div id="imagePreview" class="row mt-3 g-2"></div>
                </div>

                <!-- Row 9: Trạng thái -->
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1"
                            <?= ($product['status'] ?? 1) == 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="status">
                            <span class="text-success">Kích hoạt</span>
                            <small class="text-muted">(Hiển thị sản phẩm trên hệ thống)</small>
                        </label>
                    </div>
                </div>

                <!-- Divider -->
                <hr>

                <!-- Action buttons -->
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Cập nhật sản phẩm
                        </button>
                        <a href="/admin/products" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Hủy
                        </a>
                    </div>
                    <div>
                        <button type="button" class="btn btn-danger" id="deleteProductBtn">
                            <i class="bi bi-trash"></i> Xóa sản phẩm
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Variants Section -->
    <?php if (!empty($product['variants'])): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-layer-group"></i> Biến thể sản phẩm (<?= count($product['variants']) ?>)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th width="150">SKU Biến thể</th>
                            <th>Thuộc tính</th>
                            <th width="100">Giá nhập</th>
                            <th width="100">Giá bán</th>
                            <th width="120" class="text-center">Tồn kho</th>
                            <th width="100" class="text-center">Trạng thái</th>
                            <th width="150" class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($product['variants'] as $index => $variant): ?>
                            <tr>
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><code><?= htmlspecialchars($variant['sku']) ?></code></td>
                                <td>
                                    <?php 
                                    $attributes = json_decode($variant['attributes'], true);
                                    if ($attributes):
                                        foreach ($attributes as $key => $value): 
                                    ?>
                                        <span class="badge bg-secondary me-1">
                                            <?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?>
                                        </span>
                                    <?php 
                                        endforeach;
                                    else:
                                    ?>
                                        <span class="text-muted">Mặc định</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?= number_format($variant['unit_cost'], 0, ',', '.') ?> đ
                                </td>
                                <td class="text-end">
                                    <strong><?= number_format($variant['price'], 0, ',', '.') ?> đ</strong>
                                </td>
                                <td class="text-center">
                                    <?php 
                                    $stock = $variant['total_stock'] ?? 0;
                                    $class = $stock > 10 ? 'success' : ($stock > 0 ? 'warning' : 'danger');
                                    ?>
                                    <a href="/admin/inventory/detail/<?= $variant['id'] ?>" 
                                       class="text-decoration-none" 
                                       title="Xem chi tiết tồn kho">
                                        <span class="badge bg-<?= $class ?>">
                                            <i class="fas fa-box"></i> <?= number_format($stock) ?>
                                        </span>
                                    </a>
                                </td>
                                <td class="text-center">
                                    <?php if ($variant['is_active'] == 1): ?>
                                        <span class="badge bg-success">Kích hoạt</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Vô hiệu</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="/admin/inventory/adjust/<?= $variant['id'] ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Điều chỉnh tồn kho">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="/admin/inventory/detail/<?= $variant['id'] ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i> 
                <strong>Lưu ý:</strong> Để quản lý tồn kho cho từng variant, click vào số lượng hoặc nút điều chỉnh.
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== CATEGORY SELECTION LOGIC =====
    // Chỉ cho phép chọn 1 danh mục cha và các con của nó
    const categoryCheckboxes = document.querySelectorAll('.category-checkbox');

    function updateCategorySelection() {
        // Lấy tất cả danh mục đã chọn
        const checkedCategories = Array.from(categoryCheckboxes).filter(cb => cb.checked);

        // Lấy danh mục cha đã chọn (level 0)
        const checkedParents = checkedCategories.filter(cb => {
            return cb.closest('.form-check').classList.contains('category-level-0');
        });

        if (checkedParents.length > 0) {
            // Đã chọn ít nhất 1 danh mục cha
            const selectedParentId = checkedParents[0].value;

            // Hiển thị thông báo danh mục cha đã chọn
            const allCategories = <?= json_encode($categories) ?>;
            const selectedParent = allCategories.find(cat => cat.id == selectedParentId);
            if (selectedParent) {
                document.getElementById('categorySelectionInfo').style.display = 'block';
                document.getElementById('selectedParentName').textContent = selectedParent.name;
            }

            // Disable tất cả danh mục cha khác
            categoryCheckboxes.forEach(checkbox => {
                const checkDiv = checkbox.closest('.form-check');
                const isParent = checkDiv.classList.contains('category-level-0');

                if (isParent && checkbox.value !== selectedParentId) {
                    checkbox.disabled = true;
                    checkDiv.style.opacity = '0.5';
                    checkDiv.style.cursor = 'not-allowed';
                } else if (isParent && checkbox.value === selectedParentId) {
                    checkbox.disabled = false;
                    checkDiv.style.opacity = '1';
                    checkDiv.style.cursor = 'pointer';
                }
            });

            // Lọc và disable các danh mục con không thuộc parent đã chọn
            categoryCheckboxes.forEach(checkbox => {
                const checkDiv = checkbox.closest('.form-check');
                const isChild = !checkDiv.classList.contains('category-level-0');

                if (isChild) {
                    const currentCategory = allCategories.find(cat => cat.id == checkbox.value);

                    // Kiểm tra xem category này có thuộc parent đã chọn không
                    if (currentCategory && currentCategory.parent_id == selectedParentId) {
                        // Cho phép chọn các con của parent
                        checkbox.disabled = false;
                        checkDiv.style.opacity = '1';
                        checkDiv.style.cursor = 'pointer';
                    } else {
                        // Disable các con của parent khác
                        checkbox.disabled = true;
                        checkbox.checked = false; // Bỏ chọn nếu đang được chọn
                        checkDiv.style.opacity = '0.5';
                        checkDiv.style.cursor = 'not-allowed';
                    }
                }
            });

        } else {
            // Chưa chọn danh mục cha nào → enable tất cả và ẩn thông báo
            document.getElementById('categorySelectionInfo').style.display = 'none';

            categoryCheckboxes.forEach(checkbox => {
                const checkDiv = checkbox.closest('.form-check');
                const originalDisabled = checkbox.dataset.originalDisabled === 'true';

                if (!originalDisabled) {
                    checkbox.disabled = false;
                    checkDiv.style.opacity = '1';
                    checkDiv.style.cursor = 'pointer';
                }
            });
        }
    }

    // Lưu trạng thái disabled ban đầu (do is_active = 0)
    categoryCheckboxes.forEach(checkbox => {
        checkbox.dataset.originalDisabled = checkbox.disabled;
    });

    // Lắng nghe sự kiện thay đổi checkbox
    categoryCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function(e) {
            const checkDiv = this.closest('.form-check');
            const isParent = checkDiv.classList.contains('category-level-0');

            // Nếu đang cố gắng chọn danh mục cha thứ 2
            if (isParent && this.checked) {
                const otherParentsChecked = Array.from(categoryCheckboxes).some(cb => {
                    return cb !== this &&
                        cb.checked &&
                        cb.closest('.form-check').classList.contains(
                            'category-level-0');
                });

                if (otherParentsChecked) {
                    e.preventDefault();
                    this.checked = false;
                    alert(
                        'Chỉ được chọn một danh mục cha! Vui lòng bỏ chọn danh mục cha hiện tại trước.'
                    );
                    return;
                }
            }

            updateCategorySelection();
        });
    });

    // Chạy lần đầu khi load trang
    updateCategorySelection();

    // Preview ảnh mới khi upload
    const imagesInput = document.getElementById('images');
    const imagePreview = document.getElementById('imagePreview');

    if (imagesInput) {
        imagesInput.addEventListener('change', function(e) {
            imagePreview.innerHTML = '';
            const files = e.target.files;

            if (files.length > 0) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const col = document.createElement('div');
                            col.className = 'col-md-2';
                            col.innerHTML = `
                                <div class="card">
                                    <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <div class="card-body p-2 text-center">
                                        <small class="text-muted">${i === 0 ? 'Ảnh chính mới' : 'Ảnh phụ'}</small>
                                    </div>
                                </div>
                            `;
                            imagePreview.appendChild(col);
                        };
                        reader.readAsDataURL(file);
                    }
                }
            }
        });
    }

    // Xóa ảnh
    document.querySelectorAll('.delete-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Bạn có chắc muốn xóa ảnh này?')) return;

            const imageId = this.dataset.imageId;
            fetch('/admin/products/delete-image', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `image_id=${imageId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('image-' + imageId).remove();
                        alert('Đã xóa ảnh thành công!');
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(err => alert('Có lỗi xảy ra!'));
        });
    });

    // Đặt ảnh chính
    document.querySelectorAll('.set-primary-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const imageId = this.dataset.imageId;
            fetch('/admin/products/set-primary-image', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `image_id=${imageId}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Lỗi: ' + data.message);
                    }
                })
                .catch(err => alert('Có lỗi xảy ra!'));
        });
    });

    // Xóa sản phẩm
    const deleteBtn = document.getElementById('deleteProductBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (!confirm('Bạn có chắc muốn xóa sản phẩm này? Hành động không thể hoàn tác!')) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/products/<?= $product['id'] ?>/delete';
            document.body.appendChild(form);
            form.submit();
        });
    }

    // Validate giá
    const unitCostInput = document.getElementById('unit_cost');
    const priceInput = document.getElementById('price');
    const salePriceInput = document.getElementById('sale_price');

    if (priceInput && unitCostInput) {
        priceInput.addEventListener('change', function() {
            const unitCost = parseFloat(unitCostInput.value) || 0;
            const price = parseFloat(this.value) || 0;

            if (price < unitCost) {
                alert('Giá bán phải lớn hơn hoặc bằng giá nhập!');
                this.value = unitCost;
            }
        });
    }

    if (salePriceInput && priceInput) {
        salePriceInput.addEventListener('change', function() {
            const price = parseFloat(priceInput.value) || 0;
            const salePrice = parseFloat(this.value) || 0;

            if (salePrice > 0 && salePrice >= price) {
                alert('Giá khuyến mãi phải nhỏ hơn giá bán!');
                this.value = '';
            }
        });
    }
});
</script>