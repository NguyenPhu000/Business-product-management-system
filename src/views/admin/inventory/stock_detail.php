<?php

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
                                <td><strong class="text-success"><?= number_format($variant['price'] ?? 0) ?> đ</strong>
                                </td>
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

    <div class="row g-3 mb-4">
        <!-- Current Stock -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrapper <?= $textClass ?>">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Số lượng tồn</div>
                    <div class="stat-number <?= $textClass ?>"><?= number_format($currentStock) ?></div>
                    <div class="stat-footer"><?= htmlspecialchars($product['unit'] ?? 'đơn vị') ?></div>
                </div>
            </div>
        </div>

        <!-- Threshold -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrapper text-primary">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Ngưỡng tối thiểu</div>
                    <div class="stat-number text-primary"><?= number_format($minThreshold) ?></div>
                    <div class="stat-footer">Cảnh báo khi dưới ngưỡng</div>
                </div>
            </div>
        </div>

        <!-- Stock Value -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrapper text-info">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Giá trị tồn kho</div>
                    <div class="stat-number text-info" style="font-size: 1.25rem;"><?= number_format($stockValue) ?> đ
                    </div>
                    <div class="stat-footer"><?= number_format($currentStock) ?> ×
                        <?= number_format($variant['unit_cost'] ?? 0) ?>đ</div>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
            <div class="stat-card">
                <div class="stat-icon-wrapper <?= $textClass ?>">
                    <i class="fas fa-<?= $statusIcon ?>"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-label">Trạng thái</div>
                    <div class="stat-number" style="font-size: 1rem;">
                        <span class="badge <?= $bgClass ?> text-white px-3 py-2"><?= $statusText ?></span>
                    </div>
                    <div class="stat-footer">
                        <?php if ($currentStock > $minThreshold): ?>
                        Còn <?= number_format($currentStock - $minThreshold) ?> trên ngưỡng
                        <?php elseif ($currentStock > 0): ?>
                        Cần nhập thêm <?= number_format($minThreshold - $currentStock + 1) ?>
                        <?php else: ?>
                        Đã hết hàng
                        <?php endif; ?>
                    </div>
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
                        <input type="number" class="form-control form-control-lg" id="min_threshold"
                            name="min_threshold" value="<?= $minThreshold ?>" min="0" placeholder="Nhập số lượng"
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
                                        <span
                                            class="<?= $trans['quantity_change'] > 0 ? 'text-success' : 'text-danger' ?> fw-bold">
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
    transform: translateY(-3px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0.5rem;
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
    line-height: 1.2;
    margin-bottom: 0.25rem;
}

.stat-footer {
    font-size: 0.75rem;
    color: #858796;
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