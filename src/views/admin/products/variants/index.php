<?php
/**
 * View: Quản lý biến thể sản phẩm
 * Path: src/views/admin/products/variants/index.php
 */
?>

<div class="container-fluid">
    <!-- Header - Modern Design -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-2">
                <i class="fas fa-palette text-primary"></i> Quản lý biến thể sản phẩm
            </h2>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-dark fs-6">
                    <i class="fas fa-box"></i> <?= htmlspecialchars($product['name']) ?>
                </span>
                <span class="badge bg-secondary">
                    <i class="fas fa-barcode"></i> <?= htmlspecialchars($product['sku']) ?>
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/products/<?= $product['id'] ?>/variants/create" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle"></i> Thêm biến thể
            </a>
            <a href="/admin/products" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Flash Messages - Compact -->
    <?php if ($flash = \Helpers\AuthHelper::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Variants Table - Modern Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="fas fa-list-ul"></i> Danh sách biến thể (<?= count($variants) ?>)
            </h6>
        </div>
        <div class="card-body p-0">
            <?php if (empty($variants)): ?>
                <div class="p-5 text-center">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-3">Chưa có biến thể nào.</p>
                    <a href="/admin/products/<?= $product['id'] ?>/variants/create" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Thêm biến thể đầu tiên
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="50" class="text-center">#</th>
                                <th width="150"><i class="fas fa-barcode"></i> SKU</th>
                                <th><i class="fas fa-tags"></i> Thuộc tính</th>
                                <th width="110" class="text-end"><i class="fas fa-coins"></i> Giá nhập</th>
                                <th width="110" class="text-end"><i class="fas fa-dollar-sign"></i> Giá bán</th>
                                <th width="110" class="text-center"><i class="fas fa-boxes"></i> Tồn kho</th>
                                <th width="130"><i class="fas fa-qrcode"></i> Barcode</th>
                                <th width="100" class="text-center"><i class="fas fa-toggle-on"></i> Trạng thái</th>
                                <th width="200" class="text-center"><i class="fas fa-cogs"></i> Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($variants as $index => $variant): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $index + 1 ?></td>
                                    <td><code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($variant['sku']) ?></code></td>
                                    <td>
                                        <?php
                                        $attributes = !empty($variant['attributes']) ? json_decode($variant['attributes'], true) : [];
                                        if (!empty($attributes)):
                                            foreach ($attributes as $key => $value):
                                        ?>
                                                <span class="badge bg-light text-dark border me-1 mb-1">
                                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($key) ?>: <strong><?= htmlspecialchars($value) ?></strong>
                                                </span>
                                            <?php
                                            endforeach;
                                        else:
                                            ?>
                                            <span class="text-muted"><i class="fas fa-minus"></i> Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end text-muted">
                                        <?= number_format($variant['unit_cost'], 0, ',', '.') ?> đ
                                    </td>
                                    <td class="text-end">
                                        <strong class="text-success"><?= number_format($variant['price'], 0, ',', '.') ?> đ</strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($variant['total_stock'])): ?>
                                            <?php
                                            $stock = $variant['total_stock'];
                                            $class = $stock > 10 ? 'success' : ($stock > 0 ? 'warning' : 'danger');
                                            $icon = $stock > 10 ? 'check-circle' : ($stock > 0 ? 'exclamation-triangle' : 'times-circle');
                                            ?>
                                            <a href="/admin/inventory/detail/<?= $variant['id'] ?>" class="text-decoration-none">
                                                <span class="badge bg-<?= $class ?> bg-opacity-10 text-<?= $class ?> border border-<?= $class ?>">
                                                    <i class="fas fa-<?= $icon ?>"></i> <?= number_format($stock) ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                <i class="fas fa-box"></i> 0
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($variant['barcode']): ?>
                                            <code class="bg-light px-2 py-1 rounded"><?= htmlspecialchars($variant['barcode']) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($variant['is_active'] == 1): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">
                                                <i class="fas fa-check-circle"></i> Kích hoạt
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                                <i class="fas fa-ban"></i> Vô hiệu
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm" role="group">
                                            <a href="/admin/products/<?= $product['id'] ?>/variants/<?= $variant['id'] ?>/edit"
                                                class="btn btn-sm btn-outline-primary"
                                                title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="/admin/inventory/adjust/<?= $variant['id'] ?>"
                                                class="btn btn-sm btn-outline-warning"
                                                title="Điều chỉnh tồn kho">
                                                <i class="fas fa-boxes"></i>
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-danger btn-delete"
                                                data-id="<?= $variant['id'] ?>"
                                                data-sku="<?= htmlspecialchars($variant['sku']) ?>"
                                                title="Xóa variant">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Info Box -->
    <div class="card border-info">
        <div class="card-body">
            <h6 class="text-info"><i class="fas fa-info-circle"></i> Hướng dẫn</h6>
            <ul class="mb-0">
                <li><strong>Biến thể sản phẩm</strong> giúp bạn quản lý các phiên bản khác nhau của cùng 1 sản phẩm.</li>
                <li>Ví dụ: iPhone 13 Pro Max có các biến thể:
                    <code>Màu Xanh - 256GB</code>,
                    <code>Màu Đen - 512GB</code>,
                    <code>Màu Vàng - 128GB</code>
                </li>
                <li>Mỗi biến thể có <strong>giá riêng, SKU riêng, barcode riêng</strong> và được quản lý tồn kho riêng.</li>
                <li>Thuộc tính thường dùng: Màu sắc, Dung lượng, Kích thước, Chất liệu...</li>
            </ul>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Delete variant
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const variantId = this.dataset.id;
                const variantSku = this.dataset.sku;

                if (confirm(`Bạn có chắc muốn xóa biến thể "${variantSku}"?\n\nHành động này không thể hoàn tác!`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/products/<?= $product['id'] ?>/variants/${variantId}/delete`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
</script>
