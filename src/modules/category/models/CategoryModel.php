<?php

namespace Modules\Category\Models;

use Core\BaseModel;

/**
 * CategoryModel - Quản lý danh mục sản phẩm
 * 
 * Chức năng:
 * - Quản lý cây danh mục phân cấp (parent-child)
 * - CRUD danh mục
 * - Lấy danh mục con, danh mục cha
 * - Kiểm tra slug trùng lặp
 */
class CategoryModel extends BaseModel
{
    protected string $table = 'categories';
    protected string $primaryKey = 'id';

    /**
     * Lấy tất cả danh mục kèm thông tin parent
     */
    public function getAllWithParent(): array
    {
        $sql = "SELECT c.*, p.name as parent_name 
                FROM {$this->table} c 
                LEFT JOIN {$this->table} p ON c.parent_id = p.id 
                ORDER BY c.sort_order ASC, c.name ASC";
        
        return $this->query($sql);
    }

    /**
     * Lấy danh mục với phân trang
     * 
     * @param int $page Trang hiện tại (bắt đầu từ 1)
     * @param int $perPage Số lượng/trang
     * @return array ['data' => [], 'total' => int, 'page' => int, 'perPage' => int, 'totalPages' => int]
     */
    public function getAllWithPagination(int $page = 1, int $perPage = 8): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Đếm tổng số danh mục
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $countResult = $this->queryOne($countSql);
        $total = (int) ($countResult['total'] ?? 0);

        // Lấy dữ liệu phân trang
        $sql = "SELECT c.*, p.name as parent_name 
                FROM {$this->table} c 
                LEFT JOIN {$this->table} p ON c.parent_id = p.id 
                ORDER BY c.id ASC
                LIMIT {$perPage} OFFSET {$offset}";

        $data = $this->query($sql);

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Lấy danh mục cha (parent_id = NULL)
     */
    public function getParentCategories(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE parent_id IS NULL 
                ORDER BY sort_order ASC, name ASC";
        
        return $this->query($sql);
    }

    /**
     * Lấy danh mục con của một danh mục
     */
    public function getChildren(int $parentId): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE parent_id = ? 
                ORDER BY sort_order ASC, name ASC";
        
