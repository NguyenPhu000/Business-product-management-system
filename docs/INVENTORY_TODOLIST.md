# 📦 TODOLIST TRIỂN KHAI MODULE QUẢN LÝ TỒN KHO (INVENTORY)

**Ngày tạo**: 10/11/2025  
**Branch**: `feature/inventory-management`  
**Trạng thái**: 🟡 CHUẨN BỊ TRIỂN KHAI  
**Ước tính thời gian**: 12-15 giờ (2-3 ngày)

---

## 🎯 MỤC TIÊU

Xây dựng module **Quản lý tồn kho (Inventory)** theo kiến trúc **HỖN HỢP** (Option 3):

### ✅ Yêu cầu nghiệp vụ:

1. **Theo dõi tồn kho** theo 2 level:

   - **Product Level** (Tổng quan): Hiển thị tổng tồn kho của sản phẩm
   - **Variant Level** (Chi tiết): Quản lý tồn kho từng biến thể (màu, size...)

2. **Cảnh báo tự động**:

   - Sản phẩm sắp hết hàng (Low Stock)
   - Sản phẩm hết hàng (Out of Stock)
   - Hiển thị badge cảnh báo trên sidebar

3. **Lịch sử xuất nhập kho**:

   - Ghi nhận mọi thay đổi tồn kho
   - Phân loại: Nhập (import), Xuất (export), Điều chỉnh (adjust)
   - Liên kết với Purchase Order, Sales Order

4. **Điều chỉnh tồn kho thủ công**:
   - Kiểm kho định kỳ
   - Điều chỉnh số lượng với ghi chú lý do

### ✅ Yêu cầu kỹ thuật:

- Tuân thủ 100% [`CODING_RULES.md`](CODING_RULES.md)
- Sử dụng schema từ [`Database.md`](Database.md)
- MVC Pattern + Service Layer
- Transaction để đảm bảo data consistency

---

## 📊 KIẾN TRÚC TỔNG QUAN

### **Database Schema** (Từ Database.md):

```sql
-- Table: inventory (Tồn kho theo variant + warehouse)
inventory:
  - id (PK)
  - product_variant_id (FK → product_variants.id)
  - warehouse (VARCHAR, default='default')
  - quantity (INT)
  - min_threshold (INT, ngưỡng cảnh báo)
  - last_updated (DATETIME)
  - UNIQUE(product_variant_id, warehouse)

-- Table: inventory_transactions (Lịch sử xuất nhập)
inventory_transactions:
  - id (PK)
  - product_variant_id (FK)
  - warehouse (VARCHAR)
  - type (ENUM: 'import', 'export', 'adjust')
  - quantity_change (INT, có thể âm)
  - reference_type (VARCHAR: 'purchase_order', 'sales_order', 'manual_adjustment')
  - reference_id (INT)
  - note (TEXT)
  - created_by (FK → users.id)
  - created_at (DATETIME)
```

### **Module Structure**:

```
src/modules/inventory/
├── controllers/
│   └── InventoryController.php         # Routing layer
├── services/
│   ├── InventoryService.php            # Core business logic
│   └── StockTransactionService.php     # Quản lý lịch sử (tách riêng)
├── models/
│   ├── InventoryModel.php              # Table: inventory
│   └── InventoryTransactionModel.php   # Table: inventory_transactions
└── views/ (trong src/views/admin/inventory/)
    ├── stock_list.php                  # Danh sách tồn kho (Product level)
    ├── stock_detail.php                # Chi tiết variant
    ├── low_stock.php                   # Cảnh báo sắp hết hàng
    ├── adjust_stock.php                # Form điều chỉnh tồn kho
    └── stock_history.php               # Lịch sử xuất nhập kho
```

---

## 🗓️ KẾ HOẠCH TRIỂN KHAI

### **PHASE 1: CHUẨN BỊ & SETUP (⏱️ 30 phút)**

#### **Task 1.1: Tạo branch và backup**

```bash
# Checkout develop
git checkout develop
git pull origin develop

# Tạo backup
git branch backup-before-inventory-$(date +%Y%m%d)
git push origin backup-before-inventory-$(date +%Y%m%d)

# Tạo feature branch
git checkout -b feature/inventory-management
git push -u origin feature/inventory-management
```

