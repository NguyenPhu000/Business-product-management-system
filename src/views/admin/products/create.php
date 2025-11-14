<?php
/**
 * View: Thêm mới sản phẩm
 * Path: src/views/admin/products/create.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="bi bi-plus-circle"></i> Thêm sản phẩm mới
        </h1>
        <a href="/admin/products" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i>
        <?= $flash ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-info-circle"></i> Thông tin sản phẩm
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/products/store" enctype="multipart/form-data" id="productForm">

                <!-- Row 1: SKU và Tên sản phẩm -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="sku" class="form-label">
                            Mã sản phẩm (SKU) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="sku" name="sku" value="<?= $autoSku ?>"
                                required placeholder="VD: PRD-ABC123">
                            <button class="btn btn-primary" type="button" id="generateSku"
                                title="Nhấn để tạo mã sản phẩm tự động">
                                <i class="bi bi-magic"></i> Tạo mã tự động
                            </button>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i> Mã định danh duy nhất. Nhấn nút "Tạo mã tự động" để hệ thống tạo mã ngẫu nhiên.
                        </small>
                    </div>

                    <div class="col-md-8">
                        <label for="name" class="form-label">
                            Tên sản phẩm <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Nhập tên sản phẩm"
                            required>
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
                                    ?>
                            <div class="form-check category-level-<?= $level ?>">
                                <input class="form-check-input category-checkbox" type="checkbox" name="category_ids[]"
                                    value="<?= $category['id'] ?>" id="cat_<?= $category['id'] ?>" <?= $disabled ?>>
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
                            <i class="fas fa-info-circle"></i> 
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
                            <option value="<?= $brand['id'] ?>">
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
                            <option value="cái" selected>Cái</option>
                            <option value="hộp">Hộp</option>
                            <option value="kg">Kg (Kilogram)</option>
                            <option value="gram">Gram</option>
                            <option value="lít">Lít</option>
                            <option value="ml">ML (Mililít)</option>
                            <option value="thùng">Thùng</option>
                            <option value="bộ">Bộ</option>
                            <option value="chiếc">Chiếc</option>
                            <option value="gói">Gói</option>
                            <option value="chai">Chai</option>
                            <option value="lon">Lon</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="tax_rate" class="form-label">Thuế VAT (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="tax_rate" name="tax_rate" min="0" max="100"
                                step="0.01" value="0" placeholder="VD: 10 (cho VAT 10%)">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Nhập % thuế VAT (0-100)</small>
                    </div>
                </div>

                <!-- Row 4: Giá nhập - Giá bán - Giá khuyến mãi với Auto Calculate -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="unit_cost" class="form-label">
                            Giá nhập <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="unit_cost" name="unit_cost" min="0"
                                step="1000" value="0" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-muted">Giá nhập từ nhà cung cấp</small>
                    </div>

                    <div class="col-md-4">
                        <label for="price" class="form-label">
                            Giá bán <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="price" name="price" min="0" step="1000"
                                value="0" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">Giá bán cho khách</small>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary btn-sm auto-price"
                                    data-margin="20">+20%</button>
                                <button type="button" class="btn btn-outline-primary btn-sm auto-price"
                                    data-margin="30">+30%</button>
                                <button type="button" class="btn btn-outline-primary btn-sm auto-price"
                                    data-margin="50">+50%</button>
                            </div>
                        </div>
                        <small id="profit-info" class="text-success d-block"></small>
                    </div>

                    <div class="col-md-4">
                        <label for="sale_price" class="form-label">Giá khuyến mãi</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="sale_price" name="sale_price" min="0"
                                step="1000" placeholder="Để trống nếu không KM">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <small class="text-muted">Giá ưu đãi</small>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-danger btn-sm auto-sale"
                                    data-discount="5">-5%</button>
                                <button type="button" class="btn btn-outline-danger btn-sm auto-sale"
                                    data-discount="10">-10%</button>
                                <button type="button" class="btn btn-outline-danger btn-sm auto-sale"
                                    data-discount="15">-15%</button>
                            </div>
                        </div>
                        <small id="discount-info" class="text-danger d-block"></small>
                    </div>
                </div>

                <!-- Thông tin lợi nhuận -->
                <div class="alert alert-info mb-3" id="price-summary" style="display: none;">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <strong>Giá nhập:</strong><br>
                            <span id="summary-cost" class="text-primary fs-5">0 đ</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Giá bán:</strong><br>
                            <span id="summary-price" class="text-success fs-5">0 đ</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Lợi nhuận:</strong><br>
                            <span id="summary-profit" class="text-warning fs-5">0 đ</span>
                        </div>
                        <div class="col-md-3">
                            <strong>Margin:</strong><br>
                            <span id="summary-margin" class="text-info fs-5">0%</span>
                        </div>
                    </div>
                </div>

                <!-- Row 5: Mô tả ngắn -->
                <div class="mb-3">
                    <label for="short_desc" class="form-label">Mô tả ngắn</label>
                    <textarea class="form-control" id="short_desc" name="short_desc" rows="3"
                        placeholder="Mô tả ngắn gọn về sản phẩm (tối đa 500 ký tự)" maxlength="500"></textarea>
                    <small class="text-muted">Hiển thị trong danh sách sản phẩm</small>
                </div>

                <!-- Row 6: Mô tả dài -->
                <div class="mb-3">
                    <label for="long_desc" class="form-label">Mô tả chi tiết</label>
                    <textarea class="form-control" id="long_desc" name="long_desc" rows="6"
                        placeholder="Mô tả đầy đủ về sản phẩm, tính năng, ưu điểm..."></textarea>
                    <small class="text-muted">Hiển thị trong trang chi tiết sản phẩm</small>
                </div>

                <!-- Row 7: Upload hình ảnh -->
                <div class="mb-3">
                    <label for="images" class="form-label">
                        Hình ảnh sản phẩm (đa ảnh)
                        <span id="imageCounter" class="badge bg-secondary ms-2" style="display: none;">0/5 ảnh</span>
                    </label>
                    <input type="file" class="form-control" id="images" name="images[]"
                        accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                    <small class="text-muted">
                        Chọn nhiều ảnh (jpg, png, gif, webp). Ảnh đầu tiên sẽ là ảnh chính. Tối đa 5MB/ảnh, tối đa 5
                        ảnh.
                    </small>

                    <!-- Preview container -->
                    <div id="imagePreview" class="row mt-3 g-2"></div>
                </div>

                <!-- Row 8: Trạng thái -->
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                        <label class="form-check-label" for="status">
                            <span class="text-success">Kích hoạt</span>
                            <small class="text-muted">(Hiển thị sản phẩm trên hệ thống)</small>
                        </label>
                    </div>
                </div>

                <!-- Divider -->
                <hr>

                <!-- Action buttons - Sticky version -->
                <div id="formActions" class="form-actions-sticky">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check-circle"></i> Lưu sản phẩm
                            </button>
                            <button type="reset" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-redo"></i> Đặt lại
                            </button>
                        </div>
                        <a href="/admin/products" class="btn btn-link btn-lg text-muted">
                            <i class="fas fa-times-circle"></i> Hủy bỏ
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ghi chú hướng dẫn -->
    <div class="alert alert-info" role="alert">
        <h6 class="alert-heading"><i class="fas fa-info-circle"></i> Hướng dẫn</h6>
        <ul class="mb-0">
            <li>Các trường có dấu <span class="text-danger">*</span> là bắt buộc</li>
            <li>Mã SKU phải là duy nhất trong hệ thống</li>
            <li>Có thể gán sản phẩm vào nhiều danh mục cùng lúc</li>
            <li>Ảnh đầu tiên được chọn sẽ tự động là ảnh đại diện</li>
            <li>Sau khi tạo, bạn có thể quản lý <strong>biến thể sản phẩm</strong> (màu sắc, kích thước) và <strong>giá
                    bán</strong> ở trang chỉnh sửa</li>
        </ul>
    </div>
</div>

<style>
/* Category selector styles */
.category-selector {
    background: #f8f9fa;
}

