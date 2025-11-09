<?php

namespace Modules\Category\Controllers;

use Core\Controller;
use Modules\Category\Services\SupplierService;
use Helpers\AuthHelper;
use Exception;

/**
 * SupplierController - Routing layer cho quản lý nhà cung cấp
 * 
 * Chỉ xử lý request/response, logic nằm trong SupplierService
 */
class SupplierController extends Controller
{
    private SupplierService $supplierService;

    public function __construct()
    {
        $this->supplierService = new SupplierService();
    }

    /**
     * Hiển thị danh sách nhà cung cấp
     */
    public function index(): void
    {
        $keyword = $this->input('keyword', '');

        $suppliers = $keyword
            ? $this->supplierService->searchSuppliers($keyword)
            : $this->supplierService->getAllSuppliers();

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

        try {
            $data = [
                'name' => $this->input('name', ''),
                'contact' => $this->input('contact', ''),
                'phone' => $this->input('phone', ''),
                'email' => $this->input('email', ''),
                'address' => $this->input('address', ''),
                'is_active' => $this->input('is_active', 0)
            ];

            $this->supplierService->createSupplier($data);
            AuthHelper::setFlash('success', 'Thêm nhà cung cấp thành công!');
            $this->redirect('/admin/suppliers');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/suppliers/create');
        }
    }

    /**
     * Hiển thị form sửa nhà cung cấp
     */
    public function edit(int $id): void
    {
        $supplier = $this->supplierService->getSupplierWithOrderCount($id);

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

        try {
            $data = [
                'name' => $this->input('name', ''),
                'contact' => $this->input('contact', ''),
                'phone' => $this->input('phone', ''),
                'email' => $this->input('email', ''),
                'address' => $this->input('address', ''),
                'is_active' => $this->input('is_active', 0)
            ];

            $this->supplierService->updateSupplier($id, $data);
            AuthHelper::setFlash('success', 'Cập nhật nhà cung cấp thành công!');
            $this->redirect('/admin/suppliers');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/suppliers/edit/' . $id);
        }
    }

    /**
     * Xóa nhà cung cấp
     */
    public function destroy(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/suppliers');
            return;
        }

        try {
            $this->supplierService->deleteSupplier($id);
            AuthHelper::setFlash('success', 'Xóa nhà cung cấp thành công!');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/suppliers');
    }

    /**
     * Toggle trạng thái active
     */
    public function toggle(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $result = $this->supplierService->toggleActive($id);
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Xem chi tiết nhà cung cấp
     */
    public function detail(int $id): void
    {
        $supplier = $this->supplierService->getSupplierWithOrderCount($id);

        if (!$supplier) {
            AuthHelper::setFlash('error', 'Nhà cung cấp không tồn tại');
            $this->redirect('/admin/suppliers');
            return;
        }

        $orderHistory = $this->supplierService->getOrderHistory($id);
        $totalValue = $this->supplierService->getTotalOrderValue($id);

        $this->view('admin/suppliers/detail', [
            'supplier' => $supplier,
            'orderHistory' => $orderHistory,
            'totalValue' => $totalValue,
            'pageTitle' => 'Chi tiết nhà cung cấp: ' . $supplier['name']
        ]);
    }
}