- [ ] Tạo branch `feature/inventory-management`
- [ ] Push branch lên remote
- [ ] Tạo backup branch

#### **Task 1.2: Kiểm tra database schema**

```bash
# Kiểm tra tables đã tồn tại chưa
mysql -u root -p business_product_management_system -e "SHOW TABLES LIKE 'inventory%';"
```

- [ ] Verify table `inventory` tồn tại
- [ ] Verify table `inventory_transactions` tồn tại
- [ ] Verify table `product_variants` tồn tại
- [ ] Nếu chưa có, chạy migration từ `Database.md`

#### **Task 1.3: Tạo cấu trúc thư mục**

```bash
# Tạo thư mục module
mkdir -p src/modules/inventory/controllers
mkdir -p src/modules/inventory/services
mkdir -p src/modules/inventory/models
mkdir -p src/views/admin/inventory
```

- [ ] Tạo `src/modules/inventory/controllers/`
- [ ] Tạo `src/modules/inventory/services/`
- [ ] Tạo `src/modules/inventory/models/`
- [ ] Tạo `src/views/admin/inventory/`

---

### **PHASE 2: MODELS - DATA ACCESS LAYER (⏱️ 2-3 giờ)**

#### **Task 2.1: Tạo InventoryModel.php**

**File**: `src/modules/inventory/models/InventoryModel.php`

**Namespace**: `Modules\Inventory\Models`

**Methods cần implement**:

- [ ] `getVariantStock(int $variantId, string $warehouse)` - Lấy tồn kho của 1 variant
- [ ] `getProductStock(int $productId)` - Aggregate tồn kho của product (tất cả variants)
- [ ] `getInventoryListWithDetails(array $filters, int $limit, int $offset)` - Danh sách với filter
- [ ] `updateStock(int $variantId, int $quantityChange, string $warehouse)` - Cập nhật tồn kho (cộng dồn)
- [ ] `setStock(int $variantId, int $newQuantity, string $warehouse)` - Set số lượng cụ thể
- [ ] `getLowStockProducts(int $limit)` - Sản phẩm sắp hết hàng
- [ ] `getOutOfStockProducts(int $limit)` - Sản phẩm hết hàng
- [ ] `getStockStats()` - Thống kê tổng quan
- [ ] `updateThreshold(int $variantId, int $minThreshold, string $warehouse)` - Cập nhật ngưỡng cảnh báo

**Testing**:

- [ ] Test `getVariantStock()` với variant_id hợp lệ
- [ ] Test `updateStock()` với số dương và số âm
- [ ] Test `getLowStockProducts()` return đúng sản phẩm có `quantity <= min_threshold`
- [ ] Test `getStockStats()` tính toán đúng

#### **Task 2.2: Tạo InventoryTransactionModel.php**

**File**: `src/modules/inventory/models/InventoryTransactionModel.php`

**Namespace**: `Modules\Inventory\Models`

**Methods cần implement**:

- [ ] `recordTransaction(array $data)` - Ghi nhận giao dịch
- [ ] `getVariantHistory(int $variantId, string $warehouse, int $limit)` - Lịch sử của variant
- [ ] `getProductHistory(int $productId, string $warehouse, int $limit)` - Lịch sử của product
- [ ] `getTransactionsWithFilter(array $filters, int $limit, int $offset)` - Danh sách giao dịch
- [ ] `countTransactions(array $filters)` - Đếm số lượng giao dịch
- [ ] `getTransactionStats(string $fromDate, string $toDate)` - Thống kê theo loại

**Testing**:

- [ ] Test `recordTransaction()` với đầy đủ fields
- [ ] Test `getVariantHistory()` return đúng thứ tự (mới nhất trước)
- [ ] Test `getTransactionStats()` group by type đúng

---

### **PHASE 3: SERVICES - BUSINESS LOGIC LAYER (⏱️ 3-4 giờ)** ⭐⭐⭐

#### **Task 3.1: Tạo InventoryService.php**

