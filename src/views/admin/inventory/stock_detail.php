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
                <i class="fas fa-info-circle"></i> Thông tin sản phẩm
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
                                <td><?= number_format($variant['unit_cost'] ?? 0) ?> đ</td>
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

    <!-- Stock Statistics - Single Warehouse Compact Design -->
    <?php
    // Lấy thông tin kho duy nhất
    $currentStock = 0;
    $minThreshold = 0;
    $stockValue = 0;
    if (!empty($stockInfo) && is_array($stockInfo)) {
        $firstStock = $stockInfo[0] ?? null;
        if ($firstStock) {
            $currentStock = $firstStock['quantity'] ?? 0;
            $minThreshold = $firstStock['min_threshold'] ?? 0;
            $stockValue = $currentStock * ($variant['unit_cost'] ?? 0);
        }
    }
    $bgClass = $currentStock > $minThreshold ? 'bg-success' : ($currentStock > 0 ? 'bg-warning' : 'bg-danger');
    $textClass = $currentStock > $minThreshold ? 'text-success' : ($currentStock > 0 ? 'text-warning' : 'text-danger');
    $statusText = $currentStock > $minThreshold ? 'Đủ hàng' : ($currentStock > 0 ? 'Sắp hết' : 'Hết hàng');
    $statusIcon = $currentStock > $minThreshold ? 'check-circle' : ($currentStock > 0 ? 'exclamation-triangle' : 'times-circle');
    ?>

    <div class="row g-4 mb-4">
        <!-- Current Stock -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 <?= $bgClass ?> bg-opacity-10">
                <div class="card-body p-4 text-center">
                    <div class="mb-2">
                        <i class="fas fa-cubes fa-3x <?= $textClass ?>"></i>
                    </div>
                    <div class="text-muted small text-uppercase fw-bold mb-2">
                        SỐ LƯỢNG TỒN
                    </div>
                    <div class="h1 mb-2 fw-bold <?= $textClass ?>">
                        <?= number_format($currentStock) ?>
                    </div>
                    <small class="text-muted"><?= htmlspecialchars($product['unit'] ?? 'đơn vị') ?></small>
                </div>
            </div>
        </div>

        <!-- Threshold -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-primary bg-opacity-10">
                <div class="card-body p-4 text-center">
                    <div class="mb-2">
                        <i class="fas fa-exclamation-triangle fa-3x text-primary"></i>
                    </div>
                    <div class="text-muted small text-uppercase fw-bold mb-2">
                        NGƯỠNG TỐI THIỂU
                    </div>
                    <div class="h1 mb-2 fw-bold text-primary">
                        <?= number_format($minThreshold) ?>
                    </div>
                    <small class="text-muted">Cảnh báo khi dưới ngưỡng</small>
                </div>
            </div>
        </div>

        <!-- Stock Value -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-info bg-opacity-10">
                <div class="card-body p-4 text-center">
                    <div class="mb-2">
                        <i class="fas fa-dollar-sign fa-3x text-info"></i>
                    </div>
                    <div class="text-muted small text-uppercase fw-bold mb-2">
                        GIÁ TRỊ TỒN KHO
                    </div>
                    <div class="h4 mb-2 fw-bold text-info">
                        <?= number_format($stockValue) ?> đ
                    </div>
                    <small class="text-muted"><?= number_format($currentStock) ?> × <?= number_format($variant['unit_cost'] ?? 0) ?>đ</small>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 <?= $bgClass ?> bg-opacity-10">
                <div class="card-body p-4 text-center">
                    <div class="mb-2">
                        <i class="fas fa-<?= $statusIcon ?> fa-3x <?= $textClass ?>"></i>
                    </div>
                    <div class="text-muted small text-uppercase fw-bold mb-2">
                        TRẠNG THÁI
                    </div>
                    <div class="h4 mb-2">
                        <span class="badge <?= $bgClass ?> text-white px-3 py-2">
                            <?= $statusText ?>
                        </span>
                    </div>
                    <small class="text-muted">
                        <?php if ($currentStock > $minThreshold): ?>
                            Còn <?= number_format($currentStock - $minThreshold) ?> trên ngưỡng
                        <?php elseif ($currentStock > 0): ?>
                            Cần nhập thêm <?= number_format($minThreshold - $currentStock + 1) ?>
                        <?php else: ?>
                            Đã hết hàng hoàn toàn
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Threshold -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="fas fa-cog"></i> Cập nhật ngưỡng tồn kho
            </h6>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="/admin/inventory/threshold/<?= $variantId ?>" id="thresholdForm">
                <input type="hidden" name="warehouse" value="Kho chính">

                <div class="row g-3 align-items-end">
                    <div class="col-md-6 col-lg-4">
                        <label for="min_threshold" class="form-label fw-semibold">
                            <i class="fas fa-exclamation-triangle text-warning"></i> Ngưỡng tồn kho tối thiểu
                        </label>
                        <input type="number"
                            class="form-control form-control-lg"
                            id="min_threshold"
                            name="min_threshold"
                            value="<?= $minThreshold ?>"
                            min="0"
                            placeholder="Nhập số lượng"
                            required>
                        <div class="form-text">Cảnh báo khi tồn kho thấp hơn hoặc bằng giá trị này</div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Cập nhật ngưỡng
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Transaction History -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="fas fa-history"></i> Lịch sử giao dịch
            </h6>
            <a href="/admin/inventory/history?variant_id=<?= $variantId ?>" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-list-ul"></i> Xem tất cả
            </a>
        </div>
        <div class="card-body p-4">
            <?php if (empty($history)): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> Chưa có giao dịch nào.
                </div>
            <?php else: ?>
                <!-- Timeline -->
                <div class="timeline">
                    <?php foreach ($history as $trans): ?>
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
                                                    <i class="fas fa-comment-alt"></i>
                                                    <?= htmlspecialchars($trans['note']) ?>
                                                </p>
                                            <?php endif; ?>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-user"></i>
                                                    <?= htmlspecialchars($trans['created_by_fullname'] ?? $trans['created_by_name'] ?? 'N/A') ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i>
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