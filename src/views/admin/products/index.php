<?php

/**
 * View: Danh sách sản phẩm
 * Path: src/views/admin/products/index.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-box"></i> Quản lý sản phẩm <span class="text-muted fs-6 fw-normal">Tổng:
                <?= $totalProducts ?> sản phẩm</span></h2>
        <a href="/admin/products/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = \Helpers\AuthHelper::getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?= $flash ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle"></i> <?= $flash ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Bộ lọc
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="/admin/products" id="filterForm">
                <!-- Tìm kiếm và các bộ lọc ngang -->
                <div class="row mb-3 align-items-end"
                    style="display: flex !important; flex-wrap: wrap !important; gap: 1rem;">
                    <!-- Tìm kiếm -->
                    <div class="col-lg-3 col-md-6" style="flex: 0 0 auto; margin-right: 1rem;">
                        <label for="keyword" class="form-label mb-2 fw-semibold" style="font-size: 0.95rem;">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </label>
                        <input type="text" class="form-control form-control-lg" id="keyword" name="keyword"
                            placeholder="Tên sản phẩm hoặc SKU..."
                            value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>"
                            style="padding: 0.75rem 1rem; font-size: 1rem;">
                    </div>

                    <!-- Danh mục -->
                    <div class="col-lg-2 col-md-6" style="flex: 0 0 auto; margin-right: 1rem;">
                        <label for="category_id" class="form-label mb-2 fw-semibold" style="font-size: 0.95rem;">Danh
                            mục</label>
                        <select class="form-select form-select-lg" id="category_id" name="category_id"
                            style="padding: 0.75rem 1rem; font-size: 1rem;">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($categories as $category): ?>
                                <?php
                                $indent = str_repeat('&nbsp;&nbsp;', $category['level'] ?? 0);
                                $selected = ($filters['category_id'] ?? '') == $category['id'] ? 'selected' : '';
                                ?>
                                <option value="<?= $category['id'] ?>" <?= $selected ?>>
                                    <?= $indent ?><?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Thương hiệu -->
                    <div class="col-lg-2 col-md-6" style="flex: 0 0 auto; margin-right: 1rem;">
                        <label for="brand_id" class="form-label mb-2 fw-semibold" style="font-size: 0.95rem;">Thương
                            hiệu</label>
                        <select class="form-select form-select-lg" id="brand_id" name="brand_id"
                            style="padding: 0.75rem 1rem; font-size: 1rem;">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($brands as $brand): ?>
                                <?php $selected = ($filters['brand_id'] ?? '') == $brand['id'] ? 'selected' : ''; ?>
                                <option value="<?= $brand['id'] ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($brand['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Trạng thái -->
                    <div class="col-lg-2 col-md-6" style="flex: 0 0 auto; margin-right: 1rem;">
                        <label for="status" class="form-label mb-2 fw-semibold" style="font-size: 0.95rem;">Trạng
                            thái</label>
                        <select class="form-select form-select-lg" id="status" name="status"
                            style="padding: 0.75rem 1rem; font-size: 1rem;">
                            <option value="">-- Tất cả --</option>
                            <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Hiển thị
                            </option>
                            <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Đã ẩn</option>
                        </select>
                    </div>

                    <!-- Sắp xếp -->
                    <div class="col-lg-2 col-md-6" style="flex: 0 0 auto; margin-right: 1rem;">
                        <label for="sort_by" class="form-label mb-2 fw-semibold" style="font-size: 0.95rem;">Sắp
                            xếp</label>
                        <select class="form-select form-select-lg" id="sort_by" name="sort_by"
                            style="padding: 0.75rem 1rem; font-size: 1rem;">
                            <option value="created_at_desc"
                                <?= ($filters['sort_by'] ?? 'created_at_desc') === 'created_at_desc' ? 'selected' : '' ?>>
                                Mới nhất
                            </option>
                            <option value="created_at_asc"
                                <?= ($filters['sort_by'] ?? '') === 'created_at_asc' ? 'selected' : '' ?>>
                                Cũ nhất
                            </option>
                            <option value="price_asc"
                                <?= ($filters['sort_by'] ?? '') === 'price_asc' ? 'selected' : '' ?>>
                                Giá thấp → cao
                            </option>
                            <option value="price_desc"
                                <?= ($filters['sort_by'] ?? '') === 'price_desc' ? 'selected' : '' ?>>
                                Giá cao → thấp
                            </option>
                            <option value="name_asc"
                                <?= ($filters['sort_by'] ?? '') === 'name_asc' ? 'selected' : '' ?>>
                                Tên A → Z
                            </option>
                            <option value="name_desc"
                                <?= ($filters['sort_by'] ?? '') === 'name_desc' ? 'selected' : '' ?>>
                                Tên Z → A
                            </option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-lg-1 col-md-12" style="flex: 0 0 auto;">
                        <label class="form-label mb-2 fw-semibold"
                            style="visibility: hidden; font-size: 0.95rem;">.</label>
                        <button type="submit" class="btn btn-primary btn-lg w-100"
                            style="padding: 0.75rem 1rem; font-size: 1rem;">
                            <i class="fas fa-search"></i> Tìm
                        </button>
                    </div>
                </div>

                <!-- Nút đặt lại -->
                <div class="row">
                    <div class="col-12">
                        <a href="/admin/products" class="btn btn-secondary btn-lg"
                            style="padding: 0.75rem 1.5rem; font-size: 1rem;">
                            <i class="fas fa-redo"></i> Đặt lại
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Danh sách sản phẩm
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle"></i>
                Chưa có sản phẩm nào. <a href="/admin/products/create">Thêm sản phẩm đầu tiên</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th width="100">Hình ảnh</th>
                            <th width="120">SKU</th>
                            <th>
                                <a href="?sort_by=name_<?= ($filters['sort_by'] ?? '') === 'name_asc' ? 'desc' : 'asc' ?><?= !empty($filters['keyword']) ? '&keyword=' . urlencode($filters['keyword']) : '' ?><?= !empty($filters['category_id']) ? '&category_id=' . $filters['category_id'] : '' ?><?= !empty($filters['brand_id']) ? '&brand_id=' . $filters['brand_id'] : '' ?><?= isset($filters['status']) && $filters['status'] !== '' ? '&status=' . $filters['status'] : '' ?>"
                                    class="text-decoration-none text-dark">
                                    Tên sản phẩm
                                    <?php if (($filters['sort_by'] ?? '') === 'name_asc'): ?>
                                    <i class="fas fa-arrow-up"></i>
                                    <?php elseif (($filters['sort_by'] ?? '') === 'name_desc'): ?>
                                    <i class="fas fa-arrow-down"></i>
                                    <?php else: ?>
                                    <i class="fas fa-arrow-down-up text-muted"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Danh mục</th>
                            <th width="130">
                                <a href="?sort_by=price_<?= ($filters['sort_by'] ?? '') === 'price_asc' ? 'desc' : 'asc' ?><?= !empty($filters['keyword']) ? '&keyword=' . urlencode($filters['keyword']) : '' ?><?= !empty($filters['category_id']) ? '&category_id=' . $filters['category_id'] : '' ?><?= !empty($filters['brand_id']) ? '&brand_id=' . $filters['brand_id'] : '' ?><?= isset($filters['status']) && $filters['status'] !== '' ? '&status=' . $filters['status'] : '' ?>"
                                    class="text-decoration-none text-dark">
                                    Giá bán
                                    <?php if (($filters['sort_by'] ?? '') === 'price_asc'): ?>
                                    <i class="fas fa-arrow-up text-success"></i>
                                    <?php elseif (($filters['sort_by'] ?? '') === 'price_desc'): ?>
                                    <i class="fas fa-arrow-down text-danger"></i>
                                    <?php else: ?>
                                    <i class="fas fa-arrow-down-up text-muted"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th width="100">Trạng thái</th>
                            <th width="220">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $index => $product): ?>
                        <tr>
                            <td class="text-center">
                                <?= ($currentPage - 1) * 20 + $index + 1 ?>
                            </td>
                            <td>
                                <img src="<?= $product['primary_image'] ?? '/assets/images/no-image.png' ?>"
                                    alt="<?= htmlspecialchars($product['name']) ?>" class="img-thumbnail"
                                    style="width: 60px; height: 60px; object-fit: cover;">
                            </td>
                            <td>
                                <code><?= htmlspecialchars($product['sku']) ?></code>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                <?php if (!empty($product['short_desc'])): ?>
                                <br>
                                <small class="text-muted">
                                    <?= htmlspecialchars(mb_substr($product['short_desc'], 0, 80)) ?>...
                                </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($product['category_names'])): ?>
                                <?php
                                            $categories = explode(', ', $product['category_names']);
                                            $colors = ['primary', 'success', 'danger', 'warning', 'info', 'secondary', 'dark'];
                                            foreach ($categories as $index => $cat):
                                                $colorClass = $colors[$index % count($colors)];
                                            ?>
                                <span
                                    class="badge-category badge-<?= $colorClass ?>"><?= htmlspecialchars($cat) ?></span>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <span class="text-muted">Chưa phân loại</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (!empty($product['sale_price']) && $product['sale_price'] > 0): ?>
                                <div>
                                    <small class="text-muted text-decoration-line-through">
                                        <?= number_format($product['price'], 0, ',', '.') ?> đ
                                    </small>
                                </div>
                                <div>
                                    <strong class="text-danger">
                                        <?= number_format($product['sale_price'], 0, ',', '.') ?> đ
                                    </strong>
                                </div>
                                <?php else: ?>
                                <strong class="text-primary">
                                    <?= number_format($product['price'] ?? 0, 0, ',', '.') ?> đ
                                </strong>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="form-check form-switch d-inline-block">
                                    <input class="form-check-input toggle-status" type="checkbox" role="switch"
                                        data-id="<?= $product['id'] ?>" <?= $product['status'] == 1 ? 'checked' : '' ?>
                                        title="Click để ẩn/hiện sản phẩm">
                                </div>
                                <?php if ($product['status'] == 1): ?>
                                <small class="text-success d-block">Đang bán</small>
                                <?php else: ?>
                                <small class="text-secondary d-block">Đã ẩn</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="/admin/products/edit/<?= $product['id'] ?>" class="btn btn-outline-warning"
                                        title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="/admin/products/<?= $product['id'] ?>/variants"
                                        class="btn btn-outline-info" title="Quản lý biến thể">
                                        <i class="fas fa-palette"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger btn-delete"
                                        data-id="<?= $product['id'] ?>"
                                        data-name="<?= htmlspecialchars($product['name']) ?>" title="Xóa sản phẩm">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <?php
                    // Build query string để giữ lại các filter
                    $queryParams = [];
                    if (!empty($filters['keyword'])) $queryParams[] = 'keyword=' . urlencode($filters['keyword']);
                    if (!empty($filters['category_id'])) $queryParams[] = 'category_id=' . $filters['category_id'];
                    if (!empty($filters['brand_id'])) $queryParams[] = 'brand_id=' . $filters['brand_id'];
                    if (isset($filters['status']) && $filters['status'] !== '') $queryParams[] = 'status=' . $filters['status'];
                    if (!empty($filters['sort_by'])) $queryParams[] = 'sort_by=' . $filters['sort_by'];
                    $queryString = !empty($queryParams) ? '&' . implode('&', $queryParams) : '';
                    ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <!-- Previous -->
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage - 1 ?><?= $queryString ?>"
                            aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <!-- Page numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?><?= $queryString ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>

                    <!-- Next -->
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $currentPage + 1 ?><?= $queryString ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle product status
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const productId = this.dataset.id;
            const isChecked = this.checked;
            const statusText = this.parentElement.nextElementSibling;

            fetch(`/admin/products/toggle/${productId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update text
                        if (isChecked) {
                            statusText.textContent = 'Đang bán';
                            statusText.className = 'text-success d-block';
                        } else {
                            statusText.textContent = 'Đã ẩn';
                            statusText.className = 'text-secondary d-block';
                        }
                    } else {
                        alert('Lỗi: ' + data.message);
                        this.checked = !isChecked; // Revert
                    }
                })
                .catch(err => {
                    alert('Có lỗi xảy ra!');
                    this.checked = !isChecked; // Revert
                });
        });
    });

    // Delete product
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();

            const productId = this.dataset.id;
            const productName = this.dataset.name;

            console.log('Delete button clicked:', {
                productId,
                productName
            });

            if (confirm(
                    `Bạn có chắc chắn muốn xóa sản phẩm "${productName}"?\n\nHành động này không thể hoàn tác!`
                )) {
                console.log('User confirmed delete');

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/products/delete/${productId}`;

                console.log('Form action:', form.action);

                document.body.appendChild(form);
                form.submit();
            } else {
                console.log('User cancelled delete');
            }
        });
    });
});
</script>

<style>
/* Custom styles cho trang danh sách sản phẩm */
.card-header.bg-light {
    background-color: #f8f9fc !important;
    border-bottom: 2px solid #e3e6f0;
}

