<?php

namespace Modules\Category\Services;

use Modules\Category\Models\CategoryModel;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Exception;

/**
 * CategoryService - Business logic cho quản lý danh mục sản phẩm
 */
class CategoryService
{
    private CategoryModel $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
    }

    /**
     * Lấy tất cả danh mục với parent
     */
    public function getAllWithParent(): array
    {
        return $this->categoryModel->getAllWithParent();
    }

    /**
     * Lấy danh mục với phân trang
     * 
     * @param int $page
     * @param int $perPage
     * @return array
     */
    public function getCategoriesWithPagination(int $page = 1, int $perPage = 8): array
    {
        return $this->categoryModel->getAllWithPagination($page, $perPage);
    }

    /**
     * Lấy cây danh mục
     */
    public function getCategoryTree(): array
    {
        return $this->categoryModel->getCategoryTree();
    }

    /**
     * Lấy danh sách danh mục cha
     */
    public function getParentCategories(): array
    {
        return $this->categoryModel->getParentCategories();
    }

    /**
     * Lấy danh mục theo ID
     */
    public function getCategory(int $id): ?array
    {
        return $this->categoryModel->find($id);
    }

    /**
     * Tạo danh mục mới
     */
    public function createCategory(array $data): int
    {
        // Validate
        if (empty($data['name'])) {
            throw new Exception('Tên danh mục không được để trống');
        }

        // Tạo slug nếu chưa có
        $slug = !empty($data['slug']) ? trim($data['slug']) : '';
        if (empty($slug)) {
            $slug = $this->categoryModel->generateSlug($data['name']);
        } else {
            // Kiểm tra slug đã tồn tại chưa
            if ($this->categoryModel->slugExists($slug)) {
                throw new Exception('Slug đã tồn tại, vui lòng chọn slug khác');
            }
        }

        // Chuẩn bị dữ liệu
        $categoryData = [
            'name' => trim($data['name']),
            'slug' => $slug,
            'parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0,
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0
        ];

        $categoryId = $this->categoryModel->create($categoryData);

        if (!$categoryId) {
            throw new Exception('Có lỗi xảy ra khi tạo danh mục');
        }

        // Ghi log
        LogHelper::log('create', 'category', $categoryId, $categoryData);

        return $categoryId;
    }

    /**
     * Cập nhật danh mục
     */
    public function updateCategory(int $id, array $data): array
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            throw new Exception('Danh mục không tồn tại');
        }

        // Validate
        if (empty($data['name'])) {
            throw new Exception('Tên danh mục không được để trống');
        }

        $slug = !empty($data['slug']) ? trim($data['slug']) : '';

        // Kiểm tra slug trùng lặp
        if (!empty($slug) && $this->categoryModel->slugExists($slug, $id)) {
            throw new Exception('Slug đã tồn tại, vui lòng chọn slug khác');
        }

        // Kiểm tra parent_id không được là chính nó hoặc danh mục con của nó
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

        if ($parentId == $id) {
            throw new Exception('Không thể chọn chính nó làm danh mục cha');
        }

        if ($parentId && $this->categoryModel->isParentOf($id, $parentId)) {
            throw new Exception('Không thể chọn danh mục con làm danh mục cha');
        }

        // Chuẩn bị dữ liệu
        $categoryData = [
            'name' => trim($data['name']),
            'slug' => $slug ?: $this->categoryModel->generateSlug($data['name']),
            'parent_id' => $parentId,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 0,
            'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : 0
        ];

        // Kiểm tra xem is_active có thay đổi không
        $oldIsActive = $category['is_active'];
        $newIsActive = $categoryData['is_active'];

        // Cập nhật các field khác trước
        $dataWithoutActive = $categoryData;
        unset($dataWithoutActive['is_active']);

        if (!empty($dataWithoutActive)) {
            $this->categoryModel->update($id, $dataWithoutActive);
        }

        $message = 'Cập nhật danh mục thành công!';

        // Nếu is_active thay đổi, dùng updateActiveStatus để cascade
        if ($oldIsActive != $newIsActive) {
            $this->categoryModel->updateActiveStatus($id, $newIsActive);

            // Đếm số danh mục con bị ảnh hưởng
            if ($newIsActive == 0) {
                $childCount = count($this->categoryModel->getAllChildrenIds($id));
                if ($childCount > 0) {
                    $message = "Cập nhật danh mục thành công! Đã ẩn {$childCount} danh mục con.";
                }
            }
        }

        // Ghi log
        LogHelper::log('update', 'category', $id, $categoryData);

        return ['success' => true, 'message' => $message];
    }

    /**
     * Xóa danh mục
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            throw new Exception('Danh mục không tồn tại');
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
            throw new Exception($message);
        }

        // Xóa danh mục
        $success = $this->categoryModel->delete($id);

        if (!$success) {
            throw new Exception('Có lỗi xảy ra khi xóa danh mục');
        }

        // Ghi log
        LogHelper::log('delete', 'category', $id, $category);

        return true;
    }

    /**
     * Toggle trạng thái active
     */
    public function toggleActive(int $id): array
    {
        $category = $this->categoryModel->find($id);

        if (!$category) {
            throw new Exception('Danh mục không tồn tại');
        }

        $newStatus = $category['is_active'] ? 0 : 1;

        // Kiểm tra nếu muốn bật danh mục con nhưng cha đang ẩn
        if ($newStatus == 1 && $category['parent_id']) {
            $parent = $this->categoryModel->find($category['parent_id']);
            if ($parent && $parent['is_active'] == 0) {
                throw new Exception('Không thể bật danh mục này vì danh mục cha đang ẩn. Vui lòng bật danh mục cha trước!');
            }
        }

        // Sử dụng method mới để cập nhật cả danh mục con
        $success = $this->categoryModel->updateActiveStatus($id, $newStatus);

        if (!$success) {
            throw new Exception('Có lỗi xảy ra hoặc danh mục cha đang ẩn');
        }

        $message = $newStatus ? 'Đã bật danh mục' : 'Đã ẩn danh mục';

        // Nếu ẩn danh mục cha, đếm số danh mục con bị ảnh hưởng
        $affectedChildren = [];
        if ($newStatus == 0) {
            $childCount = count($this->categoryModel->getAllChildrenIds($id));
            if ($childCount > 0) {
                $message .= " và {$childCount} danh mục con";
                $affectedChildren = $this->categoryModel->getAllChildrenIds($id);
            }
        }

        LogHelper::log('toggle_active', 'category', $id, [
            'is_active' => $newStatus,
            'affected_children' => $affectedChildren
        ]);

        return [
            'success' => true,
            'is_active' => $newStatus,
            'message' => $message
        ];
    }

    /**
     * Lấy danh sách parent categories loại trừ chính nó
     */
    public function getParentCategoriesExcept(int $excludeId): array
    {
        $parentCategories = $this->categoryModel->getParentCategories();

        // Loại bỏ chính nó khỏi danh sách parent
        return array_filter($parentCategories, function ($cat) use ($excludeId) {
            return $cat['id'] != $excludeId;
        });
    }
}