**File**: `src/modules/inventory/services/InventoryService.php`

**Namespace**: `Modules\Inventory\Services`

**Core Methods**:

- [ ] `getInventoryList(array $filters, int $page, int $perPage)` - Danh sách tồn kho (group by product)
- [ ] `getProductInventoryDetails(int $productId)` - Chi tiết tồn kho của product (bao gồm tất cả variants)
- [ ] `adjustStock(int $variantId, int $newQuantity, string $reason, int $userId, string $warehouse)` - Điều chỉnh tồn kho thủ công
- [ ] `importStock(int $variantId, int $quantity, string $referenceType, int $referenceId, int $userId, string $warehouse)` - Nhập kho (được gọi từ PurchaseService)
- [ ] `exportStock(int $variantId, int $quantity, string $referenceType, int $referenceId, int $userId, string $warehouse)` - Xuất kho (được gọi từ SalesService)
- [ ] `getLowStockProducts(int $limit)` - Sản phẩm sắp hết
- [ ] `getOutOfStockProducts(int $limit)` - Sản phẩm hết
- [ ] `getInventoryStats()` - Thống kê tổng quan
- [ ] `updateStockThreshold(int $variantId, int $minThreshold, string $warehouse)` - Cập nhật ngưỡng
- [ ] `initializeVariantInventory(int $variantId, int $initialQuantity, int $minThreshold, string $warehouse)` - Khởi tạo inventory cho variant mới

**Business Rules**:

- [ ] **Validation**: Số lượng không được âm
- [ ] **Transaction**: Mọi thay đổi tồn kho phải có transaction
- [ ] **Logging**: Ghi lại lịch sử vào `inventory_transactions`
- [ ] **Error Handling**: Throw exception với message rõ ràng
- [ ] **Stock Check**: Kiểm tra đủ hàng trước khi xuất kho

**Testing**:

- [ ] Test `adjustStock()` với số lượng mới hợp lệ
- [ ] Test `adjustStock()` với số lượng âm → Exception
- [ ] Test `adjustStock()` với reason rỗng → Exception
- [ ] Test `importStock()` cập nhật inventory đúng và ghi log
- [ ] Test `exportStock()` với tồn kho đủ
- [ ] Test `exportStock()` với tồn kho không đủ → Exception
- [ ] Test transaction rollback khi có lỗi

#### **Task 3.2: Tạo StockTransactionService.php**

**File**: `src/modules/inventory/services/StockTransactionService.php`

**Namespace**: `Modules\Inventory\Services`

**Methods**:

- [ ] `getVariantHistory(int $variantId, string $warehouse, int $limit)` - Lịch sử variant
- [ ] `getProductHistory(int $productId, string $warehouse, int $limit)` - Lịch sử product
- [ ] `getTransactions(array $filters, int $page, int $perPage)` - Danh sách giao dịch với phân trang
- [ ] `countTransactions(array $filters)` - Đếm số lượng
- [ ] `getTransactionStats(string $fromDate, string $toDate)` - Thống kê

**Testing**:

- [ ] Test `getTransactions()` với filter by type
- [ ] Test `getTransactions()` với filter by date range
- [ ] Test pagination đúng

---

### **PHASE 4: CONTROLLERS - ROUTING LAYER (⏱️ 1-2 giờ)**

#### **Task 4.1: Tạo InventoryController.php**

**File**: `src/modules/inventory/controllers/InventoryController.php`

**Namespace**: `Modules\Inventory\Controllers`

**Routes cần implement**:

- [ ] `GET /admin/inventory` → `index()` - Danh sách tồn kho
- [ ] `GET /admin/inventory/low-stock` → `lowStock()` - Sản phẩm sắp hết
- [ ] `GET /admin/inventory/out-of-stock` → `outOfStock()` - Sản phẩm hết hàng
- [ ] `GET /admin/inventory/detail/{productId}` → `detail()` - Chi tiết product (tất cả variants)
- [ ] `GET /admin/inventory/adjust/{variantId}` → `adjustForm()` - Form điều chỉnh
- [ ] `POST /admin/inventory/adjust/{variantId}` → `adjust()` - Xử lý điều chỉnh
- [ ] `GET /admin/inventory/history/{variantId}` → `history()` - Lịch sử variant
- [ ] `POST /admin/inventory/update-threshold/{variantId}` → `updateThreshold()` - Cập nhật ngưỡng

