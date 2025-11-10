<?php
/**
 * View: Quản lý biến thể sản phẩm
 * Path: src/views/admin/products/variants/index.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-palette"></i> Quản lý biến thể sản phẩm
            </h1>
            <p class="text-muted mb-0">
                Sản phẩm: <strong><?= htmlspecialchars($product['name']) ?></strong> 
                <small>(SKU: <?= htmlspecialchars($product['sku']) ?>)</small>
            </p>
        </div>
        <div>
            <a href="/admin/products/<?= $product['id'] ?>/variants/create" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Thêm biến thể
            </a>
            <a href="/admin/products" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = \Helpers\AuthHelper::getFlash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Variants Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-list-ul"></i> Danh sách biến thể
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($variants)): ?>
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i>
                    Chưa có biến thể nào. <a href="/admin/products/<?= $product['id'] ?>/variants/create">Thêm biến thể đầu tiên</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th width="150">SKU Biến thể</th>
                                <th>Thuộc tính</th>
                                <th width="100">Giá nhập</th>
                                <th width="100">Giá bán</th>
                                <th width="120">Tồn kho</th>
                                <th width="120">Barcode</th>
                                <th width="100">Trạng thái</th>
                                <th width="180">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($variants as $index => $variant): ?>
                                <tr>
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td><code><?= htmlspecialchars($variant['sku']) ?></code></td>
                                    <td>
                                        <?php 
                                        $attributes = json_decode($variant['attributes'], true);
                                        if ($attributes):
                                            foreach ($attributes as $key => $value): 
                                        ?>
                                            <span class="badge bg-secondary me-1">
                                                <?= htmlspecialchars($key) ?>: <?= htmlspecialchars($value) ?>
                                            </span>
                                        <?php 
                                            endforeach;
                                        else:
                                        ?>
                                            <span class="text-muted">Không có</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?= number_format($variant['unit_cost'], 0, ',', '.') ?> đ
                                    </td>
                                    <td class="text-end">
                                        <strong><?= number_format($variant['price'], 0, ',', '.') ?> đ</strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($variant['total_stock'])): ?>
                                            <?php 
                                            $stock = $variant['total_stock'];
                                            $class = $stock > 10 ? 'success' : ($stock > 0 ? 'warning' : 'danger');
                                            ?>
                                            <a href="/admin/inventory/detail/<?= $variant['id'] ?>" class="text-decoration-none">
                                                <span class="badge bg-<?= $class ?>">
                                                    <i class="fas fa-box"></i> <?= number_format($stock) ?>
                                                </span>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $variant['barcode'] ? htmlspecialchars($variant['barcode']) : '<span class="text-muted">-</span>' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($variant['is_active'] == 1): ?>
                                            <span class="badge bg-success">Kích hoạt</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Vô hiệu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="/admin/inventory/adjust/<?= $variant['id'] ?>" 
                                               class="btn btn-sm btn-warning" 
                                               title="Điều chỉnh tồn kho">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger btn-delete" 
                                                    data-id="<?= $variant['id'] ?>"
                                                    data-sku="<?= htmlspecialchars($variant['sku']) ?>"
                                                    title="Xóa variant">
                                                <i class="bi bi-trash3"></i>
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
            <h6 class="text-info"><i class="bi bi-info-circle"></i> Hướng dẫn</h6>
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
