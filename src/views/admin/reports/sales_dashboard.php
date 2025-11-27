<?php
/**
 * Báo Cáo Doanh Thu Toàn Diện
 * - KPI Summary
 * - Daily Trend Chart (Line)
 * - Category Distribution Chart (Pie)
 * - Top 10 Products Table
 * - Product Details Table (Paginated)
 * - Category Details Table
 * - Filters: Date Range, Category, Brand
 */
$startDate = $data['start_date'] ?? null;
$endDate = $data['end_date'] ?? null;
$kpis = $data['kpis'] ?? [];
$chartDaily = $data['chart_daily'] ?? null;
$chartCategory = $data['chart_category'] ?? null;
$topProducts = $data['top_products'] ?? [];
$productDetails = $data['product_details'] ?? [];
$categoryDetails = $data['category_details'] ?? [];
$pagination = $data['pagination'] ?? [];
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-chart-bar"></i> Báo Cáo Doanh Thu
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/dashboard" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
    </div>

    <!-- PHẦN 1: FILTER -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-filter"></i> Bộ Lọc</h6>
                </div>
                <div class="card-body">
                    <form method="GET" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="startDate" class="form-label">Từ ngày</label>
                            <input type="date" id="startDate" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label for="endDate" class="form-label">Đến ngày</label>
                            <input type="date" id="endDate" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Lọc
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

    <!-- PHẦN 2: KPI SUMMARY CARDS -->
    <div class="row mb-4">
        <!-- Total Revenue -->
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted small mb-2">Tổng Doanh Thu</h6>
                            <h3 class="text-primary mb-0">
                                <?= number_format($kpis['total_revenue'] ?? 0) ?><small class="text-muted fs-6"> ₫</small>
                            </h3>
                        </div>
                        <i class="fas fa-dollar-sign text-primary fs-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gross Profit -->
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted small mb-2">Lợi Nhuận Gộp</h6>
                            <h3 class="text-success mb-0">
                                <?= number_format($kpis['gross_profit'] ?? 0) ?><small class="text-muted fs-6"> ₫</small>
                            </h3>
                        </div>
                        <i class="fas fa-chart-line text-success fs-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted small mb-2">Tổng Đơn Hàng</h6>
                            <h3 class="text-info mb-0">
                                <?= number_format($kpis['total_orders'] ?? 0) ?>
                            </h3>
                        </div>
                        <i class="fas fa-shopping-cart text-info fs-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Order Value -->
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted small mb-2">Giá Trị Trung Bình</h6>
                            <h3 class="text-warning mb-0">
                                <?= number_format($kpis['average_order_value'] ?? 0) ?><small class="text-muted fs-6"> ₫</small>
                            </h3>
                        </div>
                        <i class="fas fa-balance-scale text-warning fs-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profit Margin -->
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted small mb-2">Tỷ Suất Lợi Nhuận</h6>
                            <h3 class="text-danger mb-0">
                                <?= $kpis['profit_margin_percent'] ?? 0 ?>%
                            </h3>
                        </div>
                        <i class="fas fa-percent text-danger fs-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customers -->
        <div class="col-md-2 mb-3">
            <div class="card border-0 shadow-sm bg-secondary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted small mb-2">Khách Hàng</h6>
                            <h3 class="text-secondary mb-0">
                                <?= number_format($kpis['unique_customers'] ?? 0) ?>
                            </h3>
                        </div>
                        <i class="fas fa-users text-secondary fs-4 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PHẦN 3: TREND CHARTS -->
    <div class="row mb-4">
        <!-- Daily Trend Line Chart -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-chart-line"></i> Xu Hướng Doanh Thu & Lợi Nhuận (Theo Ngày)</h6>
                </div>
                <div class="card-body" style="position: relative; height: 350px;">
                    <?php if ($chartDaily && !empty($chartDaily['labels'])): ?>
                        <canvas id="trendChart"></canvas>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">Không có dữ liệu để hiển thị biểu đồ.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Category Distribution Bar Chart -->
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Doanh Thu Theo Danh Mục</h6>
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#categoryFilterCollapse" aria-expanded="false">
                            Chọn Danh Mục
                        </button>
                    </div>
                </div>
                
                <!-- Category Filter Section -->
                <div class="collapse" id="categoryFilterCollapse">
                    <div class="card-body bg-light border-bottom">
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label"><strong>Chọn danh mục để hiển thị:</strong></label>
                                <div id="categoryCheckboxes" class="category-tree-container" style="max-height: 400px; overflow-y: auto; padding: 15px; overflow-x: hidden;">
                                    <style>
                                        /* Fix scrollbar causing layout shift */
                                        .category-tree-container,
                                        .table-responsive {
                                            scrollbar-gutter: stable;
                                        }
                                        
                                        .category-item {
                                            margin-bottom: 6px;
                                            position: relative;
                                        }
                                        .category-item .form-check {
                                            display: flex;
                                            align-items: center;
                                        }
                                        .category-item .form-check-input {
                                            margin-right: 8px;
                                            margin-top: 0;
                                            flex-shrink: 0;
                                        }
                                        .category-item label {
                                            white-space: nowrap;
                                            overflow: hidden;
                                            text-overflow: ellipsis;
                                        }
                                        .connector-line {
                                            position: absolute;
                                            background-color: #ddd;
                                        }
                                        .connector-vertical {
                                            position: absolute;
                                            background-color: #ddd;
                                        }
                                    </style>
                                    <?php if (!empty($category_tree)): ?>
                                        <!-- Render Category Tree with Connectors -->
                                        <?php 
                                        function renderCategoryTree($categories, $level = 0) {
                                            $count = count($categories);
                                            foreach ($categories as $index => $cat) {
                                                $isLastChild = ($index === $count - 1);
                                                $hasChildren = !empty($cat['children']);
                                        ?>
                                            <div class="category-item" style="padding-left: <?= $level * 20 ?>px;">
                                                <!-- Connector lines cho con -->
                                                <?php if ($level > 0): ?>
                                                    <!-- Ngang từ cha -->
                                                    <div class="connector-line" style="
                                                        position: absolute;
                                                        left: <?= ($level * 20) - 10 ?>px;
                                                        top: 15px;
                                                        width: 10px;
                                                        height: 1px;
                                                    "></div>
                                                    <!-- Dọc từ cha (nếu không phải con cuối) -->
                                                    <?php if (!$isLastChild): ?>
                                                        <div class="connector-vertical" style="
                                                            position: absolute;
                                                            left: <?= ($level * 20) - 10 ?>px;
                                                            top: 15px;
                                                            width: 1px;
                                                            height: 26px;
                                                        "></div>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <div class="form-check">
                                                    <input class="form-check-input category-checkbox" 
                                                           type="checkbox" 
                                                           id="category_<?= $cat['id'] ?>" 
                                                           value="<?= $cat['id'] ?>" 
                                                           checked 
                                                           data-category-name="<?= htmlspecialchars($cat['name']) ?>"
                                                           data-has-children="<?= $hasChildren ? 'true' : 'false' ?>"
                                                           data-parent-id="category_<?= $cat['id'] ?>">
                                                    <label class="form-check-label" for="category_<?= $cat['id'] ?>">
                                                        <?php if ($hasChildren): ?>
                                                            <i class="fas fa-folder" style="color: #ffc107; width: 16px; text-align: center;"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-tag" style="color: #6c757d; width: 16px; text-align: center;"></i>
                                                        <?php endif; ?>
                                                        <span style="font-weight: <?= $hasChildren ? '600' : '400' ?>; margin-left: 6px;">
                                                            <?= htmlspecialchars($cat['name']) ?>
                                                        </span>
                                                        <small class="text-muted ms-2">(<?= number_format($cat['total_revenue'] ?? 0) ?> ₫)</small>
                                                    </label>
                                                </div>
                                                
                                                <!-- Render Children -->
                                                <?php if ($hasChildren): ?>
                                                    <div class="category-children" data-parent-id="category_<?= $cat['id'] ?>">
                                                        <?php renderCategoryTree($cat['children'], $level + 1); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php 
                                            }
                                        }
                                        renderCategoryTree($category_tree);
                                        ?>

                                        <!-- Uncategorized -->
                                        <hr class="my-3">
                                        <div class="category-item">
                                            <div class="form-check">
                                                <input class="form-check-input category-checkbox" 
                                                       type="checkbox" 
                                                       id="category_0" 
                                                       value="0" 
                                                       checked 
                                                       data-category-name="Không có danh mục"
                                                       data-has-children="false">
                                                <label class="form-check-label" for="category_0">
                                                    <i class="fas fa-inbox" style="color: #6c757d; width: 16px; text-align: center;"></i>
                                                    <span style="margin-left: 6px;">Không có danh mục</span>
                                                </label>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Không có danh mục nào.</p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-primary" onclick="updateCategoryChart()">
                                        <i class="fas fa-sync"></i> Cập Nhật Biểu Đồ
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="selectAllCategories()">
                                        <i class="fas fa-check-double"></i> Chọn Tất Cả
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="clearAllCategories()">
                                        <i class="fas fa-times-circle"></i> Bỏ Chọn Tất Cả
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body" style="position: relative; height: 350px;">
                    <?php if ($chartCategory && !empty($chartCategory['labels'])): ?>
                        <canvas id="categoryChart"></canvas>
                    <?php else: ?>
                        <div class="alert alert-info mt-3">Không có dữ liệu để hiển thị biểu đồ.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PHẦN 4: CATEGORY DETAILS TABLE -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-list"></i> Chi Tiết Doanh Thu Theo Danh Mục</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($categoryDetails)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 30%;">Danh Mục</th>
                                        <th style="width: 10%; text-align: right;">Số Đơn Hàng</th>
                                        <th style="width: 10%; text-align: right;">Số Sản Phẩm</th>
                                        <th style="width: 12%; text-align: right;">Tổng Số Lượng</th>
                                        <th style="width: 14%; text-align: right;">Doanh Thu</th>
                                        <th style="width: 14%; text-align: right;">Lợi Nhuận</th>
                                        <th style="width: 10%; text-align: right;">Tỷ Suất %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Tách danh mục thường từ "Không có danh mục"
                                    $normalCategories = [];
                                    $uncategorized = null;
                                    
                                    foreach ($categoryDetails as $cat) {
                                        if ($cat['category_name'] === 'Không có danh mục') {
                                            $uncategorized = $cat;
                                        } else {
                                            $normalCategories[] = $cat;
                                        }
                                    }
                                    
                                    // Hiển thị danh mục bình thường
                                    foreach ($normalCategories as $cat): 
                                    ?>
                                        <tr class="category-details-row" data-category-id="<?= $cat['id'] ?>">
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <strong><?= htmlspecialchars($cat['category_name']) ?></strong>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="filterBrandAndProductByCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars($cat['category_name']) ?>', event)" 
                                                            title="Xem thương hiệu và sản phẩm của danh mục này">
                                                        Xem Chi Tiết
                                                    </button>
                                                </div>
                                            </td>
                                            <td style="text-align: right;"><?= $cat['order_count'] ?></td>
                                            <td style="text-align: right;"><?= $cat['product_count'] ?></td>
                                            <td style="text-align: right;"><?= number_format($cat['total_quantity']) ?></td>
                                            <td style="text-align: right;" class="text-success"><strong><?= number_format($cat['total_revenue']) ?> ₫</strong></td>
                                            <td style="text-align: right;" class="text-info"><strong><?= number_format($cat['total_profit']) ?> ₫</strong></td>
                                            <td style="text-align: right;"><?= $cat['profit_margin_percent'] ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    
                                    <!-- Hiển thị "Không có danh mục" ở dưới cùng -->
                                    <?php if ($uncategorized): ?>
                                        <tr class="category-details-row" data-category-id="0" style="background-color: #f8f9fa;">
                                            <td>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <strong style="color: #6c757d;">📌 <?= htmlspecialchars($uncategorized['category_name']) ?></strong>
                                                    <button class="btn btn-sm btn-outline-primary" 
                                                            onclick="filterBrandAndProductByCategory(0, 'Không có danh mục', event)" 
                                                            title="Xem thương hiệu và sản phẩm của danh mục này">
                                                        Xem Chi Tiết
                                                    </button>
                                                </div>
                                            </td>
                                            <td style="text-align: right;"><?= $uncategorized['order_count'] ?></td>
                                            <td style="text-align: right;"><?= $uncategorized['product_count'] ?></td>
                                            <td style="text-align: right;"><?= number_format($uncategorized['total_quantity']) ?></td>
                                            <td style="text-align: right;" class="text-success"><strong><?= number_format($uncategorized['total_revenue']) ?> ₫</strong></td>
                                            <td style="text-align: right;" class="text-info"><strong><?= number_format($uncategorized['total_profit']) ?> ₫</strong></td>
                                            <td style="text-align: right;"><?= $uncategorized['profit_margin_percent'] ?>%</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info m-3">Không có dữ liệu danh mục.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PHẦN 5: BRAND DETAILS TABLE -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-tag"></i> Doanh Thu Theo Thương Hiệu</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($supplier_details)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">Thương Hiệu</th>
                                        <th style="width: 10%; text-align: right;">Số Đơn Hàng</th>
                                        <th style="width: 10%; text-align: right;">Số Sản Phẩm</th>
                                        <th style="width: 12%; text-align: right;">Tổng Số Lượng</th>
                                        <th style="width: 14%; text-align: right;">Doanh Thu</th>
                                        <th style="width: 14%; text-align: right;">Lợi Nhuận</th>
                                        <th style="width: 10%; text-align: right;">Tỷ Suất %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($supplier_details as $brand): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($brand['brand_name']) ?></strong></td>
                                            <td style="text-align: right;"><?= $brand['order_count'] ?></td>
                                            <td style="text-align: right;"><?= $brand['product_count'] ?></td>
                                            <td style="text-align: right;"><?= number_format($brand['total_quantity']) ?></td>
                                            <td style="text-align: right;" class="text-success"><strong><?= number_format($brand['total_revenue']) ?> ₫</strong></td>
                                            <td style="text-align: right;" class="text-info"><strong><?= number_format($brand['total_profit']) ?> ₫</strong></td>
                                            <td style="text-align: right;"><?= $brand['profit_margin_percent'] ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info m-3">Không có dữ liệu thương hiệu.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- PHẦN 6: PRODUCT DETAILS TABLE (Paginated) -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-table"></i> Chi Tiết Doanh Thu Theo Sản Phẩm (Trang <?= $pagination['current_page'] ?? 1 ?>/<?= $pagination['total_pages'] ?? 1 ?>)</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($productDetails)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%;">Sản Phẩm</th>
                                        <th style="width: 12%;">Danh Mục</th>
                                        <th style="width: 12%;">SKU</th>
                                        <th style="width: 8%; text-align: right;">Giá Vốn</th>
                                        <th style="width: 8%; text-align: right;">Bán được</th>
                                        <th style="width: 10%; text-align: right;">Doanh Thu</th>
                                        <th style="width: 10%; text-align: right;">Lợi Nhuận</th>
                                        <th style="width: 8%; text-align: right;">Tỷ Suất %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($productDetails as $product): ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($product['product_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($product['category_name']) ?></td>
                                            <td><code><?= htmlspecialchars($product['variant_sku'] ?? $product['product_sku'] ?? '-') ?></code></td>
                                            <td style="text-align: right;"><?= number_format($product['cost_price'] ?? 0) ?> ₫</td>
                                            <td style="text-align: right;"><?= number_format($product['quantity_sold']) ?></td>
                                            <td style="text-align: right;" class="text-success"><strong><?= number_format($product['total_revenue']) ?> ₫</strong></td>
                                            <td style="text-align: right;" class="text-info"><strong><?= number_format($product['gross_profit']) ?> ₫</strong></td>
                                            <td style="text-align: right;"><?= $product['profit_margin_percent'] ?>%</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if (($pagination['total_pages'] ?? 1) > 1): ?>
                            <nav aria-label="Page navigation" class="d-flex justify-content-center mt-3 mb-3">
                                <ul class="pagination">
                                    <?php for ($i = 1; $i <= ($pagination['total_pages'] ?? 1); $i++): ?>
                                        <li class="page-item <?= $i === ($pagination['current_page'] ?? 1) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $startDate ? '&start_date=' . $startDate : '' ?><?= $endDate ? '&end_date=' . $endDate : '' ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info m-3">Không có dữ liệu sản phẩm.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
/**
 * Xử lý logic cha-con cho category checkboxes
 */
function setupCategoryTreeLogic() {
    const checkboxes = document.querySelectorAll('.category-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const currentId = this.id;
            const hasChildren = this.getAttribute('data-has-children') === 'true';
            const parentId = this.getAttribute('data-parent-id');
            
            if (hasChildren) {
                // Nếu chọn cha → tick hết con
                const childrenContainer = document.querySelector(`.category-children[data-parent-id="${parentId}"]`);
                if (childrenContainer) {
                    const childCheckboxes = childrenContainer.querySelectorAll('.category-checkbox');
                    childCheckboxes.forEach(child => {
                        child.checked = this.checked;
                        // Trigger change event cho con
                        child.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }
            } else {
                // Nếu bỏ chọn con → bỏ chọn cha
                if (!this.checked) {
                    // Tìm cha gần nhất
                    let parent = this.closest('.category-children');
                    if (parent) {
                        const parentCheckbox = document.querySelector(`#${parent.getAttribute('data-parent-id')}`);
                        if (parentCheckbox && parentCheckbox.checked) {
                            parentCheckbox.checked = false;
                            parentCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                    }
                }
            }
            
            // Cập nhật chart và data khi thay đổi checkbox
            updateCategoryChart();
            updateCategoryDetailsTable();
        });
    });
}

/**
 * Cập nhật bảng Chi Tiết Doanh Thu Theo Danh Mục khi filter
 */
function updateCategoryDetailsTable() {
    const checkedCheckboxes = document.querySelectorAll('.category-checkbox:checked');
    const selectedCategories = Array.from(checkedCheckboxes).map(cb => cb.value);
    
    // Ẩn/hiện các dòng theo danh mục được chọn
    const rows = document.querySelectorAll('.category-details-row');
    rows.forEach(row => {
        const categoryId = row.getAttribute('data-category-id');
        if (selectedCategories.includes(categoryId)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // Setup category tree logic
    setupCategoryTreeLogic();
    
    // Lưu trữ dữ liệu biểu đồ gốc cho lọc danh mục
    window.originalCategoryChartData = <?= json_encode($chartCategory) ?>;
    
    // Trend Line Chart
    <?php if ($chartDaily && !empty($chartDaily['labels'])): ?>
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    window.trendChart = new Chart(trendCtx, {
        type: 'line',
        data: <?= json_encode($chartDaily) ?>,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                label += context.parsed.y.toLocaleString('vi-VN') + ' ₫';
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' ₫';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>

    // Category Bar Chart (Horizontal)
    <?php if ($chartCategory && !empty($chartCategory['labels'])): ?>
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    window.categoryChart = new Chart(categoryCtx, {
        type: 'bar',
        data: <?= json_encode($chartCategory) ?>,
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Doanh thu: ' + context.parsed.x.toLocaleString('vi-VN') + ' ₫';
                        }
                    }
                }
            },
            onClick: function(event, elements) {
                // Tắt click handler trên chart - chỉ sử dụng button trong bảng danh mục
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('vi-VN') + ' ₫';
                        }
                    }
                }
            }
        }
    });
    <?php endif; ?>
    
});
</script>

