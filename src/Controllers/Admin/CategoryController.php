<?php

namespace Controllers\Admin;

use Core\Controller;
use Models\CategoryModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;

/**
 * CategoryController - Quản lý danh mục sản phẩm
 */
class CategoryController extends Controller
{
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Hiển thị danh sách danh mục
     */
    public function index(): void
    {
        $categories = $this->categoryModel->getAllWithParent();
        $categoryTree = $this->categoryModel->getCategoryTree();
        
        $this->view('admin/categories/index', [
            'categories' => $categories,
            'categoryTree' => $categoryTree,
            'pageTitle' => 'Quản lý danh mục sản phẩm'
        ]);
    }

    /**
     * Hiển thị form tạo danh mục mới
     */
    public function create(): void
    {
        $parentCategories = $this->categoryModel->getParentCategories();
        
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

        $name = trim($this->input('name', ''));
        $slug = trim($this->input('slug', ''));
        $parentId = $this->input('parent_id', null);
        $isActive = $this->input('is_active', 0);
        $sortOrder = $this->input('sort_order', 0);

        // Validate
        if (empty($name)) {
            AuthHelper::setFlash('error', 'Tên danh mục không được để trống');
            $this->redirect('/admin/categories/create');
            return;
        }

        // Tạo slug nếu chưa có
        if (empty($slug)) {
            $slug = $this->categoryModel->generateSlug($name);
        } else {
            // Kiểm tra slug đã tồn tại chưa
            if ($this->categoryModel->slugExists($slug)) {
                AuthHelper::setFlash('error', 'Slug đã tồn tại, vui lòng chọn slug khác');
                $this->redirect('/admin/categories/create');
                return;
            }
        }

        // Tạo danh mục
        $data = [
            'name' => $name,
            'slug' => $slug,
            'parent_id' => $parentId ?: null,
            'is_active' => $isActive ? 1 : 0,
            'sort_order' => (int) $sortOrder
        ];

        $categoryId = $this->categoryModel->create($data);

        if ($categoryId) {
            // Ghi log
            LogHelper::log('create', 'category', $categoryId, $data);
            
            AuthHelper::setFlash('success', 'Thêm danh mục thành công!');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/categories');
    }

    /**
     * Hiển thị form sửa danh mục
     */
    public function edit(int $id): void
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            AuthHelper::setFlash('error', 'Danh mục không tồn tại');
            $this->redirect('/admin/categories');
            return;
        }

        $parentCategories = $this->categoryModel->getParentCategories();
        
        // Loại bỏ chính nó và các danh mục con khỏi danh sách parent
        $parentCategories = array_filter($parentCategories, function($cat) use ($id) {
            return $cat['id'] != $id;
        });

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

        $category = $this->categoryModel->find($id);

        if (!$category) {
            AuthHelper::setFlash('error', 'Danh mục không tồn tại');
            $this->redirect('/admin/categories');
            return;
        }

        $name = trim($this->input('name', ''));
        $slug = trim($this->input('slug', ''));
        $parentId = $this->input('parent_id', null);
        $isActive = $this->input('is_active', 0);
        $sortOrder = $this->input('sort_order', 0);

        // Validate
        if (empty($name)) {
            AuthHelper::setFlash('error', 'Tên danh mục không được để trống');
            $this->redirect('/admin/categories/edit/' . $id);
            return;
        }

        // Kiểm tra slug trùng lặp
        if (!empty($slug) && $this->categoryModel->slugExists($slug, $id)) {
            AuthHelper::setFlash('error', 'Slug đã tồn tại, vui lòng chọn slug khác');
            $this->redirect('/admin/categories/edit/' . $id);
            return;
        }

        // Kiểm tra parent_id không được là chính nó hoặc danh mục con của nó
        if ($parentId == $id) {
            AuthHelper::setFlash('error', 'Không thể chọn chính nó làm danh mục cha');
            $this->redirect('/admin/categories/edit/' . $id);
            return;
        }

        if ($parentId && $this->categoryModel->isParentOf($id, $parentId)) {
            AuthHelper::setFlash('error', 'Không thể chọn danh mục con làm danh mục cha');
            $this->redirect('/admin/categories/edit/' . $id);
            return;
        }

        // Cập nhật danh mục
        $data = [
            'name' => $name,
            'slug' => $slug ?: $this->categoryModel->generateSlug($name),
            'parent_id' => $parentId ?: null,
            'is_active' => $isActive ? 1 : 0,
            'sort_order' => (int) $sortOrder
        ];

