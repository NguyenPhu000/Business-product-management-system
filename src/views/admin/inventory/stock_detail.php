<?php

/**
 * View: Chi tiết tồn kho variant
 * Path: src/views/admin/inventory/stock_detail.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-box text-primary"></i> Chi Tiết Tồn Kho
        </h2>
        <div class="d-flex gap-2">
            <a href="/admin/inventory" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="/admin/inventory/adjust/<?= $variant['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Điều chỉnh
            </a>
        </div>
    </div>

    <!-- Product Info Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-info-circle"></i> Thông tin sản phẩm
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th width="35%">Tên sản phẩm:</th>
                                <td><strong><?= htmlspecialchars($product['name'] ?? 'N/A') ?></strong></td>
                            </tr>
                            <tr>
                                <th>SKU sản phẩm:</th>
                                <td><code><?= htmlspecialchars($product['sku'] ?? 'N/A') ?></code></td>
                            </tr>
                            <tr>
                                <th>SKU Variant:</th>
                                <td><code><?= htmlspecialchars($variant['sku'] ?? 'N/A') ?></code></td>
                            </tr>
                            <tr>
                                <th>Thuộc tính:</th>
                                <td>
                                    <?php if (!empty($variant['attributes'])): ?>
                                        <?php foreach (json_decode($variant['attributes'], true) as $key => $value): ?>
                                            <span class="badge bg-secondary me-1">
                                                <?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Không có</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th width="35%">Giá bán:</th>
                                <td><strong class="text-success"><?= number_format($variant['price'] ?? 0) ?> đ</strong></td>
                            </tr>
                            <tr>
                                <th>Giá nhập:</th>
                                <td><?= number_format($variant['cost'] ?? 0) ?> đ</td>
                            </tr>
                            <tr>
                                <th>Danh mục:</th>
                                <td><?= htmlspecialchars($product['category_name'] ?? 'N/A') ?></td>
                            </tr>
                            <tr>
                                <th>Trạng thái:</th>
                                <td>
                                    <?php if (($variant['status'] ?? 0) == 1): ?>
                                        <span class="badge bg-success">Hoạt động</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Ngừng hoạt động</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Statistics -->
    <div class="row mb-4">
        <?php foreach ($inventory as $inv): ?>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-<?= $inv['quantity'] > $inv['min_threshold'] ? 'success' : ($inv['quantity'] > 0 ? 'warning' : 'danger') ?> shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-uppercase mb-1">
                                    <?= htmlspecialchars($inv['warehouse']) ?>
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <?= number_format($inv['quantity']) ?>
                                    <small class="text-muted"><?= htmlspecialchars($product['unit'] ?? 'đơn vị') ?></small>
                                </div>
                                <small class="text-muted">
                                    Ngưỡng tối thiểu: <?= number_format($inv['min_threshold']) ?>
                                </small>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-warehouse fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Total Stock -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng tồn kho
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= number_format(array_sum(array_column($inventory, 'quantity'))) ?>
                                <small class="text-muted"><?= htmlspecialchars($product['unit'] ?? 'đơn vị') ?></small>
                            </div>
                            <small class="text-muted">
                                Giá trị: <?= number_format(array_sum(array_column($inventory, 'quantity')) * ($variant['cost'] ?? 0)) ?> đ
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Threshold -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-gear"></i> Cập nhật ngưỡng tồn kho
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/inventory/threshold/<?= $variant['id'] ?>" id="thresholdForm">
                <div class="row">
                    <?php foreach ($inventory as $inv): ?>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <label class="form-label fw-semibold">
                                <?= htmlspecialchars($inv['warehouse']) ?>
                            </label>
                            <input type="number"
                                class="form-control"
                                name="thresholds[<?= $inv['id'] ?>]"
                                value="<?= $inv['min_threshold'] ?>"
                                min="0"
                                placeholder="Ngưỡng tối thiểu">
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Cập nhật ngưỡng
                </button>
            </form>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-clock-history"></i> Lịch sử giao dịch
            </h6>
            <a href="/admin/inventory/history?variant_id=<?= $variant['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-list-ul"></i> Xem tất cả
            </a>
        </div>
        <div class="card-body">
            <?php if (empty($transactions)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> Chưa có giao dịch nào.
                </div>
            <?php else: ?>
                <!-- Timeline -->
                <div class="timeline">
                    <?php foreach ($transactions as $trans): ?>
                        <div class="timeline-item mb-4">
                            <div class="d-flex">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <?php
                                    $typeIcon = [
                                        'import' => ['icon' => 'arrow-down', 'color' => 'success'],
                                        'export' => ['icon' => 'arrow-up', 'color' => 'danger'],
                                        'adjust' => ['icon' => 'edit', 'color' => 'warning'],
                                        'return' => ['icon' => 'rotate-left', 'color' => 'info']
                                    ];
                                    $type = $trans['type'] ?? 'adjust';
                                    $iconData = $typeIcon[$type] ?? ['icon' => 'circle', 'color' => 'secondary'];
                                    ?>
                                    <div class="timeline-icon bg-<?= $iconData['color'] ?> text-white rounded-circle d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px;">
                                        <i class="fas fa-<?= $iconData['icon'] ?>"></i>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-grow-1 ms-3">
                                    <div class="card">
                                        <div class="card-body py-2">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong>
                                                        <?php
                                                        $typeLabel = [
                                                            'import' => 'Nhập kho',
                                                            'export' => 'Xuất kho',
                                                            'adjust' => 'Điều chỉnh',
                                                            'return' => 'Trả hàng'
                                                        ];
                                                        echo $typeLabel[$type] ?? $type;
                                                        ?>
                                                    </strong>
                                                    <span class="badge bg-info ms-2">
                                                        <?= htmlspecialchars($trans['warehouse']) ?>
                                                    </span>
                                                </div>
                                                <span class="<?= $trans['quantity_change'] > 0 ? 'text-success' : 'text-danger' ?> fw-bold">
                                                    <?= $trans['quantity_change'] > 0 ? '+' : '' ?><?= number_format($trans['quantity_change']) ?>
                                                </span>
                                            </div>

                                            <?php if (!empty($trans['note'])): ?>
                                                <p class="text-muted mb-2 small">
                                                    <i class="bi bi-chat-left-text"></i>
                                                    <?= htmlspecialchars($trans['note']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="bi bi-person"></i>
                                                    <?= htmlspecialchars($trans['created_by_fullname'] ?? $trans['created_by_name'] ?? 'N/A') ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="bi bi-clock"></i>
                                                    <?= date('d/m/Y H:i', strtotime($trans['created_at'])) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 0.25rem solid #4e73df !important;
    }

    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }

    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }

    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }

    .timeline {
        position: relative;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
    }

    .timeline-item {
        position: relative;
    }
</style>

<script>
    document.getElementById('thresholdForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Cập nhật ngưỡng thành công!');
                    location.reload();
                } else {
                    alert('Lỗi: ' + (data.message || 'Không thể cập nhật'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi cập nhật!');
            });
    });
</script>