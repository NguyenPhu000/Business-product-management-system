<?php
/**
 * View: Danh sách thương hiệu
 */
$pageTitle = $pageTitle ?? 'Quản lý thương hiệu';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-tag"></i> <?= $pageTitle ?></h2>
        <div class="d-flex gap-2">
            <form method="GET" action="/admin/brands" class="d-flex">
                <input type="text" name="keyword" class="form-control" placeholder="Tìm kiếm thương hiệu..."
                    value="<?= htmlspecialchars($keyword ?? '') ?>">
                <button type="submit" class="btn btn-secondary ms-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="/admin/brands/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm thương hiệu
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

    <!-- Brands Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">STT</th>
                        <th width="15%">Logo</th>
                        <th width="25%">Tên thương hiệu</th>
                        <th width="30%">Mô tả</th>
                        <th width="10%">Số SP</th>
                        <th width="10%">Trạng thái</th>
                        <th width="15%">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($brands)): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p>Chưa có thương hiệu nào</p>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php 
                    $stt = isset($pagination) ? (($pagination['page'] - 1) * $pagination['perPage']) + 1 : 1;
                    foreach ($brands as $brand): 
                    ?>
                    <tr>
                        <td><?= $stt++ ?></td>
                        <td>
                            <?php if ($brand['logo_url']): ?>
                            <!-- Hiển thị logo từ base64 hoặc URL -->
                            <img src="<?= htmlspecialchars($brand['logo_url']) ?>"
                                alt="<?= htmlspecialchars($brand['name']) ?>" 
                                class="brand-logo img-thumbnail"
                                style="max-width: 80px; max-height: 80px; object-fit: contain;">
                            <?php else: ?>
                            <div class="text-muted"><i class="fas fa-image"></i> Chưa có</div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($brand['name']) ?></strong></td>
                        <td>
                            <?php if ($brand['description']): ?>
                            <?= htmlspecialchars(mb_substr($brand['description'], 0, 100)) ?>
                            <?= mb_strlen($brand['description']) > 100 ? '...' : '' ?>
                            <?php else: ?>
                            <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info"><?= $brand['product_count'] ?? 0 ?> sản phẩm</span>
                        </td>
                        <td>
                            <span class="badge <?= $brand['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                <?= $brand['is_active'] ? 'Hoạt động' : 'Ẩn' ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="/admin/brands/edit/<?= $brand['id'] ?>" class="btn btn-sm btn-warning"
                                    title="Sửa">
                                    <i class="fas fa-pen"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-id="<?= $brand['id'] ?>"
                                    data-name="<?= htmlspecialchars($brand['name']) ?>" onclick="deleteBrand(this)"
                                    title="Xóa">
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
        
        <?php if (isset($pagination) && $pagination && $pagination['totalPages'] > 1): ?>
        <!-- Pagination -->
        <div class="card-footer">
            <div class="d-flex flex-column align-items-center gap-3">
                <div>
                    <span class="text-muted">
                        Hiển thị <?= count($brands) ?> / <?= $pagination['total'] ?> thương hiệu
                    </span>
                </div>
                <nav aria-label="Phân trang thương hiệu">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Trang đầu -->
                        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=1" aria-label="Đầu">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        
                        <!-- Trang trước -->
                        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= max(1, $pagination['page'] - 1) ?>" aria-label="Trước">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        
                        <?php
                        // Logic hiển thị các số trang
                        $startPage = max(1, $pagination['page'] - 2);
                        $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                        
                        // Nếu gần đầu, hiện thêm trang sau
                        if ($pagination['page'] <= 3) {
                            $endPage = min($pagination['totalPages'], 5);
                        }
                        
                        // Nếu gần cuối, hiện thêm trang trước
                        if ($pagination['page'] >= $pagination['totalPages'] - 2) {
                            $startPage = max(1, $pagination['totalPages'] - 4);
                        }
                        ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <!-- Trang sau -->
                        <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= min($pagination['totalPages'], $pagination['page'] + 1) ?>" aria-label="Sau">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        
                        <!-- Trang cuối -->
                        <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $pagination['totalPages'] ?>" aria-label="Cuối">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Form xóa ẩn -->
<form id="deleteForm" method="POST" style="display: none;"></form>

<link rel="stylesheet" href="/assets/css/brand-style.css">

<script>
function deleteBrand(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');

    if (confirm('Bạn có chắc chắn muốn xóa thương hiệu "' + name +
            '"?\n\nLưu ý: Chỉ có thể xóa nếu không có sản phẩm nào thuộc thương hiệu này.')) {
        const form = document.getElementById('deleteForm');
        form.action = '/admin/brands/delete/' + id;
        form.method = 'POST';
        form.submit();
    }
}
</script>