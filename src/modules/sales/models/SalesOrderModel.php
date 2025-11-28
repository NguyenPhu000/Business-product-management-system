<?php
namespace Modules\Sales\Models;

use Core\BaseModel;

class SalesOrderModel extends BaseModel
{
    protected string $table = 'sales_orders';
    protected string $primaryKey = 'id';

    public function createOrder(array $data): int
    {
        return $this->create($data);
    }

    public function generateOrderNumber(): string
    {
        $prefix = 'SO' . date('Ymd');
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE DATE(created_at) = ?";
        $row = $this->queryOne($sql, [date('Y-m-d')]);
        $num = ((int) ($row['cnt'] ?? 0)) + 1;
        return $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
    }
}

