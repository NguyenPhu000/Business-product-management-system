<?php

namespace Modules\Inventory\Controllers;

use Core\Controller;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Modules\Inventory\Services\InventoryService;
use Modules\Inventory\Services\StockTransactionService;
use Exception;

/**
 * InventoryController - Quản lý tồn kho (theo MVC Pattern)
 * Controller chỉ xử lý routing, validation, gọi service và trả về view
 */
class InventoryController extends Controller
{
    private InventoryService $inventoryService;
    private StockTransactionService $transactionService;

    public function __construct()
    {
        $this->inventoryService = new InventoryService();
        $this->transactionService = new StockTransactionService();
    }

    /**
     * Route 1: GET /admin/inventory
     * Hiển thị danh sách tồn kho
     */
    public function index(): void
    {
        $page = (int) ($this->input('page') ?? 1);
        $perPage = (int) ($this->input('per_page') ?? 50);

        // Validate per_page (allow 25, 50, 100, 200)
        if (!in_array($perPage, [25, 50, 100, 200])) {
            $perPage = 50;
        }

        // Lấy bộ lọc mở rộng
        $filters = [
            'warehouse' => $this->input('warehouse'),
            'stock_status' => $this->input('stock_status'),
            'search' => $this->input('search'),
            'category_id' => $this->input('category_id'),
            'brand_id' => $this->input('brand_id'),
            'quantity_min' => $this->input('quantity_min'),
            'quantity_max' => $this->input('quantity_max'),
            'sort_by' => $this->input('sort_by', 'last_updated'),
            'sort_order' => $this->input('sort_order', 'DESC')
        ];

        try {
            $result = $this->inventoryService->getStockList($filters, $page, $perPage);
            $stats = $this->inventoryService->getLowStockAlerts();

            // Get categories and brands for filters
            $categories = [];
            $brands = [];

            try {
                $categoryModel = new \Modules\Category\Models\CategoryModel();
                $categories = $categoryModel->getActiveCategories();
            } catch (Exception $catError) {
                error_log('[Inventory] Category Error: ' . $catError->getMessage());
            }

            try {
                $brandModel = new \Modules\Category\Models\BrandModel();
                $brands = $brandModel->getActiveBrands();
            } catch (Exception $brandError) {
                error_log('[Inventory] Brand Error: ' . $brandError->getMessage());
            }

            $this->view('admin/inventory/stock_list', [
                'title' => 'Quản lý Tồn Kho',
                'products' => $result['data'],
                'pagination' => $result['pagination'],
                'filters' => $filters,
                'stats' => $stats,
                'categories' => $categories,
                'brands' => $brands,
                'perPage' => $perPage,
                'currentPage' => 'inventory'
            ]);
        } catch (Exception $e) {
            error_log('[Inventory] List Error: ' . $e->getMessage());
            error_log('[Inventory] Stack trace: ' . $e->getTraceAsString());
            $this->redirect('/admin/dashboard?error=' . urlencode('Lỗi tải danh sách tồn kho: ' . $e->getMessage()));
        }
    }

    /**
     * Route 2: GET /admin/inventory/detail/{id}
     * Hiển thị chi tiết tồn kho của 1 variant
     */
    public function detail(int $id): void
    {
        // Use canonical warehouse key 'default' as the system now stores warehouses with this name
        $warehouse = $this->input('warehouse', 'default');

        try {
            // Get variant with product info
            $variantModel = new \Modules\Product\Models\VariantModel();
            $variant = $variantModel->getWithProduct($id);

            if (!$variant) {
                AuthHelper::setFlash('error', 'Không tìm thấy variant');
                $this->redirect('/admin/inventory');
                return;
            }

            // Get product info
            $productModel = new \Modules\Product\Models\ProductModel();
            $product = $productModel->find($variant['product_id']);

            // Get stock info and history (wrap in array for view compatibility)
            $stockData = $this->inventoryService->getStockInfo($id, $warehouse);
            $stockInfo = [$stockData]; // Wrap in array vì view expect array
            $history = $this->transactionService->getVariantTransactionHistory($id, $warehouse, 50);

            $this->view('admin/inventory/stock_detail', [
                'title' => 'Chi Tiết Tồn Kho',
                'variantId' => $id,
                'variant' => $variant,
                'product' => $product,
                'stockInfo' => $stockInfo,
                'history' => $history,
                'warehouse' => $warehouse,
                'currentPage' => 'inventory'
            ]);
        } catch (Exception $e) {
            error_log('[Inventory] Detail Error: ' . $e->getMessage());
            $this->redirect('/admin/inventory?error=' . urlencode('Lỗi tải chi tiết tồn kho'));
        }
    }

