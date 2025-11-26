<?php
/**
 * Báo Cáo Sản Phẩm Giá Trị Cao
 */
$topN = $_GET['topN'] ?? 20;
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-gem"></i> Sản Phẩm Giá Trị Cao (Top <?= $topN ?>)
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/reports" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay Lại Dashboard
            </a>
        </div>
    </div>

    <!-- Information Box -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle"></i> Thông Tin Quan Trọng
                </h5>
                <small>
                    <strong>Sản phẩm giá trị cao</strong> là những sản phẩm có <strong>tổng giá trị tồn kho lớn</strong> 
                    (Số Lượng × Giá Vốn). Đây là những sản phẩm cần sự quản lý cẩn thận vì:
                    <ul class="mt-2 mb-0">
                        <li><strong>💰 Vốn lớn:</strong> Buộc vốn lớn trong hàng tồn kho</li>
                        <li><strong>⚖️ Cân bằng dòng tiền:</strong> Cần bán nhanh để giải phóng vốn</li>
                        <li><strong>📊 Ảnh hưởng lợi nhuận:</strong> Nếu ít bán sẽ ảnh hưởng nghiêm trọng</li>
                    </ul>
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="topN" class="form-label">
                                <i class="fas fa-list-ol"></i> Hiển Thị Top
                            </label>
                            <select id="topN" name="topN" class="form-select">
                                <option value="10" <?= $topN == 10 ? 'selected' : '' ?>>Top 10</option>
                                <option value="20" <?= $topN == 20 ? 'selected' : '' ?>>Top 20</option>
                                <option value="30" <?= $topN == 30 ? 'selected' : '' ?>>Top 30</option>
                                <option value="50" <?= $topN == 50 ? 'selected' : '' ?>>Top 50</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Tìm Kiếm
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="/admin/reports/high-value" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- High Value Products Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-table"></i> Danh Sách Sản Phẩm Giá Trị Cao
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($data['products'] ?? false): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">Xếp Hạng</th>
                                    <th>Sản Phẩm</th>
                                    <th style="width: 100px;">Danh Mục</th>
                                    <th style="width: 80px;">Tồn Kho</th>
                                    <th style="width: 100px;">Giá Vốn/Cái</th>
                                    <th style="width: 120px;">Giá Trị Tồn</th>
                                    <th style="width: 100px;">% Tổng Vốn</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                $total_stock_value = array_sum(array_column($data['products'], 'stock_value'));
                                foreach ($data['products'] as $prod): 
                                ?>
                                <tr>
                                    <td>
                                        <?php if ($rank <= 3): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-star"></i> #<?= $rank ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">#<?= $rank ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($prod['product_name'] ?? 'N/A') ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            SKU: <?= htmlspecialchars($prod['sku'] ?? 'N/A') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small><?= htmlspecialchars($prod['category_name'] ?? 'N/A') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= number_format($prod['current_quantity'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($prod['formatted_unit_cost'] ?? '₫0') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-primary">
                                            <?= htmlspecialchars($prod['formatted_stock_value'] ?? '₫0') ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div style="width: 100px;">
                                            <small class="text-muted">
                                                <?= number_format(($prod['stock_value'] ?? 0) / ($total_stock_value ?: 1) * 100, 1) ?>%
                                            </small>
                                            <div class="progress" style="height: 15px;">
                                                <div class="progress-bar bg-primary" role="progressbar" 
                                                     style="width: <?= min(($prod['stock_value'] ?? 0) / ($total_stock_value ?: 1) * 100, 100) ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php $rank++; endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info m-3" role="alert">
                        <i class="fas fa-info-circle"></i> Không có dữ liệu sản phẩm giá trị cao.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php if ($data['products'] ?? false): ?>
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-boxes"></i> Số Sản Phẩm
                    </h6>
                    <h4 class="text-primary mb-0">
                        <?= count($data['products']) ?>
                    </h4>
                    <small class="text-muted">trong top này</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-cubes"></i> Tổng Tồn Kho
                    </h6>
                    <h4 class="text-primary mb-0">
                        <?= number_format(array_sum(array_column($data['products'], 'current_quantity')) ?? 0) ?>
                    </h4>
                    <small class="text-muted">đơn vị</small>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-money-bill"></i> Tổng Giá Trị Tồn
                    </h6>
                    <h4 class="text-primary mb-0">
                        <?php 
                        $total_value = 0;
                        foreach ($data['products'] as $prod) {
                            $total_value += ($prod['stock_value'] ?? 0);
                        }
                        echo $total_value > 1000000000 
                            ? number_format($total_value / 1000000000, 2) . ' tỷ ₫'
                            : number_format($total_value) . ' ₫';
                        ?>
                    </h4>
                    <small class="text-muted">vốn buộc</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Risk Management Box -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-shield-alt"></i> Quản Lý Rủi Ro
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Giám Sát Sức Khoẻ Doanh Số:</h6>
                            <ul class="small">
                                <li>📊 Theo dõi tỷ lệ bán hàng hàng ngày/tuần</li>
                                <li>🔄 Nếu bán quá chậm → cần khuyến mãi</li>
                                <li>⚠️ Nếu bán chậm → cộng vào slow-moving list</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Tối Ưu Hóa Dòng Tiền:</h6>
                            <ul class="small">
                                <li>💳 Tính toán chu kỳ vốn (Days Inventory Outstanding)</li>
                                <li>📈 Tăng velocity - bán nhanh hơn</li>
                                <li>🎯 Đặt hàng thông minh dựa vào nhu cầu</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
