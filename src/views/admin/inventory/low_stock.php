<?php
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>
            <i class="fas fa-exclamation-triangle text-warning"></i> Cảnh Báo Tồn Kho
            <span class="text-muted fs-6 fw-normal">
                Tổng: <?= $totalAlerts ?? 0 ?> cảnh báo
            </span>
        </h2>
        <div class="d-flex gap-2">
            <a href="/admin/inventory" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="/admin/inventory/history" class="btn btn-info">
                <i class="fas fa-history"></i> Lịch sử
            </a>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Sắp hết hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($lowStockProducts ?? []) ?> sản phẩm
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Hết hàng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= count($outOfStockProducts ?? []) ?> sản phẩm
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-box-open fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Products -->
    <?php if (!empty($lowStockProducts)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle"></i> Sản phẩm sắp hết hàng (<?= count($lowStockProducts) ?>)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="20%">Sản phẩm</th>
                                <th width="12%">SKU</th>
                                <th width="15%">Variant</th>
                                <th width="8%">Kho</th>
                                <th width="8%" class="text-center">Tồn kho</th>
                                <th width="8%" class="text-center">Ngưỡng</th>
                                <th width="8%" class="text-center">Thiếu</th>
                                <th width="12%">Cập nhật</th>
                                <th width="10%" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($lowStockProducts as $product): ?>
                                <tr class="table-warning">
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
                                        <strong class="text-warning fs-5"><?= number_format($product['quantity']) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format($product['min_threshold']) ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-warning text-dark">
                                            <?= number_format($product['min_threshold'] - $product['quantity']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($product['last_updated'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/inventory/detail/<?= $product['variant_id'] ?>?warehouse=<?= urlencode($product['warehouse']) ?>"
                                                class="btn btn-outline-info btn-sm" title="Chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/admin/purchase/create?variant_id=<?= $product['variant_id'] ?>"
                                                class="btn btn-outline-success btn-sm" title="Tạo phiếu nhập">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="/admin/sales/create?variant_id=<?= $product['variant_id'] ?>"
                                                class="btn btn-outline-danger btn-sm" title="Tạo phiếu xuất">
                                                <i class="fas fa-minus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Out of Stock Products -->
    <?php if (!empty($outOfStockProducts)): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-box-open"></i> Sản phẩm hết hàng (<?= count($outOfStockProducts) ?>)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">ID</th>
                                <th width="20%">Sản phẩm</th>
                                <th width="12%">SKU</th>
                                <th width="15%">Variant</th>
                                <th width="10%">Kho</th>
                                <th width="15%">Cập nhật lần cuối</th>
                                <th width="10%" class="text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($outOfStockProducts as $product): ?>
                                <tr class="table-danger">
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
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d/m/Y H:i', strtotime($product['last_updated'])) ?>
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/inventory/detail/<?= $product['variant_id'] ?>?warehouse=<?= urlencode($product['warehouse']) ?>"
                                                class="btn btn-outline-info btn-sm" title="Chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="/admin/purchase/create?variant_id=<?= $product['variant_id'] ?>"
                                                class="btn btn-outline-success btn-sm" title="Tạo phiếu nhập">
                                                <i class="fas fa-plus"></i>
                                            </a>
                                            <a href="/admin/sales/create?variant_id=<?= $product['variant_id'] ?>"
                                                class="btn btn-outline-danger btn-sm" title="Tạo phiếu xuất">
                                                <i class="fas fa-minus"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- No Alerts -->
    <?php if (empty($lowStockProducts) && empty($outOfStockProducts)): ?>
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                <h4>Không có cảnh báo nào</h4>
                <p class="text-muted">Tất cả sản phẩm đều có tồn kho đầy đủ.</p>
                <a href="/admin/inventory" class="btn btn-primary mt-3">
                    <i class="fas fa-list"></i> Xem danh sách tồn kho
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Quick import removed: use full Purchase form for imports and Sales form for exports -->
<script>
    // Quick import functionality removed to keep behavior consistent with inventory list.
</script>