<?php
/**
 * Báo Cáo Doanh Thu
 */
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$page = $_GET['page'] ?? 1;
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-dollar-sign"></i> Báo Cáo Doanh Thu
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/reports" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay Lại Dashboard
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <?php if ($data['summary'] ?? false): ?>
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-money-bill-wave"></i> Tổng Doanh Thu
                    </h6>
                    <h4 class="text-success mb-0">
                        <?= htmlspecialchars($data['summary']['formatted_total_revenue'] ?? '₫0') ?>
                    </h4>
                    <small class="text-muted"><?= number_format($data['summary']['total_quantity'] ?? 0) ?> sản phẩm bán</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-shopping-cart"></i> Tổng Đơn Hàng
                    </h6>
                    <h4 class="text-primary mb-0">
                        <?= number_format($data['summary']['total_orders'] ?? 0) ?>
                    </h4>
                    <small class="text-muted">đơn hàng</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-calculator"></i> Giá Trị Đơn Trung Bình
                    </h6>
                    <h4 class="text-info mb-0">
                        <?= htmlspecialchars($data['summary']['formatted_avg_order_value'] ?? '₫0') ?>
                    </h4>
                    <small class="text-muted">trên mỗi đơn</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-box"></i> Tổng SKU Bán
                    </h6>
                    <h4 class="text-warning mb-0">
                        <?= number_format($data['summary']['total_products'] ?? 0) ?>
                    </h4>
                    <small class="text-muted">sản phẩm khác nhau</small>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
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
                            <a href="/admin/reports/sales" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="row mb-4">
        <div class="col-md-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="by-product-tab" data-bs-toggle="tab" 
                            data-bs-target="#by-product-content" type="button" role="tab" 
                            aria-controls="by-product-content" aria-selected="true">
                        <i class="fas fa-list"></i> Doanh Thu Theo Sản Phẩm
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="by-category-tab" data-bs-toggle="tab" 
                            data-bs-target="#by-category-content" type="button" role="tab" 
                            aria-controls="by-category-content" aria-selected="false">
                        <i class="fas fa-tags"></i> Doanh Thu Theo Danh Mục
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="daily-trend-tab" data-bs-toggle="tab" 
                            data-bs-target="#daily-trend-content" type="button" role="tab" 
                            aria-controls="daily-trend-content" aria-selected="false">
                        <i class="fas fa-chart-area"></i> Xu Hướng Hàng Ngày
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Tab Contents -->
    <div class="tab-content">
        <!-- By Product Tab -->
        <div class="tab-pane fade show active" id="by-product-content" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar"></i> Doanh Thu Chi Tiết Theo Sản Phẩm
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($data['by_product'] ?? false): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">STT</th>
                                            <th>Sản Phẩm</th>
                                            <th style="width: 100px;">Số Lượng Bán</th>
                                            <th style="width: 150px;">Doanh Thu</th>
                                            <th style="width: 100px;">Số Đơn</th>
                                            <th style="width: 100px;">Giá Bình Quân</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $stt = 1; foreach ($data['by_product'] as $prod): ?>
                                        <tr>
                                            <td><small class="text-muted"><?= $stt++ ?></small></td>
                                            <td>
                                                <strong><?= htmlspecialchars($prod['product_name'] ?? 'N/A') ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    Danh mục: <?= htmlspecialchars($prod['category_name'] ?? 'N/A') ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= number_format($prod['total_quantity'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    <?= htmlspecialchars($prod['formatted_total_revenue'] ?? '₫0') ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <small><?= number_format($prod['order_count'] ?? 0) ?></small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?= htmlspecialchars($prod['formatted_avg_price'] ?? '₫0') ?>
                                                </small>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info m-3" role="alert">
                                <i class="fas fa-info-circle"></i> Không có dữ liệu doanh thu.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- By Category Tab -->
        <div class="tab-pane fade" id="by-category-content" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-pie"></i> Doanh Thu Theo Danh Mục
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($data['by_category'] ?? false): ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 50px;">STT</th>
                                            <th>Danh Mục</th>
                                            <th style="width: 100px;">Số Sản Phẩm</th>
                                            <th style="width: 150px;">Doanh Thu</th>
                                            <th style="width: 120px;">% Doanh Thu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $stt = 1; foreach ($data['by_category'] as $cat): ?>
                                        <tr>
                                            <td><small class="text-muted"><?= $stt++ ?></small></td>
                                            <td>
                                                <strong><?= htmlspecialchars($cat['category_name'] ?? 'N/A') ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= number_format($cat['product_count'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    <?= htmlspecialchars($cat['formatted_total_revenue'] ?? '₫0') ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?= ($cat['revenue_percent'] ?? 0) ?>%">
                                                        <small><?= number_format($cat['revenue_percent'] ?? 0, 1) ?>%</small>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info m-3" role="alert">
                                <i class="fas fa-info-circle"></i> Không có dữ liệu doanh thu theo danh mục.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Trend Tab -->
        <div class="tab-pane fade" id="daily-trend-content" role="tabpanel">
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line"></i> Xu Hướng Doanh Thu Hàng Ngày
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <?php if ($data['daily_trend'] ?? false): ?>
                            <?php
                                $maxRevenue = 0;
                                if (!empty($data['daily_trend'])) {
                                    $vals = array_column($data['daily_trend'], 'total_revenue');
                                    $maxRevenue = max($vals) ?: 0;
                                }
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 120px;">Ngày</th>
                                            <th style="width: 100px;">Số Đơn</th>
                                            <th style="width: 100px;">Số Lượng</th>
                                            <th style="width: 150px;">Doanh Thu</th>
                                            <th>Biểu Đồ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['daily_trend'] as $daily): ?>
                                        <tr>
                                            <td>
                                                <strong>
                                                    <?= date('d/m/Y', strtotime($daily['sale_date'] ?? date('Y-m-d'))) ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    <?= number_format($daily['order_count'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    <?= number_format($daily['total_quantity'] ?? 0) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong class="text-success">
                                                    <?= htmlspecialchars($daily['formatted_total_revenue'] ?? '₫0') ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: <?= min(($daily['total_revenue'] ?? 0) / ($maxRevenue ?: 1) * 100, 100) ?>%">
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <div class="alert alert-info m-3" role="alert">
                                <i class="fas fa-info-circle"></i> Không có dữ liệu xu hướng hàng ngày.
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
