<?php
/**
 * Báo Cáo Sản Phẩm Bán Chạy Nhất
 */
$topN = $_GET['topN'] ?? 10;
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-fire"></i> Sản Phẩm Bán Chạy Nhất (Top <?= $topN ?>)
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
                                <option value="5" <?= $topN == 5 ? 'selected' : '' ?>>Top 5</option>
                                <option value="10" <?= $topN == 10 ? 'selected' : '' ?>>Top 10</option>
                                <option value="15" <?= $topN == 15 ? 'selected' : '' ?>>Top 15</option>
                                <option value="20" <?= $topN == 20 ? 'selected' : '' ?>>Top 20</option>
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
                            <a href="/admin/reports/top-selling" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-crown"></i> Top <?= $topN ?> Sản Phẩm Bán Chạy
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
                                    <th style="width: 120px;">Danh Mục</th>
                                    <th style="width: 100px;">Số Lượng Bán</th>
                                    <th style="width: 150px;">Doanh Thu</th>
                                    <th style="width: 100px;">Số Đơn</th>
                                    <th style="width: 100px;">% Doanh Thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $rank = 1;
                                $total_revenue = array_sum(array_column($data['products'], 'total_revenue'));
                                foreach ($data['products'] as $prod): 
                                ?>
                                <tr class="<?= $rank <= 3 ? 'table-light' : '' ?>">
                                    <td>
                                        <?php if ($rank == 1): ?>
                                            <span class="badge bg-warning">
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
                                        <?php if ($prod['brand_name'] ?? false): ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-tag"></i> <?= htmlspecialchars($prod['brand_name']) ?>
                                        </small>
                                        <?php endif; ?>
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
                                        <small><?= number_format($prod['order_count'] ?? 0) ?></small>
                                    </td>
                                    <td>
                                        <div style="width: 100px;">
                                            <small class="text-muted">
                                                <?= number_format(($prod['total_revenue'] ?? 0) / ($total_revenue ?: 1) * 100, 1) ?>%
                                            </small>
                                            <div class="progress" style="height: 15px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: <?= ($prod['total_revenue'] ?? 0) / ($total_revenue ?: 1) * 100 ?>%">
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
                        <i class="fas fa-info-circle"></i> Không có dữ liệu sản phẩm bán chạy.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendation Box -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-lightbulb"></i> Gợi Ý Chiến Lược
                    </h5>
                    <ul class="small">
                        <li><strong>Tập trung quảng cáo:</strong> Các sản phẩm top này đang bán tốt, cần duy trì đủ hàng</li>
                        <li><strong>Tối ưu giá:</strong> Cân nhắc các chiến lược tăng giá hoặc giảm chi phí vận hành</li>
                        <li><strong>Bundle products:</strong> Tạo combo với sản phẩm hàng đầu để tăng bán những sản phẩm khác</li>
                        <li><strong>Phân tích xu hướng:</strong> Theo dõi xem doanh số có giảm trong thời gian qua không</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
