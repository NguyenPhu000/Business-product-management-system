<?php
/**
 * View: Danh sách sản phẩm
 * Path: src/views/admin/products/index.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-box-seam"></i> Quản lý sản phẩm <span class="text-muted fs-6 fw-normal">Tổng: <?= $totalProducts ?> sản phẩm</span></h2>
        <a href="/admin/products/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Thêm sản phẩm
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = \Helpers\AuthHelper::getFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> <?= $flash ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> <?= $flash ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-funnel"></i> Bộ lọc
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="/admin/products" id="filterForm">
                <!-- Row 1: Tìm kiếm -->
                <div class="mb-3">
                    <label for="keyword" class="form-label fw-semibold">
                        <i class="bi bi-search"></i> Tìm kiếm
                    </label>
                    <input type="text" class="form-control form-control-lg" id="keyword" name="keyword"
                        placeholder="Nhập tên sản phẩm hoặc mã SKU..." value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>">
                </div>

                <!-- Row 2: Bộ lọc ngang -->
                <div class="row g-2 mb-3 align-items-end">
                    <div class="col-lg-3 col-md-6">
                        <label for="category_id" class="form-label mb-1 small fw-semibold">Danh mục</label>
                        <select class="form-select form-select-sm" id="category_id" name="category_id">
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

                    <div class="col-lg-3 col-md-6">
                        <label for="brand_id" class="form-label mb-1 small fw-semibold">Thương hiệu</label>
                        <select class="form-select form-select-sm" id="brand_id" name="brand_id">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($brands as $brand): ?>
                            <?php $selected = ($filters['brand_id'] ?? '') == $brand['id'] ? 'selected' : ''; ?>
                            <option value="<?= $brand['id'] ?>" <?= $selected ?>>
                                <?= htmlspecialchars($brand['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-6">
                        <label for="status" class="form-label mb-1 small fw-semibold">Trạng thái</label>
                        <select class="form-select form-select-sm" id="status" name="status">
                            <option value="">-- Tất cả --</option>
                            <option value="1" <?= ($filters['status'] ?? '') === '1' ? 'selected' : '' ?>>Hiển thị
                            </option>
                            <option value="0" <?= ($filters['status'] ?? '') === '0' ? 'selected' : '' ?>>Đã ẩn</option>
                        </select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <label for="sort_by" class="form-label mb-1 small fw-semibold">Sắp xếp</label>
                        <select class="form-select form-select-sm" id="sort_by" name="sort_by">
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
                </div>

                <!-- Row 3: Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-search"></i> Tìm kiếm
                            </button>
                            <a href="/admin/products" class="btn btn-secondary px-4">
                                <i class="bi bi-arrow-counterclockwise"></i> Đặt lại
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-table"></i> Danh sách sản phẩm
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
            <div class="alert alert-info text-center">
                <i class="bi bi-info-circle"></i>
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
                                    <i class="bi bi-arrow-up"></i>
                                    <?php elseif (($filters['sort_by'] ?? '') === 'name_desc'): ?>
                                    <i class="bi bi-arrow-down"></i>
                                    <?php else: ?>
                                    <i class="bi bi-arrow-down-up text-muted"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Danh mục</th>
                            <th width="130">
                                <a href="?sort_by=price_<?= ($filters['sort_by'] ?? '') === 'price_asc' ? 'desc' : 'asc' ?><?= !empty($filters['keyword']) ? '&keyword=' . urlencode($filters['keyword']) : '' ?><?= !empty($filters['category_id']) ? '&category_id=' . $filters['category_id'] : '' ?><?= !empty($filters['brand_id']) ? '&brand_id=' . $filters['brand_id'] : '' ?><?= isset($filters['status']) && $filters['status'] !== '' ? '&status=' . $filters['status'] : '' ?>"
                                    class="text-decoration-none text-dark">
                                    Giá bán
                                    <?php if (($filters['sort_by'] ?? '') === 'price_asc'): ?>
                                    <i class="bi bi-arrow-up text-success"></i>
                                    <?php elseif (($filters['sort_by'] ?? '') === 'price_desc'): ?>
                                    <i class="bi bi-arrow-down text-danger"></i>
                                    <?php else: ?>
                                    <i class="bi bi-arrow-down-up text-muted"></i>
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
                                            foreach ($categories as $cat): ?>
                                <span class="badge bg-info me-1"><?= htmlspecialchars($cat) ?></span>
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
                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="/admin/products/<?= $product['id'] ?>/edit" class="btn btn-sm btn-warning">
                                        <i class="bi bi-pencil"></i> Sửa
                                    </a>
                                    <a href="/admin/products/<?= $product['id'] ?>/variants"
                                        class="btn btn-sm btn-info">
                                        <i class="bi bi-palette"></i> Biến thể
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                                        data-id="<?= $product['id'] ?>"
                                        data-name="<?= htmlspecialchars($product['name']) ?>">
                                        <i class="bi bi-trash"></i> Xóa
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

            fetch(`/admin/products/${productId}/toggle`, {
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
        button.addEventListener('click', function() {
            const productId = this.dataset.id;
            const productName = this.dataset.name;

            if (confirm(
                    `Bạn có chắc chắn muốn xóa sản phẩm "${productName}"?\n\nHành động này không thể hoàn tác!`
                )) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/products/${productId}/delete`;
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
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

.form-select, .form-control {
    border: 1px solid #d1d3e2;
    border-radius: 0.35rem;
    transition: all 0.3s ease;
}

.form-select:focus, .form-control:focus {
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

/* Responsive */
@media (max-width: 768px) {
    .row.g-3 > div {
        margin-bottom: 0.5rem;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
}
</style>
