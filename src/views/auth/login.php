<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Business Product Management System</title>
    <script src="https://kit.fontawesome.com/42a96a500e.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/assets/css/modern-login.css">
</head>

<body>
    <div class="login-container">
        <form action="/admin/login" method="POST" class="login-card" id="loginForm">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Please sign in to your account</p>
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

            <div class="form-group">
                <label for="email">Username or Email</label>
            </div>
            <div class="inputForm">
                <i class="fas fa-user"></i>
                <input type="text" id="email" name="email" class="input"
                    placeholder="Enter your username or email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
            </div>
            <div class="inputForm">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" class="input"
                    placeholder="Enter your password" required>
                <span class="password-toggle" onclick="togglePassword('password')">
                    <i class="fas fa-eye" id="password-icon"></i>
                </span>
            </div>

            <div class="flex-row">
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Remember me</label>
                </div>
                <a href="/forgot-password" class="forgot-password">Forgot password?</a>
            </div>

            <button type="submit" class="button-submit">Sign In</button>
        </form>
    </div>

    <script src="/assets/js/modern-login.js"></script>
    <script>
        // Xóa tất cả session data khi vào trang login
        sessionStorage.clear();

        // Kiểm tra nếu vừa logout, reload trang một lần
        if (sessionStorage.getItem('justLoggedOut') === 'true') {
            sessionStorage.removeItem('justLoggedOut');
            window.location.reload(true); // Force reload from server
        }

        // Đánh dấu nếu có tham số từ logout
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('from') === 'logout') {
            sessionStorage.setItem('justLoggedOut', 'true');
            // Xóa parameter khỏi URL
            window.history.replaceState({}, '', '/admin/login');
            window.location.reload(true);
        }

        // Ngăn back button quay lại trang admin
        window.history.pushState(null, '', window.location.href);
        window.onpopstate = function() {
            window.history.pushState(null, '', window.location.href);
        };
    </script>
</body>

</html>