    /**
     * Route 3: GET /admin/inventory/low-stock
     * Hiển thị danh sách cảnh báo sắp hết hàng
     */
    public function lowStock(): void
    {
        $limit = (int) ($this->input('limit') ?? 100);

        try {
            $alerts = $this->inventoryService->getLowStockAlerts($limit);

            $this->view('admin/inventory/low_stock', [
                'title' => 'Cảnh Báo Sắp Hết Hàng',
                'lowStockProducts' => $alerts['low_stock'],
                'outOfStockProducts' => $alerts['out_of_stock'],
                'totalAlerts' => $alerts['total_alerts'],
                'currentPage' => 'inventory'
            ]);
        } catch (Exception $e) {
            error_log('[Inventory] Low Stock Error: ' . $e->getMessage());
            $this->redirect('/admin/inventory?error=' . urlencode('Lỗi tải cảnh báo'));
        }
    }

    /**
     * Route 4: GET /admin/inventory/adjust/{id}
     * Hiển thị form điều chỉnh tồn kho
     */
    public function adjustForm(int $id): void
    {
        $warehouse = $this->input('warehouse', 'default');

        try {
            // Get variant with product info
            $variantModel = new \Modules\Product\Models\VariantModel();
            $variant = $variantModel->getWithProduct($id);

            if (!$variant) {
                AuthHelper::setFlash('error', 'Không tìm thấy variant');
                $this->redirect('/admin/inventory');
                return;
            }

            // Get product info
            $productModel = new \Modules\Product\Models\ProductModel();
            $product = $productModel->find($variant['product_id']);

            // Get inventory for single warehouse (wrap in array for compatibility)
            $stockInfo = $this->inventoryService->getStockInfo($id, $warehouse);
            $inventory = [$stockInfo]; // Wrap in array vì view expect array of inventories

            $this->view('admin/inventory/adjust_stock', [
                'title' => 'Điều Chỉnh Tồn Kho',
                'variantId' => $id,
                'variant' => $variant,
                'product' => $product,
                'inventory' => $inventory,
                'warehouse' => $warehouse,
                'currentPage' => 'inventory'
            ]);
        } catch (Exception $e) {
            error_log('[Inventory] Adjust Form Error: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Lỗi tải form điều chỉnh: ' . $e->getMessage());
            $this->redirect('/admin/inventory');
        }
    }

