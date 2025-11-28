<?php
/**
 * View: Tạo Phiếu Xuất
 * Path: src/views/admin/sales/create.php
 */
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-file-export text-danger"></i> Tạo Phiếu Xuất</h2>
        <div>
            <a href="/admin/inventory" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
        </div>
    </div>

    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
        <div class="alert alert-danger"><?= $flash ?></div>
    <?php endif; ?>
    <?php if ($flash = \Helpers\AuthHelper::getFlash('success')): ?>
        <div class="alert alert-success"><?= $flash ?></div>
    <?php endif; ?>

    <form method="POST" action="/admin/sales/store" id="salesForm">
        <div class="card mb-4">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Khách hàng (Chọn nhà cung cấp như khách)</label>
                        <select name="supplier_id" class="form-select">
                            <option value="">-- Chọn (tùy chọn) --</option>
                            <?php foreach ($suppliers as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ngày xuất</label>
                        <input type="date" name="sale_date_display" class="form-control" value="<?= date('Y-m-d') ?>" disabled>
                        <input type="hidden" name="sale_date" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Ghi chú</label>
                        <input type="text" name="note" class="form-control" placeholder="Ghi chú chung (tùy chọn)">
                    </div>
                </div>

                <hr>

                <h6>Mã SKU sản phẩm</h6>
                <div id="itemsContainer">
                    <div class="row mb-2 item-row">
                        <div class="col-md-5">
                            <?php if (!empty($preselectVariant)): ?>
                                <?php
                                    $vm = null;
                                    foreach ($variants as $v) {
                                        if ($v['id'] == $preselectVariant) { $vm = $v; break; }
                                    }
                                ?>
                                <div class="form-control bg-light">
                                    <?php if ($vm): ?>
                                        <strong>[<?= htmlspecialchars($vm['sku']) ?>]</strong>
                                    <?php else: ?>
                                        <strong>Variant #<?= $preselectVariant ?></strong>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" name="variant_id[]" value="<?= $preselectVariant ?>">
                            <?php else: ?>
                                <select name="variant_id[]" class="form-select">
                                    <option value="">-- Chọn biến thể --</option>
                                    <?php foreach ($variants as $v): ?>
                                        <option value="<?= $v['id'] ?>">[<?= htmlspecialchars($v['sku']) ?>] <?= htmlspecialchars($v['product_id'] ?? $v['sku']) ?></option>
                                    <?php endforeach; ?>    
                                </select>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2">
                            <input type="number" name="quantity[]" class="form-control" min="1" placeholder="Số lượng" value="<?= !empty($preselectVariant) ? 1 : '' ?>" required>
                        </div>
                        <div class="col-md-3">
                            <input type="number" step="0.01" name="sale_price[]" class="form-control" placeholder="Giá bán" value="0.00" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button class="btn btn-danger">Tạo phiếu xuất</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    // Simple row remove handler (kept minimal as in purchase view)
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-remove')) {
            const row = e.target.closest('.item-row');
            const container = document.getElementById('itemsContainer');
            if (container.querySelectorAll('.item-row').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input, select').forEach(el => el.value = '');
            }
        }
    });
</script>