**Controller Rules** (theo CODING_RULES.md):

- [ ] **Chỉ xử lý request/response** - Không có business logic
- [ ] **Gọi Service** - Mọi logic nằm trong Service
- [ ] **Validate input** - Kiểm tra request data
- [ ] **Handle exceptions** - Try-catch và hiển thị flash message
- [ ] **Redirect** - Sau khi xử lý xong

**Testing**:

- [ ] Test `index()` hiển thị đúng danh sách
- [ ] Test `adjustForm()` load đúng form với data
- [ ] Test `adjust()` với input hợp lệ → Success flash + redirect
- [ ] Test `adjust()` với input không hợp lệ → Error flash + redirect back

---

### **PHASE 5: VIEWS - PRESENTATION LAYER (⏱️ 2-3 giờ)**

#### **Task 5.1: Tạo stock_list.php**

**File**: `src/views/admin/inventory/stock_list.php`

**Chức năng**:

- [ ] Hiển thị danh sách tồn kho theo **Product Level** (tổng số lượng tất cả variants)
- [ ] Filter: Warehouse, Stock Status (Low/Out/All), Search
- [ ] Hiển thị: Product Name, SKU, Total Quantity, Stock Status, Actions
- [ ] Badge màu:
  - 🟢 In Stock (xanh)
  - 🟡 Low Stock (vàng)
  - 🔴 Out of Stock (đỏ)
- [ ] Action buttons: View Detail, Adjust Stock
- [ ] Pagination

**Design**:

- [ ] Dùng Bootstrap table responsive
- [ ] Font Awesome icons: `fa-warehouse`, `fa-box`, `fa-exclamation-triangle`
- [ ] Stats cards ở trên: Total Products, Total Quantity, Low Stock Count, Out of Stock Count

#### **Task 5.2: Tạo stock_detail.php**

**File**: `src/views/admin/inventory/stock_detail.php`

**Chức năng**:

- [ ] Hiển thị thông tin product
- [ ] Hiển thị danh sách **TẤT CẢ VARIANTS** với số lượng tồn kho
- [ ] Mỗi variant có: SKU, Attributes (màu, size...), Quantity, Min Threshold, Warehouse
- [ ] Action: Adjust Stock cho từng variant
- [ ] Lịch sử gần đây (10 transactions gần nhất)

#### **Task 5.3: Tạo low_stock.php**

**File**: `src/views/admin/inventory/low_stock.php`

**Chức năng**:

- [ ] Danh sách sản phẩm có `quantity <= min_threshold`
- [ ] Sắp xếp theo mức độ nghiêm trọng (quantity - min_threshold ASC)
- [ ] Highlight màu đỏ
- [ ] Action: Adjust Stock, View Purchase Order

#### **Task 5.4: Tạo adjust_stock.php**

**File**: `src/views/admin/inventory/adjust_stock.php`

**Chức năng**:

- [ ] Form điều chỉnh tồn kho
- [ ] Hiển thị số lượng hiện tại
- [ ] Input: New Quantity, Reason (required)
- [ ] Tính và hiển thị chênh lệch (difference)
- [ ] Button: Save, Cancel

**Validation**:

- [ ] New Quantity >= 0
- [ ] Reason không được rỗng

#### **Task 5.5: Tạo stock_history.php**

**File**: `src/views/admin/inventory/stock_history.php`

**Chức năng**:

- [ ] Hiển thị lịch sử xuất nhập kho
- [ ] Filter: Type (Import/Export/Adjust), Date Range, Warehouse
- [ ] Mỗi record có: Date, Type, Quantity Change, Reference, Note, Created By
- [ ] Icon theo type:
  - 📥 Import (xanh)
  - 📤 Export (đỏ)
  - ⚙️ Adjust (xám)
- [ ] Pagination

---

