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
                        <th>ID</th>
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
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?= $supplier['id'] ?></td>
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
