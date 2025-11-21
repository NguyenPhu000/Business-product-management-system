<?php
// PurchaseController.php - Quản lý đơn mua hàng (nhập kho)
namespace Modules\Purchase\Controllers;

use Core\Controller;
use Helpers\AuthHelper;
use Helpers\LogHelper;
use Modules\Purchase\Services\PurchaseService;
use Modules\Category\Models\SupplierModel;
use Modules\Product\Models\VariantModel;
use Exception;

/**
 * PurchaseController - minimal implementation for creating purchase orders
 */
class PurchaseController extends Controller
{
	private PurchaseService $service;

	public function __construct()
	{
		$this->service = new PurchaseService();
	}

	/**
	 * GET /admin/purchase/create
	 */
	public function create(): void
	{
		try {
			$supplierModel = new SupplierModel();
			$suppliers = $supplierModel->getActiveSuppliers();

			$variantModel = new VariantModel();
			$variants = $variantModel->all('id', 'ASC');

			$preselect = (int) $this->input('variant_id');

			$this->view('admin/purchase/create', [
				'title' => 'Tạo Phiếu Nhập',
				'suppliers' => $suppliers,
				'variants' => $variants,
				'preselectVariant' => $preselect,
				'currentPage' => 'purchase'
			]);
		} catch (Exception $e) {
			error_log('[Purchase] Create Form Error: ' . $e->getMessage());
			AuthHelper::setFlash('error', 'Lỗi tải form tạo phiếu nhập: ' . $e->getMessage());
			$this->redirect('/admin/inventory');
		}
	}

	/**
	 * POST /admin/purchase/store
	 */
	public function store(): void
	{
		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->error('Method not allowed', 405);
			return;
		}

		$supplierId = (int) $this->input('supplier_id');
		$purchaseDate = $this->input('purchase_date') ?: date('Y-m-d');
		$note = trim($this->input('note', ''));

	$variantIds = $this->input('variant_id') ?: [];
	$quantities = $this->input('quantity') ?: [];
	$importPrices = $this->input('import_price') ?: [];

	// Prepare variant model to fetch default unit_cost when import_price not provided
	$variantModel = new VariantModel();

		if (empty($variantIds) || !is_array($variantIds)) {
			AuthHelper::setFlash('error', 'Vui lòng thêm ít nhất 1 sản phẩm vào phiếu nhập.');
			$this->redirect('/admin/purchase/create');
			return;
		}

		$items = [];
		foreach ($variantIds as $i => $vid) {
			$vid = (int) $vid;
			$qty = (int) ($quantities[$i] ?? 0);
			$price = isset($importPrices[$i]) ? (float) $importPrices[$i] : null;

			if ($vid <= 0 || $qty <= 0) {
				continue;
			}

			// If no price provided, use variant's stored unit_cost
			if ($price === null || $price <= 0) {
				try {
					$v = $variantModel->find($vid);
					$price = isset($v['unit_cost']) ? (float) $v['unit_cost'] : 0.0;
				} catch (\Exception $e) {
					$price = 0.0;
				}
			}

			$items[] = [
				'variant_id' => $vid,
				'quantity' => $qty,
				'import_price' => $price
			];
		}

		if (empty($items)) {
			AuthHelper::setFlash('error', 'Không có dòng hợp lệ trong phiếu nhập.');
			$this->redirect('/admin/purchase/create');
			return;
		}

		try {
			$userId = AuthHelper::id();
			$result = $this->service->createPurchase($supplierId, $items, $userId, $purchaseDate, $note);

			// Build meta information for user log: po_number, supplier, purchase_date, items summary
			$metaItems = [];
			foreach ($items as $it) {
				$metaItems[] = [
					'variant_id' => $it['variant_id'] ?? null,
					'quantity' => $it['quantity'] ?? null,
					'import_price' => $it['import_price'] ?? null
				];
			}

			$meta = [
				'po_number' => $result['po_number'] ?? null,
				'supplier_id' => $supplierId ?: null,
				'purchase_date' => $purchaseDate,
				'warehouse' => 'default',
				'items' => $metaItems
			];

			LogHelper::log('Tạo phiếu nhập #' . ($result['po_number'] ?? ($result['purchase_id'] ?? '')), 'purchase', $result['purchase_id'] ?? null, $meta);

			AuthHelper::setFlash('success', $result['message'] ?? 'Tạo phiếu nhập thành công');
			$this->redirect('/admin/inventory');
		} catch (Exception $e) {
			error_log('[Purchase] Store Error: ' . $e->getMessage());
			AuthHelper::setFlash('error', 'Lỗi tạo phiếu nhập: ' . $e->getMessage());
			$this->redirect('/admin/purchase/create');
		}
	}
}
