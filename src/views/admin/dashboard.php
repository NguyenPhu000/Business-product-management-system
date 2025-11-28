<?php
$stats = $data['stats'] ?? [];
$businessStats = $data['businessStats'] ?? [];
$inventoryStats = $data['inventoryStats'] ?? [];
$lowStockProducts = $data['lowStockProducts'] ?? [];
$topSellingProducts = $data['topSellingProducts'] ?? [];
$revenueChart = $data['revenueChart'] ?? [];
$recentLogs = $data['recentLogs'] ?? [];
?>

<!-- Welcome Banner -->
<div class="dashboard-welcome">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="welcome-title">
                <i class="fas fa-tachometer-alt"></i>
                Chào mừng trở lại, <?= \Core\View::e(\Helpers\AuthHelper::user()['full_name'] ?? 'Admin') ?>!
            </h1>
            <p class="welcome-subtitle">Tổng quan hoạt động kinh doanh hôm nay</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="welcome-date">
                <i class="fas fa-calendar-alt"></i>
                <?= date('d/m/Y - H:i') ?>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="quick-actions-bar">
    <a href="/admin/sales/create" class="quick-action-btn btn-sales">
        <i class="fas fa-cash-register"></i>
        <span>Xuất hàng</span>
    </a>
    <a href="/admin/purchase/create" class="quick-action-btn btn-purchase">
        <i class="fas fa-cart-plus"></i>
        <span>Nhập hàng</span>
    </a>
    <a href="/admin/products/create" class="quick-action-btn btn-product">
        <i class="fas fa-plus-circle"></i>
        <span>Thêm sản phẩm</span>
    </a>
    <a href="/admin/inventory" class="quick-action-btn btn-inventory">
        <i class="fas fa-warehouse"></i>
        <span>Kiểm kho</span>
    </a>
    <a href="/admin/reports/sales" class="quick-action-btn btn-report">
        <i class="fas fa-chart-line"></i>
        <span>Báo cáo</span>
    </a>
</div>

<!-- KPI Cards Row 1: Business Overview -->
<div class="dashboard-section">
    <h5 class="section-title">
        <i class="fas fa-chart-bar"></i> Tổng quan kinh doanh
    </h5>
    <div class="stats-grid-4">
        <div class="stat-card-modern card-gradient-blue" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-icon">
                <i class="fas fa-box-open"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($businessStats['totalProducts'] ?? 0) ?></div>
                <div class="stat-label">Sản phẩm</div>
            </div>
            <div class="stat-trend">
                <i class="fas fa-arrow-up"></i> Active
            </div>
        </div>

        <div class="stat-card-modern card-gradient-purple" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($businessStats['totalVariants'] ?? 0) ?></div>
                <div class="stat-label">Biến thể</div>
            </div>
            <div class="stat-trend">
                <i class="fas fa-check-circle"></i> Active
            </div>
        </div>

        <div class="stat-card-modern card-gradient-green" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($businessStats['totalCategories'] ?? 0) ?></div>
                <div class="stat-label">Danh mục</div>
            </div>
        </div>

        <div class="stat-card-modern card-gradient-orange" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-icon">
                <i class="fas fa-truck"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($businessStats['totalSuppliers'] ?? 0) ?></div>
                <div class="stat-label">Nhà cung cấp</div>
            </div>
        </div>
    </div>
</div>