        return $this->query($sql, [$parentId]);
    }

    /**
     * Lấy cây danh mục đầy đủ (parent với children)
     */
    public function getCategoryTree(): array
    {
        $parents = $this->getParentCategories();
        
        foreach ($parents as &$parent) {
            $parent['children'] = $this->getChildren($parent['id']);
        }
        
        return $parents;
    }

    /**
     * Lấy danh sách danh mục dạng phẳng (flat) với level để hiển thị trong form
     * Ví dụ: 
     * - Điện Thoại (level 0)
     *     - Tai Nghe (level 1)
     * - Laptop (level 0)
     */
    public function getFlatCategoryTree(): array
    {
        $tree = $this->getCategoryTree();
        $flat = [];
        
        foreach ($tree as $parent) {
            // Thêm danh mục cha
            $parent['level'] = 0;
            $flat[] = $parent;
            
            // Thêm các danh mục con
            if (!empty($parent['children'])) {
                foreach ($parent['children'] as $child) {
                    $child['level'] = 1;
                    $flat[] = $child;
                    
                    // Nếu có con cấp 2 (thêm logic đệ quy nếu cần)
                    if (!empty($child['children'])) {
                        foreach ($child['children'] as $grandchild) {
                            $grandchild['level'] = 2;
                            $flat[] = $grandchild;
                        }
                    }
                }
            }
        }
        
        return $flat;
    }

    /**
     * Kiểm tra slug đã tồn tại chưa
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->queryOne($sql, $params);
        return $result['total'] > 0;
    }

    /**
     * Tạo slug từ name
     */
    public function generateSlug(string $name): string
    {
        // Chuyển tiếng Việt sang không dấu
        $name = $this->removeVietnameseTones($name);
        
        // Chuyển thành lowercase và thay khoảng trắng bằng dấu gạch ngang
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Đảm bảo slug unique
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Xóa dấu tiếng Việt
     */
    private function removeVietnameseTones(string $str): string
    {
        $marTViet = [
            "à", "á", "ạ", "ả", "ã", "â", "ầ", "ấ", "ậ", "ẩ", "ẫ", "ă", "ằ", "ắ", "ặ", "ẳ", "ẵ",
            "è", "é", "ẹ", "ẻ", "ẽ", "ê", "ề", "ế", "ệ", "ể", "ễ",
            "ì", "í", "ị", "ỉ", "ĩ",
            "ò", "ó", "ọ", "ỏ", "õ", "ô", "ồ", "ố", "ộ", "ổ", "ỗ", "ơ", "ờ", "ớ", "ợ", "ở", "ỡ",
            "ù", "ú", "ụ", "ủ", "ũ", "ư", "ừ", "ứ", "ự", "ử", "ữ",
            "ỳ", "ý", "ỵ", "ỷ", "ỹ",
            "đ",
            "À", "Á", "Ạ", "Ả", "Ã", "Â", "Ầ", "Ấ", "Ậ", "Ẩ", "Ẫ", "Ă", "Ằ", "Ắ", "Ặ", "Ẳ", "Ẵ",
            "È", "É", "Ẹ", "Ẻ", "Ẽ", "Ê", "Ề", "Ế", "Ệ", "Ể", "Ễ",
            "Ì", "Í", "Ị", "Ỉ", "Ĩ",
            "Ò", "Ó", "Ọ", "Ỏ", "Õ", "Ô", "Ồ", "Ố", "Ộ", "Ổ", "Ỗ", "Ơ", "Ờ", "Ớ", "Ợ", "Ở", "Ỡ",
            "Ù", "Ú", "Ụ", "Ủ", "Ũ", "Ư", "Ừ", "Ứ", "Ự", "Ử", "Ữ",
            "Ỳ", "Ý", "Ỵ", "Ỷ", "Ỹ",
            "Đ"
        ];
        
        $marKoDau = [
            "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a", "a",
            "e", "e", "e", "e", "e", "e", "e", "e", "e", "e", "e",
            "i", "i", "i", "i", "i",
            "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o", "o",
            "u", "u", "u", "u", "u", "u", "u", "u", "u", "u", "u",
            "y", "y", "y", "y", "y",
            "d",
            "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A", "A",
            "E", "E", "E", "E", "E", "E", "E", "E", "E", "E", "E",
            "I", "I", "I", "I", "I",
            "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O", "O",
            "U", "U", "U", "U", "U", "U", "U", "U", "U", "U", "U",
            "Y", "Y", "Y", "Y", "Y",
            "D"
        ];
        
        return str_replace($marTViet, $marKoDau, $str);
    }

    /**
     * Lấy breadcrumb của danh mục
     */
    public function getBreadcrumb(int $categoryId): array
    {
        $breadcrumb = [];
        $category = $this->find($categoryId);
        
        while ($category) {
            array_unshift($breadcrumb, $category);
            
            if ($category['parent_id']) {
                $category = $this->find($category['parent_id']);
            } else {
                break;
            }
        }
        
        return $breadcrumb;
    }

    /**
     * Kiểm tra có phải danh mục cha của categoryId không
     */
    public function isParentOf(int $parentId, int $categoryId): bool
    {
        $breadcrumb = $this->getBreadcrumb($categoryId);
        
        foreach ($breadcrumb as $item) {
            if ($item['id'] == $parentId) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Lấy tất cả danh mục active
     */
    public function getActiveCategories(): array
    {
        return $this->where(['is_active' => 1], 'sort_order', 'ASC');
    }

    /**
     * Lấy tất cả danh mục con (đệ quy)
     */
    public function getAllChildrenIds(int $parentId): array
    {
        $childIds = [];
        $children = $this->getChildren($parentId);
        
        foreach ($children as $child) {
            $childIds[] = $child['id'];
            // Đệ quy lấy danh mục con của con
            $grandChildren = $this->getAllChildrenIds($child['id']);
            $childIds = array_merge($childIds, $grandChildren);
        }
        
        return $childIds;
    }

    /**
     * Cập nhật trạng thái ẩn/hiện danh mục và các con
     */
    public function updateActiveStatus(int $categoryId, int $isActive): bool
    {
        // Cập nhật danh mục hiện tại
        $this->update($categoryId, ['is_active' => $isActive]);
        
        // Nếu ẩn danh mục cha (is_active = 0)
        if ($isActive == 0) {
            // Ẩn tất cả danh mục con (cascade)
            $childIds = $this->getAllChildrenIds($categoryId);
            
            if (!empty($childIds)) {
                // Sử dụng query trực tiếp để cập nhật hàng loạt
                $ids = implode(',', array_map('intval', $childIds));
                $sql = "UPDATE {$this->table} SET is_active = 0 WHERE id IN ({$ids})";
                $this->execute($sql);
            }
        } 
        // Nếu bật danh mục con (is_active = 1)
        // CHỈ BẬT NẾU DANH MỤC CHA CŨNG ĐANG ACTIVE
        elseif ($isActive == 1) {
            $category = $this->find($categoryId);
            
            // Kiểm tra nếu có parent_id
            if ($category && $category['parent_id']) {
                $parent = $this->find($category['parent_id']);
                
                // Nếu cha đang ẩn, không cho phép bật con
                if ($parent && $parent['is_active'] == 0) {
                    // Rollback - giữ trạng thái ẩn
                    $this->update($categoryId, ['is_active' => 0]);
                    return false;
                }
            }
        }
        
        return true;
    }

    /**
     * Kiểm tra danh mục có thể hiển thị không (kiểm tra cả cha)
     */
    public function isVisibleWithParent(int $categoryId): bool
    {
        $category = $this->find($categoryId);
        
        if (!$category || !$category['is_active']) {
            return false;
        }
        
        // Kiểm tra danh mục cha
        if ($category['parent_id']) {
            return $this->isVisibleWithParent($category['parent_id']);
        }
        
        return true;
    }

    /**
     * Cập nhật thứ tự sắp xếp
     */
    public function updateSortOrder(int $id, int $sortOrder): bool
    {
        return $this->update($id, ['sort_order' => $sortOrder]);
    }

    /**
     * Đếm số lượng sản phẩm trong danh mục
     */
    public function countProducts(int $categoryId): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM product_categories 
                WHERE category_id = ?";
        
        $result = $this->queryOne($sql, [$categoryId]);
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Kiểm tra có thể xóa danh mục không (không có sản phẩm và không có danh mục con)
     */
    public function canDelete(int $categoryId): array
    {
        $hasProducts = $this->countProducts($categoryId) > 0;
        $hasChildren = count($this->getChildren($categoryId)) > 0;
        
        return [
            'can_delete' => !$hasProducts && !$hasChildren,
            'has_products' => $hasProducts,
            'has_children' => $hasChildren,
        ];
    }
}
