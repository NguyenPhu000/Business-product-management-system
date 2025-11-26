<?php
/**
 * Báo Cáo Sản Phẩm Tồn Kho Lâu, Ít Bán
 */
$topN = $_GET['topN'] ?? 20;
$daysThreshold = $_GET['daysThreshold'] ?? 30;
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-turtle"></i> Sản Phẩm Tồn Kho Lâu, Ít Bán (Top <?= $topN ?>)
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
                        <div class="col-md-3">
                            <label for="daysThreshold" class="form-label">
                                <i class="fas fa-calendar-days"></i> Không Bán Từ (Ngày)
                            </label>
                            <select id="daysThreshold" name="daysThreshold" class="form-select">
                                <option value="14" <?= $daysThreshold == 14 ? 'selected' : '' ?>>14 ngày trở lên</option>
                                <option value="30" <?= $daysThreshold == 30 ? 'selected' : '' ?>>30 ngày trở lên</option>
                                <option value="60" <?= $daysThreshold == 60 ? 'selected' : '' ?>>60 ngày trở lên</option>
                                <option value="90" <?= $daysThreshold == 90 ? 'selected' : '' ?>>90 ngày trở lên</option>
                                <option value="180" <?= $daysThreshold == 180 ? 'selected' : '' ?>>180 ngày trở lên</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Tìm Kiếm
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="/admin/reports/slow-moving" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Box -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Chú Ý Quan Trọng
                </h5>
                    <small>
                    Các sản phẩm dưới đây có tồn kho cao nhưng <strong>ít hoặc không bán trong thời gian dài</strong> (><?= htmlspecialchars($daysThreshold) ?> ngày).
                    Đây là những sản phẩm có nguy cơ cao về:
                    <ul class="mt-2 mb-0">
                        <li>Lãng phí tài chính (vốn bị buộc)</li>
                        <li>Chi phí lưu kho cao</li>
                        <li>Hàng có thể bị lỗi, hư hỏng, lỗi thời</li>
                    </ul>
                </small>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Slow Moving Products Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Danh Sách Sản Phẩm Tồn Kho Lâu, Ít Bán
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
                                    <th style="width: 100px;">Danh Mục</th>
                                    <th style="width: 80px;">Tồn Kho</th>
                                    <th style="width: 100px;">Giá Vốn/Cái</th>
                                    <th style="width: 120px;">Giá Trị Tồn</th>
                                    <th style="width: 120px;">Lần Cuối Bán</th>
                                    <th style="width: 90px;">Ngày Chưa Bán</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 1; foreach ($data['products'] as $prod): ?>
                                <tr class="table-warning">
                                    <td><small class="text-muted"><?= $stt++ ?></small></td>
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
                                        <span class="badge bg-danger">
                                            <?= number_format($prod['current_quantity'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= htmlspecialchars($prod['formatted_unit_cost'] ?? '₫0') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong class="text-danger">
                                            <?= htmlspecialchars($prod['formatted_stock_value'] ?? '₫0') ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php if ($prod['last_sale_date'] ?? false): ?>
                                            <small>
                                                <?= date('d/m/Y', strtotime($prod['last_sale_date'])) ?>
                                            </small>
                                        <?php else: ?>
                                            <small class="text-danger">
                                                <i class="fas fa-ban"></i> Chưa Bao Giờ
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?= number_format($prod['days_since_last_sale'] ?? 0) ?> ngày
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success m-3" role="alert">
                        <i class="fas fa-check-circle"></i> Tốt! Không có sản phẩm tồn kho lâu, ít bán theo tiêu chí này.
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
                        <i class="fas fa-lightbulb"></i> Hành Động Đề Xuất
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Ngắn Hạn:</h6>
                            <ul class="small">
                                <li>📢 <strong>Khuyến mãi, giảm giá</strong> để kích thích bán hàng</li>
                                <li>🎁 <strong>Bundle products</strong> - kết hợp với sản phẩm hot bán</li>
                                <li>📱 <strong>Quảng cáo trên mạng xã hội</strong> những sản phẩm này</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Dài Hạn:</h6>
                            <ul class="small">
                                <li>❌ <strong>Dừng kinh doanh</strong> - nếu không có tiềm năng</li>
                                <li>🔄 <strong>Thay thế hoặc cải tiến</strong> - phiên bản mới hơn</li>
                                <li>📊 <strong>Phân tích</strong> - tìm hiểu tại sao ít bán</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