<!-- KPI Cards Row 2: Inventory Overview -->
<div class="dashboard-section">
    <h5 class="section-title">
        <i class="fas fa-warehouse"></i> Tình trạng kho hàng
    </h5>
    <div class="stats-grid-4">
        <div class="stat-card-modern card-gradient-cyan" data-aos="fade-up" data-aos-delay="0">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($inventoryStats['totalValue'] ?? 0, 0, ',', '.') ?>đ</div>
                <div class="stat-label">Giá trị tồn kho</div>
            </div>
        </div>

        <div class="stat-card-modern card-gradient-success" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-icon">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($inventoryStats['totalQuantity'] ?? 0) ?></div>
                <div class="stat-label">Tổng số lượng</div>
            </div>
        </div>

        <div class="stat-card-modern card-gradient-warning <?= ($inventoryStats['lowStock'] ?? 0) > 0 ? 'pulse-warning' : '' ?>" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($inventoryStats['lowStock'] ?? 0) ?></div>
                <div class="stat-label">Sắp hết hàng</div>
            </div>
            <?php if (($inventoryStats['lowStock'] ?? 0) > 0): ?>
                <a href="/admin/inventory/low-stock" class="stat-action">
                    <i class="fas fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>

        <div class="stat-card-modern card-gradient-danger <?= ($inventoryStats['outOfStock'] ?? 0) > 0 ? 'pulse-danger' : '' ?>" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-value"><?= number_format($inventoryStats['outOfStock'] ?? 0) ?></div>
                <div class="stat-label">Hết hàng</div>
            </div>
            <?php if (($inventoryStats['outOfStock'] ?? 0) > 0): ?>
                <a href="/admin/inventory/low-stock" class="stat-action">
                    <i class="fas fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Charts & Tables Row -->
<div class="row">
    <!-- Revenue Chart -->
    <div class="col-lg-8 mb-4">
        <div class="dashboard-card" data-aos="fade-up">
            <div class="card-header-modern">
                <h5 class="card-title-modern">
                    <i class="fas fa-chart-line"></i> Doanh thu 7 ngày gần đây
                </h5>
                <a href="/admin/reports/sales" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-external-link-alt"></i> Xem chi tiết
                </a>
            </div>
            <div class="card-body-modern">
                <?php if (!empty($revenueChart['labels'])): ?>
                    <canvas id="revenueChart" height="80"></canvas>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-chart-line"></i>
                        <p>Chưa có dữ liệu doanh thu</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- System Stats -->
    <div class="col-lg-4 mb-4">
        <div class="dashboard-card" data-aos="fade-up" data-aos-delay="100">
            <div class="card-header-modern">
                <h5 class="card-title-modern">
                    <i class="fas fa-cog"></i> Hệ thống
                </h5>
            </div>
            <div class="card-body-modern">
                <div class="system-stat-item">
                    <div class="system-stat-icon bg-primary">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="system-stat-info">
                        <div class="system-stat-value"><?= number_format($stats['totalUsers'] ?? 0) ?></div>
                        <div class="system-stat-label">Người dùng</div>
                    </div>
                </div>

                <div class="system-stat-item">
                    <div class="system-stat-icon bg-success">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="system-stat-info">
                        <div class="system-stat-value"><?= number_format($stats['activeUsers'] ?? 0) ?></div>
                        <div class="system-stat-label">Đang hoạt động</div>
                    </div>
                </div>

                <div class="system-stat-item">
                    <div class="system-stat-icon bg-info">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <div class="system-stat-info">
                        <div class="system-stat-value"><?= number_format($stats['totalRoles'] ?? 0) ?></div>
                        <div class="system-stat-label">Vai trò</div>
                    </div>
                </div>

                <div class="system-stat-item">
                    <div class="system-stat-icon bg-warning">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="system-stat-info">
                        <div class="system-stat-value"><?= number_format($stats['totalLogs'] ?? 0) ?></div>
                        <div class="system-stat-label">Log hoạt động</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Tables Row -->
