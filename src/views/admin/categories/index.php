<?php
/**
 * View: Danh sách danh mục sản phẩm
 */
$pageTitle = $pageTitle ?? 'Quản lý danh mục';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-folder-tree"></i> <?= $pageTitle ?></h2>
        <div class="d-flex gap-2">
            <form method="GET" action="/admin/categories" class="d-flex">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm danh mục..."
                    value="<?= htmlspecialchars($keyword ?? '') ?>">
                <button type="submit" class="btn btn-secondary ms-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="/admin/categories/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm danh mục
            </a>
        </div>
    </div>

    <!-- Flash messages -->
    <?php if (isset($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i>
        <?= $_SESSION['flash_success']; unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= $_SESSION['flash_error']; unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Category Tree View -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-sitemap me-2"></i>Cấu trúc danh mục</h5>
        </div>
        <div class="card-body">
            <?php if (empty($categoryTree)): ?>
            <p class="text-muted"><i class="fas fa-inbox me-2"></i>Chưa có danh mục nào</p>
            <?php else: ?>
            <div class="category-tree">
                <?php foreach ($categoryTree as $parent): ?>
                <div class="category-parent mb-3">
                    <div class="d-flex align-items-center category-item p-3 bg-light rounded">
                        <span class="badge bg-primary me-2">Cha</span>
                        <strong class="flex-grow-1">
                            <i class="fas fa-folder"></i> <?= htmlspecialchars($parent['name']) ?>
                        </strong>
                        <span class="badge <?= $parent['is_active'] ? 'bg-success' : 'bg-secondary' ?> me-2">
                            <?= $parent['is_active'] ? 'Hoạt động' : 'Ẩn' ?>
                        </span>
                        <span class="badge bg-info me-2">
                            Thứ tự: <?= $parent['sort_order'] ?>
                        </span>
                        <div class="btn-group">
                            <a href="/admin/categories/edit/<?= $parent['id'] ?>" class="btn btn-sm btn-warning"
                                title="Sửa">
                                <i class="fas fa-pen"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-danger" data-id="<?= $parent['id'] ?>"
                                data-name="<?= htmlspecialchars($parent['name']) ?>" onclick="deleteCategory(this)"
                                title="Xóa">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($parent['children'])): ?>
                    <div class="category-children ms-5 mt-2">
                        <?php foreach ($parent['children'] as $child): ?>
                        <div class="d-flex align-items-center category-item p-2 mb-2 bg-white border rounded">
                            <span class="badge bg-secondary me-2">Con</span>
                            <span class="flex-grow-1">
                                <i class="fas fa-folder-open"></i> <?= htmlspecialchars($child['name']) ?>
                            </span>
                            <span class="badge <?= $child['is_active'] ? 'bg-success' : 'bg-secondary' ?> me-2">
                                <?= $child['is_active'] ? 'Hoạt động' : 'Ẩn' ?>
                            </span>
                            <span class="badge bg-info me-2">
                                Thứ tự: <?= $child['sort_order'] ?>
                            </span>
                            <div class="btn-group">
                                <a href="/admin/categories/edit/<?= $child['id'] ?>" class="btn btn-sm btn-warning"
                                    title="Sửa">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-id="<?= $child['id'] ?>"
                                    data-name="<?= htmlspecialchars($child['name']) ?>" onclick="deleteCategory(this)"
                                    title="Xóa">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table View -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Danh sách tất cả danh mục</h5>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên danh mục</th>
                        <th>Slug</th>
                        <th>Danh mục cha</th>
                        <th>Thứ tự</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Chưa có danh mục nào</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?= $category['id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($category['name']) ?></strong>
                        </td>
                        <td>
                            <code><?= htmlspecialchars($category['slug']) ?></code>
                        </td>
                        <td>
                            <?php if ($category['parent_name']): ?>
                            <span class="badge bg-secondary"><?= htmlspecialchars($category['parent_name']) ?></span>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $category['sort_order'] ?></td>
                        <td>
                            <span class="badge <?= $category['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $category['is_active'] ? 'Hoạt động' : 'Ẩn' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/categories/edit/<?= $category['id'] ?>" class="btn btn-sm btn-warning"
                                    title="Sửa">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-id="<?= $category['id'] ?>"
                                    data-name="<?= htmlspecialchars($category['name']) ?>"
                                    onclick="deleteCategory(this)" title="Xóa">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Form xóa ẩn -->
<form id="deleteForm" method="POST" style="display: none;"></form>

<link rel="stylesheet" href="/assets/css/category-style.css">

<script>
function deleteCategory(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');

    if (confirm('Bạn có chắc chắn muốn xóa danh mục "' + name +
            '"?\n\nLưu ý: Chỉ có thể xóa nếu danh mục không có sản phẩm và không có danh mục con.')) {
        const form = document.getElementById('deleteForm');
        form.action = '/admin/categories/delete/' + id;
        form.method = 'POST';
        form.submit();
    }
}
</script>