<?php
/**
 * View: Thêm biến thể sản phẩm
 * Path: src/views/admin/products/variants/create.php
 */
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="bi bi-plus-circle"></i> Thêm biến thể sản phẩm
            </h1>
            <p class="text-muted mb-0">
                Sản phẩm: <strong><?= htmlspecialchars($product['name']) ?></strong>
            </p>
        </div>
        <a href="/admin/products/<?= $product['id'] ?>/variants" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i>
            <?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="bi bi-info-circle"></i> Thông tin biến thể
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/products/<?= $product['id'] ?>/variants/store" id="variantForm">
                
                <!-- Row 1: SKU và Barcode -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="sku" class="form-label">
                            SKU Biến thể <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="sku" 
                                   name="sku" 
                                   value="<?= $product['sku'] ?>-VAR-<?= strtoupper(uniqid()) ?>"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" id="generateSku" title="Tạo mã tự động">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                        <small class="text-muted">Mã định danh duy nhất cho biến thể</small>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="barcode" class="form-label">Barcode / EAN</label>
                        <input type="text" 
                               class="form-control" 
                               id="barcode" 
                               name="barcode" 
                               placeholder="Nhập mã vạch (nếu có)">
                        <small class="text-muted">Dùng để quét mã vạch khi bán hàng</small>
                    </div>
                </div>

                <!-- Row 2: Thuộc tính - Màu sắc, Size, Dung lượng -->
                <div class="card bg-light mb-3">
                    <div class="card-header">
                        <strong><i class="bi bi-palette"></i> Thuộc tính biến thể</strong>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="color" class="form-label">Màu sắc</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="color" 
                                       name="color" 
                                       placeholder="VD: Đen, Trắng, Xanh...">
                            </div>

                            <div class="col-md-4">
                                <label for="size" class="form-label">Kích thước / Size</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="size" 
                                       name="size" 
                                       placeholder="VD: S, M, L, XL, 39, 40...">
                            </div>

                            <div class="col-md-4">
                                <label for="capacity" class="form-label">Dung lượng / Bộ nhớ</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="capacity" 
                                       name="capacity" 
                                       placeholder="VD: 64GB, 128GB, 256GB...">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="custom_attr_name" class="form-label">Thuộc tính tùy chỉnh</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="custom_attr_name" 
                                       name="custom_attr_name" 
                                       placeholder="VD: Chất liệu, Xuất xứ...">
                            </div>

                            <div class="col-md-6">
                                <label for="custom_attr_value" class="form-label">Giá trị</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="custom_attr_value" 
                                       name="custom_attr_value" 
                                       placeholder="VD: Cotton, Việt Nam...">
                            </div>
                        </div>

                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> 
                            Chọn các thuộc tính phù hợp với loại sản phẩm của bạn. Có thể để trống nếu không cần.
                        </small>
                    </div>
                </div>

                <!-- Row 3: Giá nhập - Giá bán -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="unit_cost" class="form-label">
                            Giá nhập <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="unit_cost" 
                                   name="unit_cost" 
                                   min="0" 
                                   step="0.01" 
                                   value="<?= $product['unit_cost'] ?? 0 ?>"
                                   required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-muted">Giá nhập của biến thể này</small>
                    </div>

                    <div class="col-md-6">
                        <label for="price" class="form-label">
                            Giá bán <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" 
                                   class="form-control" 
                                   id="price" 
                                   name="price" 
                                   min="0" 
                                   step="0.01" 
                                   value="<?= $product['price'] ?? 0 ?>"
                                   required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-muted">Giá bán cho khách hàng</small>
                    </div>
                </div>

                <!-- Row 4: Trạng thái -->
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" 
                               type="checkbox" 
                               id="is_active" 
                               name="is_active" 
                               value="1" 
                               checked>
                        <label class="form-check-label" for="is_active">
                            <span class="text-success">Kích hoạt</span> 
                            <small class="text-muted">(Có thể bán biến thể này)</small>
                        </label>
                    </div>
                </div>

                <!-- Divider -->
                <hr>

                <!-- Action buttons -->
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Lưu biến thể
                        </button>
                        <a href="/admin/products/<?= $product['id'] ?>/variants" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Hủy
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Example Box -->
    <div class="card border-success">
        <div class="card-body">
            <h6 class="text-success"><i class="bi bi-lightbulb"></i> Ví dụ biến thể</h6>
            <p><strong>Sản phẩm:</strong> iPhone 13 Pro Max</p>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Màu sắc</th>
                        <th>Dung lượng</th>
                        <th>Giá bán</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>IP13PM-BLUE-256</code></td>
                        <td>Xanh Dương</td>
                        <td>256GB</td>
                        <td>27.990.000 đ</td>
                    </tr>
                    <tr>
                        <td><code>IP13PM-BLACK-512</code></td>
                        <td>Đen</td>
                        <td>512GB</td>
                        <td>32.990.000 đ</td>
                    </tr>
                    <tr>
                        <td><code>IP13PM-GOLD-128</code></td>
                        <td>Vàng</td>
                        <td>128GB</td>
                        <td>24.990.000 đ</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate SKU
    document.getElementById('generateSku').addEventListener('click', function() {
        const productSku = '<?= $product['sku'] ?>';
        const uniqueId = Math.random().toString(36).substring(2, 8).toUpperCase();
        document.getElementById('sku').value = `${productSku}-VAR-${uniqueId}`;
    });

    // Validate giá
    const unitCostInput = document.getElementById('unit_cost');
    const priceInput = document.getElementById('price');

    if (priceInput && unitCostInput) {
        priceInput.addEventListener('change', function() {
            const unitCost = parseFloat(unitCostInput.value) || 0;
            const price = parseFloat(this.value) || 0;
            
            if (price < unitCost) {
                alert('Giá bán phải lớn hơn hoặc bằng giá nhập!');
                this.value = unitCost;
            }
        });
    }
});
</script>
