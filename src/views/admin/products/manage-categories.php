<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Gán danh mục sản phẩm' ?></title>
    <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/admin-style.css">
    <link rel="stylesheet" href="/assets/css/product-category-style.css">
</head>
<body>
    <?php include __DIR__ . '/../layouts/header.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Quản lý danh mục sản phẩm</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="/admin/products" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>

                <?php if (isset($_SESSION['flash'])): ?>
                    <?php foreach ($_SESSION['flash'] as $type => $message): ?>
                        <div class="alert alert-<?= $type ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['flash']); ?>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Thông tin sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Tên sản phẩm:</strong> <?= htmlspecialchars($product['name'] ?? '') ?></p>
                                <p><strong>SKU:</strong> <?= htmlspecialchars($product['sku'] ?? '') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Giá:</strong> <?= number_format($product['price'] ?? 0) ?> VNĐ</p>
                                <p><strong>Tồn kho:</strong> <?= $product['stock_quantity'] ?? 0 ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" action="/admin/products/manage-categories/<?= $product['id'] ?>">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Chọn danh mục</h5>
                            <small class="text-muted">Bạn có thể chọn nhiều danh mục cho sản phẩm</small>
                        </div>
                        <div class="card-body">
                            <div class="category-tree">
                                <?php
                                function renderCategoryTree($categories, $assignedIds, $level = 0) {
                                    foreach ($categories as $category):
                                        $isChecked = in_array($category['id'], $assignedIds);
                                        $indent = str_repeat('—', $level);
                                ?>
                                    <div class="form-check category-item level-<?= $level ?>">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            name="category_ids[]" 
                                            value="<?= $category['id'] ?>"
                                            id="category_<?= $category['id'] ?>"
                                            <?= $isChecked ? 'checked' : '' ?>
                                        >
                                        <label class="form-check-label" for="category_<?= $category['id'] ?>">
                                            <?= $indent ?> <?= htmlspecialchars($category['name']) ?>
                                            <?php if (!$category['is_active']): ?>
                                                <span class="badge bg-secondary">Ẩn</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php
                                        if (!empty($category['children'])) {
                                            renderCategoryTree($category['children'], $assignedIds, $level + 1);
                                        }
                                    endforeach;
                                }
                                
                                renderCategoryTree($categoryTree, $assignedCategoryIds);
                                ?>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                            <a href="/admin/products" class="btn btn-secondary">Hủy</a>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script src="/assets/js/jquery.min.js"></script>
    <script src="/assets/js/bootstrap.min.js"></script>
    <script>
        // Toggle select all/none
        document.addEventListener('DOMContentLoaded', function() {
            // Thêm nút select all/none
            const cardBody = document.querySelector('.category-tree');
            const btnGroup = document.createElement('div');
            btnGroup.className = 'mb-3';
            btnGroup.innerHTML = `
                <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">Chọn tất cả</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="selectNone">Bỏ chọn tất cả</button>
            `;
            cardBody.insertBefore(btnGroup, cardBody.firstChild);

            // Xử lý sự kiện
            document.getElementById('selectAll').addEventListener('click', function() {
                document.querySelectorAll('.category-tree input[type="checkbox"]').forEach(cb => cb.checked = true);
            });

            document.getElementById('selectNone').addEventListener('click', function() {
                document.querySelectorAll('.category-tree input[type="checkbox"]').forEach(cb => cb.checked = false);
            });
        });
    </script>
</body>
</html>