### **PHASE 6: ROUTES & INTEGRATION (⏱️ 1 giờ)**

#### **Task 6.1: Cập nhật routes.php**

**File**: `config/routes.php`

```php
// ============ INVENTORY ROUTES (Tồn kho) ============
$router->get('/admin/inventory', 'Modules\Inventory\Controllers\InventoryController@index', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/inventory/low-stock', 'Modules\Inventory\Controllers\InventoryController@lowStock', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/inventory/out-of-stock', 'Modules\Inventory\Controllers\InventoryController@outOfStock', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/inventory/detail/{id}', 'Modules\Inventory\Controllers\InventoryController@detail', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/inventory/adjust/{id}', 'Modules\Inventory\Controllers\InventoryController@adjustForm', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/inventory/adjust/{id}', 'Modules\Inventory\Controllers\InventoryController@adjust', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->get('/admin/inventory/history/{id}', 'Modules\Inventory\Controllers\InventoryController@history', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
$router->post('/admin/inventory/update-threshold/{id}', 'Modules\Inventory\Controllers\InventoryController@updateThreshold', [AuthMiddleware::class, AdminOnlyMiddleware::class]);
```

- [ ] Thêm 8 routes vào `routes.php`
- [ ] Test từng route bằng browser/Postman

#### **Task 6.2: Cập nhật sidebar**

**File**: `src/views/admin/layout/sidebar.php`

```php
<!-- Menu Quản lý kho -->
<li class="nav-item">
    <a class="nav-link <?= $isInventoryMenuActive ? '' : 'collapsed' ?>"
       href="#"
       data-bs-toggle="collapse"
       data-bs-target="#inventoryMenu">
        <i class="fas fa-warehouse"></i>
        <span>Quản lý kho</span>
        <?php if ($lowStockCount > 0): ?>
            <span class="badge bg-danger ms-auto"><?= $lowStockCount ?></span>
        <?php endif; ?>
    </a>
    <ul id="inventoryMenu" class="nav-content collapse <?= $isInventoryMenuActive ? 'show' : '' ?>">
        <li>
            <a href="/admin/inventory">
                <i class="bi bi-circle"></i><span>Tồn kho</span>
            </a>
        </li>
        <li>
            <a href="/admin/inventory/low-stock">
                <i class="bi bi-circle"></i><span>Sắp hết hàng</span>
                <?php if ($lowStockCount > 0): ?>
                    <span class="badge bg-warning"><?= $lowStockCount ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li>
            <a href="/admin/inventory/out-of-stock">
                <i class="bi bi-circle"></i><span>Hết hàng</span>
                <?php if ($outOfStockCount > 0): ?>
                    <span class="badge bg-danger"><?= $outOfStockCount ?></span>
                <?php endif; ?>
            </a>
        </li>
    </ul>
</li>
```

- [ ] Thêm menu "Quản lý kho" vào sidebar
- [ ] Badge cảnh báo hiển thị số lượng sản phẩm sắp hết/hết hàng
- [ ] Active state khi đang ở trang inventory

#### **Task 6.3: Cập nhật Dashboard**

**File**: `src/modules/dashboard/services/DashboardService.php`

- [ ] Thêm method `getLowStockCount()` để lấy số lượng sản phẩm sắp hết
- [ ] Hiển thị widget "Low Stock Alert" trên dashboard
- [ ] Link đến `/admin/inventory/low-stock`

---

### **PHASE 7: TESTING (⏱️ 2-3 giờ)** ⭐⭐⭐

#### **Task 7.1: Unit Testing - Models**

- [ ] **InventoryModel**:

  - [ ] Test `getVariantStock()` với variant_id hợp lệ/không hợp lệ
  - [ ] Test `updateStock()` với số dương/âm
  - [ ] Test `setStock()` overwrite số lượng đúng
  - [ ] Test `getLowStockProducts()` filter đúng điều kiện

- [ ] **InventoryTransactionModel**:
  - [ ] Test `recordTransaction()` với đầy đủ fields
  - [ ] Test `getVariantHistory()` return đúng order
  - [ ] Test `getTransactionStats()` aggregate đúng

