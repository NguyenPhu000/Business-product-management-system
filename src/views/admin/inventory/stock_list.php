<?php

/**
 * View: Danh sách tồn kho
 * Path: src/views/admin/inventory/stock_list.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="bi bi-box-seam-fill text-primary"></i> Quản lý Tồn Kho
            <span class="text-muted fs-6 fw-normal">
                Tổng: <?= count($products ?? []) ?> sản phẩm
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
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Sắp hết hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($stats['low_stock'] ?? []) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Hết hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($stats['out_of_stock'] ?? []) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box-open fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tổng cảnh báo
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $stats['total_alerts'] ?? 0 ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đang hiển thị
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($products ?? []) ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-funnel"></i> Bộ lọc
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="/admin/inventory" id="filterForm">
                <div class="row mb-3 align-items-end">
                    <!-- Tìm kiếm -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="search" class="form-label mb-2 fw-semibold">
                            <i class="bi bi-search"></i> Tìm kiếm
                        </label>
                        <input type="text" class="form-control form-control-lg" id="search" name="search"
                            placeholder="Tên sản phẩm hoặc SKU..."
                            value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>

                    <!-- Kho -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label for="warehouse" class="form-label mb-2 fw-semibold">Kho hàng</label>
                        <select class="form-select form-select-lg" id="warehouse" name="warehouse">
                            <option value="">-- Tất cả kho --</option>
                            <option value="default"
                                <?= ($filters['warehouse'] ?? '') === 'default' ? 'selected' : '' ?>>
                                Kho mặc định
                            </option>
                            <option value="warehouse1"
                                <?= ($filters['warehouse'] ?? '') === 'warehouse1' ? 'selected' : '' ?>>
                                Kho 1
                            </option>
                            <option value="warehouse2"
                                <?= ($filters['warehouse'] ?? '') === 'warehouse2' ? 'selected' : '' ?>>
                                Kho 2
                            </option>
                        </select>
                    </div>

                    <!-- Trạng thái tồn kho -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label for="stock_status" class="form-label mb-2 fw-semibold">Trạng thái</label>
                        <select class="form-select form-select-lg" id="stock_status" name="stock_status">
                            <option value="">-- Tất cả --</option>
                            <option value="low" <?= ($filters['stock_status'] ?? '') === 'low' ? 'selected' : '' ?>>
                                Sắp hết hàng
                            </option>
                            <option value="out" <?= ($filters['stock_status'] ?? '') === 'out' ? 'selected' : '' ?>>
                                Hết hàng
                            </option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-search"></i> Lọc
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-6 mb-3">
                        <a href="/admin/inventory" class="btn btn-secondary btn-lg w-100">
                            <i class="bi bi-x-circle"></i> Xóa lọc
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
                <i class="bi bi-table"></i> Danh sách tồn kho
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($products)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> Không tìm thấy sản phẩm nào.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="stockTable">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="15%">Sản phẩm</th>
                                <th width="10%">SKU</th>
                                <th width="15%">Variant</th>
                                <th width="8%">Kho</th>
                                <th width="8%" class="text-center">Tồn kho</th>
                                <th width="8%" class="text-center">Ngưỡng</th>
                                <th width="10%" class="text-center">Trạng thái</th>
                                <th width="12%">Cập nhật</th>
                                <th width="9%" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?= $product['product_id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($product['product_name']) ?></strong>
                                    </td>
                                    <td>
                                        <code><?= htmlspecialchars($product['product_sku']) ?></code>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($product['variant_sku']) ?>
                                            <?php if (!empty($product['variant_attributes'])): ?>
                                                <br><span
                                                    class="badge bg-secondary"><?= htmlspecialchars($product['variant_attributes']) ?></span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($product['warehouse']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <strong class="fs-5"><?= number_format($product['quantity']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($product['min_threshold']) ?>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $status = $product['stock_status'] ?? 'in_stock';
                                        $badges = [
                                            'out_of_stock' => '<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Hết hàng</span>',
                                            'low_stock' => '<span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle"></i> Sắp hết</span>',
                                            'in_stock' => '<span class="badge bg-success"><i class="fas fa-check-circle"></i> Còn hàng</span>'
                                        ];
                                        echo $badges[$status] ?? $badges['in_stock'];
                                        ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($product['last_updated'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="/admin/inventory/detail?id=<?= $product['variant_id'] ?>&warehouse=<?= urlencode($product['warehouse']) ?>"
                                                class="btn btn-info btn-sm" title="Chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/admin/inventory/adjust?variant_id=<?= $product['variant_id'] ?>&warehouse=<?= urlencode($product['warehouse']) ?>"
                                                class="btn btn-warning btn-sm" title="Điều chỉnh">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (!empty($pagination) && $pagination['last_page'] > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- Previous -->
                            <li class="page-item <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $pagination['current_page'] - 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                    <i class="bi bi-chevron-left"></i>
                                </a>
                            </li>

                            <!-- Page Numbers -->
                            <?php
                            $start = max(1, $pagination['current_page'] - 2);
                            $end = min($pagination['last_page'], $pagination['current_page'] + 2);
                            for ($i = $start; $i <= $end; $i++):
                            ?>
                                <li class="page-item <?= $i === $pagination['current_page'] ? 'active' : '' ?>">
                                    <a class="page-link"
                                        href="?page=<?= $i ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li
                                class="page-item <?= $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $pagination['current_page'] + 1 ?><?= http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : '' ?>">
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }

    .text-xs {
        font-size: .7rem;
    }

    #stockTable tbody tr:hover {
        background-color: #f8f9fc;
    }
</style>

<script>
    // Auto submit form on filter change
    document.querySelectorAll('#filterForm select').forEach(select => {
        select.addEventListener('change', () => {
            document.getElementById('filterForm').submit();
        });
    });
</script>