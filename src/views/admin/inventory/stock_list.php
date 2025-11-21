<?php
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-boxes text-primary"></i> Quản lý Tồn Kho
            <span class="text-muted fs-6 fw-normal">
                Tổng: <?= $pagination['total'] ?? count($products ?? []) ?> sản phẩm
            </span>
        </h2>
        <div class="d-flex gap-2">
            <a href="/admin/inventory/low-stock" class="btn btn-warning">
                <i class="fas fa-exclamation-triangle"></i> Cảnh báo (<?= $stats['total_alerts'] ?? 0 ?>)
            </a>
            <a href="/admin/inventory/history" class="btn btn-info">
                <i class="fas fa-history"></i> Lịch sử giao dịch
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrapper text-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Sắp hết hàng</div>
                    <div class="stat-number"><?= count($stats['low_stock'] ?? []) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrapper text-danger">
                    <i class="fas fa-box-open"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Hết hàng</div>
                    <div class="stat-number"><?= count($stats['out_of_stock'] ?? []) ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrapper text-info">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Tổng cảnh báo</div>
                    <div class="stat-number"><?= $stats['total_alerts'] ?? 0 ?></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrapper text-primary">
                    <i class="fas fa-list"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Đang hiển thị</div>
                    <div class="stat-number"><?= count($products ?? []) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body p-3">
            <form method="GET" action="/admin/inventory" id="filterForm">
                <div class="d-flex flex-wrap align-items-end gap-2 mb-2">
                    <div class="flex-fill" style="min-width: 200px; max-width: 300px;">
                        <label for="search" class="form-label text-muted small mb-1">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </label>
                        <input type="text" class="form-control form-control-sm" id="search" name="search"
                            placeholder="Tên, SKU sản phẩm..."
                            value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>
                    <div style="min-width: 150px;">
                        <label for="category_id" class="form-label text-muted small mb-1">Danh mục</label>
                        <select class="form-select form-select-sm" id="category_id" name="category_id">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($categories ?? [] as $cat): ?>
                                <option value="<?= $cat['id'] ?>"
                                    <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="min-width: 150px;">
                        <label for="brand_id" class="form-label text-muted small mb-1">Thương hiệu</label>
                        <select class="form-select form-select-sm" id="brand_id" name="brand_id">
                            <option value="">-- Tất cả --</option>
                            <?php foreach ($brands ?? [] as $brand): ?>
                                <option value="<?= $brand['id'] ?>"
                                    <?= ($filters['brand_id'] ?? '') == $brand['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($brand['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="min-width: 140px;">
                        <label for="stock_status" class="form-label text-muted small mb-1">Trạng thái</label>
                        <select class="form-select form-select-sm" id="stock_status" name="stock_status">
                            <option value="">-- Tất cả --</option>
                            <option value="low" <?= ($filters['stock_status'] ?? '') === 'low' ? 'selected' : '' ?>>Sắp
                                hết</option>
                            <option value="out" <?= ($filters['stock_status'] ?? '') === 'out' ? 'selected' : '' ?>>Hết
                                hàng</option>
                        </select>
                    </div>
                    <div style="min-width: 150px;">
                        <label for="sort_by" class="form-label text-muted small mb-1">Sắp xếp</label>
                        <select class="form-select form-select-sm" id="sort_by" name="sort_by">
                            <option value="last_updated"
                                <?= ($filters['sort_by'] ?? 'last_updated') === 'last_updated' ? 'selected' : '' ?>>Mới
                                nhất</option>
                            <option value="product_name"
                                <?= ($filters['sort_by'] ?? '') === 'product_name' ? 'selected' : '' ?>>Tên A-Z</option>
                            <option value="quantity"
                                <?= ($filters['sort_by'] ?? '') === 'quantity' ? 'selected' : '' ?>>Số lượng</option>
                            <option value="min_threshold"
                                <?= ($filters['sort_by'] ?? '') === 'min_threshold' ? 'selected' : '' ?>>Ngưỡng</option>
                        </select>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fas fa-search"></i> Tìm
                        </button>
                        <a href="/admin/inventory" class="btn btn-secondary btn-sm px-3">
                            <i class="fas fa-redo"></i>
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-sm px-2" data-bs-toggle="collapse"
                            data-bs-target="#advancedFilters">
                            <i class="fas fa-sliders-h"></i>
                        </button>
                    </div>
                </div>
                <div class="collapse" id="advancedFilters">
                    <hr class="my-2">
                    <div class="d-flex flex-wrap align-items-end gap-2">
                        <div style="min-width: 120px;">
                            <label for="quantity_min" class="form-label text-muted small mb-1">Tồn từ</label>
                            <input type="number" class="form-control form-control-sm" id="quantity_min"
                                name="quantity_min" placeholder="Min" min="0"
                                value="<?= htmlspecialchars($filters['quantity_min'] ?? '') ?>">
                        </div>
                        <div style="min-width: 120px;">
                            <label for="quantity_max" class="form-label text-muted small mb-1">Tồn đến</label>
                            <input type="number" class="form-control form-control-sm" id="quantity_max"
                                name="quantity_max" placeholder="Max" min="0"
                                value="<?= htmlspecialchars($filters['quantity_max'] ?? '') ?>">
                        </div>
                        <div style="min-width: 120px;">
                            <label for="sort_order" class="form-label text-muted small mb-1">Thứ tự</label>
                            <select class="form-select form-select-sm" id="sort_order" name="sort_order">
                                <option value="DESC"
                                    <?= ($filters['sort_order'] ?? 'DESC') === 'DESC' ? 'selected' : '' ?>>Giảm dần
                                </option>
                                <option value="ASC" <?= ($filters['sort_order'] ?? '') === 'ASC' ? 'selected' : '' ?>>
                                    Tăng dần</option>
                            </select>
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
                <i class="fas fa-table"></i> Danh sách tồn kho
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Không tìm thấy sản phẩm nào.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="stockTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Sản phẩm</th>
                                <th>Biến thể</th>
                                <th>SKU</th>
                                <th>Tồn kho</th>
                                <th>Ngưỡng</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $index = ($pagination['from'] ?? 1);
                            foreach ($products as $product):
                                $rowClass = '';
                                if ($product['stock_status'] === 'out_of_stock') {
                                    $rowClass = 'table-danger-subtle';
                                } elseif ($product['stock_status'] === 'low_stock') {
                                    $rowClass = 'table-warning-subtle';
                                }
                                $stockPercent = $product['min_threshold'] > 0 ? min(100, ($product['quantity'] / $product['min_threshold']) * 100) : 100;
                            ?>
                                <tr class="<?= $rowClass ?>">
                                    <td><?= $index++ ?></td>
                                    <td><strong><?= htmlspecialchars($product['product_name']) ?></strong></td>
                                    <td>
                                        <?php if (!empty($product['variant_attributes'])): ?>
                                            <?php
                                            $attrs = json_decode($product['variant_attributes'], true);
                                            if (is_array($attrs)) {
                                                foreach ($attrs as $key => $value) {
                                                    echo '<span class="badge bg-secondary me-1">' . htmlspecialchars(ucfirst($key)) . ': ' . htmlspecialchars($value) . '</span>';
                                                }
                                            }
                                            ?>
                                        <?php else: ?>
                                            <span class="text-muted">Mặc định</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><code><?= htmlspecialchars($product['variant_sku'] ?? $product['product_sku']) ?></code>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong class="mb-1"><?= number_format($product['quantity']) ?></strong>
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar <?= $product['stock_status'] === 'out_of_stock' ? 'bg-danger' : ($product['stock_status'] === 'low_stock' ? 'bg-warning' : 'bg-success') ?>"
                                                    role="progressbar" style="width: <?= $stockPercent ?>%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= number_format($product['min_threshold']) ?></td>
                                    <td>
                                        <?php if ($product['stock_status'] === 'out_of_stock'): ?>
                                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Hết hàng</span>
                                        <?php elseif ($product['stock_status'] === 'low_stock'): ?>
                                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Sắp
                                                hết</span>
                                        <?php else: ?>
                                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Còn hàng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/inventory/detail/<?= $product['variant_id'] ?>"
                                                class="btn btn-outline-info" title="Chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <!-- Removed direct 'Điều chỉnh' button from list view (kept in detail/edit pages) -->
                                            <a href="/admin/purchase/create?variant_id=<?= $product['variant_id'] ?>"
                                                class="btn btn-outline-success" title="Tạo phiếu nhập">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="/admin/sales/create?variant_id=<?= $product['variant_id'] ?>"
                                                class="btn btn-outline-danger" title="Tạo phiếu xuất">
                                                <i class="fas fa-minus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (!empty($pagination)): ?>
                    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-muted small">
                                Hiển thị <strong><?= $pagination['from'] ?? 0 ?></strong> đến
                                <strong><?= $pagination['to'] ?? 0 ?></strong>
                                trong tổng số <strong><?= $pagination['total'] ?? 0 ?></strong> bản ghi
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <label class="text-muted small mb-0">Hiển thị:</label>
                                <select class="form-select form-select-sm" style="width: auto;"
                                    onchange="changePerPage(this.value)">
                                    <option value="25" <?= ($perPage ?? 50) == 25 ? 'selected' : '' ?>>25</option>
                                    <option value="50" <?= ($perPage ?? 50) == 50 ? 'selected' : '' ?>>50</option>
                                    <option value="100" <?= ($perPage ?? 50) == 100 ? 'selected' : '' ?>>100</option>
                                    <option value="200" <?= ($perPage ?? 50) == 200 ? 'selected' : '' ?>>200</option>
                                </select>
                            </div>
                        </div>
                        <?php if ($pagination['last_page'] > 1): ?>
                            <nav aria-label="Page navigation">
                                <ul class="pagination pagination-sm mb-0">
                                    <?php if ($pagination['current_page'] > 2): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="?page=1&per_page=<?= $perPage ?? 50 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="?page=<?= $pagination['current_page'] - 1 ?>&per_page=<?= $perPage ?? 50 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    </li>
                                    <?php
                                    $start = max(1, $pagination['current_page'] - 2);
                                    $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                                    if ($start > 1):
                                    ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="?page=1&per_page=<?= $perPage ?? 50 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">1</a>
                                        </li>
                                        <?php if ($start > 2): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php for ($i = $start; $i <= $end; $i++): ?>
                                        <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                            <a class="page-link"
                                                href="?page=<?= $i ?>&per_page=<?= $perPage ?? 50 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php if ($end < $pagination['last_page']): ?>
                                        <?php if ($end < $pagination['last_page'] - 1): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="?page=<?= $pagination['last_page'] ?>&per_page=<?= $perPage ?? 50 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                                <?= $pagination['last_page'] ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    <li
                                        class="page-item <?= $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' ?>">
                                        <a class="page-link"
                                            href="?page=<?= $pagination['current_page'] + 1 ?>&per_page=<?= $perPage ?? 50 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                    <?php if ($pagination['current_page'] < $pagination['last_page'] - 1): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                                href="?page=<?= $pagination['last_page'] ?>&per_page=<?= $perPage ?? 50 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .stat-card {
        background-color: #fff;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .stat-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.05);
        flex-shrink: 0;
    }

    .stat-icon-wrapper i {
        font-size: 1.5rem;
    }

    .stat-content {
        flex: 1;
        min-width: 0;
    }

    .stat-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
    }

    #stockTable tbody tr {
        transition: all 0.2s ease;
    }

    #stockTable tbody tr:hover {
        background-color: #f8f9fc;
        transform: scale(1.01);
    }

    .table-danger-subtle {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    .table-warning-subtle {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
    }

    .progress-bar {
        transition: width 0.6s ease;
    }

    .pagination-sm .page-link {
        padding: 0.4rem 0.65rem;
        font-size: 0.875rem;
        border-radius: 0.25rem;
        margin: 0 2px;
    }

    .pagination-sm .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
        font-weight: 600;
    }

    .pagination-sm .page-link:hover {
        background-color: #f8f9fc;
        color: #4e73df;
    }

    .pagination-sm .page-item.disabled .page-link {
        background-color: transparent;
        border-color: #dee2e6;
    }
</style>

<script>
    document.querySelectorAll('#filterForm select').forEach(select => {
        select.addEventListener('change', () => {
            document.getElementById('filterForm').submit();
        });
    });

    function changePerPage(perPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', perPage);
        url.searchParams.set('page', '1');
        window.location.href = url.toString();
    }

    // Quick import removed: use full Purchase form for imports
</script>

<!-- Quick import modal removed - use purchase form instead -->