#### **Task 7.2: Integration Testing - Services**

- [ ] **InventoryService**:

  - [ ] Test `adjustStock()`:

    - [ ] Input hợp lệ → Cập nhật inventory + Ghi log
    - [ ] Input không hợp lệ → Throw exception
    - [ ] Transaction rollback khi có lỗi

  - [ ] Test `importStock()`:

    - [ ] Số lượng tăng đúng
    - [ ] Log type='import' được tạo
    - [ ] Reference đúng

  - [ ] Test `exportStock()`:
    - [ ] Tồn kho đủ → Trừ thành công
    - [ ] Tồn kho không đủ → Exception
    - [ ] Log type='export' được tạo

- [ ] **StockTransactionService**:
  - [ ] Test `getTransactions()` với filter
  - [ ] Test pagination

#### **Task 7.3: E2E Testing - User Flow**

**Scenario 1: Xem danh sách tồn kho**

- [ ] Login → Vào /admin/inventory
- [ ] Danh sách hiển thị đúng
- [ ] Filter by stock status hoạt động
- [ ] Search hoạt động
- [ ] Pagination hoạt động

**Scenario 2: Điều chỉnh tồn kho**

- [ ] Vào /admin/inventory/adjust/{variantId}
- [ ] Form hiển thị số lượng hiện tại đúng
- [ ] Nhập số lượng mới + lý do
- [ ] Submit → Success flash message
- [ ] Số lượng cập nhật đúng
- [ ] Lịch sử ghi nhận đúng

**Scenario 3: Xem sản phẩm sắp hết hàng**

- [ ] Vào /admin/inventory/low-stock
- [ ] Danh sách hiển thị đúng (quantity <= min_threshold)
- [ ] Sắp xếp đúng theo mức độ nghiêm trọng
- [ ] Badge cảnh báo hiển thị

**Scenario 4: Xem lịch sử**

- [ ] Vào /admin/inventory/history/{variantId}
- [ ] Lịch sử hiển thị đúng thứ tự (mới nhất trước)
- [ ] Filter by type hoạt động
- [ ] Filter by date range hoạt động

#### **Task 7.4: Test tích hợp với Purchase Module**

_(Làm sau khi hoàn thành Purchase Module)_

- [ ] Tạo Purchase Order
- [ ] Complete Purchase Order
- [ ] Verify inventory tăng đúng
- [ ] Verify log type='import' được tạo

---

### **PHASE 8: DOCUMENTATION & CLEANUP (⏱️ 1 giờ)**

#### **Task 8.1: Viết PHPDoc**

- [ ] Tất cả class có PHPDoc header
- [ ] Tất cả public methods có PHPDoc với @param, @return, @throws
- [ ] Comment tiếng Việt cho logic phức tạp

#### **Task 8.2: Update composer autoload**

```bash
composer dump-autoload
```

- [ ] Chạy `composer dump-autoload`
- [ ] Test autoload hoạt động

#### **Task 8.3: Tạo CHANGELOG**

**File**: `docs/INVENTORY_CHANGELOG.md`

- [ ] Ghi lại những gì đã implement
- [ ] Ghi lại changes so với requirement ban đầu
- [ ] Ghi lại known issues (nếu có)

#### **Task 8.4: Update README**

- [ ] Thêm section "Inventory Module" vào README.md
- [ ] Hướng dẫn sử dụng cơ bản
- [ ] Screenshot (nếu có)

---

### **PHASE 9: CODE REVIEW & MERGE (⏱️ 1 giờ)**

#### **Task 9.1: Self Review**

- [ ] **CODING_RULES.md Compliance**:

  - [ ] Namespace đúng PSR-4
  - [ ] Controllers chỉ có routing logic
  - [ ] Business logic trong Service
  - [ ] Comments tiếng Việt
  - [ ] PHPDoc đầy đủ

- [ ] **Code Quality**:

  - [ ] Không có code duplicate
  - [ ] Không có hard code
  - [ ] Error handling đầy đủ
  - [ ] Variable naming rõ ràng

- [ ] **Security**:
  - [ ] Sử dụng prepared statements
  - [ ] Input validation
  - [ ] Middleware authentication