<div class="row">
    <!-- Top Selling Products -->
    <div class="col-lg-6 mb-4">
        <div class="dashboard-card" data-aos="fade-up">
            <div class="card-header-modern">
                <h5 class="card-title-modern">
                    <i class="fas fa-fire"></i> Top sản phẩm bán chạy
                </h5>
                <span class="badge bg-success">7 ngày qua</span>
            </div>
            <div class="card-body-modern p-0">
                <?php if (!empty($topSellingProducts)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover modern-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sản phẩm</th>
                                    <th class="text-end">Đã bán</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topSellingProducts as $index => $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($index === 0): ?>
                                                <span class="rank-badge gold"><i class="fas fa-trophy"></i></span>
                                            <?php elseif ($index === 1): ?>
                                                <span class="rank-badge silver"><i class="fas fa-medal"></i></span>
                                            <?php elseif ($index === 2): ?>
                                                <span class="rank-badge bronze"><i class="fas fa-award"></i></span>
                                            <?php else: ?>
                                                <span class="rank-number"><?= $index + 1 ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="product-name-cell">
                                                <?= \Core\View::e($product['product_name']) ?>
                                                <small class="text-muted d-block"><?= \Core\View::e($product['sku']) ?></small>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="badge bg-primary"><?= number_format($product['total_sold']) ?></span>
                                        </td>
                                        <td class="text-end">
                                            <strong><?= number_format($product['total_revenue'], 0, ',', '.') ?>đ</strong>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-bag"></i>
                        <p>Chưa có dữ liệu bán hàng</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Low Stock Alert -->
    <div class="col-lg-6 mb-4">
        <div class="dashboard-card" data-aos="fade-up" data-aos-delay="100">
            <div class="card-header-modern">
                <h5 class="card-title-modern">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Sản phẩm sắp hết
                </h5>
                <a href="/admin/inventory/low-stock" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-eye"></i> Xem tất cả
                </a>
            </div>
            <div class="card-body-modern p-0">
                <?php if (!empty($lowStockProducts)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover modern-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Tồn kho</th>
                                    <th class="text-center">Ngưỡng</th>
                                    <th class="text-center">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($lowStockProducts as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="product-name-cell">
                                                <?= \Core\View::e($product['product_name']) ?>
                                                <small class="text-muted d-block"><?= \Core\View::e($product['sku']) ?></small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger"><?= number_format($product['current_stock'] ?? 0) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted">10</span>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            $currentStock = $product['current_stock'] ?? 0;
                                            $threshold = 10; // Ngưỡng mặc định
                                            $percent = $threshold > 0 ? ($currentStock / $threshold) * 100 : 0;
                                            ?>
                                            <?php if ($percent < 30): ?>
                                                <span class="status-badge status-critical">
                                                    <i class="fas fa-exclamation-circle"></i> Rất thấp
                                                </span>
                                            <?php elseif ($percent < 70): ?>
                                                <span class="status-badge status-warning">
                                                    <i class="fas fa-exclamation-triangle"></i> Thấp
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge status-ok">
                                                    <i class="fas fa-check-circle"></i> OK
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle text-success"></i>
                        <p class="text-success">Tất cả sản phẩm đều đủ hàng!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-12">
        <div class="dashboard-card" data-aos="fade-up">
            <div class="card-header-modern">
                <h5 class="card-title-modern">
                    <i class="fas fa-history"></i> Hoạt động gần đây
                </h5>
                <a href="/admin/logs" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list"></i> Xem tất cả
                </a>
            </div>
            <div class="card-body-modern">
                <?php if (!empty($recentLogs)): ?>
                    <div class="activity-timeline">
                        <?php foreach (array_slice($recentLogs, 0, 8) as $log): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <strong><?= \Core\View::e($log['username'] ?? 'Unknown') ?></strong>
                                        <span class="activity-action"><?= \Core\View::e($log['action']) ?></span>
                                    </div>
                                    <div class="activity-time">
                                        <i class="fas fa-clock"></i>
                                        <?= \Helpers\FormatHelper::datetime($log['created_at']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>Chưa có hoạt động nào</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<?php if (!empty($revenueChart['labels'])): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('revenueChart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: <?= json_encode($revenueChart['labels']) ?>,
                        datasets: [{
                            label: 'Doanh thu (VNĐ)',
                            data: <?= json_encode($revenueChart['revenues']) ?>,
                            borderColor: '#667eea',
                            backgroundColor: 'rgba(102, 126, 234, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 5,
                            pointHoverRadius: 7,
                            pointBackgroundColor: '#667eea',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                borderColor: '#667eea',
                                borderWidth: 1,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + 'đ';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000000) {
                                            return (value / 1000000).toFixed(1) + 'M';
                                        } else if (value >= 1000) {
                                            return (value / 1000).toFixed(0) + 'K';
                                        }
                                        return value;
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
<?php endif; ?>

<!-- AOS Animation -->
<link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 800,
        easing: 'ease-out-cubic',
        once: true
    });
</script>

<style>