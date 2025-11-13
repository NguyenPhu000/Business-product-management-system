View: Sửa biến thể sản phẩmPath: src/views/admin/products/variants/edit.php<?php

                                                                            /**
                                                                             * View: Sửa biến thể sản phẩm
                                                                             * Path: src/views/admin/products/variants/edit.php
                                                                             */

                                                                            // Parse attributes
                                                                            $attributes = !empty($variant['attributes']) ? json_decode($variant['attributes'], true) : [];
                                                                            $color = $attributes['Màu sắc'] ?? '';
                                                                            $size = $attributes['Kích thước'] ?? '';
                                                                            $capacity = $attributes['Dung lượng'] ?? '';

                                                                            // Custom attributes (loại trừ các thuộc tính chuẩn)
                                                                            $customAttrs = array_diff_key($attributes, array_flip(['Màu sắc', 'Kích thước', 'Dung lượng']));
                                                                            $customAttrName = !empty($customAttrs) ? key($customAttrs) : '';
                                                                            $customAttrValue = !empty($customAttrs) ? current($customAttrs) : '';

                                                                            // Inventory info
                                                                            $totalStock = !empty($variant['inventory']) ? array_sum(array_column($variant['inventory'], 'quantity')) : 0;
                                                                            ?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit"></i> Sửa biến thể sản phẩm
            </h1>
            <p class="text-muted mb-0">
                Sản phẩm: <strong><?= htmlspecialchars($product['name']) ?></strong>
                | SKU Variant: <code><?= htmlspecialchars($variant['sku']) ?></code>
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

    <!-- Current Stock Info -->
    <?php if (!empty($variant['inventory'])): ?>
        <div class="card bg-info bg-opacity-10 mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-0">
                            <i class="fas fa-box"></i> Tồn kho hiện tại:
                            <strong
                                class="text-<?= $totalStock > 10 ? 'success' : ($totalStock > 0 ? 'warning' : 'danger') ?>">
                                <?= number_format($totalStock) ?> đơn vị
                            </strong>
                        </h6>
                        <small class="text-muted">Để thay đổi tồn kho, vui lòng dùng chức năng Điều chỉnh tồn kho</small>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/admin/inventory/adjust/<?= $variant['id'] ?>" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-edit"></i> Điều chỉnh tồn kho
                        </a>
                        <a href="/admin/inventory/detail/<?= $variant['id'] ?>" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-info-circle"></i> Chi tiết
                        </a>
                    </div>
                </div>
            </div>
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
            <form method="POST" action="/admin/products/<?= $product['id'] ?>/variants/<?= $variant['id'] ?>/update"
                id="variantForm">

                <!-- Row 1: SKU và Barcode -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="sku" class="form-label">
                            SKU Biến thể <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="sku" name="sku"
                            value="<?= htmlspecialchars($variant['sku']) ?>" required>
                        <small class="text-muted">Mã định danh duy nhất cho biến thể</small>
                    </div>

                    <div class="col-md-6">
                        <label for="barcode" class="form-label">Barcode / EAN</label>
                        <input type="text" class="form-control" id="barcode" name="barcode"
                            value="<?= htmlspecialchars($variant['barcode'] ?? '') ?>"
                            placeholder="Nhập mã vạch (nếu có)">
                        <small class="text-muted">Dùng để quét mã vạch khi bán hàng</small>
                    </div>
                </div>

                <!-- Row 2: Thuộc tính -->
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
                                        <option value="<?= $value ?>" <?= $color === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                    <option value="__custom__" <?= !empty($color) && !isset($attributeOptions['color']['options'][$color]) ? 'selected' : '' ?>>✏️ Nhập tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 <?= !empty($color) && !isset($attributeOptions['color']['options'][$color]) ? '' : 'd-none' ?>"
                                    id="color_custom" value="<?= htmlspecialchars($color) ?>" placeholder="Nhập màu tùy chỉnh">
                            </div>

                            <div class="col-md-4">
                                <label for="size" class="form-label">
                                    <i class="fas fa-<?= $attributeOptions['size']['icon'] ?>"></i>
                                    <?= $attributeOptions['size']['label'] ?>
                                </label>
                                <select class="form-select" id="size" name="size" data-allow-custom="true">
                                    <option value="">-- Chọn hoặc nhập tùy chỉnh --</option>
                                    <?php foreach ($attributeOptions['size']['options'] as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $size === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                    <option value="__custom__" <?= !empty($size) && !isset($attributeOptions['size']['options'][$size]) ? 'selected' : '' ?>>✏️ Nhập tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 <?= !empty($size) && !isset($attributeOptions['size']['options'][$size]) ? '' : 'd-none' ?>"
                                    id="size_custom" value="<?= htmlspecialchars($size) ?>" placeholder="Nhập size tùy chỉnh">
                            </div>

                            <div class="col-md-4">
                                <label for="capacity" class="form-label">
                                    <i class="fas fa-<?= $attributeOptions['storage']['icon'] ?>"></i>
                                    <?= $attributeOptions['storage']['label'] ?>
                                </label>
                                <select class="form-select" id="capacity" name="capacity" data-allow-custom="true">
                                    <option value="">-- Chọn hoặc nhập tùy chỉnh --</option>
                                    <?php foreach ($attributeOptions['storage']['options'] as $value => $label): ?>
                                        <option value="<?= $value ?>" <?= $capacity === $value ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                    <option value="__custom__" <?= !empty($capacity) && !isset($attributeOptions['storage']['options'][$capacity]) ? 'selected' : '' ?>>✏️ Nhập tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 <?= !empty($capacity) && !isset($attributeOptions['storage']['options'][$capacity]) ? '' : 'd-none' ?>"
                                    id="capacity_custom" value="<?= htmlspecialchars($capacity) ?>" placeholder="Nhập dung lượng tùy chỉnh">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <label for="custom_attr_name" class="form-label">
                                    <i class="fas fa-tag"></i> Thuộc tính bổ sung
                                </label>
                                <select class="form-select" id="custom_attr_name" name="custom_attr_name" data-allow-custom="true">
                                    <option value="">-- Chọn loại thuộc tính --</option>
                                    <option value="material" <?= $customAttrName === 'material' ? 'selected' : '' ?>>Chất liệu</option>
                                    <option value="origin" <?= $customAttrName === 'origin' ? 'selected' : '' ?>>Xuất xứ</option>
                                    <option value="version" <?= $customAttrName === 'version' ? 'selected' : '' ?>>Phiên bản</option>
                                    <option value="weight" <?= $customAttrName === 'weight' ? 'selected' : '' ?>>Trọng lượng</option>
                                    <option value="ram" <?= $customAttrName === 'ram' ? 'selected' : '' ?>>RAM</option>
                                    <option value="__custom__" <?= !empty($customAttrName) && !in_array($customAttrName, ['material', 'origin', 'version', 'weight', 'ram']) ? 'selected' : '' ?>>✏️ Nhập tên tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 <?= !empty($customAttrName) && !in_array($customAttrName, ['material', 'origin', 'version', 'weight', 'ram']) ? '' : 'd-none' ?>"
                                    id="custom_attr_name_custom" value="<?= htmlspecialchars($customAttrName) ?>" placeholder="Nhập tên thuộc tính">
                            </div>

                            <div class="col-md-6">
                                <label for="custom_attr_value" class="form-label">
                                    <i class="fas fa-edit"></i> Giá trị
                                </label>
                                <select class="form-select" id="custom_attr_value" name="custom_attr_value" data-allow-custom="true">
                                    <option value="">-- Chọn giá trị --</option>
                                    <option value="__custom__" selected>✏️ Nhập tùy chỉnh...</option>
                                </select>
                                <input type="text" class="form-control mt-2 <?= !empty($customAttrValue) ? '' : 'd-none' ?>"
                                    id="custom_attr_value_custom" value="<?= htmlspecialchars($customAttrValue) ?>" placeholder="Nhập giá trị">
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
                            <input type="number" class="form-control" id="unit_cost" name="unit_cost" min="0"
                                step="0.01" value="<?= $variant['unit_cost'] ?>" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-muted">Giá nhập của biến thể này</small>
                    </div>

                    <div class="col-md-6">
                        <label for="price" class="form-label">
                            Giá bán <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="price" name="price" min="0" step="0.01"
                                value="<?= $variant['price'] ?>" required>
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        <small class="text-muted">Giá bán cho khách hàng</small>
                    </div>
                </div>

                <!-- Row 4: Trạng thái -->
                <div class="mb-4">
                    <label class="form-label">Trạng thái</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                            <?= $variant['is_active'] == 1 ? 'checked' : '' ?>>
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
                            <i class="fas fa-check-circle"></i> Cập nhật
                        </button>
                        <a href="/admin/products/<?= $product['id'] ?>/variants" class="btn btn-outline-secondary">
                            <i class="fas fa-times-circle"></i> Hủy
                        </a>
                    </div>
                    <button type="button" class="btn btn-danger" id="btnDelete">
                        <i class="fas fa-trash-alt"></i> Xóa biến thể
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Attribute options for custom attribute value dropdown
        const attributeOptions = <?= json_encode($attributeOptions) ?>;

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
                    this.value = '';
                } else {
                    customInput.classList.add('d-none');
                    customInput.required = false;
                    if (this.value) {
                        customInput.value = '';
                    }
                }
            });

            customInput.addEventListener('blur', function() {
                if (this.value.trim()) {
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

                attrValueSelect.innerHTML = '<option value="">-- Chọn giá trị --</option>';

                if (selectedAttr && selectedAttr !== '__custom__' && attributeOptions[selectedAttr]) {
                    const options = attributeOptions[selectedAttr].options;
                    for (const [value, label] of Object.entries(options)) {
                        const option = document.createElement('option');
                        option.value = value;
                        option.text = label;
                        attrValueSelect.appendChild(option);
                    }

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

        // Delete button
        document.getElementById('btnDelete').addEventListener('click', function() {
            if (confirm('Bạn có chắc muốn xóa biến thể này?\n\nHành động này không thể hoàn tác!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/products/<?= $product['id'] ?>/variants/<?= $variant['id'] ?>/delete';
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
</script>