#### **Task 9.2: Commit & Push**

```bash
# Add tất cả files
git add src/modules/inventory/
git add src/views/admin/inventory/
git add config/routes.php
git add docs/INVENTORY_TODOLIST.md
git add docs/INVENTORY_CHANGELOG.md

# Commit với message rõ ràng
git commit -m "feat(inventory): Implement inventory management module

- Add InventoryModel and InventoryTransactionModel
- Add InventoryService and StockTransactionService
- Add InventoryController with 8 routes
- Add 5 views (stock_list, stock_detail, low_stock, adjust_stock, stock_history)
- Add sidebar menu with low stock badge
- Add unit tests and integration tests
- Update routes.php
- Update composer autoload

Refs: #INVENTORY-001"

# Push lên remote
git push origin feature/inventory-management
```

- [ ] Commit với message rõ ràng
- [ ] Push lên remote branch

#### **Task 9.3: Tạo Pull Request**

- [ ] Tạo PR: `feature/inventory-management` → `develop`
- [ ] Title: `[Feature] Inventory Management Module`
- [ ] Description:

  - Mô tả chức năng
  - Checklist tasks đã hoàn thành
  - Screenshots
  - Testing notes

- [ ] Assign reviewer (nếu có)
- [ ] Link TODOLIST và CHANGELOG

#### **Task 9.4: Merge & Deploy**

_(Sau khi review và approve)_

```bash
# Merge vào develop
git checkout develop
git merge feature/inventory-management

# Push develop
git push origin develop

# Tag version (optional)
git tag -a v1.1.0-inventory -m "Add inventory management module"
git push origin v1.1.0-inventory

# Xóa feature branch (nếu không cần nữa)
git branch -d feature/inventory-management
git push origin --delete feature/inventory-management
```

- [ ] Merge vào develop
- [ ] Tag version (optional)
- [ ] Deploy lên staging/production

---

## ✅ CHECKLIST CUỐI CÙNG

### **Code Compliance**:

- [ ] ✅ Tuân thủ 100% CODING_RULES.md
- [ ] ✅ MVC Pattern đúng chuẩn
- [ ] ✅ Service Layer tách biệt
- [ ] ✅ Namespace PSR-4
- [ ] ✅ PHPDoc đầy đủ
- [ ] ✅ Comments tiếng Việt

### **Functionality**:

- [ ] ✅ Theo dõi tồn kho (Product + Variant level)
- [ ] ✅ Cảnh báo Low Stock / Out of Stock
- [ ] ✅ Lịch sử xuất nhập kho đầy đủ
- [ ] ✅ Điều chỉnh tồn kho thủ công
- [ ] ✅ Tích hợp với Purchase Module (API sẵn sàng)

### **Testing**:

- [ ] ✅ Unit tests pass
- [ ] ✅ Integration tests pass
- [ ] ✅ E2E tests pass
- [ ] ✅ Không có bug critical

### **Documentation**:

- [ ] ✅ PHPDoc đầy đủ
- [ ] ✅ TODOLIST hoàn thành
- [ ] ✅ CHANGELOG được tạo
- [ ] ✅ README updated

### **Deployment**:

- [ ] ✅ Composer autoload updated
- [ ] ✅ Database migration chạy thành công
- [ ] ✅ Routes hoạt động
- [ ] ✅ Sidebar menu hiển thị

---

## 📊 PROGRESS TRACKING

| Phase     | Task                 | Status  | Time       | Note               |
| --------- | -------------------- | ------- | ---------- | ------------------ |
| 1         | Chuẩn bị & Setup     | ⬜ TODO | 0.5h       |                    |
| 2         | Models               | ⬜ TODO | 2-3h       |                    |
| 3         | Services             | ⬜ TODO | 3-4h       | ⭐ Quan trọng nhất |
| 4         | Controllers          | ⬜ TODO | 1-2h       |                    |
| 5         | Views                | ⬜ TODO | 2-3h       |                    |
| 6         | Routes & Integration | ⬜ TODO | 1h         |                    |
| 7         | Testing              | ⬜ TODO | 2-3h       | ⭐ Quan trọng      |
| 8         | Documentation        | ⬜ TODO | 1h         |                    |
| 9         | Review & Merge       | ⬜ TODO | 1h         |                    |
| **TOTAL** |                      | **⬜**  | **12-15h** | **2-3 ngày**       |

