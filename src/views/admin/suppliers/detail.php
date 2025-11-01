<?php
/**
 * View: Chi tiết nhà cung cấp
 */
$pageTitle = $pageTitle ?? 'Chi tiết nhà cung cấp';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-info-circle"></i> <?= $pageTitle ?></h2>
        <div>
            <a href="/admin/suppliers/edit/<?= $supplier['id'] ?>" class="btn btn-warning">
                <i class="fas fa-edit"></i> Sửa
            </a>
            <a href="/admin/suppliers" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin nhà cung cấp -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <div class="supplier-contact">
                        <div class="supplier-contact-item">
                            <i class="fas fa-building supplier-contact-icon"></i>
                            <div class="supplier-contact-content">
                                <div class="supplier-contact-label">Tên nhà cung cấp</div>
                                <div class="supplier-contact-value"><?= htmlspecialchars($supplier['name']) ?></div>
                            </div>
                        </div>

                        <div class="supplier-contact-item">
                            <i class="fas fa-user supplier-contact-icon"></i>
                            <div class="supplier-contact-content">
                                <div class="supplier-contact-label">Người liên hệ</div>
                                <div class="supplier-contact-value">
                                    <?= $supplier['contact'] ? htmlspecialchars($supplier['contact']) : '<span class="text-muted">Chưa có</span>' ?>
                                </div>
                            </div>
                        </div>

                        <div class="supplier-contact-item">
                            <i class="fas fa-phone supplier-contact-icon"></i>
                            <div class="supplier-contact-content">
                                <div class="supplier-contact-label">Số điện thoại</div>
                                <div class="supplier-contact-value">
                                    <?php if ($supplier['phone']): ?>
                                        <a href="tel:<?= $supplier['phone'] ?>"><?= htmlspecialchars($supplier['phone']) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="supplier-contact-item">
                            <i class="fas fa-envelope supplier-contact-icon"></i>
                            <div class="supplier-contact-content">
                                <div class="supplier-contact-label">Email</div>
                                <div class="supplier-contact-value">
                                    <?php if ($supplier['email']): ?>
                                        <a href="mailto:<?= $supplier['email'] ?>"><?= htmlspecialchars($supplier['email']) ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">Chưa có</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="supplier-contact-item">
                            <i class="fas fa-map-marker-alt supplier-contact-icon"></i>
                            <div class="supplier-contact-content">
                                <div class="supplier-contact-label">Địa chỉ</div>
                                <div class="supplier-contact-value">
                                    <?= $supplier['address'] ? htmlspecialchars($supplier['address']) : '<span class="text-muted">Chưa có</span>' ?>
                                </div>
                            </div>
                        </div>

                        <div class="supplier-contact-item">
                            <i class="fas fa-toggle-on supplier-contact-icon"></i>
                            <div class="supplier-contact-content">
                                <div class="supplier-contact-label">Trạng thái</div>
                                <div class="supplier-contact-value">
                                    <span class="badge <?= $supplier['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $supplier['is_active'] ? 'Hoạt động' : 'Ngừng hoạt động' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Thống kê -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    <div class="supplier-stats">
                        <div class="supplier-stat-card">
                            <div class="supplier-stat-value"><?= $supplier['order_count'] ?? 0 ?></div>
                            <div class="supplier-stat-label">Tổng đơn hàng</div>
                        </div>
                        <div class="supplier-stat-card">
                            <div class="supplier-stat-value">
                                <?= number_format($totalValue ?? 0, 0, ',', '.') ?>đ
                            </div>
                            <div class="supplier-stat-label">Tổng giá trị</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Lịch sử đơn hàng -->
    <?php if (!empty($orderHistory)): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Lịch sử đơn hàng gần đây</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover order-history-table">
                    <thead>
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Ngày tạo</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orderHistory as $order): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($order['po_number'] ?? '#' . $order['id']) ?></code></td>
                                <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                <td><?= number_format($order['total_amount'], 0, ',', '.') ?>đ</td>
                                <td>
                                    <span class="badge order-status-badge bg-<?= $order['status'] === 'completed' ? 'success' : ($order['status'] === 'cancelled' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<link rel="stylesheet" href="/assets/css/supplier-style.css">