.form-label.fw-semibold {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
}

.form-control-lg {
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.form-select,
.form-control {
    border: 1px solid #d1d3e2;
    border-radius: 0.35rem;
    transition: all 0.3s ease;
}

.form-select:focus,
.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

.btn {
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.35rem;
    transition: all 0.3s ease;
}

.btn-primary {
    min-width: 120px;
}

.btn-secondary {
    min-width: 120px;
}

.d-flex.gap-2 {
    gap: 0.5rem;
}

/* Table styling */
.table {
    border-collapse: separate;
    border-spacing: 0;
}

.table thead th {
    background: #7b7a7aff;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    padding: 1rem 0.75rem;
    border: none;
    vertical-align: middle;
}

.table thead th a {
    color: white !important;
    text-decoration: none;
}

.table thead th a:hover {
    color: #e0e0e0 !important;
}

.table tbody tr {
    transition: all 0.2s ease;
    background: white;
}

.table tbody tr:hover {
    background: #f8f9fc;
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.table tbody td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-color: #e3e6f0;
}

.img-thumbnail {
    border-radius: 8px;
    border: 2px solid #e3e6f0;
    transition: transform 0.2s ease;
}

.img-thumbnail:hover {
    transform: scale(1.1);
    border-color: #4e73df;
}

.badge {
    padding: 0.35rem 0.65rem;
    font-weight: 500;
    font-size: 0.75rem;
    border-radius: 6px;
}

/* Category badges - chỉ viền, không background */
.badge-category {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    margin: 0.15rem;
    font-size: 0.75rem;
    font-weight: 500;
    border-radius: 20px;
    border: 1.5px solid;
    background: transparent !important;
    transition: all 0.2s ease;
}

.badge-category.badge-primary {
    color: #4e73df;
    border-color: #4e73df;
}

.badge-category.badge-primary:hover {
    background: rgba(78, 115, 223, 0.1) !important;
}

.badge-category.badge-success {
    color: #1cc88a;
    border-color: #1cc88a;
}

.badge-category.badge-success:hover {
    background: rgba(28, 200, 138, 0.1) !important;
}

.badge-category.badge-danger {
    color: #e74a3b;
    border-color: #e74a3b;
}

.badge-category.badge-danger:hover {
    background: rgba(231, 74, 59, 0.1) !important;
}

.badge-category.badge-warning {
    color: #f6c23e;
    border-color: #f6c23e;
}

.badge-category.badge-warning:hover {
    background: rgba(246, 194, 62, 0.1) !important;
}

.badge-category.badge-info {
    color: #36b9cc;
    border-color: #36b9cc;
}

.badge-category.badge-info:hover {
    background: rgba(54, 185, 204, 0.1) !important;
}

.badge-category.badge-secondary {
    color: #858796;
    border-color: #858796;
}

.badge-category.badge-secondary:hover {
    background: rgba(133, 135, 150, 0.1) !important;
}

.badge-category.badge-dark {
    color: #5a5c69;
    border-color: #5a5c69;
}

.badge-category.badge-dark:hover {
    background: rgba(90, 92, 105, 0.1) !important;
}

.btn-group .btn {
    padding: 0.4rem 0.75rem;
    font-size: 0.875rem;
}

.btn-outline-warning {
    color: #f6c23e;
    border-color: #f6c23e;
}

.btn-outline-warning:hover {
    background-color: #f6c23e;
    border-color: #f6c23e;
    color: #fff;
}

.btn-outline-info {
    color: #36b9cc;
    border-color: #36b9cc;
}

.btn-outline-info:hover {
    background-color: #36b9cc;
    border-color: #36b9cc;
    color: #fff;
}

.btn-outline-danger {
    color: #e74a3b;
    border-color: #e74a3b;
}

.btn-outline-danger:hover {
    background-color: #e74a3b;
    border-color: #e74a3b;
    color: #fff;
}

/* Responsive */
@media (max-width: 768px) {
    .row.g-3>div {
        margin-bottom: 0.5rem;
    }

    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>