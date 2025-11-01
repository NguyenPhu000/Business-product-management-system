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
                    alert(data.message + '\n\nTrang sẽ tự động tải lại sau 10 giây để cập nhật trạng thái.');

                    // Cập nhật trạng thái trong bảng
                    const row = document.getElementById(`request-${id}`);
                    if (row) {
                        row.querySelector('td:nth-child(5)').innerHTML =
                            '<span class="badge badge-success"><i class="fas fa-check"></i> Đã phê duyệt</span>';
                        row.querySelector('td:nth-child(7)').textContent = 'Vừa xong';
                        row.querySelector('td:nth-child(8)').innerHTML = '<span class="text-muted">Đã xử lý</span>';
                    }

                    // Reload trang sau 10 giây để cập nhật trạng thái "Đã hoàn tất"
                    setTimeout(() => {
                        location.reload();
                    }, 10000);

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
                    alert(data.message + '\n\nYêu cầu sẽ tự động bị xóa sau 10 giây.');

                    // Cập nhật trạng thái trong bảng
                    const row = document.getElementById(`request-${id}`);
                    if (row) {
                        row.querySelector('td:nth-child(5)').innerHTML =
                            '<span class="badge badge-danger"><i class="fas fa-times"></i> Đã từ chối - Sẽ xóa sau 10s</span>';
                        row.querySelector('td:nth-child(7)').textContent = 'Vừa xong';
                        row.querySelector('td:nth-child(8)').innerHTML =
                            '<span class="text-muted">Đang xử lý...</span>';
                    }

                    // Tự động xóa request và reload sau 10 giây
                    setTimeout(() => {
                        fetch('/admin/password-reset/delete/' + id, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(deleteData => {
                                console.log('Đã xóa request:', deleteData);
                                location.reload();
                            })
                            .catch(error => {
                                console.error('Lỗi khi xóa:', error);
                                location.reload();
                            });
                    }, 10000);

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
    let currentRequestIds = [<?= implode(',', array_column($requests, 'id')) ?>];

    function checkCancelledRequests() {
        fetch('/admin/password-reset/check-cancelled')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.cancelledIds && data.cancelledIds.length > 0) {
                    console.log('Phát hiện request bị hủy:', data.cancelledIds);

                    // Cập nhật UI cho các row đã tồn tại
                    data.cancelledIds.forEach(cancelledId => {
                        const row = document.getElementById(`request-${cancelledId}`);
                        if (row) {
                            // Cập nhật trạng thái
                            row.querySelector('td:nth-child(5)').innerHTML =
                                '<span class="badge badge-warning"><i class="fas fa-ban"></i> Đã hủy - Sẽ xóa sau 10s</span>';
                            row.querySelector('td:nth-child(8)').innerHTML =
                                '<span class="text-muted">Người dùng đã hủy yêu cầu</span>';

                            // Highlight row
                            row.style.backgroundColor = '#fff3cd';
                        }
                    });

                    // Hiển thị thông báo
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-warning alert-dismissible fade show';
                    alertDiv.style.cssText =
                        'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 350px;';
                    alertDiv.innerHTML = `
                        <i class="fas fa-exclamation-triangle"></i> ${data.cancelledIds.length} yêu cầu đã bị hủy bởi người dùng!
                        <br><small>Sẽ tự động xóa và reload sau 10 giây...</small>
                        <button type="button" class="close" onclick="this.parentElement.remove()">&times;</button>
                    `;
                    document.body.appendChild(alertDiv);

                    // LUÔN reload sau 10 giây (dù có tìm thấy row hay không)
                    setTimeout(() => {
                        console.log('Đang xóa các request bị hủy và reload trang...');

                        // Xóa từng request
                        const deletePromises = data.cancelledIds.map(id => {
                            return fetch('/admin/password-reset/delete/' + id, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                }
                            });
                        });

                        // Dù xóa thành công hay lỗi, vẫn reload
                        Promise.all(deletePromises)
                            .finally(() => {
                                location.reload();
                            });
                    }, 10000);
                }
            })
            .catch(error => {
                console.error('Error checking cancelled requests:', error);
            });
    }

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
    setInterval(checkCancelledRequests, 5000);

    // Kiểm tra ngay khi trang load
    setTimeout(updatePendingCount, 1000);
    setTimeout(checkCancelledRequests, 1000);
</script>