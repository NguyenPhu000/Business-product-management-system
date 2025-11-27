<?php
/**
 * Báo cáo & Thống kê - Dashboard
 */
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-3">
                <i class="fas fa-chart-line"></i> Báo Cáo & Thống Kê
            </h1>
        </div>
    </div>

    <!-- Report Selection Dropdown -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="fas fa-folder-open"></i> Chọn Báo Cáo
                    </h5>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-warehouse"></i> Báo Cáo Tồn Kho
                            </h6>
                            <div class="d-grid gap-2">
                                <!-- Danh sách tồn kho được loại bỏ từ Reports module -->
                                <a href="/admin/reports/inventory-over-time" class="btn btn-outline-primary btn-sm text-start">
                                    <i class="fas fa-history"></i> Tồn theo thời gian (Nhập - Xuất)
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-chart-bar"></i> Báo Cáo Doanh Thu & Lợi Nhuận
                            </h6>
                            <div class="d-grid gap-2">
                                <a href="/admin/reports/sales" class="btn btn-outline-success btn-sm text-start">
                                    <i class="fas fa-dollar-sign"></i> Doanh thu theo sản phẩm/danh mục
                                </a>
                                <a href="/admin/reports/profit" class="btn btn-outline-success btn-sm text-start">
                                    <i class="fas fa-money-bill-wave"></i> Lợi nhuận gộp theo sản phẩm
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-star"></i> Báo Cáo Top Sản Phẩm
                            </h6>
                            <div class="d-grid gap-2">
                                <a href="/admin/reports/top-selling" class="btn btn-outline-info btn-sm text-start">
                                    <i class="fas fa-fire"></i> Sản phẩm bán chạy nhất
                                </a>
                                <a href="/admin/reports/slow-moving" class="btn btn-outline-info btn-sm text-start">
                                    <i class="fas fa-turtle"></i> Sản phẩm tồn kho lâu, ít bán
                                </a>
                                <a href="/admin/reports/dead-stock" class="btn btn-outline-info btn-sm text-start">
                                    <i class="fas fa-skull-crossbones"></i> Dead Stock (chưa bao giờ bán)
                                </a>
                                <a href="/admin/reports/high-value" class="btn btn-outline-info btn-sm text-start">
                                    <i class="fas fa-gem"></i> Sản phẩm giá trị cao
                                </a>
                                <a href="/admin/reports/top-profit" class="btn btn-outline-info btn-sm text-start">
                                    <i class="fas fa-award"></i> Sản phẩm lợi nhuận cao
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-info-circle"></i> Hướng Dẫn
                            </h6>
                            <div class="alert alert-info" role="alert">
                                <small>
                                    <strong>Chọn loại báo cáo phù hợp:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Tồn Kho:</strong> Kiểm tra tình hình hàng trong kho</li>
                                        <li><strong>Doanh Thu:</strong> Theo dõi doanh thu bán hàng</li>
                                        <li><strong>Lợi Nhuận:</strong> Phân tích lợi nhuận chi tiết</li>
                                        <li><strong>Top Sản Phẩm:</strong> Nhận diện sản phẩm chiến lược</li>
                                    </ul>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-box-open text-primary fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-2">Tổng Báo Cáo</h6>
                            <p class="mb-0 text-dark fs-5">
                                <strong>7 loại báo cáo</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-chart-bar text-success fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-2">Dữ Liệu</h6>
                            <p class="mb-0 text-dark fs-5">
                                <strong>Realtime</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-filter text-warning fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-2">Lọc Dữ Liệu</h6>
                            <p class="mb-0 text-dark fs-5">
                                <strong>Theo ngày</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                <i class="fas fa-download text-info fs-4"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="card-title text-muted mb-2">Xuất Báo Cáo</h6>
                            <p class="mb-0 text-dark fs-5">
                                <strong>PDF, Excel</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Guide -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="fas fa-book"></i> Hướng Dẫn Sử Dụng
                    </h5>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">1. Báo Cáo Tồn Kho</h6>
                            <ul class="small">
                                <li>Xem danh sách sản phẩm theo trạng thái (còn hàng, sắp hết, hết)</li>
                                <li>Theo dõi lịch sử nhập xuất hàng theo thời gian</li>
                                <li>Phân tích tình hình kho hàng chi tiết</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">2. Báo Cáo Doanh Thu & Lợi Nhuận</h6>
                            <ul class="small">
                                <li>Thống kê doanh thu theo sản phẩm, danh mục, ngày</li>
                                <li>Tính lợi nhuận gộp (doanh thu - giá vốn)</li>
                                <li>Phân tích margin lợi nhuận theo sản phẩm</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mt-3">3. Báo Cáo Top Sản Phẩm</h6>
                            <ul class="small">
                                <li>Xác định sản phẩm bán chạy nhất</li>
                                <li>Phát hiện slow moving inventory (hàng tồn lâu)</li>
                                <li>Nhận diện dead stock cần thanh lý</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mt-3">4. Phân Tích Chiến Lược</h6>
                            <ul class="small">
                                <li>Sản phẩm giá trị cao: Cần theo dõi kỹ</li>
                                <li>Sản phẩm lợi nhuận cao: Khuyến khích bán</li>
                                <li>Sản phẩm ít bán: Cân nhắc dừng kinh doanh</li>
                            </ul>
                        </div>
                    </div>
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

.btn-outline-primary:hover,
.btn-outline-success:hover,
.btn-outline-info:hover {
    background-color: transparent;
    transform: translateX(5px);
    transition: all 0.3s ease;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>
