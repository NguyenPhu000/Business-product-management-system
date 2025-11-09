<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trạng thái yêu cầu - Business Product Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/modern-login.css">
    <link rel="stylesheet" href="/assets/css/check-request-status.css">
</head>

<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">B</div>
                <h1 class="login-title">Trạng thái yêu cầu đặt lại mật khẩu</h1>
            </div>

            <?php if ($status === 'pending'): ?>
                <div class="status-pending">
                    <div class="status-icon">⏳</div>
                    <h2 style="color: #856404; margin: 10px 0;">Đang chờ phê duyệt</h2>
                    <p style="color: #856404; margin-bottom: 15px;">
                        Yêu cầu của bạn đã được gửi thành công và đang chờ admin xét duyệt.
                    </p>
                    <hr style="margin: 20px 0; border-color: #ffc107;">
                    <strong style="color: #856404;">Bước tiếp theo:</strong>
                    <ol class="steps" style="color: #856404;">
                        <li>Chờ admin phê duyệt yêu cầu (thường trong vài phút)</li>
                        <li>Sau khi được phê duyệt, quay lại trang <a href="/forgot-password" style="color: #007bff;">Quên
                                mật khẩu</a></li>
                        <li>Nhập lại email của bạn</li>
                        <li>Đặt mật khẩu mới</li>
                    </ol>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button onclick="location.reload()" class="btn-login" style="flex: 1; background: #17a2b8;">
                            🔄 Kiểm tra lại trạng thái
                        </button>
                        <button onclick="cancelRequest(<?= $userId ?>)" class="btn-login"
                            style="flex: 1; background: #dc3545;">
                            ❌ Hủy yêu cầu
                        </button>
                    </div>
                </div>

            <?php elseif ($status === 'approved'): ?>
                <div class="status-approved">
                    <div class="status-icon">✅</div>
                    <h2 style="color: #155724; margin: 10px 0;">Đã được phê duyệt!</h2>
                    <p style="color: #155724; margin-bottom: 15px;">
                        Yêu cầu của bạn đã được admin phê duyệt. Bạn có thể đặt mật khẩu mới ngay bây giờ.
                    </p>
                    <a href="/forgot-password" class="btn-login" style="display: inline-block; text-decoration: none;">
                        Đặt mật khẩu mới →
                    </a>
                </div>

            <?php elseif ($status === 'rejected'): ?>
                <div class="status-rejected">
                    <div class="status-icon">❌</div>
                    <h2 style="color: #721c24; margin: 10px 0;">Đã bị từ chối</h2>
                    <p style="color: #721c24; margin-bottom: 15px;">
                        Yêu cầu đặt lại mật khẩu của bạn đã bị admin từ chối.
                    </p>
                    <hr style="margin: 20px 0; border-color: #dc3545;">
                    <p style="color: #721c24;">
                        Vui lòng liên hệ với quản trị viên để biết thêm thông tin chi tiết.<br>
                        Bạn có thể gửi lại yêu cầu mới nếu cần.
                    </p>
                    <a href="/forgot-password" class="btn-login"
                        style="display: inline-block; text-decoration: none; margin-top: 15px;">
                        Gửi yêu cầu mới
                    </a>
                </div>

            <?php else: ?>
                <div class="alert alert-info">
                    <p>Không tìm thấy yêu cầu đặt lại mật khẩu.</p>
                    <a href="/forgot-password" class="btn-login"
                        style="display: inline-block; text-decoration: none; margin-top: 15px;">
                        Gửi yêu cầu mới
                    </a>
                </div>
            <?php endif; ?>

            <div class="login-footer" style="margin-top: 20px;">
                <a href="/admin/login">← Quay lại đăng nhập</a>
            </div>
        </div>
    </div>

    <script>
        // Debug: Kiểm tra userId
        console.log('Status:', '<?= $status ?>');
        console.log('UserId:', <?= isset($userId) ? $userId : 'null' ?>);
        console.log('Email:', '<?= $email ?? '' ?>');

        // Xóa session storage
        sessionStorage.clear();
        localStorage.clear();

        // Hàm hủy yêu cầu
        function cancelRequest(userId) {
            console.log('cancelRequest called with userId:', userId);

            if (!userId) {
                alert('❌ Lỗi: Không tìm thấy userId');
                return;
            }

            if (!confirm('Bạn có chắc chắn muốn hủy yêu cầu đặt lại mật khẩu?')) {
                return;
            }

            console.log('Sending cancel request...');

            fetch('/forgot-password/cancel-request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        user_id: userId
                    })
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success) {
                        alert('✅ Đã hủy yêu cầu thành công!');
                        window.location.href = '/admin/login';
                    } else {
                        alert('❌ Lỗi: ' + (data.message || 'Không thể hủy yêu cầu'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Có lỗi xảy ra khi hủy yêu cầu');
                });
        }

        // Auto refresh nếu đang pending (mỗi 15 giây)
        <?php if ($status === 'pending'): ?>
            setTimeout(() => {
                location.reload();
            }, 15000);
        <?php endif; ?>
    </script>
</body>

</html>