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
                <i class="fas fa-plus-circle"></i> Thêm biến thể sản phẩm
            </h1>
            <p class="text-muted mb-0">
                Sản phẩm: <strong><?= htmlspecialchars($product['name']) ?></strong>
            </p>
        </div>
        <a href="/admin/products/<?= $product['id'] ?>/variants" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <!-- Flash Messages -->
    <?php if ($flash = \Helpers\AuthHelper::getFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i>
            <?= $flash ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-info-circle"></i> Thông tin biến thể
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
                                <i class="fas fa-sync-alt"></i>
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
                <?php $attributeOptions = require __DIR__ . '/attribute_options.php'; ?>
                <div class="card bg-light mb-3">
                    <div class="card-header">
                        <strong><i class="fas fa-palette"></i> Thuộc tính biến thể</strong>
                        <small class="text-muted">(Chọn từ danh sách hoặc nhập tùy chỉnh)</small>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="color" class="form-label">
                                    <i class="fas fa-<?= $attributeOptions['color']['icon'] ?>"></i>
                                    <?= $attributeOptions['color']['label'] ?>
                                </label>
                                <select class="form-select" id="color" name="color" data-allow-custom="true">
                                    <option value="">-- Chọn hoặc nhập tùy chỉnh --</option>
                                    <?php foreach ($attributeOptions['color']['options'] as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                    <option value="__custom__">✏️ Nhập tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" id="color_custom" placeholder="Nhập màu tùy chỉnh">
                            </div>

                            <div class="col-md-4">
                                <label for="size" class="form-label">
                                    <i class="fas fa-<?= $attributeOptions['size']['icon'] ?>"></i>
                                    <?= $attributeOptions['size']['label'] ?>
                                </label>
                                <select class="form-select" id="size" name="size" data-allow-custom="true">
                                    <option value="">-- Chọn hoặc nhập tùy chỉnh --</option>
                                    <?php foreach ($attributeOptions['size']['options'] as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                    <option value="__custom__">✏️ Nhập tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" id="size_custom" placeholder="Nhập size tùy chỉnh">
                            </div>

                            <div class="col-md-4">
                                <label for="capacity" class="form-label">
                                    <i class="fas fa-<?= $attributeOptions['storage']['icon'] ?>"></i>
                                    <?= $attributeOptions['storage']['label'] ?>
                                </label>
                                <select class="form-select" id="capacity" name="capacity" data-allow-custom="true">
                                    <option value="">-- Chọn hoặc nhập tùy chỉnh --</option>
                                    <?php foreach ($attributeOptions['storage']['options'] as $value => $label): ?>
                                        <option value="<?= $value ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                    <option value="__custom__">✏️ Nhập tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" id="capacity_custom" placeholder="Nhập dung lượng tùy chỉnh">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="custom_attr_name" class="form-label">
                                    <i class="fas fa-tag"></i> Thuộc tính bổ sung
                                </label>
                                <select class="form-select" id="custom_attr_name" name="custom_attr_name" data-allow-custom="true">
                                    <option value="">-- Chọn loại thuộc tính --</option>
                                    <option value="material">Chất liệu</option>
                                    <option value="origin">Xuất xứ</option>
                                    <option value="version">Phiên bản</option>
                                    <option value="weight">Trọng lượng</option>
                                    <option value="ram">RAM</option>
                                    <option value="__custom__">✏️ Nhập tên tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" id="custom_attr_name_custom" placeholder="Nhập tên thuộc tính">
                            </div>

                            <div class="col-md-6">
                                <label for="custom_attr_value" class="form-label">
                                    <i class="fas fa-edit"></i> Giá trị
                                </label>
                                <select class="form-select" id="custom_attr_value" name="custom_attr_value" data-allow-custom="true" disabled>
                                    <option value="">-- Chọn giá trị --</option>
                                </select>
                                <input type="text" class="form-control mt-2 d-none" id="custom_attr_value_custom" placeholder="Nhập giá trị">
                            </div>
                        </div>

                        <small class="text-muted mt-2 d-block">
                            <i class="fas fa-info-circle"></i>
                            Chọn các thuộc tính từ danh sách có sẵn hoặc nhập tùy chỉnh. Có thể để trống nếu không cần.
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

                <!-- Row 4: Inventory - Tồn kho ban đầu -->
                <div class="card bg-info bg-opacity-10 mb-3">
                    <div class="card-header bg-info text-white">
                        <strong><i class="fas fa-box"></i> Quản lý tồn kho</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="initial_stock" class="form-label">
                                    Số lượng nhập kho ban đầu
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                        class="form-control"
                                        id="initial_stock"
                                        name="initial_stock"
                                        min="0"
                                        value="0">
                                    <span class="input-group-text">Đơn vị</span>
                                </div>
                                <small class="text-muted">Để 0 nếu chưa nhập kho</small>
                            </div>

                            <div class="col-md-6">
                                <label for="min_threshold" class="form-label">
                                    Ngưỡng cảnh báo tồn kho thấp
                                </label>
                                <div class="input-group">
                                    <input type="number"
                                        class="form-control"
                                        id="min_threshold"
                                        name="min_threshold"
                                        min="0"
                                        value="10">
                                    <span class="input-group-text">Đơn vị</span>
                                </div>
                                <small class="text-muted">Cảnh báo khi tồn kho dưới mức này</small>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> Hệ thống sẽ tự động tạo phiếu nhập kho nếu bạn nhập số lượng ban đầu > 0
                        </div>
                    </div>
                </div>

                <!-- Row 5: Trạng thái -->
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
                            <i class="fas fa-check-circle"></i> Lưu biến thể
                        </button>
                        <a href="/admin/products/<?= $product['id'] ?>/variants" class="btn btn-outline-secondary">
                            <i class="fas fa-times-circle"></i> Hủy
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Example Box -->
    <div class="card border-success">
        <div class="card-body">
            <h6 class="text-success"><i class="fas fa-lightbulb"></i> Ví dụ biến thể</h6>
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
        // Attribute options for custom attribute value dropdown
        const attributeOptions = <?= json_encode($attributeOptions) ?>;

        // Generate SKU
        document.getElementById('generateSku').addEventListener('click', function() {
            const productSku = '<?= $product['sku'] ?>';
            const uniqueId = Math.random().toString(36).substring(2, 8).toUpperCase();
            document.getElementById('sku').value = `${productSku}-VAR-${uniqueId}`;
        });

        // Handle custom input for select dropdowns
        function handleCustomSelect(selectId) {
            const select = document.getElementById(selectId);
            const customInput = document.getElementById(selectId + '_custom');

            if (!select || !customInput) return;

            select.addEventListener('change', function() {
                if (this.value === '__custom__') {
                    customInput.classList.remove('d-none');
                    customInput.required = true;
                    customInput.focus();
                    // Reset the actual select to use custom input value
                    this.value = '';
                } else {
                    customInput.classList.add('d-none');
                    customInput.required = false;
                    customInput.value = '';
                }
            });

            // When custom input changes, update the hidden field
            customInput.addEventListener('blur', function() {
                if (this.value.trim()) {
                    // Create a temporary option and select it
                    const option = document.createElement('option');
                    option.value = this.value;
                    option.text = this.value;
                    option.selected = true;
                    select.insertBefore(option, select.querySelector('[value="__custom__"]'));
                }
            });
        }

        // Apply to all attribute selects
        handleCustomSelect('color');
        handleCustomSelect('size');
        handleCustomSelect('capacity');
        handleCustomSelect('custom_attr_name');
        handleCustomSelect('custom_attr_value');

        // Handle custom attribute name change to populate value options
        const attrNameSelect = document.getElementById('custom_attr_name');
        const attrValueSelect = document.getElementById('custom_attr_value');

        if (attrNameSelect && attrValueSelect) {
            attrNameSelect.addEventListener('change', function() {
                const selectedAttr = this.value;

                // Clear existing options
                attrValueSelect.innerHTML = '<option value="">-- Chọn giá trị --</option>';

                if (selectedAttr && selectedAttr !== '__custom__' && attributeOptions[selectedAttr]) {
                    // Populate options from predefined values
                    const options = attributeOptions[selectedAttr].options;
                    for (const [value, label] of Object.entries(options)) {
                        const option = document.createElement('option');
                        option.value = value;
                        option.text = label;
                        attrValueSelect.appendChild(option);
                    }

                    // Add custom option
                    const customOption = document.createElement('option');
                    customOption.value = '__custom__';
                    customOption.text = '✏️ Nhập tùy chỉnh...';
                    attrValueSelect.appendChild(customOption);

                    attrValueSelect.disabled = false;
                } else if (selectedAttr === '__custom__') {
                    attrValueSelect.disabled = false;
                    const customOption = document.createElement('option');
                    customOption.value = '__custom__';
                    customOption.text = '✏️ Nhập tùy chỉnh...';
                    attrValueSelect.appendChild(customOption);
                } else {
                    attrValueSelect.disabled = true;
                }
            });
        }

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
