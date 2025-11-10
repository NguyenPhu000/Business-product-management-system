<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= $title ?? 'Admin' ?> - Business Product Management System</title>
    <link rel="stylesheet" href="/assets/css/admin-style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="admin-wrapper">
        <?php \Core\View::include('admin/layout/sidebar'); ?>

        <div class="admin-content">
            <?php \Core\View::include('admin/layout/header', ['title' => $title ?? 'Dashboard']); ?>

            <div class="content-area">
                <?php if ($error = \Helpers\AuthHelper::getFlash('error')): ?>
                    <div class="alert alert-error">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($success = \Helpers\AuthHelper::getFlash('success')): ?>
                    <div class="alert alert-success">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <?= $content ?? '' ?>
            </div>
        </div>
    </div>

    <script src="/assets/js/app.js"></script>
    <script>
        // Kiểm tra session và ngăn back button
        (function() {
            // Kiểm tra xem user có thực sự đăng nhập không (check từ server)
            const isLoggedIn = <?= \Helpers\AuthHelper::check() ? 'true' : 'false' ?>;

            if (!isLoggedIn) {
                // Nếu không đăng nhập, redirect ngay về login
                window.location.replace('/admin/login');
            } else {
                // Đánh dấu đã đăng nhập
                sessionStorage.setItem('authenticated', Date.now().toString());
            }

            // Ngăn back button bằng cách thay thế history
            window.history.pushState(null, '', window.location.href);
            window.onpopstate = function() {
                if (!isLoggedIn || !sessionStorage.getItem('authenticated')) {
                    window.location.replace('/admin/login');
                } else {
                    window.history.pushState(null, '', window.location.href);
                }
            };
        })();
    </script>
</body>

</html>