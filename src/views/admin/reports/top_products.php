<?php
/**
 * Báo Cáo Top Sản Phẩm
 * - Sản phẩm bán chạy nhất (theo doanh thu)
 * - Sản phẩm tồn kho lâu, ít bán
 */
$startDate = $data['start_date'] ?? null;
$endDate = $data['end_date'] ?? null;
$topSellingProducts = $data['top_selling_products'] ?? [];
$slowMovingProducts = $data['slow_moving_products'] ?? [];
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-chart-line"></i> Top Sản Phẩm
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/dashboard" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay Lại
            </a>
        </div>
    </div>

    <!-- FILTER SECTION -->
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
                            <a href="/admin/reports/top-products" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- TOP SELLING PRODUCTS -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-fire"></i> Sản Phẩm Bán Chạy Nhất</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($topSellingProducts)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">STT</th>
                                        <th style="width: 20%;">Sản Phẩm</th>
                                        <th style="width: 12%;">SKU</th>
                                        <th style="width: 10%; text-align: right;">Số Lượng Bán</th>
                                        <th style="width: 12%; text-align: right;">Doanh Thu</th>
                                        <th style="width: 12%; text-align: right;">Lợi Nhuận</th>
                                        <th style="width: 10%; text-align: right;">Tỷ Suất %</th>
                                        <th style="width: 9%; text-align: right;">Số Đơn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topSellingProducts as $index => $product): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?= $index + 1 ?></span></td>
                                            <td><strong><?= htmlspecialchars($product['product_name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($product['variant_sku'] ?? $product['product_sku'] ?? '-') ?></code></td>
                                            <td style="text-align: right;" class="fw-bold"><?= number_format($product['total_quantity_sold']) ?></td>
                                            <td style="text-align: right;">
                                                <span class="text-success"><strong><?= number_format($product['total_revenue']) ?> ₫</strong></span>
                                            </td>
                                            <td style="text-align: right;">
                                                <span class="text-info"><strong><?= number_format($product['gross_profit']) ?> ₫</strong></span>
                                            </td>
                                            <td style="text-align: right;">
                                                <span class="<?= $product['profit_margin_percent'] > 0 ? 'text-success' : 'text-danger' ?>">
                                                    <?= $product['profit_margin_percent'] ?>%
                                                </span>
                                            </td>
                                            <td style="text-align: right;"><?= $product['order_count'] ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info m-3">Không có dữ liệu sản phẩm bán chạy.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SLOW MOVING PRODUCTS -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-box"></i> Sản Phẩm Tồn Kho Lâu, Ít Bán</h6>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($slowMovingProducts)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">STT</th>
                                        <th style="width: 20%;">Sản Phẩm</th>
                                        <th style="width: 12%;">SKU</th>
                                        <th style="width: 12%; text-align: right;">Tồn Hiện Tại</th>
                                        <th style="width: 10%; text-align: right;">Số Lượng Bán</th>
                                        <th style="width: 12%; text-align: right;">Doanh Thu</th>
                                        <th style="width: 14%; text-align: center;">Lần Bán Cuối</th>
                                        <th style="width: 12%; text-align: right;">Ngày Cách (Ngày)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($slowMovingProducts as $index => $product): ?>
                                        <tr>
                                            <td><span class="badge bg-warning text-dark"><?= $index + 1 ?></span></td>
                                            <td><strong><?= htmlspecialchars($product['product_name']) ?></strong></td>
                                            <td><code><?= htmlspecialchars($product['variant_sku'] ?? $product['product_sku'] ?? '-') ?></code></td>
                                            <td style="text-align: right;" class="fw-bold text-danger">
                                                <?= number_format($product['current_stock']) ?>
                                            </td>
                                            <td style="text-align: right;"><?= number_format($product['total_quantity_sold']) ?></td>
                                            <td style="text-align: right;">
                                                <?= number_format($product['total_revenue']) ?> ₫
                                            </td>
                                            <td style="text-align: center;">
                                                <small class="text-muted">
                                                    <?= $product['last_sale_date'] ? date('d/m/Y', strtotime($product['last_sale_date'])) : 'Chưa bán' ?>
                                                </small>
                                            </td>
                                            <td style="text-align: right;">
                                                <span class="badge bg-danger">
                                                    <?= $product['days_since_last_sale'] ?? 'N/A' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info m-3">Không có sản phẩm tồn kho lâu, ít bán.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Fix scrollbar causing layout shift */
.table-responsive {
    scrollbar-gutter: stable;
}
</style>
