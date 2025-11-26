<?php
/**
 * Báo Cáo Sản Phẩm Lợi Nhuận Cao
 */
$topN = $_GET['topN'] ?? 20;
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-award"></i> Sản Phẩm Lợi Nhuận Cao (Top <?= $topN ?>)
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/reports" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay Lại Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label for="topN" class="form-label">
                                <i class="fas fa-list-ol"></i> Top
                            </label>
                            <select id="topN" name="topN" class="form-select">
                                <option value="10" <?= $topN == 10 ? 'selected' : '' ?>>Top 10</option>
                                <option value="20" <?= $topN == 20 ? 'selected' : '' ?>>Top 20</option>
                                <option value="30" <?= $topN == 30 ? 'selected' : '' ?>>Top 30</option>
                                <option value="50" <?= $topN == 50 ? 'selected' : '' ?>>Top 50</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="startDate" class="form-label">
                                <i class="fas fa-calendar"></i> Từ Ngày
                            </label>
                            <input type="date" id="startDate" name="start_date" 
                                   class="form-control" value="<?= htmlspecialchars($start_date) ?>">
                        </div>
                        <div class="col-md-2">
                            <label for="endDate" class="form-label">
                                <i class="fas fa-calendar"></i> Đến Ngày
                            </label>
                            <input type="date" id="endDate" name="end_date" 
                                   class="form-control" value="<?= htmlspecialchars($end_date) ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Tìm Kiếm
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="/admin/reports/top-profit" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Profit Products Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-crown"></i> Top <?= $topN ?> Sản Phẩm Lợi Nhuận Cao
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
                                    <th style="width: 100px;">Số Lượng Bán</th>
                                    <th style="width: 120px;">Doanh Thu</th>
                                    <th style="width: 120px;">Giá Vốn</th>
                                    <th style="width: 120px;">Lợi Nhuận</th>
                                    <th style="width: 80px;">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                $total_profit = array_sum(array_column($data['products'], 'gross_profit'));
                                foreach ($data['products'] as $prod): 
                                ?>
                                <tr class="<?= $rank <= 3 ? 'table-light' : '' ?>">
                                    <td>
                                        <?php if ($rank == 1): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-medal"></i> #<?= $rank ?>
                                            </span>
                                        <?php elseif ($rank == 2): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-medal"></i> #<?= $rank ?>
                                            </span>
                                        <?php elseif ($rank == 3): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-medal"></i> #<?= $rank ?>
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
                                        <span class="badge bg-success">
                                            <?= number_format($prod['total_quantity'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">
                                            <?= htmlspecialchars($prod['formatted_total_revenue'] ?? '₫0') ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?= htmlspecialchars($prod['formatted_total_cost'] ?? '₫0') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">
                                            <?= htmlspecialchars($prod['formatted_gross_profit'] ?? '₫0') ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <div style="width: 80px;">
                                            <small class="text-success">
                                                <strong><?= number_format($prod['profit_margin'] ?? 0, 1) ?>%</strong>
                                            </small>
                                            <div class="progress" style="height: 15px;">
                                                <?php 
                                                $margin = $prod['profit_margin'] ?? 0;
                                                $color = $margin >= 40 ? 'success' : ($margin >= 20 ? 'info' : 'warning');
                                                ?>
                                                <div class="progress-bar bg-<?= $color ?>" role="progressbar" 
                                                     style="width: <?= min($margin, 100) ?>%">
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
                        <i class="fas fa-info-circle"></i> Không có dữ liệu sản phẩm lợi nhuận cao.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php if ($data['products'] ?? false): ?>
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-boxes"></i> Số Sản Phẩm
                    </h6>
                    <h4 class="text-success mb-0">
                        <?= count($data['products']) ?>
                    </h4>
                    <small class="text-muted">trong top này</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-dollar-sign"></i> Tổng Lợi Nhuận
                    </h6>
                    <h4 class="text-success mb-0">
                        <?php 
                        $total_profit = 0;
                        foreach ($data['products'] as $prod) {
                            $total_profit += ($prod['gross_profit'] ?? 0);
                        }
                        echo $total_profit > 1000000000 
                            ? number_format($total_profit / 1000000000, 2) . 'B ₫'
                            : number_format($total_profit) . ' ₫';
                        ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-percentage"></i> Avg Margin
                    </h6>
                    <h4 class="text-success mb-0">
                        <?= number_format(array_sum(array_column($data['products'], 'profit_margin')) / count($data['products']), 1) ?>%
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-chart-line"></i> % Tổng Lợi
                    </h6>
                    <h4 class="text-success mb-0">
                        <?php 
                        // Calculate percentage of top products profit vs all products profit
                        // This would need total profit from all products to calculate accurately
                        echo "Xem trang lợi nhuận";
                        ?>
                    </h4>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Strategy Box -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-lightbulb"></i> Chiến Lược Tối Ưu Hóa Lợi Nhuận
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>🎯 Tập Trung Bán:</h6>
                            <ul class="small">
                                <li>Ưu tiên bán các sản phẩm top lợi nhuận</li>
                                <li>Nhân viên bán hàng tập trung vào những sản phẩm này</li>
                                <li>Khuyến khích mua thêm thông qua cross-selling</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>💡 Tối Ưu Hóa:</h6>
                            <ul class="small">
                                <li>Kiếm các cách giảm chi phí sản xuất/nhập hàng</li>
                                <li>Tăng giá bán từ từ nếu có thể</li>
                                <li>Tìm kiếm nhà cung cấp rẻ hơn để tăng margin</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
