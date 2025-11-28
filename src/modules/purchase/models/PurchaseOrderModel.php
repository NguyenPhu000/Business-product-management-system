<?php
// PurchaseOrderModel.php - Bảng purchase_orders
namespace Modules\Purchase\Models;

use Core\BaseModel;

class PurchaseOrderModel extends BaseModel
{
	protected string $table = 'purchase_orders';
	protected string $primaryKey = 'id';

	/**
	 * Tạo PO mới
	 * @param array $data
	 * @return int ID
	 */
	public function createOrder(array $data): int
	{
		return $this->create($data);
	}

	public function updateTotal(int $id, float $total): bool
	{
		return $this->update($id, ['total_amount' => $total]);
	}

	public function generatePONumber(): string
	{
		$prefix = 'PO' . date('Ymd');
		// Simple increment based on count today
		$sql = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE DATE(created_at) = ?";
		$row = $this->queryOne($sql, [date('Y-m-d')]);
		$num = ((int) ($row['cnt'] ?? 0)) + 1;
		return $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
	}
}