.category-selector .form-check {
    padding: 0.5rem;
    border-bottom: 1px solid #e9ecef;
}

.category-selector .form-check:last-child {
    border-bottom: none;
}

.category-selector .form-check:hover {
    background: #e9ecef;
}

.category-level-0 {
    font-weight: 600;
}

.category-level-1 {
    padding-left: 1rem;
}

.category-level-2 {
    padding-left: 2rem;
}

.category-level-3 {
    padding-left: 3rem;
}

/* Image preview styles */
#imagePreview .preview-item {
    position: relative;
    border: 2px solid #dee2e6;
    border-radius: 0.5rem;
    overflow: hidden;
    transition: all 0.3s ease;
    background-color: #f8f9fa;
}

#imagePreview .preview-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

#imagePreview .preview-item img {
    width: 100%;
    height: 200px;
    object-fit: contain;
    object-position: center;
    display: block;
    background-color: #fff;
}

#imagePreview .preview-item .badge {
    position: absolute;
    top: 0.5rem;
    left: 0.5rem;
    z-index: 10;
}

#imagePreview .preview-item .btn-remove {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>

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
                           cb.closest('.form-check').classList.contains('category-level-0');
                });
                
                if (otherParentsChecked) {
                    e.preventDefault();
                    this.checked = false;
                    alert('Chỉ được chọn một danh mục cha! Vui lòng bỏ chọn danh mục cha hiện tại trước.');
                    return;
                }
            }
            
            updateCategorySelection();
        });
    });
    
    // Chạy lần đầu khi load trang
    updateCategorySelection();

    // Generate SKU
    document.getElementById('generateSku').addEventListener('click', function() {
        const randomStr = Math.random().toString(36).substring(2, 10).toUpperCase();
        document.getElementById('sku').value = 'PRD-' + randomStr;
    });

    // Image preview
    const imageInput = document.getElementById('images');
    const previewContainer = document.getElementById('imagePreview');

    imageInput.addEventListener('change', function(e) {
        previewContainer.innerHTML = '';
        const files = Array.from(e.target.files);

        files.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    const col = document.createElement('div');
                    col.className = 'col-md-3 col-sm-4 col-6 mb-3';

                    const badge = index === 0 ?
                        '<span class="badge bg-success">Ảnh chính</span>' :
                        '<span class="badge bg-secondary">Ảnh phụ</span>';

                    col.innerHTML = `
                        <div class="preview-item">
                            ${badge}
                            <img src="${e.target.result}" alt="Preview" style="width: 100%; height: 200px; object-fit: contain; display: block;">
                            <div class="text-center p-2 bg-light">
                                <small class="text-muted text-truncate d-block" style="max-width: 100%;" title="${file.name}">${file.name}</small>
                                <small class="text-muted">${(file.size / 1024).toFixed(1)} KB</small>
                            </div>
                        </div>
                    `;

                    previewContainer.appendChild(col);
                };

                reader.readAsDataURL(file);
            }
        });
    });

    // Form validation
    document.getElementById('productForm').addEventListener('submit', function(e) {
        const categoryCheckboxes = document.querySelectorAll('.category-checkbox:checked');

        if (categoryCheckboxes.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất một danh mục!');
            return false;
        }

        // Confirm submit
        if (!confirm('Bạn có chắc chắn muốn tạo sản phẩm này?')) {
            e.preventDefault();
            return false;
        }
    });

    // Status switch label update
    document.getElementById('status').addEventListener('change', function() {
        const label = this.nextElementSibling.querySelector('span');
        if (this.checked) {
            label.textContent = 'Kích hoạt';
            label.className = 'text-success';
        } else {
            label.textContent = 'Đã ẩn';
            label.className = 'text-danger';
        }
    });

    // ===== AUTO PRICE CALCULATION =====
    const unitCostInput = document.getElementById('unit_cost');
    const priceInput = document.getElementById('price');
    const salePriceInput = document.getElementById('sale_price');
    const taxRateInput = document.getElementById('tax_rate');

    // Format number với dấu phẩy
    function formatMoney(num) {
        return new Intl.NumberFormat('vi-VN').format(Math.round(num)) + ' đ';
    }

    // Tính toán và hiển thị thông tin
    function updatePriceSummary() {
        const unitCost = parseFloat(unitCostInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        const salePrice = parseFloat(salePriceInput.value) || 0;

        if (unitCost > 0 || price > 0) {
            document.getElementById('price-summary').style.display = 'block';

            // Cập nhật summary
            document.getElementById('summary-cost').textContent = formatMoney(unitCost);
            document.getElementById('summary-price').textContent = formatMoney(price);

            // Tính lợi nhuận: Ưu tiên giá khuyến mãi nếu có, không thì lấy giá bán
            const finalPrice = salePrice > 0 ? salePrice : price;
            const profit = finalPrice - unitCost;
            document.getElementById('summary-profit').textContent = formatMoney(profit);

            // Tính margin %
            const margin = unitCost > 0 ? ((profit / unitCost) * 100).toFixed(1) : 0;
            document.getElementById('summary-margin').textContent = margin + '%';
            document.getElementById('summary-margin').className = margin >= 20 ? 'text-success fs-5' :
                'text-warning fs-5';

            // Hiển thị profit info (theo giá cuối cùng)
            if (finalPrice > unitCost) {
                document.getElementById('profit-info').textContent =
                    `↑ Lãi: ${formatMoney(profit)} (${margin}%)`;
                document.getElementById('profit-info').className = 'text-success d-block';
            } else if (finalPrice < unitCost) {
                document.getElementById('profit-info').textContent = `↓ Lỗ: ${formatMoney(Math.abs(profit))}`;
                document.getElementById('profit-info').className = 'text-danger d-block';
            } else {
                document.getElementById('profit-info').textContent = '= Hòa vốn';
                document.getElementById('profit-info').className = 'text-warning d-block';
            }

            // Hiển thị discount info
            if (salePrice > 0 && price > 0) {
                const discount = ((price - salePrice) / price * 100).toFixed(1);
                document.getElementById('discount-info').textContent =
                    `Giảm ${discount}% (tiết kiệm ${formatMoney(price - salePrice)})`;
            } else {
                document.getElementById('discount-info').textContent = '';
            }
        } else {
            document.getElementById('price-summary').style.display = 'none';
        }
    }

    // Auto calculate giá bán từ giá nhập + margin
    document.querySelectorAll('.auto-price').forEach(btn => {
        btn.addEventListener('click', function() {
            const margin = parseFloat(this.dataset.margin);
            const unitCost = parseFloat(unitCostInput.value) || 0;

            if (unitCost > 0) {
                const calculatedPrice = Math.round(unitCost * (1 + margin / 100) / 1000) *
                1000; // Làm tròn đến nghìn
                priceInput.value = calculatedPrice;
                updatePriceSummary();

                // Highlight button
                document.querySelectorAll('.auto-price').forEach(b => b.classList.remove(
                    'active'));
                this.classList.add('active');
            } else {
                alert('Vui lòng nhập giá nhập trước!');
            }
        });
    });

    // Auto calculate giá khuyến mãi từ giá bán - discount
    document.querySelectorAll('.auto-sale').forEach(btn => {
        btn.addEventListener('click', function() {
            const discount = parseFloat(this.dataset.discount);
            const price = parseFloat(priceInput.value) || 0;

            if (price > 0) {
                const calculatedSale = Math.round(price * (1 - discount / 100) / 1000) *
                1000; // Làm tròn đến nghìn
                salePriceInput.value = calculatedSale;
                updatePriceSummary();

                // Highlight button
                document.querySelectorAll('.auto-sale').forEach(b => b.classList.remove(
                    'active'));
                this.classList.add('active');
            } else {
                alert('Vui lòng nhập giá bán trước!');
            }
        });
    });

    // Lắng nghe thay đổi giá để update summary
    [unitCostInput, priceInput, salePriceInput].forEach(input => {
        input.addEventListener('input', updatePriceSummary);
        input.addEventListener('change', updatePriceSummary);
    });

    // Gợi ý thuế phổ biến khi click vào ô thuế
    taxRateInput.addEventListener('focus', function() {
        if (this.value == '0') {
            const common = confirm(
                'Thuế VAT phổ biến:\n- 0%: Không thuế\n- 5%: Hàng thiết yếu\n- 8%: Dịch vụ\n- 10%: Hàng hóa thông thường\n\nBạn có muốn chọn 10% (phổ biến nhất)?'
                );
            if (common) {
                this.value = '10';
            }
        }
    });
});
</script>