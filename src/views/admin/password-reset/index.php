<?php

use Helpers\AuthHelper;
use Helpers\FormatHelper;

$title = 'Quản lý yêu cầu đặt lại mật khẩu';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">
            <i class="fas fa-key"></i> Yêu cầu đặt lại mật khẩu
        </h1>
        <div class="badge badge-warning" style="font-size: 16px; padding: 10px 20px;">
            <i class="fas fa-clock"></i> <?= $pendingCount ?> yêu cầu đang chờ
        </div>
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
                                <?php if ($request['approved_by']): ?>
                                <small>ID: <?= $request['approved_by'] ?></small>
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
                                <span class="text-muted">Đã xử lý</span>
                                <?php endif; ?>

                                <!-- Nút xóa cho tất cả request (chỉ admin) -->
                                <button class="btn btn-sm btn-danger" onclick="deleteRequest(<?= $request['id'] ?>)"
                                    title="Xóa request">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function approveRequest(id) {
    if (!confirm(
            'Bạn có chắc muốn phê duyệt yêu cầu này?\n\nSau khi phê duyệt, người dùng có thể vào trang "Quên mật khẩu" để tự đổi mật khẩu mới.'
        )) {
        return;
    }

    fetch(`/admin/password-reset/approve/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);

                // Cập nhật trạng thái trong bảng
                const row = document.getElementById(`request-${id}`);
                if (row) {
                    row.querySelector('td:nth-child(5)').innerHTML =
                        '<span class="badge badge-success"><i class="fas fa-check"></i> Đã phê duyệt</span>';
                    row.querySelector('td:nth-child(7)').textContent = 'Vừa xong';
                    row.querySelector('td:nth-child(8)').innerHTML = '<span class="text-muted">Đã xử lý</span>';
                }

                // Cập nhật số lượng pending
                updatePendingCount();
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi phê duyệt!');
        });
}

function rejectRequest(id) {
    if (!confirm('Bạn có chắc muốn từ chối yêu cầu này?')) {
        return;
    }

    fetch(`/admin/password-reset/reject/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);

                // Cập nhật trạng thái trong bảng
                const row = document.getElementById(`request-${id}`);
                if (row) {
                    row.querySelector('td:nth-child(5)').innerHTML =
                        '<span class="badge badge-danger"><i class="fas fa-times"></i> Đã từ chối</span>';
                    row.querySelector('td:nth-child(7)').textContent = 'Vừa xong';
                    row.querySelector('td:nth-child(8)').innerHTML = '<span class="text-muted">Đã xử lý</span>';
                }

                // Cập nhật số lượng pending
                updatePendingCount();
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi từ chối!');
        });
}

// Xóa request
function deleteRequest(id) {
    if (!confirm('Bạn có chắc muốn XÓA VĨNH VIỄN request này?\n\nHành động này không thể hoàn tác!')) {
        return;
    }

    fetch('/admin/password-reset/delete/' + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);

                // Xóa hàng khỏi bảng
                const row = document.getElementById(`request-${id}`);
                if (row) {
                    row.remove();
                }

                // Reload trang nếu không còn request nào
                const tbody = document.querySelector('table tbody');
                if (tbody && tbody.children.length === 0) {
                    location.reload();
                }

                // Cập nhật số lượng pending
                updatePendingCount();
            } else {
                alert(data.message || 'Có lỗi xảy ra!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi xóa!');
        });
}

// Auto-refresh: Kiểm tra yêu cầu mới mỗi 10 giây
let lastPendingCount = <?= $pendingCount ?>;

function updatePendingCount() {
    fetch('/admin/password-reset/check-new')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const newCount = data.pendingCount;

                // Cập nhật badge số lượng
                const badge = document.querySelector('.badge-warning');
                if (badge) {
                    badge.innerHTML = `<i class="fas fa-clock"></i> ${newCount} yêu cầu đang chờ`;
                }

                // Nếu có yêu cầu mới, reload trang để hiển thị
                if (newCount > lastPendingCount) {
                    // Hiển thị thông báo
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-info alert-dismissible fade show';
                    alertDiv.style.cssText =
                        'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
                    alertDiv.innerHTML = `
                        <i class="fas fa-bell"></i> Có ${newCount - lastPendingCount} yêu cầu mới!
                        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                    `;
                    document.body.appendChild(alertDiv);

                    // Tự động ẩn sau 3 giây và reload
                    setTimeout(() => {
                        alertDiv.remove();
                        location.reload();
                    }, 3000);
                }

                lastPendingCount = newCount;
            }
        })
        .catch(error => {
            console.error('Error checking new requests:', error);
        });
}

// Kiểm tra mỗi 5 giây
setInterval(updatePendingCount, 5000);

// Kiểm tra ngay khi trang load
setTimeout(updatePendingCount, 1000);
</script>