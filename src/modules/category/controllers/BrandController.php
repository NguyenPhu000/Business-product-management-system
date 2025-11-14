<?php

namespace Modules\Category\Controllers;

use Core\Controller;
use Modules\Category\Services\BrandService;
use Helpers\AuthHelper;
use Exception;

/**
 * BrandController - Routing layer cho quản lý thương hiệu
 * 
 * Chỉ xử lý request/response, logic nằm trong BrandService
 */
class BrandController extends Controller
{
    private BrandService $brandService;

    public function __construct()
    {
        $this->brandService = new BrandService();
    }

    /**
     * Hiển thị danh sách thương hiệu
     */
    public function index(): void
    {
        $keyword = $this->input('keyword', '');
        $page = (int) $this->input('page', 1);
        $perPage = 8; // 8 thương hiệu mỗi trang

        if ($keyword) {
            // Tìm kiếm không phân trang
            $brands = $this->brandService->searchBrands($keyword);
            $pagination = null;
        } else {
            // Lấy dữ liệu với phân trang
            $result = $this->brandService->getBrandsWithPagination($page, $perPage);
            $brands = $result['data'];
            $pagination = [
                'total' => $result['total'],
                'page' => $result['page'],
                'perPage' => $result['perPage'],
                'totalPages' => $result['totalPages']
            ];
        }

        $this->view('admin/brands/index', [
            'brands' => $brands,
            'keyword' => $keyword,
            'pagination' => $pagination,
            'pageTitle' => 'Quản lý thương hiệu'
        ]);
    }

    /**
     * Hiển thị form tạo thương hiệu mới
     */
    public function create(): void
    {
        $this->view('admin/brands/create', [
            'pageTitle' => 'Thêm thương hiệu mới'
        ]);
    }

    /**
     * Xử lý tạo thương hiệu mới
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/brands');
            return;
        }

        try {
            $data = [
                'name' => $this->input('name', ''),
                'description' => $this->input('description', ''),
                'is_active' => $this->input('is_active', 0)
            ];

            $this->brandService->createBrand($data);
            AuthHelper::setFlash('success', 'Thêm thương hiệu thành công!');
            $this->redirect('/admin/brands');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/brands/create');
        }
    }

    /**
     * Hiển thị form sửa thương hiệu
     */
    public function edit(int $id): void
    {
        $brand = $this->brandService->getBrandWithProductCount($id);

        if (!$brand) {
            AuthHelper::setFlash('error', 'Thương hiệu không tồn tại');
            $this->redirect('/admin/brands');
            return;
        }

        $this->view('admin/brands/edit', [
            'brand' => $brand,
            'pageTitle' => 'Sửa thương hiệu: ' . $brand['name']
        ]);
    }

    /**
     * Xử lý cập nhật thương hiệu
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/brands');
            return;
        }

        try {
            $data = [
                'name' => $this->input('name', ''),
                'description' => $this->input('description', ''),
                'is_active' => $this->input('is_active', 0)
            ];

            $this->brandService->updateBrand($id, $data);
            AuthHelper::setFlash('success', 'Cập nhật thương hiệu thành công!');
            $this->redirect('/admin/brands');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/brands/edit/' . $id);
        }
    }

    /**
     * Xóa thương hiệu
     */
    public function destroy(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/brands');
            return;
        }

        try {
            $this->brandService->deleteBrand($id);
            AuthHelper::setFlash('success', 'Xóa thương hiệu thành công!');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/brands');
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
            $result = $this->brandService->toggleActive($id);
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
