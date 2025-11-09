<?php

namespace Modules\Category\Services;

use Modules\Category\Models\SupplierModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * SupplierService - Business logic cho quản lý nhà cung cấp
 */
class SupplierService
{
    private SupplierModel $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
    }

    /**
     * Lấy tất cả nhà cung cấp với số lượng đơn hàng
     */
    public function getAllSuppliers(): array
    {
        return $this->supplierModel->getAllWithOrderCount();
    }

    /**
     * Tìm kiếm nhà cung cấp
     */
    public function searchSuppliers(string $keyword): array
    {
        return $this->supplierModel->search($keyword);
    }

    /**
     * Lấy nhà cung cấp theo ID với số lượng đơn hàng
     */
    public function getSupplierWithOrderCount(int $id): ?array
    {
        return $this->supplierModel->findWithOrderCount($id);
    }

    /**
     * Lấy nhà cung cấp theo ID
     */
    public function getSupplier(int $id): ?array
    {
        return $this->supplierModel->find($id);
    }

    /**
     * Tạo nhà cung cấp mới
     */
    public function createSupplier(array $data): int
    {
        // Validate
        if (empty($data['name'])) {
            throw new Exception('Tên nhà cung cấp không được để trống');
        }

        // Validate email
        $email = !empty($data['email']) ? trim($data['email']) : '';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }

        // Kiểm tra email đã tồn tại
        if (!empty($email) && $this->supplierModel->emailExists($email)) {
            throw new Exception('Email đã tồn tại');
        }

        // Kiểm tra phone đã tồn tại
        $phone = !empty($data['phone']) ? trim($data['phone']) : '';
        if (!empty($phone) && $this->supplierModel->phoneExists($phone)) {
            throw new Exception('Số điện thoại đã tồn tại');
        }

        // Chuẩn bị dữ liệu
        $supplierData = [
            'name' => trim($data['name']),
            'contact' => !empty($data['contact']) ? trim($data['contact']) : null,
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'address' => !empty($data['address']) ? trim($data['address']) : null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0
        ];

        $supplierId = $this->supplierModel->create($supplierData);

        if (!$supplierId) {
            throw new Exception('Có lỗi xảy ra khi tạo nhà cung cấp');
        }

        // Ghi log
        LogHelper::log('create', 'supplier', $supplierId, $supplierData);

        return $supplierId;
    }

    /**
     * Cập nhật nhà cung cấp
     */
    public function updateSupplier(int $id, array $data): bool
    {
        $supplier = $this->supplierModel->find($id);

        if (!$supplier) {
            throw new Exception('Nhà cung cấp không tồn tại');
        }

        // Validate
        if (empty($data['name'])) {
            throw new Exception('Tên nhà cung cấp không được để trống');
        }

        // Validate email
        $email = !empty($data['email']) ? trim($data['email']) : '';
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }

        // Kiểm tra email trùng lặp
        if (!empty($email) && $this->supplierModel->emailExists($email, $id)) {
            throw new Exception('Email đã tồn tại');
        }

        // Kiểm tra phone trùng lặp
        $phone = !empty($data['phone']) ? trim($data['phone']) : '';
        if (!empty($phone) && $this->supplierModel->phoneExists($phone, $id)) {
            throw new Exception('Số điện thoại đã tồn tại');
        }

        // Chuẩn bị dữ liệu
        $supplierData = [
            'name' => trim($data['name']),
            'contact' => !empty($data['contact']) ? trim($data['contact']) : null,
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'address' => !empty($data['address']) ? trim($data['address']) : null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0
        ];

        $success = $this->supplierModel->update($id, $supplierData);

        if (!$success) {
            throw new Exception('Có lỗi xảy ra khi cập nhật nhà cung cấp');
        }

        // Ghi log
        LogHelper::log('update', 'supplier', $id, $supplierData);

        return true;
    }

    /**
     * Xóa nhà cung cấp
     */
    public function deleteSupplier(int $id): bool
    {
        $supplier = $this->supplierModel->find($id);

        if (!$supplier) {
            throw new Exception('Nhà cung cấp không tồn tại');
        }

        // Kiểm tra có thể xóa không
        $canDelete = $this->supplierModel->canDelete($id);

        if (!$canDelete['can_delete']) {
            throw new Exception('Không thể xóa nhà cung cấp này vì đang có ' . $canDelete['order_count'] . ' đơn hàng');
        }

        // Xóa nhà cung cấp
        $success = $this->supplierModel->delete($id);

        if (!$success) {
            throw new Exception('Có lỗi xảy ra khi xóa nhà cung cấp');
        }

        // Ghi log
        LogHelper::log('delete', 'supplier', $id, $supplier);

        return true;
    }

    /**
     * Toggle trạng thái active
     */
    public function toggleActive(int $id): array
    {
        $success = $this->supplierModel->toggleActive($id);

        if (!$success) {
            throw new Exception('Có lỗi xảy ra khi thay đổi trạng thái');
        }

        $supplier = $this->supplierModel->find($id);

        // Ghi log
        LogHelper::log('toggle_active', 'supplier', $id, ['is_active' => $supplier['is_active']]);

        return [
            'success' => true,
            'is_active' => $supplier['is_active']
        ];
    }

    /**
     * Lấy lịch sử đơn hàng của nhà cung cấp
     */
    public function getOrderHistory(int $id): array
    {
        return $this->supplierModel->getOrderHistory($id);
    }

    /**
     * Lấy tổng giá trị đơn hàng của nhà cung cấp
     */
    public function getTotalOrderValue(int $id): float
    {
        return $this->supplierModel->getTotalOrderValue($id);
    }
}
