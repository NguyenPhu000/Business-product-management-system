<?php
/**
 * Báo Cáo Dead Stock - Sản Phẩm Chưa Bao Giờ Bán
 */
$topN = $_GET['topN'] ?? 20;
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3">
                <i class="fas fa-skull-crossbones"></i> Dead Stock - Sản Phẩm Chưa Bao Giờ Bán
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="/admin/reports" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay Lại Dashboard
            </a>
        </div>
    </div>

    <!-- Alert -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h5 class="alert-heading">
                    <i class="fas fa-fire-alt"></i> Cảnh Báo Quan Trọng
                </h5>
                <small>
                    <strong>Dead Stock</strong> là những sản phẩm có tồn kho nhưng <strong>CHƯA BAO GIỜ XUẤT HIỆN TRONG BẤT KỲ ĐƠN BÁN HÀNG NÀO</strong>.
                    Đây là dấu hiệu của:
                    <ul class="mt-2 mb-0">
                        <li><strong>❌ Hàng không bán được:</strong> Có thể lỗi, hết hạn, hoặc không ai muốn</li>
                        <li><strong>💰 Lãng phí vốn:</strong> Tiền bị buộc vô nghĩa</li>
                        <li><strong>📦 Chiếm chỗ kho:</strong> Ảnh hưởng đến hiệu suất lưu kho</li>
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
                                <i class="fas fa-list-ol"></i> Hiển Thị
                            </label>
                            <select id="topN" name="topN" class="form-select">
                                <option value="10" <?= $topN == 10 ? 'selected' : '' ?>>Top 10</option>
                                <option value="20" <?= $topN == 20 ? 'selected' : '' ?>>Top 20</option>
                                <option value="50" <?= $topN == 50 ? 'selected' : '' ?>>Top 50</option>
                                <option value="100" <?= $topN == 100 ? 'selected' : '' ?>>Tất Cả</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Tìm Kiếm
                            </button>
                        </div>
                        <div class="col-md-2">
                            <a href="/admin/reports/dead-stock" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-redo"></i> Đặt Lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Dead Stock Products Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-circle"></i> Danh Sách Dead Stock
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if ($data['products'] ?? false): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-danger">
                                <tr>
                                    <th style="width: 50px;">STT</th>
                                    <th>Sản Phẩm</th>
                                    <th style="width: 100px;">Danh Mục</th>
                                    <th style="width: 80px;">Tồn Kho</th>
                                    <th style="width: 100px;">Giá Vốn/Cái</th>
                                    <th style="width: 120px;">Giá Trị Tồn</th>
                                    <th style="width: 120px;">Ngày Nhập</th>
                                    <th style="width: 90px;">Tồn Từ (Ngày)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 1; foreach ($data['products'] as $prod): ?>
                                <tr class="table-danger-light">
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
                                        <small>
                                            <?php if ($prod['first_import_date'] ?? false): ?>
                                                <?= date('d/m/Y', strtotime($prod['first_import_date'])) ?>
                                            <?php else: ?>
                                                <span class="text-muted">N/A</span>
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-danger">
                                            <?= number_format($prod['days_in_stock'] ?? 0) ?> ngày
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-success m-3" role="alert">
                        <i class="fas fa-check-circle"></i> Tốt! Không có sản phẩm dead stock.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Box -->
    <?php if ($data['products'] ?? false): ?>
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-boxes"></i> Tổng Sản Phẩm Dead Stock
                    </h6>
                    <h4 class="text-danger mb-0">
                        <?= count($data['products']) ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-cubes"></i> Tổng Số Lượng Tồn
                    </h6>
                    <h4 class="text-danger mb-0">
                        <?= number_format(array_sum(array_column($data['products'], 'current_quantity')) ?? 0) ?>
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-danger">
                <div class="card-body">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-money-bill"></i> Tổng Giá Trị Tồn
                    </h6>
                    <h4 class="text-danger mb-0">
                        <?php 
                        $total_value = 0;
                        foreach ($data['products'] as $prod) {
                            $total_value += ($prod['current_quantity'] ?? 0) * ($prod['unit_cost'] ?? 0);
                        }
                        echo number_format($total_value);
                        ?> ₫
                    </h4>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Action Plan Box -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-lightbulb"></i> Kế Hoạch Hành Động
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Khẩn Cấp (1-2 Tuần):</h6>
                            <ul class="small">
                                <li>🔍 <strong>Kiểm tra chất lượng:</strong> Xem hàng còn tốt không</li>
                                <li>🏷️ <strong>Giảm giá sâu:</strong> Clearance sale để xoá hàng</li>
                                <li>🎁 <strong>Tặng kèm:</strong> Tặng kèm với sản phẩm bán chạy</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Trung Hạn (1-3 Tháng):</h6>
                            <ul class="small">
                                <li>❌ <strong>Dừng kinh doanh:</strong> Nếu không bán được</li>
                                <li>💣 <strong>Thanh lý:</strong> Bán cho lô hàng thứp, bán sỉ</li>
                                <li>📦 <strong>Tái sử dụng:</strong> Nếu có thể, thay đổi cách bán</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table-danger-light {
    background-color: #f8d7da !important;
}
.table-danger-light:hover {
    background-color: #f5c6cb !important;
}
</style>
