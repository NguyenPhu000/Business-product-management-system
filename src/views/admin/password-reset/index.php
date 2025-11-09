<?php

use Helpers\AuthHelper;
use Helpers\FormatHelper;

$title = 'Quản lý yêu cầu đặt lại mật khẩu';
?>

<link rel="stylesheet" href="/assets/css/password-reset.css">

<!-- Container với data attributes cho JS -->
<div id="password-reset-container"
    data-pending-count="<?= $pendingCount ?>"
    data-request-ids="<?= implode(',', array_column($requests, 'id')) ?>">

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 d-flex align-items-center">
                <i class="fas fa-key"></i>
                <span class="title-text">Yêu cầu đặt lại mật khẩu</span>
                <!-- Badge moved next to title and styled via CSS -->
                <span class="pending-badge"><i class="fas fa-clock"></i> <?= $pendingCount ?> yêu cầu đang chờ</span>
            </h1>
            <!-- kept right area for future controls (keeps layout) -->
            <div class="header-controls"></div>
        </div>

        <?php if ($error = AuthHelper::getFlash('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($success = AuthHelper::getFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle"></i> <?= $success ?>
                <button type="button" class="close" data-dismiss="alert">&times;</button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($requests)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Chưa có yêu cầu nào</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Người dùng</th>
                                    <th>Email</th>
                                    <th>Thời gian yêu cầu</th>
                                    <th>Trạng thái</th>
                                    <th>Người phê duyệt</th>
                                    <th>Thời gian xử lý</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($requests as $request): ?>
                                    <tr id="request-<?= $request['id'] ?>">
                                        <td><?= $request['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($request['username']) ?></strong>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($request['email']) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($request['email']) ?></td>
                                        <td><?= FormatHelper::datetime($request['requested_at']) ?></td>
                                        <td>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock"></i> Đang chờ
                                                </span>
                                            <?php elseif ($request['status'] === 'approved'): ?>
                                                <?php if ($request['new_password'] === 'changed'): ?>
                                                    <span class="badge badge-secondary">
                                                        <i class="fas fa-check-double"></i> Đã hoàn tất
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge badge-success">
                                                        <i class="fas fa-check"></i> Đã phê duyệt
                                                    </span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times"></i> Đã từ chối
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($request['approver_username'])): ?>
                                                <strong><?= htmlspecialchars($request['approver_username']) ?></strong>
                                                <?php if (!empty($request['approver_full_name'])): ?>
                                                    <br><small
                                                        class="text-muted"><?= htmlspecialchars($request['approver_full_name']) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($request['approved_at']): ?>
                                                <?= FormatHelper::datetime($request['approved_at']) ?>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <button class="btn btn-sm btn-success" onclick="approveRequest(<?= $request['id'] ?>)"
                                                    title="Phê duyệt">
                                                    <i class="fas fa-check"></i> Phê duyệt
                                                </button>
                                                <button class="btn btn-sm btn-danger" onclick="rejectRequest(<?= $request['id'] ?>)"
                                                    title="Từ chối">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Đã xử lý</span>
                                                <button class="btn btn-sm btn-danger" onclick="deleteRequest(<?= $request['id'] ?>)"
                                                    title="Xóa">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($requests) && $pagination['totalPages'] > 1): ?>
            <!-- Phân trang hiện đại -->
            <div class="modern-pagination-wrapper">
                <div class="pagination-controls">
                    <!-- Nút First và Previous -->
                    <div class="pagination-nav-buttons">
                        <a href="/admin/password-reset?page=1"
                            class="pagination-btn <?= $pagination['page'] == 1 ? 'disabled' : '' ?>" title="Trang đầu">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="/admin/password-reset?page=<?= max(1, $pagination['page'] - 1) ?>"
                            class="pagination-btn <?= $pagination['page'] == 1 ? 'disabled' : '' ?>" title="Trang trước">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    </div>

                    <!-- Hiển thị số trang -->
                    <div class="pagination-numbers">
                        <?php
                        // Hiển thị trang đầu
                        if ($pagination['page'] > 3): ?>
                            <a href="/admin/password-reset?page=1" class="page-number">1</a>
                            <?php if ($pagination['page'] > 4): ?>
                                <span class="page-dots">...</span>
                            <?php endif;
                        endif;

                        // Hiển thị các trang gần current
                        $start = max(1, $pagination['page'] - 2);
                        $end = min($pagination['totalPages'], $pagination['page'] + 2);

                        for ($i = $start; $i <= $end; $i++): ?>
                            <a href="/admin/password-reset?page=<?= $i ?>"
                                class="page-number <?= $i == $pagination['page'] ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor;

                        // Hiển thị trang cuối
                        if ($pagination['page'] < $pagination['totalPages'] - 2): ?>
                            <?php if ($pagination['page'] < $pagination['totalPages'] - 3): ?>
                                <span class="page-dots">...</span>
                            <?php endif; ?>
                            <a href="/admin/password-reset?page=<?= $pagination['totalPages'] ?>" class="page-number">
                                <?= $pagination['totalPages'] ?>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Nút Next và Last -->
                    <div class="pagination-nav-buttons">
                        <a href="/admin/password-reset?page=<?= min($pagination['totalPages'], $pagination['page'] + 1) ?>"
                            class="pagination-btn <?= $pagination['page'] == $pagination['totalPages'] ? 'disabled' : '' ?>"
                            title="Trang sau">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="/admin/password-reset?page=<?= $pagination['totalPages'] ?>"
                            class="pagination-btn <?= $pagination['page'] == $pagination['totalPages'] ? 'disabled' : '' ?>"
                            title="Trang cuối">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    </div>

                    <!-- Ô nhập trang -->
                    <div class="pagination-jump">
                        <span class="jump-label">Đến trang:</span>
                        <input type="number" id="jumpToPage" class="jump-input" min="1" max="<?= $pagination['totalPages'] ?>"
                            value="<?= $pagination['page'] ?>" placeholder="<?= $pagination['page'] ?>">
                        <button onclick="jumpToPage()" class="jump-btn" title="Chuyển trang">
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>

                <!-- Thông tin phân trang -->
                <div class="pagination-info-modern">
                    <span class="info-text">
                        Trang <strong><?= $pagination['page'] ?></strong> /
                        <strong><?= $pagination['totalPages'] ?></strong>
                        • Tổng <strong><?= $pagination['totalRecords'] ?></strong> yêu cầu
                    </span>
                </div>
            </div>

            <script>
                function jumpToPage() {
                    const input = document.getElementById('jumpToPage');
                    if (!input) return;

                    const page = input.value;
                    const maxPage = parseInt(input.max);

                    if (page && page >= 1 && page <= maxPage) {
                        window.location.href = '/admin/password-reset?page=' + page;
                    } else {
                        alert('Vui lòng nhập số trang hợp lệ (1 - ' + maxPage + ')');
                    }
                }

                // Enter để jump
                document.getElementById('jumpToPage')?.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        jumpToPage();
                    }
                });
            </script>
        <?php endif; ?>
    </div>
</div><!-- End password-reset-container -->

<!-- Load external JS file -->
<script src="/assets/js/admin-password-reset.js"></script>