**Legend**: ⬜ TODO | 🟡 IN PROGRESS | ✅ DONE | ❌ BLOCKED

---

## 🚨 DEPENDENCIES & BLOCKERS

### **Dependencies**:

1. **product_variants table** phải có dữ liệu

   - Nếu chưa có, cần tạo variants cho products trước
   - Hoặc tạm thời dùng dummy data

2. **ProductVariantModel** phải tồn tại

   - Path: `src/modules/product/models/ProductVariantModel.php`
   - Nếu chưa có, cần tạo trước

3. **AuthHelper** phải có method `getUserId()`
   - Để lấy user_id cho created_by

### **Potential Blockers**:

- [ ] Database schema chưa match với `Database.md`
- [ ] ProductVariantModel chưa được tạo
- [ ] Middleware chưa hoạt động
- [ ] Composer autoload issue

---

## 📝 NOTES

### **Lưu ý quan trọng**:

1. **Transaction là BẮT BUỘC**:

   - Mọi thay đổi inventory phải wrap trong transaction
   - Rollback nếu có lỗi

2. **Logging là BẮT BUỘC**:

   - Mọi thay đổi inventory phải ghi vào `inventory_transactions`
   - Giúp audit trail và debug

3. **Validation là BẮT BUỘC**:

   - Số lượng không được âm
   - Reference phải hợp lệ
   - User phải có quyền

4. **Performance**:

   - Aggregate queries có thể chậm nếu nhiều variants
   - Xem xét index cho `product_variant_id`, `warehouse`
   - Cache thống kê nếu cần

5. **Future Enhancement**:
   - Multi-warehouse support (đã có sẵn trong schema)
   - Batch import/export
   - Excel export
   - Barcode scanning

---

## 🎯 SUCCESS CRITERIA

Module được coi là **HOÀN THÀNH** khi:

1. ✅ Tất cả 9 phases đã complete
2. ✅ Tất cả tests pass (unit + integration + E2E)
3. ✅ Code review approved
4. ✅ Merged vào develop
5. ✅ Documentation đầy đủ
6. ✅ Demo thành công cho stakeholder

---

## 🔗 RELATED DOCUMENTS

- [`CODING_RULES.md`](CODING_RULES.md) - Quy tắc code bắt buộc
- [`Database.md`](Database.md) - Database schema
- [`REFACTOR_TODOLIST.md`](REFACTOR_TODOLIST.md) - Refactor tasks tổng thể
- `INVENTORY_CHANGELOG.md` - Changelog (sẽ tạo sau)

---

## 👥 TEAM & OWNERSHIP

**Module Owner**: [Tên bạn]  
**Reviewer**: [Tên reviewer]  
**Estimated Start**: **/**/2025  
**Estimated End**: **/**/2025  
**Actual Start**: **/**/2025  
**Actual End**: **/**/2025

---

**Ngày cập nhật cuối**: 10/11/2025  
**Version**: 1.0

---

## 💬 QUESTIONS & ANSWERS

**Q: Tại sao theo dõi theo variant thay vì product?**  
A: Schema hiện tại theo `product_variant_id`, cho phép quản lý chi tiết từng SKU. Service layer sẽ aggregate để hiển thị product level.

**Q: Multi-warehouse có cần implement ngay không?**  
A: Không, hiện tại dùng `warehouse='default'`. Schema hỗ trợ sẵn, có thể mở rộng sau.

**Q: Làm thế nào để test transaction rollback?**  
A: Mock exception trong quá trình update, verify inventory không thay đổi và transaction không ghi log.

**Q: Import/Export stock có cần permission riêng không?**  
A: Hiện tại dùng `AdminOnlyMiddleware`. Nếu cần phân quyền chi tiết hơn, tạo `InventoryManagerMiddleware`.

---

**END OF TODOLIST** 🎉
