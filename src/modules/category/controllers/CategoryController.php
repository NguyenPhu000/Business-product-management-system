<?php

namespace Modules\Category\Controllers;

use Core\Controller;
use Modules\Category\Services\CategoryService;
use Helpers\AuthHelper;
use Exception;

/**
 * CategoryController - Routing layer cho quản lý danh mục
 * 
 * Chỉ xử lý request/response, logic nằm trong CategoryService
 */
class CategoryController extends Controller
{
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->categoryService = new CategoryService();
    }

    /**
     * Hiển thị danh sách danh mục
     */
    public function index(): void
    {
        $page = (int) $this->input('page', 1);
        $perPage = 8; // 8 danh mục mỗi trang

        // Lấy dữ liệu với phân trang
        $result = $this->categoryService->getCategoriesWithPagination($page, $perPage);
        $categories = $result['data'];
        $pagination = [
            'total' => $result['total'],
            'page' => $result['page'],
            'perPage' => $result['perPage'],
            'totalPages' => $result['totalPages']
        ];

        $categoryTree = $this->categoryService->getCategoryTree();

        $this->view('admin/categories/index', [
            'categories' => $categories,
            'categoryTree' => $categoryTree,
            'pagination' => $pagination,
            'pageTitle' => 'Quản lý danh mục sản phẩm'
        ]);
    }

    /**
     * Hiển thị form tạo danh mục mới
     */
    public function create(): void
    {
        $parentCategories = $this->categoryService->getParentCategories();

        $this->view('admin/categories/create', [
            'parentCategories' => $parentCategories,
            'pageTitle' => 'Thêm danh mục mới'
        ]);
    }

    /**
     * Xử lý tạo danh mục mới
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        try {
            $data = [
                'name' => $this->input('name', ''),
                'slug' => $this->input('slug', ''),
                'parent_id' => $this->input('parent_id', null),
                'is_active' => $this->input('is_active', 0),
                'sort_order' => $this->input('sort_order', 0)
            ];

            $this->categoryService->createCategory($data);
            AuthHelper::setFlash('success', 'Thêm danh mục thành công!');
            $this->redirect('/admin/categories');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/categories/create');
        }
    }

    /**
     * Hiển thị form sửa danh mục
     */
    public function edit(int $id): void
    {
        $category = $this->categoryService->getCategory($id);

        if (!$category) {
            AuthHelper::setFlash('error', 'Danh mục không tồn tại');
            $this->redirect('/admin/categories');
            return;
        }

        $parentCategories = $this->categoryService->getParentCategoriesExcept($id);

        $this->view('admin/categories/edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
            'pageTitle' => 'Sửa danh mục: ' . $category['name']
        ]);
    }

    /**
     * Xử lý cập nhật danh mục
     */
    public function update(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        try {
            $data = [
                'name' => $this->input('name', ''),
                'slug' => $this->input('slug', ''),
                'parent_id' => $this->input('parent_id', null),
                'is_active' => $this->input('is_active', 0),
                'sort_order' => $this->input('sort_order', 0)
            ];

            $result = $this->categoryService->updateCategory($id, $data);
            AuthHelper::setFlash('success', $result['message']);
            $this->redirect('/admin/categories');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
            $this->redirect('/admin/categories/edit/' . $id);
        }
    }

    /**
     * Xóa danh mục
     */
    public function destroy(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        try {
            $this->categoryService->deleteCategory($id);
            AuthHelper::setFlash('success', 'Xóa danh mục thành công!');
        } catch (Exception $e) {
            AuthHelper::setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/categories');
    }

    /**
     * Toggle trạng thái active (AJAX)
     */
    public function toggle(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method']);
            return;
        }

        try {
            $result = $this->categoryService->toggleActive($id);
            $this->json($result);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
