<?php

/**
 * View: Form điều chỉnh tồn kho
 * Path: src/views/admin/inventory/adjust_stock.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-edit text-warning"></i> Điều Chỉnh Tồn Kho
        </h2>
        <div class="d-flex gap-2">
            <a href="/admin/inventory/detail/<?= $variant['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Current Stock Info -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-light">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-info-circle"></i> Thông tin hiện tại
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <table class="table table-borderless mb-0">
                        <tbody>
                            <tr>
                                <th width="35%">Sản phẩm:</th>
                                <td>
                                    <strong><?= htmlspecialchars($product['name'] ?? 'N/A') ?></strong>
                                    <br><small class="text-muted">SKU: <?= htmlspecialchars($product['sku'] ?? 'N/A') ?></small>
                                </td>
                            </tr>
                            <tr>
                                <th>Variant SKU:</th>
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
                                <th>Đơn vị:</th>
                                <td><?= htmlspecialchars($product['unit'] ?? 'Cái') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Current Stock by Warehouse -->
            <hr>
            <h6 class="font-weight-bold mb-3">Tồn kho hiện tại:</h6>
            <div class="row">
                <?php foreach ($inventory as $inv): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                        <div class="card border-<?= $inv['quantity'] > $inv['min_threshold'] ? 'success' : ($inv['quantity'] > 0 ? 'warning' : 'danger') ?>">
                            <div class="card-body text-center py-3">
                                <small class="text-muted d-block mb-2"><?= htmlspecialchars($inv['warehouse']) ?></small>
                                <h4 class="mb-0 text-<?= $inv['quantity'] > $inv['min_threshold'] ? 'success' : ($inv['quantity'] > 0 ? 'warning' : 'danger') ?>">
                                    <?= number_format($inv['quantity']) ?>
                                </h4>
                                <small class="text-muted">Ngưỡng: <?= number_format($inv['min_threshold']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Adjust Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="bi bi-pencil-square"></i> Form điều chỉnh
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/inventory/adjust" id="adjustForm">
                <input type="hidden" name="variant_id" value="<?= $variant['id'] ?>">

                <div class="row mb-4">
                    <!-- Warehouse -->
                    <div class="col-lg-4 mb-3">
                        <label for="warehouse" class="form-label mb-2 fw-semibold">
                            <i class="bi bi-building"></i> Kho hàng <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg" id="warehouse" name="warehouse" required>
                            <option value="">-- Chọn kho --</option>
                            <?php foreach ($inventory as $inv): ?>
                                <option value="<?= htmlspecialchars($inv['warehouse']) ?>"
                                    data-current="<?= $inv['quantity'] ?>"
                                    data-threshold="<?= $inv['min_threshold'] ?>">
                                    <?= htmlspecialchars($inv['warehouse']) ?>
                                    (Hiện: <?= number_format($inv['quantity']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Type -->
                    <div class="col-lg-4 mb-3">
                        <label for="type" class="form-label mb-2 fw-semibold">
                            <i class="bi bi-arrow-left-right"></i> Loại điều chỉnh <span class="text-danger">*</span>
                        </label>
                        <select class="form-select form-select-lg" id="type" name="type" required>
                            <option value="">-- Chọn loại --</option>
                            <option value="import">➕ Nhập kho</option>
                            <option value="export">➖ Xuất kho</option>
                            <option value="adjust">⚙️ Điều chỉnh</option>
                        </select>
                    </div>

                    <!-- Quantity Change -->
                    <div class="col-lg-4 mb-3">
                        <label for="quantity" class="form-label mb-2 fw-semibold">
                            <i class="bi bi-123"></i> Số lượng thay đổi <span class="text-danger">*</span>
                        </label>
                        <input type="number"
                            class="form-control form-control-lg"
                            id="quantity"
                            name="quantity"
                            placeholder="Nhập số lượng..."
                            min="1"
                            required>
                        <small class="text-muted">Nhập số dương (hệ thống sẽ tự xử lý +/-)</small>
                    </div>
                </div>

                <!-- Note -->
                <div class="mb-4">
                    <label for="note" class="form-label mb-2 fw-semibold">
                        <i class="bi bi-chat-left-text"></i> Ghi chú <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control"
                        id="note"
                        name="note"
                        rows="3"
                        placeholder="Lý do điều chỉnh: sai sót, hư hỏng, mất mát, kiểm kho..."
                        required></textarea>
                </div>

                <!-- Preview Card -->
                <div class="card bg-light mb-4" id="previewCard" style="display: none;">
                    <div class="card-body">
                        <h6 class="font-weight-bold mb-3">📊 Xem trước kết quả:</h6>
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm">
                                    <small class="text-muted d-block mb-2">Tồn kho hiện tại</small>
                                    <h4 class="mb-0 text-primary" id="previewCurrent">-</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm">
                                    <small class="text-muted d-block mb-2">Thay đổi</small>
                                    <h4 class="mb-0" id="previewChange">-</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm">
                                    <small class="text-muted d-block mb-2">Tồn kho sau điều chỉnh</small>
                                    <h4 class="mb-0 text-success" id="previewAfter">-</h4>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 text-center" id="previewWarning"></div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary" onclick="history.back()">
                        <i class="fas fa-times"></i> Hủy bỏ
                    </button>
                    <button type="submit" class="btn btn-warning btn-lg px-5">
                        <i class="fas fa-save"></i> Xác nhận điều chỉnh
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const warehouseSelect = document.getElementById('warehouse');
    const typeSelect = document.getElementById('type');
    const quantityInput = document.getElementById('quantity');
    const previewCard = document.getElementById('previewCard');
    const previewCurrent = document.getElementById('previewCurrent');
    const previewChange = document.getElementById('previewChange');
    const previewAfter = document.getElementById('previewAfter');
    const previewWarning = document.getElementById('previewWarning');

    // Update preview when inputs change
    [warehouseSelect, typeSelect, quantityInput].forEach(el => {
        el.addEventListener('change', updatePreview);
        el.addEventListener('input', updatePreview);
    });

    function updatePreview() {
        const warehouse = warehouseSelect.value;
        const type = typeSelect.value;
        const quantity = parseInt(quantityInput.value) || 0;

        if (!warehouse || !type || quantity <= 0) {
            previewCard.style.display = 'none';
            return;
        }

        const selectedOption = warehouseSelect.options[warehouseSelect.selectedIndex];
        const currentStock = parseInt(selectedOption.dataset.current) || 0;
        const threshold = parseInt(selectedOption.dataset.threshold) || 0;

        // Calculate change based on type
        let change = 0;
        if (type === 'import') {
            change = quantity;
        } else if (type === 'export') {
            change = -quantity;
        } else if (type === 'adjust') {
            // For adjust, let user decide sign in note
            change = quantity;
        }

        const afterStock = currentStock + change;

        // Update preview
        previewCurrent.textContent = formatNumber(currentStock);
        previewChange.textContent = (change > 0 ? '+' : '') + formatNumber(change);
        previewChange.className = 'mb-0 ' + (change > 0 ? 'text-success' : 'text-danger');
        previewAfter.textContent = formatNumber(afterStock);
        previewAfter.className = 'mb-0 ' + (afterStock >= threshold ? 'text-success' : 'text-warning');

        // Warning
        previewWarning.innerHTML = '';
        if (afterStock < 0) {
            previewWarning.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> <strong>Cảnh báo:</strong> Tồn kho sau điều chỉnh âm!</div>';
        } else if (afterStock < threshold) {
            previewWarning.innerHTML = '<div class="alert alert-warning mb-0"><i class="bi bi-exclamation-circle"></i> <strong>Lưu ý:</strong> Tồn kho sau điều chỉnh dưới ngưỡng tối thiểu.</div>';
        } else if (type === 'export' && quantity > currentStock) {
            previewWarning.innerHTML = '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-triangle"></i> <strong>Cảnh báo:</strong> Số lượng xuất vượt quá tồn kho hiện tại!</div>';
        }

        previewCard.style.display = 'block';
    }

    function formatNumber(num) {
        return num.toLocaleString('vi-VN');
    }

    // Form validation
    document.getElementById('adjustForm').addEventListener('submit', function(e) {
        const type = typeSelect.value;
        const quantity = parseInt(quantityInput.value) || 0;
        const selectedOption = warehouseSelect.options[warehouseSelect.selectedIndex];
        const currentStock = parseInt(selectedOption.dataset.current) || 0;

        // Check export quantity
        if (type === 'export' && quantity > currentStock) {
            if (!confirm('Số lượng xuất vượt quá tồn kho hiện tại. Bạn có chắc muốn tiếp tục?')) {
                e.preventDefault();
                return false;
            }
        }

        // Final confirmation
        if (!confirm('Bạn có chắc chắn muốn điều chỉnh tồn kho này?')) {
            e.preventDefault();
            return false;
        }
    });
</script>

<style>
    .form-select-lg,
    .form-control-lg {
        font-size: 1rem;
        padding: 0.75rem;
    }

    #previewCard {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card.border-success {
        border-width: 2px !important;
    }

    .card.border-warning {
        border-width: 2px !important;
    }

    .card.border-danger {
        border-width: 2px !important;
    }
</style>