<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu - Business Product Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/modern-login.css">
    <link rel="stylesheet" href="/assets/css/reset-password.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="fas fa-lock"></i>
                </div>
                <h1 class="login-title">Đặt lại mật khẩu</h1>
                <p class="login-subtitle">Nhập mật khẩu mới của bạn</p>
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

            <?php if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']): ?>
            <div class="success-box">
                <h3><i class="fas fa-check-circle"></i> Yêu cầu đã được phê duyệt</h3>
                <p>Email: <strong><?= htmlspecialchars($email) ?></strong></p>
            </div>
            <?php endif; ?>

            <form action="/forgot-password" method="POST" class="login-form">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

                <div class="form-group">
                    <label for="new_password">Mật khẩu mới</label>
                </div>
                <div class="inputForm">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="new_password" name="new_password" class="input"
                        placeholder="Nhập mật khẩu mới (tối thiểu 6 ký tự)" required minlength="6" autofocus>
                    <span class="password-toggle" onclick="togglePassword('new_password')">
                        <i class="fas fa-eye" id="new_password-icon"></i>
                    </span>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Xác nhận mật khẩu</label>
                </div>
                <div class="inputForm">
                    <i class="fas fa-lock"></i>
                    <input type="password" id="confirm_password" name="confirm_password" class="input"
                        placeholder="Nhập lại mật khẩu mới" required minlength="6">
                    <span class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye" id="confirm_password-icon"></i>
                    </span>
                </div>

                <button type="submit" class="button-submit">
                    <i class="fas fa-check"></i> Đổi mật khẩu
                </button>
            </form>

            <div class="back-link">
                <a href="/admin/login">
                    <i class="fas fa-arrow-left"></i> Quay lại đăng nhập
                </a>
            </div>
        </div>
    </div>

    <script src="/assets/js/modern-login.js"></script>
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('new_password').value;
        const confirm = document.getElementById('confirm_password').value;

        if (password !== confirm) {
            e.preventDefault();
            alert('Mật khẩu xác nhận không khớp!');
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            alert('Mật khẩu phải có ít nhất 6 ký tự!');
            return false;
        }
    });
    </script>
</body>

</html>