<!-- Functions for AJAX filtering -->
<script>
    // Filter by Category (OLD - chỉ dùng khi cần reload trang - không sử dụng hiện tại)
    function filterByCategory(categoryId, categoryName) {
        const startDate = document.getElementById('startDate')?.value || '';
        const endDate = document.getElementById('endDate')?.value || '';
        
        let url = '/admin/reports/sales?category_id=' + categoryId;
        if (startDate) url += '&start_date=' + startDate;
        if (endDate) url += '&end_date=' + endDate;
        
        window.location.href = url;
    }
    // Filter Brand and Product by Category (AJAX - không reload trang)
    function filterBrandAndProductByCategory(categoryId, categoryName, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        const startDate = document.getElementById('startDate')?.value || '';
        const endDate = document.getElementById('endDate')?.value || '';
        
        console.log('Filtering brands and products for category:', categoryId, categoryName);
        console.log('Start Date:', startDate, 'End Date:', endDate);
        
        // Gọi AJAX để lấy dữ liệu thương hiệu
        const brandUrl = `/admin/reports/sales-data/brands?category_id=${categoryId}` + 
                         (startDate ? `&start_date=${startDate}` : '') + 
                         (endDate ? `&end_date=${endDate}` : '');
        
        console.log('Fetching from:', brandUrl);
        
        fetch(brandUrl)
            .then(response => {
                console.log('Brands response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Brands raw response:', text);
                const data = JSON.parse(text);
                console.log('Brands parsed data:', data);
                updateBrandTable(data.brands || []);
            })
            .catch(err => {
                console.error('Error fetching brands:', err);
                alert('Lỗi khi lấy dữ liệu thương hiệu: ' + err.message);
            });
        
        // Gọi AJAX để lấy dữ liệu sản phẩm
        const productUrl = `/admin/reports/sales-data/products?category_id=${categoryId}` + 
                           (startDate ? `&start_date=${startDate}` : '') + 
                           (endDate ? `&end_date=${endDate}` : '');
        
        console.log('Fetching from:', productUrl);
        
        fetch(productUrl)
            .then(response => {
                console.log('Products response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Products raw response:', text);
                const data = JSON.parse(text);
                console.log('Products parsed data:', data);
                updateProductTable(data.products || [], data.pagination || {});
            })
            .catch(err => {
                console.error('Error fetching products:', err);
                alert('Lỗi khi lấy dữ liệu sản phẩm: ' + err.message);
            });
    }
    
    // Cập nhật bảng Thương Hiệu
    function updateBrandTable(brands) {
        console.log('updateBrandTable called with brands:', brands);
        
        // Tìm bảng thương hiệu bằng cách tìm card có header "Doanh Thu Theo Thương Hiệu"
        let brandTableBody = null;
        const cards = document.querySelectorAll('.card');
        
        for (let card of cards) {
            const header = card.querySelector('.card-header h6');
            if (header) {
                const headerText = header.innerText || header.textContent;
                console.log('Found header:', headerText);
                if (headerText.includes('Doanh Thu Theo Thương Hiệu')) {
                    const table = card.querySelector('table');
                    if (table) {
                        brandTableBody = table.querySelector('tbody');
                        console.log('Found brand table body');
                        break;
                    }
                }
            }
        }
        
        if (!brandTableBody) {
            console.error('Could not find brand table body');
            return;
        }
        
        // Xóa hàng cũ
        brandTableBody.innerHTML = '';
        
        if (brands.length === 0) {
            console.log('No brands data');
            brandTableBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted">Không có dữ liệu thương hiệu</td></tr>';
            return;
        }
        
        // Thêm hàng mới
        brands.forEach(brand => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${brand.brand_name}</strong></td>
                <td style="text-align: right;">${brand.order_count}</td>
                <td style="text-align: right;">${brand.product_count}</td>
                <td style="text-align: right;">${new Intl.NumberFormat('vi-VN').format(brand.total_quantity)}</td>
                <td style="text-align: right;" class="text-success"><strong>${new Intl.NumberFormat('vi-VN').format(brand.total_revenue)} ₫</strong></td>
                <td style="text-align: right;" class="text-info"><strong>${new Intl.NumberFormat('vi-VN').format(brand.total_profit)} ₫</strong></td>
                <td style="text-align: right;">${brand.profit_margin_percent}%</td>
            `;
            brandTableBody.appendChild(row);
        });
        
        console.log('Brand table updated with', brands.length, 'rows');
    }
    
    // Cập nhật bảng Sản Phẩm
    function updateProductTable(products, pagination) {
        console.log('updateProductTable called with products:', products, 'pagination:', pagination);
        
        // Tìm bảng sản phẩm bằng cách tìm card có header "Chi Tiết Doanh Thu Theo Sản Phẩm"
        let productTableBody = null;
        let productCard = null;
        const cards = document.querySelectorAll('.card');
        
        for (let card of cards) {
            const header = card.querySelector('.card-header h6');
            if (header) {
                const headerText = header.innerText || header.textContent;
                console.log('Found header:', headerText);
                if (headerText.includes('Chi Tiết Doanh Thu Theo Sản Phẩm')) {
                    const table = card.querySelector('table');
                    if (table) {
                        productTableBody = table.querySelector('tbody');
                        productCard = card;
                        console.log('Found product table body');
                        break;
                    }
                }
            }
        }
        
        if (!productTableBody) {
            console.error('Could not find product table body');
            return;
        }
        
        // Xóa hàng cũ
        productTableBody.innerHTML = '';
        
        if (products.length === 0) {
            console.log('No products data');
            productTableBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted">Không có dữ liệu sản phẩm</td></tr>';
            return;
        }
        
        // Thêm hàng mới
        products.forEach(product => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td><strong>${product.product_name}</strong></td>
                <td>${product.category_name || '-'}</td>
                <td><code>${product.variant_sku || product.product_sku || '-'}</code></td>
                <td style="text-align: right;">${new Intl.NumberFormat('vi-VN').format(product.cost_price || 0)} ₫</td>
                <td style="text-align: right;">${new Intl.NumberFormat('vi-VN').format(product.quantity_sold)}</td>
                <td style="text-align: right;" class="text-success"><strong>${new Intl.NumberFormat('vi-VN').format(product.total_revenue)} ₫</strong></td>
                <td style="text-align: right;" class="text-info"><strong>${new Intl.NumberFormat('vi-VN').format(product.gross_profit)} ₫</strong></td>
                <td style="text-align: right;">${product.profit_margin_percent}%</td>
            `;
            productTableBody.appendChild(row);
        });
        
        // Cập nhật header với thông tin trang
        if (productCard && pagination) {
            const cardHeader = productCard.querySelector('.card-header h6');
            if (cardHeader) {
                cardHeader.innerHTML = `<i class="fas fa-table"></i> Chi Tiết Doanh Thu Theo Sản Phẩm (Trang ${pagination.current_page || 1}/${pagination.total_pages || 1})`;
            }
        }
        
        console.log('Product table updated with', products.length, 'rows');
    }
    
    // Filter by Category (OLD - chỉ dùng khi cần reload trang - không sử dụng hiện tại)
    function filterByCategory(categoryId, categoryName) {
        const startDate = document.getElementById('startDate')?.value || '';
        const endDate = document.getElementById('endDate')?.value || '';
        
        let url = '/admin/reports/sales?category_id=' + categoryId;
        if (startDate) url += '&start_date=' + startDate;
        if (endDate) url += '&end_date=' + endDate;
        
        window.location.href = url;
    }
    
    // Update category chart based on selected checkboxes
    function updateCategoryChart() {
        if (typeof window.categoryChart === 'undefined') {
            alert('Biểu đồ chưa được tạo');
            return;
        }
        
        if (typeof window.originalCategoryChartData === 'undefined') {
            alert('Dữ liệu biểu đồ gốc không tìm thấy');
            return;
        }
        
        const checkboxes = document.querySelectorAll('.category-checkbox:checked');
        const selectedLabels = Array.from(checkboxes).map(cb => cb.dataset.categoryName);
        
        console.log('Selected categories:', selectedLabels);
        
        // Lọc dữ liệu biểu đồ từ dữ liệu gốc
        const originalChart = window.originalCategoryChartData;
        const filteredLabels = [];
        const filteredData = [];
        const filteredColors = [];
        
        originalChart.labels.forEach((label, index) => {
            if (selectedLabels.includes(label)) {
                filteredLabels.push(label);
                filteredData.push(originalChart.datasets[0].data[index]);
                filteredColors.push(originalChart.datasets[0].backgroundColor[index]);
            }
        });
        
        // Cập nhật biểu đồ
        window.categoryChart.data.labels = filteredLabels;
        window.categoryChart.data.datasets[0].data = filteredData;
        window.categoryChart.data.datasets[0].backgroundColor = filteredColors;
        window.categoryChart.update();
        
        console.log('Chart updated with', filteredLabels.length, 'categories');
    }
    
    // Select all categories
    function selectAllCategories() {
        document.querySelectorAll('.category-checkbox').forEach(cb => {
            cb.checked = true;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        });
        updateCategoryChart();
    }
    
    // Clear all categories
    function clearAllCategories() {
        document.querySelectorAll('.category-checkbox').forEach(cb => {
            cb.checked = false;
            cb.dispatchEvent(new Event('change', { bubbles: true }));
        });
        updateCategoryChart();
    }
</script>

