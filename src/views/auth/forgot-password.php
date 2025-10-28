<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu - Business Product Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/modern-login.css">
    <link rel="stylesheet" href="/assets/css/forgot-password.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-key"></i>
                </div>
                <h1 class="login-title">Quên mật khẩu</h1>
                <p class="login-subtitle">Nhập email để lấy lại mật khẩu mới</p>
            </div>

            <?php if ($error = \Helpers\AuthHelper::getFlash('error')): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <?php if ($success = \Helpers\AuthHelper::getFlash('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <?php
            $waitingEmail = $_SESSION['waiting_approval_email'] ?? null;
            $waitingUserId = $_SESSION['waiting_approval_user_id'] ?? null;
            ?>

            <?php if ($waitingEmail && $waitingUserId): ?>
                <!-- Hiển thị trạng thái chờ phê duyệt -->
                <div class="waiting-container" id="waitingSection">
                    <div class="spinner"></div>
                    <h3><i class="fas fa-clock"></i> Đang chờ admin phê duyệt...</h3>
                    <div class="email-info">
                        <i class="fas fa-envelope"></i> Email: <?= htmlspecialchars($waitingEmail) ?>
                    </div>
                    <button onclick="cancelWaiting()" class="cancel-btn">
                        <i class="fas fa-times"></i> Hủy và gửi yêu cầu khác
                    </button>
                </div>
            <?php else: ?>
                <!-- Form gửi yêu cầu -->
                <form action="/forgot-password" method="POST" class="login-form" id="forgotForm">
                    <div class="form-group">
                        <label for="email">Email</label>
                    </div>
                    <div class="inputForm">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="input" placeholder="Nhập địa chỉ email của bạn"
                            required autofocus>
                    </div>

                    <button type="submit" class="button-submit">
                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu
                    </button>
                </form>
            <?php endif; ?>

            <div class="back-link">
                <a href="/admin/login">
                    <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>

    <script src="/assets/js/modern-login.js"></script>
    <script>
        const waitingUserId = <?= $waitingUserId ?? 'null' ?>;

        function cancelWaiting() {
            if (confirm('Bạn có chắc muốn hủy yêu cầu đang chờ?')) {
                fetch('/admin/logout').then(() => {
                    location.reload();
                });
            }
        }

        function clearSession() {
            // Xóa session và reload
            fetch('/admin/logout').then(() => {
                window.location.href = '/forgot-password';
            });
        }

        // Polling kiểm tra trạng thái - 2 giây/lần
        if (waitingUserId) {
            let pollInterval = setInterval(() => {
                fetch('/forgot-password/check-approval/' + waitingUserId)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'approved') {
                            clearInterval(pollInterval);
                            window.location.href =
                                '/forgot-password?email=<?= urlencode($waitingEmail ?? '') ?>&approved=1';
                        } else if (data.status === 'rejected') {
                            clearInterval(pollInterval);
                            document.getElementById('waitingSection').innerHTML = `
                                <div class="rejected-box">
                                    <h3>❌ Yêu cầu bị từ chối</h3>
                                    <p>Admin đã từ chối yêu cầu của bạn.</p>
                                    <a href="/forgot-password" class="retry-btn" onclick="clearSession()">
                                        <i class="fas fa-redo"></i> Gửi yêu cầu mới
                                    </a>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            }, 2000); // Kiểm tra mỗi 2 giây
        }
    </script>
</body>

</html>