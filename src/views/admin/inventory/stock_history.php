<?php

/**
 * View: Lịch sử giao dịch kho
 * Path: src/views/admin/inventory/stock_history.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-history text-info"></i> Lịch Sử Giao Dịch Kho
            <span class="text-muted fs-6 fw-normal">
                Tổng: <?= $pagination['total'] ?? 0 ?> giao dịch
            </span>
        </h2>
        <div class="d-flex gap-2">
            <a href="/admin/inventory" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="/admin/inventory/report?<?= http_build_query(array_filter($filters ?? [])) ?>"
                class="btn btn-success">
                <i class="fas fa-file-excel"></i> Xuất CSV
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Bộ lọc
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="/admin/inventory/history" id="filterForm">
                <div class="row mb-3">
                    <!-- Tìm kiếm -->
                    <div class="col-lg-3 col-md-6 mb-3">
                        <label for="search" class="form-label mb-2 fw-semibold">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </label>
                        <input type="text" class="form-control" id="search" name="search"
                            placeholder="Tên sản phẩm hoặc SKU..."
                            value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                    </div>

                    <!-- Loại giao dịch -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label for="type" class="form-label mb-2 fw-semibold">Loại giao dịch</label>
                        <select class="form-select" id="type" name="type">
                            <option value="">-- Tất cả --</option>
                            <option value="import" <?= ($filters['type'] ?? '') === 'import' ? 'selected' : '' ?>>
                                Nhập kho
                            </option>
                            <option value="export" <?= ($filters['type'] ?? '') === 'export' ? 'selected' : '' ?>>
                                Xuất kho
                            </option>
                            <option value="adjust" <?= ($filters['type'] ?? '') === 'adjust' ? 'selected' : '' ?>>
                                Điều chỉnh
                            </option>
                            <option value="return" <?= ($filters['type'] ?? '') === 'return' ? 'selected' : '' ?>>
                                Trả hàng
                            </option>
                        </select>
                    </div>

                    <!-- Kho -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label for="warehouse" class="form-label mb-2 fw-semibold">Kho hàng</label>
                        <select class="form-select" id="warehouse" name="warehouse">
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

                    <!-- Từ ngày -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label for="from_date" class="form-label mb-2 fw-semibold">Từ ngày</label>
                        <input type="date" class="form-control" id="from_date" name="from_date"
                            value="<?= htmlspecialchars($filters['from_date'] ?? '') ?>">
                    </div>

                    <!-- Đến ngày -->
                    <div class="col-lg-2 col-md-6 mb-3">
                        <label for="to_date" class="form-label mb-2 fw-semibold">Đến ngày</label>
                        <input type="date" class="form-control" id="to_date" name="to_date"
                            value="<?= htmlspecialchars($filters['to_date'] ?? '') ?>">
                    </div>

                    <!-- Buttons -->
                    <div class="col-lg-1 col-md-6 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>

                <!-- Quick filters -->
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('today')">
                        Hôm nay
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('yesterday')">
                        Hôm qua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('week')">
                        7 ngày qua
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setDateRange('month')">
                        30 ngày qua
                    </button>
                    <a href="/admin/inventory/history" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-times-circle"></i> Xóa lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-table"></i> Danh sách giao dịch
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Không tìm thấy giao dịch nào.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th width="4%">ID</th>
                                <th width="18%">Sản phẩm</th>
                                <th width="10%">Variant</th>
                                <th width="8%">Kho</th>
                                <th width="10%" class="text-center">Loại GD</th>
                                <th width="8%" class="text-center">Số lượng</th>
                                <th width="12%">Tham chiếu</th>
                                <th width="15%">Ghi chú</th>
                                <th width="10%">Người thực hiện</th>
                                <th width="12%">Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $trans): ?>
                                <tr>
                                    <td><?= $trans['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($trans['product_name'] ?? 'N/A') ?></strong>
                                        <br><small
                                            class="text-muted"><?= htmlspecialchars($trans['product_sku'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($trans['variant_sku'] ?? '') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= htmlspecialchars($trans['warehouse']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $typeClass = [
                                            'import' => 'success',
                                            'export' => 'danger',
                                            'adjust' => 'warning',
                                            'return' => 'info'
                                        ];
                                        $typeLabel = [
                                            'import' => 'Nhập kho',
                                            'export' => 'Xuất kho',
                                            'adjust' => 'Điều chỉnh',
                                            'return' => 'Trả hàng'
                                        ];
                                        $type = $trans['type'] ?? 'adjust';
                                        ?>
                                        <span class="badge bg-<?= $typeClass[$type] ?? 'secondary' ?>">
                                            <?= $typeLabel[$type] ?? $type ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $qty = $trans['quantity_change'];
                                        $color = $qty > 0 ? 'success' : 'danger';
                                        $icon = $qty > 0 ? 'arrow-up' : 'arrow-down';
                                        ?>
                                        <strong class="text-<?= $color ?>">
                                            <i class="fas fa-<?= $icon ?>"></i>
                                            <?= $qty > 0 ? '+' : '' ?><?= number_format($qty) ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if (!empty($trans['reference_type'])): ?>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($trans['reference_type']) ?>
                                                <?php if (!empty($trans['reference_id'])): ?>
                                                    #<?= $trans['reference_id'] ?>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($trans['note'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <small>
                                            <?= htmlspecialchars($trans['created_by_fullname'] ?? $trans['created_by_name'] ?? 'N/A') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i:s', strtotime($trans['created_at'])) ?>
                                        </small>
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
                                    href="?page=<?= $pagination['current_page'] - 1 ?><?= http_build_query(array_filter($filters ?? [])) ? '&' . http_build_query(array_filter($filters ?? [])) : '' ?>">
                                    <i class="fas fa-chevron-left"></i>
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
                                        href="?page=<?= $i ?><?= http_build_query(array_filter($filters ?? [])) ? '&' . http_build_query(array_filter($filters ?? [])) : '' ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next -->
                            <li
                                class="page-item <?= $pagination['current_page'] >= $pagination['last_page'] ? 'disabled' : '' ?>">
                                <a class="page-link"
                                    href="?page=<?= $pagination['current_page'] + 1 ?><?= http_build_query(array_filter($filters ?? [])) ? '&' . http_build_query(array_filter($filters ?? [])) : '' ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <!-- Pagination Info -->
                    <div class="text-center text-muted mt-2">
                        Hiển thị <?= $pagination['from'] ?? 0 ?> - <?= $pagination['to'] ?? 0 ?>
                        trong tổng số <?= $pagination['total'] ?? 0 ?> giao dịch
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function setDateRange(range) {
        const today = new Date();
        const fromDateInput = document.getElementById('from_date');
        const toDateInput = document.getElementById('to_date');

        // Format: YYYY-MM-DD
        const formatDate = (date) => {
            return date.toISOString().split('T')[0];
        };

        switch (range) {
            case 'today':
                fromDateInput.value = formatDate(today);
                toDateInput.value = formatDate(today);
                break;
            case 'yesterday':
                const yesterday = new Date(today);
                yesterday.setDate(yesterday.getDate() - 1);
                fromDateInput.value = formatDate(yesterday);
                toDateInput.value = formatDate(yesterday);
                break;
            case 'week':
                const weekAgo = new Date(today);
                weekAgo.setDate(weekAgo.getDate() - 7);
                fromDateInput.value = formatDate(weekAgo);
                toDateInput.value = formatDate(today);
                break;
            case 'month':
                const monthAgo = new Date(today);
                monthAgo.setDate(monthAgo.getDate() - 30);
                fromDateInput.value = formatDate(monthAgo);
                toDateInput.value = formatDate(today);
                break;
        }

        document.getElementById('filterForm').submit();
    }
</script>