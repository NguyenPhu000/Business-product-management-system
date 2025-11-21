<?php
// PurchaseDetailModel.php - Chi tiết sản phẩm trong đơn mua
namespace Modules\Purchase\Models;

use Core\BaseModel;

class PurchaseDetailModel extends BaseModel
{
	protected string $table = 'purchase_details';
	protected string $primaryKey = 'id';

	/**
	 * Tạo detail record
	 * @param array $data
	 * @return int
	 */
	public function createDetail(array $data): int
	{
		return $this->create($data);
	}
}
