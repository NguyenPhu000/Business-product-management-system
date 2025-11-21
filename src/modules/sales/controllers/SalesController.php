<?php
namespace Modules\Sales\Controllers;

use Core\Controller;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Modules\Sales\Services\SalesService;
use Modules\Category\Models\SupplierModel;
use Modules\Product\Models\VariantModel;
use Exception;

class SalesController extends Controller
{
    private SalesService $service;

    public function __construct()
    {
        $this->service = new SalesService();
    }

    /**
     * GET /admin/sales/create
     */
    public function create(): void
    {
        try {
            $supplierModel = new SupplierModel();
            $suppliers = $supplierModel->getActiveSuppliers();

            $variantModel = new VariantModel();
            $variants = $variantModel->all('id', 'ASC');

            $preselect = (int) $this->input('variant_id');

            $this->view('admin/sales/create', [
                'title' => 'Tạo Phiếu Xuất',
                'suppliers' => $suppliers,
                'variants' => $variants,
                'preselectVariant' => $preselect,
                'currentPage' => 'sales'
            ]);
        } catch (Exception $e) {
            error_log('[Sales] Create Form Error: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Lỗi tải form tạo phiếu xuất: ' . $e->getMessage());
            $this->redirect('/admin/inventory');
        }
    }

    /**
     * POST /admin/sales/store
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $supplierId = (int) $this->input('supplier_id');
        $saleDate = $this->input('sale_date') ?: date('Y-m-d');
        $note = trim($this->input('note', ''));

        $variantIds = $this->input('variant_id') ?: [];
        $quantities = $this->input('quantity') ?: [];
        $salePrices = $this->input('sale_price') ?: [];

        if (empty($variantIds) || !is_array($variantIds)) {
            AuthHelper::setFlash('error', 'Vui lòng thêm ít nhất 1 sản phẩm vào phiếu xuất.');
            $this->redirect('/admin/sales/create');
            return;
        }

        $items = [];
        foreach ($variantIds as $i => $vid) {
            $vid = (int) $vid;
            $qty = (int) ($quantities[$i] ?? 0);
            $price = isset($salePrices[$i]) ? (float) $salePrices[$i] : 0.0;

            if ($vid <= 0 || $qty <= 0) {
                continue;
            }

            $items[] = [
                'variant_id' => $vid,
                'quantity' => $qty,
                'sale_price' => $price
            ];
        }

        if (empty($items)) {
            AuthHelper::setFlash('error', 'Không có dòng hợp lệ trong phiếu xuất.');
            $this->redirect('/admin/sales/create');
            return;
        }

        try {
            $userId = AuthHelper::id();

            // If supplierId provided, fetch name to store as customer_name
            $supplierName = null;
            if ($supplierId) {
                try {
                    $sm = new SupplierModel();
                    $s = $sm->find($supplierId);
                    $supplierName = $s['name'] ?? null;
                } catch (Exception $e) {
                    $supplierName = null;
                }
            }

            $result = $this->service->createSale($supplierId ?: null, $items, $userId, $saleDate, $note, $supplierName);

            // Build log meta
            $metaItems = [];
            foreach ($items as $it) {
                $metaItems[] = [
                    'variant_id' => $it['variant_id'] ?? null,
                    'quantity' => $it['quantity'] ?? null,
                    'sale_price' => $it['sale_price'] ?? null
                ];
            }

            $meta = [
                'order_number' => $result['order_number'] ?? null,
                'supplier_id' => $supplierId ?: null,
                'supplier_name' => $supplierName,
                'sale_date' => $saleDate,
                'warehouse' => 'default',
                'items' => $metaItems
            ];

            LogHelper::log('Tạo phiếu xuất #' . ($result['order_number'] ?? ($result['sales_id'] ?? '')), 'sales', $result['sales_id'] ?? null, $meta);

            AuthHelper::setFlash('success', $result['message'] ?? 'Tạo phiếu xuất thành công');
            $this->redirect('/admin/inventory');
        } catch (Exception $e) {
            error_log('[Sales] Store Error: ' . $e->getMessage());
            AuthHelper::setFlash('error', 'Lỗi tạo phiếu xuất: ' . $e->getMessage());
            $this->redirect('/admin/sales/create');
        }
    }
}

