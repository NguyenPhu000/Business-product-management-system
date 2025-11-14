<?php
/**
 * View: Danh sách nhà cung cấp
 */
$pageTitle = $pageTitle ?? 'Quản lý nhà cung cấp';
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-truck"></i> <?= $pageTitle ?></h2>
        <div class="d-flex gap-2">
            <form method="GET" action="/admin/suppliers" class="d-flex">
                <input type="text" name="keyword" class="form-control" 
                       placeholder="Tìm kiếm nhà cung cấp..." 
                       value="<?= htmlspecialchars($keyword ?? '') ?>">
                <button type="submit" class="btn btn-secondary ms-2">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            <a href="/admin/suppliers/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm nhà cung cấp
            </a>
        </div>
    </div>

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

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th width="5%">STT</th>
                        <th>Tên NCC</th>
                        <th>Người liên hệ</th>
                        <th>Điện thoại</th>
                        <th>Email</th>
                        <th>Số ĐH</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($suppliers)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Chưa có nhà cung cấp nào</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $stt = isset($pagination) ? (($pagination['page'] - 1) * $pagination['perPage']) + 1 : 1;
                        foreach ($suppliers as $supplier): 
                        ?>
                            <tr>
                                <td><?= $stt++ ?></td>
                                <td><strong><?= htmlspecialchars($supplier['name']) ?></strong></td>
                                <td><?= htmlspecialchars($supplier['contact'] ?? '-') ?></td>
                                <td>
                                    <?php if ($supplier['phone']): ?>
                                        <a href="tel:<?= $supplier['phone'] ?>">
                                            <i class="fas fa-phone"></i> <?= htmlspecialchars($supplier['phone']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($supplier['email']): ?>
                                        <a href="mailto:<?= $supplier['email'] ?>">
                                            <i class="fas fa-envelope"></i> <?= htmlspecialchars($supplier['email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $supplier['order_count'] ?? 0 ?></span>
                                </td>
                                <td>
                                    <span class="badge <?= $supplier['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $supplier['is_active'] ? 'Hoạt động' : 'Ẩn' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="/admin/suppliers/detail/<?= $supplier['id'] ?>" 
                                           class="btn btn-sm btn-info" title="Chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/suppliers/edit/<?= $supplier['id'] ?>" 
                                           class="btn btn-sm btn-warning" title="Sửa">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                data-id="<?= $supplier['id'] ?>"
                                                data-name="<?= htmlspecialchars($supplier['name']) ?>"
                                                onclick="deleteSupplier(this)" 
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
                        Hiển thị <?= count($suppliers) ?> / <?= $pagination['total'] ?> nhà cung cấp
                    </span>
                </div>
                <nav aria-label="Phân trang nhà cung cấp">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=1" aria-label="Đầu">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                        
                        <li class="page-item <?= $pagination['page'] <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= max(1, $pagination['page'] - 1) ?>" aria-label="Trước">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        </li>
                        
                        <?php
                        $startPage = max(1, $pagination['page'] - 2);
                        $endPage = min($pagination['totalPages'], $pagination['page'] + 2);
                        
                        if ($pagination['page'] <= 3) {
                            $endPage = min($pagination['totalPages'], 5);
                        }
                        
                        if ($pagination['page'] >= $pagination['totalPages'] - 2) {
                            $startPage = max(1, $pagination['totalPages'] - 4);
                        }
                        ?>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                        <li class="page-item <?= $i == $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $pagination['page'] >= $pagination['totalPages'] ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= min($pagination['totalPages'], $pagination['page'] + 1) ?>" aria-label="Sau">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        
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

<form id="deleteForm" method="POST" style="display: none;"></form>

<link rel="stylesheet" href="/assets/css/supplier-style.css">

<script>
function deleteSupplier(btn) {
    const id = btn.getAttribute('data-id');
    const name = btn.getAttribute('data-name');
    
    if (confirm('Bạn có chắc chắn muốn xóa nhà cung cấp "' + name + '"?\n\nLưu ý: Chỉ có thể xóa nếu không có đơn hàng nào.')) {
        const form = document.getElementById('deleteForm');
        form.action = '/admin/suppliers/delete/' + id;
        form.method = 'POST';
        form.submit();
    }
}
</script>
