<?php
/**
 * Báo Cáo Lợi Nhuận
 */
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$page = $_GET['page'] ?? 1;
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-money-bill-wave"></i> Báo Cáo Lợi Nhuận Gộp
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
                        <i class="fas fa-dollar-sign"></i> Tổng Doanh Thu
                    </h6>
                    <h4 class="text-primary mb-0">
                        <?= htmlspecialchars($data['summary']['formatted_total_revenue'] ?? '₫0') ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-cube"></i> Tổng Giá Vốn
                    </h6>
                    <h4 class="text-danger mb-0">
                        <?= htmlspecialchars($data['summary']['formatted_total_cost'] ?? '₫0') ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-money-bill-wave"></i> Lợi Nhuận Gộp
                    </h6>
                    <h4 class="text-success mb-0">
                        <?= htmlspecialchars($data['summary']['formatted_total_profit'] ?? '₫0') ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-percentage"></i> Margin Lợi Nhuận
                    </h6>
                    <h4 class="text-info mb-0">
                        <?= number_format($data['summary']['profit_margin'] ?? 0, 1) ?>%
                    </h4>
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
                            <a href="/admin/reports/profit" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Information Box -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-info-circle"></i> Cách Tính Lợi Nhuận Gộp
                </h5>
                <small>
                    <strong>Lợi Nhuận Gộp = Doanh Thu - Giá Vốn</strong>
                    <br>
                    • <strong>Doanh Thu:</strong> Tổng giá bán × Số lượng bán
                    <br>
                    • <strong>Giá Vốn:</strong> Giá vốn từng sản phẩm × Số lượng bán
                    <br>
                    • <strong>Margin:</strong> (Lợi Nhuận / Doanh Thu) × 100%
                    <br>
                    <em>Lợi nhuận gộp chưa trừ chi phí quản lý, vận chuyển, etc.</em>
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-table"></i> Chi Tiết Lợi Nhuận Theo Sản Phẩm
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($data['products'] ?? false): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Sản Phẩm</th>
                                    <th style="width: 100px;">Số Lượng</th>
                                    <th style="width: 120px;">Doanh Thu</th>
                                    <th style="width: 120px;">Giá Vốn</th>
                                    <th style="width: 120px;">Lợi Nhuận</th>
                                    <th style="width: 90px;">Margin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 1; foreach ($data['products'] as $prod): ?>
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
                                        <div style="width: 90px;">
                                            <small class="<?= ($prod['profit_margin'] ?? 0) >= 20 ? 'text-success' : 'text-warning' ?>">
                                                <strong><?= number_format($prod['profit_margin'] ?? 0, 1) ?>%</strong>
                                            </small>
                                            <div class="progress" style="height: 15px;">
                                                <?php 
                                                $margin_color = ($prod['profit_margin'] ?? 0) >= 30 ? 'success' 
                                                              : (($prod['profit_margin'] ?? 0) >= 20 ? 'info' 
                                                              : (($prod['profit_margin'] ?? 0) >= 10 ? 'warning' : 'danger'));
                                                ?>
                                                <div class="progress-bar bg-<?= $margin_color ?>" role="progressbar" 
                                                     style="width: <?= min($prod['profit_margin'] ?? 0, 100) ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($data['pagination'] ?? false): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($data['pagination']['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&page=1">
                                    <i class="fas fa-chevron-left"></i> Đầu
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&page=<?= $data['pagination']['current_page'] - 1 ?>">
                                    Trước
                                </a>
                            </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= ($data['pagination']['total_pages'] ?? 1); $i++): ?>
                                <?php if ($i == $data['pagination']['current_page']): ?>
                                <li class="page-item active">
                                    <span class="page-link"><?= $i ?></span>
                                </li>
                                <?php elseif ($i <= 3 || $i >= ($data['pagination']['total_pages'] ?? 1) - 2 || abs($i - $data['pagination']['current_page']) <= 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&page=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                                <?php elseif ($i == 4 || $i == ($data['pagination']['total_pages'] ?? 1) - 3): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($data['pagination']['current_page'] < ($data['pagination']['total_pages'] ?? 1)): ?>
                            <li class="page-item">
                                <a class="page-link" href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&page=<?= $data['pagination']['current_page'] + 1 ?>">
                                    Sau
                                </a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&page=<?= $data['pagination']['total_pages'] ?? 1 ?>">
                                    Cuối <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <div class="text-center text-muted small mt-2">
                        Trang <?= $data['pagination']['current_page'] ?? 1 ?> của 
                        <?= $data['pagination']['total_pages'] ?? 1 ?>
                        (Tổng: <?= number_format($data['pagination']['total'] ?? 0) ?> sản phẩm)
                    </div>
                    <?php endif; ?>
                    <?php else: ?>
                    <div class="alert alert-info m-3" role="alert">
                        <i class="fas fa-info-circle"></i> Không có dữ liệu lợi nhuận.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