    /**
     * Route 5: POST /admin/inventory/adjust
     * Xử lý điều chỉnh tồn kho
     */
    public function adjust(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $variantId = (int) $this->input('variant_id');
        $type = $this->input('type'); // import, export, adjust
        $quantity = (int) $this->input('quantity');
    $warehouse = $this->input('warehouse', 'default');
        $note = trim($this->input('note', ''));

        if (!$variantId || !$type || $quantity <= 0 || empty($note)) {
            AuthHelper::setFlash('error', 'Dữ liệu không hợp lệ. Vui lòng điền đầy đủ thông tin.');
            $this->redirect('/admin/inventory/adjust/' . $variantId);
            return;
        }

        try {
            $userId = AuthHelper::id();
            $result = null;

            // Execute operation based on type
            switch ($type) {
                case 'import':
                    $result = $this->inventoryService->importStock(
                        $variantId,
                        $quantity,
                        $userId,
                        $warehouse,
                        ['type' => 'manual', 'id' => null],
                        $note
                    );
                    break;

                case 'export':
                    $result = $this->inventoryService->exportStock(
                        $variantId,
                        $quantity,
                        $userId,
                        $warehouse,
                        ['type' => 'manual', 'id' => null],
                        $note
                    );
                    break;

                case 'adjust':
                    // For adjust, get current stock first
                    $currentStock = $this->inventoryService->getStockInfo($variantId, $warehouse);
                    $currentQty = 0;
                    foreach ($currentStock as $stock) {
                        if ($stock['warehouse'] === $warehouse) {
                            $currentQty = $stock['quantity'];
                            break;
                        }
                    }

                    $result = $this->inventoryService->adjustStock(
                        $variantId,
                        $quantity, // New quantity
                        $userId,
                        $warehouse,
                        $note
                    );
                    break;

                default:
                    throw new Exception('Loại điều chỉnh không hợp lệ');
            }

            // Log activity
            LogHelper::log(
                ucfirst($type) . " tồn kho: {$quantity} đơn vị",
                'inventory',
                $variantId,
                ['warehouse' => $warehouse, 'type' => $type, 'note' => $note]
            );

            AuthHelper::setFlash('success', $result['message'] ?? 'Điều chỉnh tồn kho thành công!');
            $this->redirect('/admin/inventory/detail/' . $variantId);
        } catch (Exception $e) {
            error_log('[Inventory] Adjust Error: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Lỗi điều chỉnh tồn kho: ' . $e->getMessage());
            $this->redirect('/admin/inventory/adjust/' . $variantId);
        }
    }

    /**
     * Route 6: GET /admin/inventory/history
     * Hiển thị lịch sử giao dịch kho
     */
    public function history(): void
    {
        $page = (int) ($this->input('page') ?? 1);
        $perPage = 50;

        $filters = [
            'warehouse' => $this->input('warehouse'),
            'type' => $this->input('type'),
            'from_date' => $this->input('from_date'),
            'to_date' => $this->input('to_date'),
            'search' => $this->input('search')
        ];

        try {
            $result = $this->transactionService->getTransactionHistory($filters, $page, $perPage);

            $this->view('admin/inventory/stock_history', [
                'title' => 'Lịch Sử Giao Dịch Kho',
                'transactions' => $result['data'],
                'pagination' => $result['pagination'],
                'filters' => $filters,
                'currentPage' => 'inventory'
            ]);
        } catch (Exception $e) {
            error_log('[Inventory] History Error: ' . $e->getMessage());
            $this->redirect('/admin/inventory?error=' . urlencode('Lỗi tải lịch sử'));
        }
    }

