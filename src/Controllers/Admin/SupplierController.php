<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\SupplierModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * SupplierController - Quản lý nhà cung cấp
 */
class SupplierController extends Controller
{
    private SupplierModel $supplierModel;

    public function __construct()
    {
        $this->supplierModel = new SupplierModel();
    }

    /**
     * Hiển thị danh sách nhà cung cấp
     */
    public function index(): void
    {
        $keyword = $this->input('keyword', '');
        
        if ($keyword) {
            $suppliers = $this->supplierModel->search($keyword);
        } else {
            $suppliers = $this->supplierModel->getAllWithOrderCount();
        }
        
        $this->view('admin/suppliers/index', [
            'suppliers' => $suppliers,
            'keyword' => $keyword,
            'pageTitle' => 'Quản lý nhà cung cấp'
        ]);
    }

    /**
     * Hiển thị form tạo nhà cung cấp mới
     */
    public function create(): void
    {
        $this->view('admin/suppliers/create', [
            'pageTitle' => 'Thêm nhà cung cấp mới'
        ]);
    }

    /**
     * Xử lý tạo nhà cung cấp mới
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/suppliers');
            return;
        }

        $name = trim($this->input('name', ''));
        $contact = trim($this->input('contact', ''));
        $phone = trim($this->input('phone', ''));
        $email = trim($this->input('email', ''));
        $address = trim($this->input('address', ''));
        $isActive = $this->input('is_active', 0);

        // Validate
        if (empty($name)) {
            AuthHelper::setFlash('error', 'Tên nhà cung cấp không được để trống');
            $this->redirect('/admin/suppliers/create');
            return;
        }

        // Validate email
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            AuthHelper::setFlash('error', 'Email không hợp lệ');
            $this->redirect('/admin/suppliers/create');
            return;
        }

        // Kiểm tra email đã tồn tại
        if (!empty($email) && $this->supplierModel->emailExists($email)) {
            AuthHelper::setFlash('error', 'Email đã tồn tại');
            $this->redirect('/admin/suppliers/create');
            return;
        }

        // Kiểm tra phone đã tồn tại
        if (!empty($phone) && $this->supplierModel->phoneExists($phone)) {
            AuthHelper::setFlash('error', 'Số điện thoại đã tồn tại');
            $this->redirect('/admin/suppliers/create');
            return;
        }

        // Tạo nhà cung cấp
        $data = [
            'name' => $name,
            'contact' => $contact ?: null,
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'address' => $address ?: null,
            'is_active' => $isActive ? 1 : 0
        ];

        $supplierId = $this->supplierModel->create($data);

        if ($supplierId) {
            LogHelper::log('create', 'supplier', $supplierId, $data);
            AuthHelper::setFlash('success', 'Thêm nhà cung cấp thành công!');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/suppliers');
    }

    /**
     * Hiển thị form sửa nhà cung cấp
     */
    public function edit(int $id): void
    {
        $supplier = $this->supplierModel->findWithOrderCount($id);

        if (!$supplier) {
            AuthHelper::setFlash('error', 'Nhà cung cấp không tồn tại');
            $this->redirect('/admin/suppliers');
            return;
        }

        $this->view('admin/suppliers/edit', [
            'supplier' => $supplier,
            'pageTitle' => 'Sửa nhà cung cấp: ' . $supplier['name']
        ]);
    }

    /**
     * Xử lý cập nhật nhà cung cấp
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/suppliers');
            return;
        }

        $supplier = $this->supplierModel->find($id);

        if (!$supplier) {
            AuthHelper::setFlash('error', 'Nhà cung cấp không tồn tại');
            $this->redirect('/admin/suppliers');
            return;
        }

        $name = trim($this->input('name', ''));
        $contact = trim($this->input('contact', ''));
        $phone = trim($this->input('phone', ''));
        $email = trim($this->input('email', ''));
        $address = trim($this->input('address', ''));
        $isActive = $this->input('is_active', 0);

        // Validate
        if (empty($name)) {
            AuthHelper::setFlash('error', 'Tên nhà cung cấp không được để trống');
            $this->redirect('/admin/suppliers/edit/' . $id);
            return;
        }

        // Validate email
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            AuthHelper::setFlash('error', 'Email không hợp lệ');
            $this->redirect('/admin/suppliers/edit/' . $id);
            return;
        }

        // Kiểm tra email trùng lặp
        if (!empty($email) && $this->supplierModel->emailExists($email, $id)) {
            AuthHelper::setFlash('error', 'Email đã tồn tại');
            $this->redirect('/admin/suppliers/edit/' . $id);
            return;
        }

        // Kiểm tra phone trùng lặp
        if (!empty($phone) && $this->supplierModel->phoneExists($phone, $id)) {
            AuthHelper::setFlash('error', 'Số điện thoại đã tồn tại');
            $this->redirect('/admin/suppliers/edit/' . $id);
            return;
        }

        // Cập nhật nhà cung cấp
        $data = [
            'name' => $name,
            'contact' => $contact ?: null,
            'phone' => $phone ?: null,
            'email' => $email ?: null,
            'address' => $address ?: null,
            'is_active' => $isActive ? 1 : 0
        ];

        $success = $this->supplierModel->update($id, $data);

        if ($success) {
            LogHelper::log('update', 'supplier', $id, $data);
            AuthHelper::setFlash('success', 'Cập nhật nhà cung cấp thành công!');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/suppliers');
    }

    /**
     * Xóa nhà cung cấp
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/suppliers');
            return;
        }

        $supplier = $this->supplierModel->find($id);

        if (!$supplier) {
            AuthHelper::setFlash('error', 'Nhà cung cấp không tồn tại');
            $this->redirect('/admin/suppliers');
            return;
        }

        // Kiểm tra có thể xóa không
        $canDelete = $this->supplierModel->canDelete($id);

        if (!$canDelete['can_delete']) {
            AuthHelper::setFlash('error', 'Không thể xóa nhà cung cấp này vì đang có ' . $canDelete['order_count'] . ' đơn hàng');
            $this->redirect('/admin/suppliers');
            return;
        }

        // Xóa nhà cung cấp
        $success = $this->supplierModel->delete($id);

        if ($success) {
            LogHelper::log('delete', 'supplier', $id, $supplier);
            AuthHelper::setFlash('success', 'Xóa nhà cung cấp thành công!');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/suppliers');
    }

    /**
     * Toggle trạng thái active
     */
    public function toggleActive(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        $success = $this->supplierModel->toggleActive($id);

        if ($success) {
            $supplier = $this->supplierModel->find($id);
            LogHelper::log('toggle_active', 'supplier', $id, ['is_active' => $supplier['is_active']]);
            $this->json(['success' => true, 'is_active' => $supplier['is_active']]);
        } else {
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra']);
        }
    }

    /**
     * Xem chi tiết nhà cung cấp
     */
    public function detail(int $id): void
    {
        $supplier = $this->supplierModel->findWithOrderCount($id);

        if (!$supplier) {
            AuthHelper::setFlash('error', 'Nhà cung cấp không tồn tại');
            $this->redirect('/admin/suppliers');
            return;
        }

        $orderHistory = $this->supplierModel->getOrderHistory($id);
        $totalValue = $this->supplierModel->getTotalOrderValue($id);

        $this->view('admin/suppliers/detail', [
            'supplier' => $supplier,
            'orderHistory' => $orderHistory,
            'totalValue' => $totalValue,
            'pageTitle' => 'Chi tiết nhà cung cấp: ' . $supplier['name']
        ]);
    }
}