        // Kiểm tra xem is_active có thay đổi không
        $oldIsActive = $category['is_active'];
        $newIsActive = $data['is_active'];
        
        // Cập nhật các field khác trước
        $dataWithoutActive = $data;
        unset($dataWithoutActive['is_active']);
        
        if (!empty($dataWithoutActive)) {
            $this->categoryModel->update($id, $dataWithoutActive);
        }
        
        // Nếu is_active thay đổi, dùng updateActiveStatus để cascade
        if ($oldIsActive != $newIsActive) {
            $this->categoryModel->updateActiveStatus($id, $newIsActive);
            
            // Đếm số danh mục con bị ảnh hưởng
            if ($newIsActive == 0) {
                $childCount = count($this->categoryModel->getAllChildrenIds($id));
                if ($childCount > 0) {
                    AuthHelper::setFlash('success', "Cập nhật danh mục thành công! Đã ẩn {$childCount} danh mục con.");
                } else {
                    AuthHelper::setFlash('success', 'Cập nhật danh mục thành công!');
                }
            } else {
                AuthHelper::setFlash('success', 'Cập nhật danh mục thành công!');
            }
        } else {
            AuthHelper::setFlash('success', 'Cập nhật danh mục thành công!');
        }

        // Ghi log
        LogHelper::log('update', 'category', $id, $data);

        $this->redirect('/admin/categories');
    }

    /**
     * Xóa danh mục
     */
    public function delete(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/categories');
            return;
        }

        $category = $this->categoryModel->find($id);

        if (!$category) {
            AuthHelper::setFlash('error', 'Danh mục không tồn tại');
            $this->redirect('/admin/categories');
            return;
        }

        // Kiểm tra có thể xóa không
        $canDelete = $this->categoryModel->canDelete($id);

        if (!$canDelete['can_delete']) {
            $message = 'Không thể xóa danh mục này vì: ';
            if ($canDelete['has_products']) {
                $message .= 'đang có sản phẩm ';
            }
            if ($canDelete['has_children']) {
                $message .= 'đang có danh mục con';
            }
            
            AuthHelper::setFlash('error', $message);
            $this->redirect('/admin/categories');
            return;
        }

        // Xóa danh mục
        $success = $this->categoryModel->delete($id);

        if ($success) {
            // Ghi log
            LogHelper::log('delete', 'category', $id, $category);
            
            AuthHelper::setFlash('success', 'Xóa danh mục thành công!');
        } else {
            AuthHelper::setFlash('error', 'Có lỗi xảy ra, vui lòng thử lại!');
        }

        $this->redirect('/admin/categories');
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

        $category = $this->categoryModel->find($id);

        if (!$category) {
            $this->json(['success' => false, 'message' => 'Danh mục không tồn tại']);
            return;
        }

        $newStatus = $category['is_active'] ? 0 : 1;
        
        // Kiểm tra nếu muốn bật danh mục con nhưng cha đang ẩn
        if ($newStatus == 1 && $category['parent_id']) {
            $parent = $this->categoryModel->find($category['parent_id']);
            if ($parent && $parent['is_active'] == 0) {
                $this->json([
                    'success' => false, 
                    'message' => 'Không thể bật danh mục này vì danh mục cha đang ẩn. Vui lòng bật danh mục cha trước!'
                ]);
                return;
            }
        }
        
        // Sử dụng method mới để cập nhật cả danh mục con
        $success = $this->categoryModel->updateActiveStatus($id, $newStatus);

        if ($success) {
            $message = $newStatus ? 'Đã bật danh mục' : 'Đã ẩn danh mục';
            
            // Nếu ẩn danh mục cha, đếm số danh mục con bị ảnh hưởng
            if ($newStatus == 0) {
                $childCount = count($this->categoryModel->getAllChildrenIds($id));
                if ($childCount > 0) {
                    $message .= " và {$childCount} danh mục con";
                }
            }
            
            LogHelper::log('toggle_active', 'category', $id, [
                'is_active' => $newStatus,
                'affected_children' => $newStatus == 0 ? $this->categoryModel->getAllChildrenIds($id) : []
            ]);
            
            $this->json(['success' => true, 'is_active' => $newStatus, 'message' => $message]);
        } else {
            $this->json(['success' => false, 'message' => 'Có lỗi xảy ra hoặc danh mục cha đang ẩn']);
        }
    }
}