    /**
     * Route 7: POST /admin/inventory/import
     * Nhập kho (API endpoint)
     */
    public function import(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $variantId = (int) $this->input('variant_id');
        $quantity = (int) $this->input('quantity');
        $warehouse = $this->input('warehouse', 'default');
        $referenceType = $this->input('reference_type');
        $referenceId = (int) $this->input('reference_id');
        $note = trim($this->input('note', ''));

        if (!$variantId || $quantity <= 0) {
            $this->error('Dữ liệu không hợp lệ', 400);
            return;
        }

        try {
            $userId = AuthHelper::id();

            $result = $this->inventoryService->importStock(
                $variantId,
                $quantity,
                $userId,
                $warehouse,
                ['type' => $referenceType, 'id' => $referenceId],
                $note
            );

            LogHelper::log(
                "Nhập kho: +{$quantity}",
                'inventory',
                $variantId,
                ['warehouse' => $warehouse]
            );

            $this->success($result, $result['message']);
        } catch (Exception $e) {
            error_log('[Inventory] Import Error: ' . $e->getMessage());
            $this->error('Lỗi nhập kho: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Route 8: POST /admin/inventory/export
     * Xuất kho (API endpoint)
     */
    public function export(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $variantId = (int) $this->input('variant_id');
        $quantity = (int) $this->input('quantity');
        $warehouse = $this->input('warehouse', 'default');
        $referenceType = $this->input('reference_type');
        $referenceId = (int) $this->input('reference_id');
        $note = trim($this->input('note', ''));

        if (!$variantId || $quantity <= 0) {
            $this->error('Dữ liệu không hợp lệ', 400);
            return;
        }

        try {
            $userId = AuthHelper::id();

            $result = $this->inventoryService->exportStock(
                $variantId,
                $quantity,
                $userId,
                $warehouse,
                ['type' => $referenceType, 'id' => $referenceId],
                $note
            );

            LogHelper::log(
                "Xuất kho: -{$quantity}",
                'inventory',
                $variantId,
                ['warehouse' => $warehouse]
            );

            $this->success($result, $result['message']);
        } catch (Exception $e) {
            error_log('[Inventory] Export Error: ' . $e->getMessage());
            $this->error('Lỗi xuất kho: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Route 9: POST /admin/inventory/transfer
     * Chuyển kho giữa các warehouse
     */
    public function transfer(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $variantId = (int) $this->input('variant_id');
        $quantity = (int) $this->input('quantity');
        $fromWarehouse = $this->input('from_warehouse');
        $toWarehouse = $this->input('to_warehouse');
        $note = trim($this->input('note', ''));

        if (!$variantId || $quantity <= 0 || !$fromWarehouse || !$toWarehouse) {
            $this->error('Dữ liệu không hợp lệ', 400);
            return;
        }

        try {
            $userId = AuthHelper::id();

            $result = $this->inventoryService->transferStock(
                $variantId,
                $quantity,
                $fromWarehouse,
                $toWarehouse,
                $userId,
                $note
            );

            LogHelper::log(
                "Chuyển kho: {$fromWarehouse} → {$toWarehouse}, SL: {$quantity}",
                'inventory',
                $variantId
            );

            $this->success($result, $result['message']);
        } catch (Exception $e) {
            error_log('[Inventory] Transfer Error: ' . $e->getMessage());
            $this->error('Lỗi chuyển kho: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Route 10: POST /admin/inventory/threshold/{id}
     * Cập nhật ngưỡng cảnh báo
     */
    public function updateThreshold(int $id): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

    $minThreshold = (int) $this->input('min_threshold');
    $warehouse = $this->input('warehouse', 'default');

        if ($minThreshold < 0) {
            $this->error('Dữ liệu không hợp lệ', 400);
            return;
        }

        try {
            $result = $this->inventoryService->updateThresholds($id, $minThreshold, $warehouse);

            if ($result) {
                LogHelper::log(
                    "Cập nhật ngưỡng cảnh báo: {$minThreshold}",
                    'inventory',
                    $id,
                    ['warehouse' => $warehouse]
                );

                AuthHelper::setFlash('success', 'Cập nhật ngưỡng cảnh báo thành công');
                $this->redirect("/admin/inventory/detail/{$id}");
            } else {
                throw new Exception('Không thể cập nhật ngưỡng');
            }
        } catch (Exception $e) {
            error_log('[Inventory] Update Threshold Error: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Lỗi cập nhật ngưỡng: ' . $e->getMessage());
            $this->redirect("/admin/inventory/detail/{$id}");
        }
    }

    /**
     * Route 11: GET /admin/inventory/report
     * Xuất báo cáo CSV
     */
    public function exportReport(): void
    {
        $filters = [
            'warehouse' => $this->input('warehouse'),
            'type' => $this->input('type'),
            'from_date' => $this->input('from_date'),
            'to_date' => $this->input('to_date')
        ];

        try {
            $csv = $this->transactionService->exportToCSV($filters);

            // Set headers cho file CSV với UTF-8 encoding
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="bao_cao_ton_kho_' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $csv;
            exit;
        } catch (Exception $e) {
            error_log('[Inventory] Export Report Error: ' . $e->getMessage());
            $this->redirect('/admin/inventory/history?error=' . urlencode('Lỗi xuất báo cáo'));
        }
    }
}
