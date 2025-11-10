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
        $perPage = 50;

        // Lấy bộ lọc
        $filters = [
            'warehouse' => $this->input('warehouse'),
            'stock_status' => $this->input('stock_status'),
            'search' => $this->input('search')
        ];

        try {
            $result = $this->inventoryService->getStockList($filters, $page, $perPage);
            $stats = $this->inventoryService->getLowStockAlerts();

            $this->view('admin/inventory/stock_list', [
                'title' => 'Quản lý Tồn Kho',
                'products' => $result['data'],
                'pagination' => $result['pagination'],
                'filters' => $filters,
                'stats' => $stats,
                'currentPage' => 'inventory'
            ]);
        } catch (Exception $e) {
            error_log('[Inventory] List Error: ' . $e->getMessage());
            $this->redirect('/admin/dashboard?error=' . urlencode('Lỗi tải danh sách tồn kho'));
        }
    }

    /**
     * Route 2: GET /admin/inventory/detail
     * Hiển thị chi tiết tồn kho của 1 variant
     */
    public function detail(): void
    {
        $variantId = (int) $this->input('id');
        $warehouse = $this->input('warehouse', 'default');

        if (!$variantId) {
            $this->redirect('/admin/inventory?error=' . urlencode('Thiếu ID variant'));
            return;
        }

        try {
            $stockInfo = $this->inventoryService->getStockInfo($variantId, $warehouse);
            $history = $this->transactionService->getVariantTransactionHistory($variantId, $warehouse, 50);

            $this->view('admin/inventory/stock_detail', [
                'title' => 'Chi Tiết Tồn Kho',
                'variantId' => $variantId,
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
     * Route 4: GET /admin/inventory/adjust
     * Hiển thị form điều chỉnh tồn kho
     */
    public function adjustForm(): void
    {
        $variantId = (int) $this->input('variant_id');
        $warehouse = $this->input('warehouse', 'default');

        if (!$variantId) {
            $this->redirect('/admin/inventory?error=' . urlencode('Thiếu ID variant'));
            return;
        }

        try {
            $stockInfo = $this->inventoryService->getStockInfo($variantId, $warehouse);

            $this->view('admin/inventory/adjust_stock', [
                'title' => 'Điều Chỉnh Tồn Kho',
                'variantId' => $variantId,
                'stockInfo' => $stockInfo,
                'warehouse' => $warehouse,
                'currentPage' => 'inventory'
            ]);
        } catch (Exception $e) {
            error_log('[Inventory] Adjust Form Error: ' . $e->getMessage());
            $this->redirect('/admin/inventory?error=' . urlencode('Lỗi tải form điều chỉnh'));
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
        $newQuantity = (int) $this->input('new_quantity');
        $warehouse = $this->input('warehouse', 'default');
        $reason = trim($this->input('reason', ''));

        if (!$variantId || $newQuantity < 0 || empty($reason)) {
            $this->error('Dữ liệu không hợp lệ. Vui lòng điền đầy đủ thông tin.', 400);
            return;
        }

        try {
            $userId = AuthHelper::id();

            $result = $this->inventoryService->adjustStock(
                $variantId,
                $newQuantity,
                $userId,
                $warehouse,
                $reason
            );

            // Log activity
            LogHelper::log(
                "Điều chỉnh tồn kho: {$result['old_stock']} → {$result['new_stock']}",
                'inventory',
                $variantId,
                ['warehouse' => $warehouse, 'reason' => $reason]
            );

            $this->success($result, $result['message']);
        } catch (Exception $e) {
            error_log('[Inventory] Adjust Error: ' . $e->getMessage());
            $this->error('Lỗi điều chỉnh tồn kho: ' . $e->getMessage(), 500);
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
     * Route 10: POST /admin/inventory/update-threshold
     * Cập nhật ngưỡng cảnh báo
     */
    public function updateThreshold(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $variantId = (int) $this->input('variant_id');
        $minThreshold = (int) $this->input('min_threshold');
        $warehouse = $this->input('warehouse', 'default');

        if (!$variantId || $minThreshold < 0) {
            $this->error('Dữ liệu không hợp lệ', 400);
            return;
        }

        try {
            $result = $this->inventoryService->updateThresholds($variantId, $minThreshold, $warehouse);

            if ($result) {
                LogHelper::log(
                    "Cập nhật ngưỡng cảnh báo: {$minThreshold}",
                    'inventory',
                    $variantId,
                    ['warehouse' => $warehouse]
                );

                $this->success(null, 'Cập nhật ngưỡng cảnh báo thành công');
            } else {
                throw new Exception('Không thể cập nhật ngưỡng');
            }
        } catch (Exception $e) {
            error_log('[Inventory] Update Threshold Error: ' . $e->getMessage());
            $this->error('Lỗi cập nhật ngưỡng: ' . $e->getMessage(), 500);
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

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="inventory_report_' . date('Y-m-d') . '.csv"');

            echo $csv;
            exit;
        } catch (Exception $e) {
            error_log('[Inventory] Export Report Error: ' . $e->getMessage());
            $this->redirect('/admin/inventory/history?error=' . urlencode('Lỗi xuất báo cáo'));
        }
    }
}
