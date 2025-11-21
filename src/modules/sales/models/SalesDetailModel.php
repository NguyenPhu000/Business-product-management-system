<?php
namespace Modules\Sales\Models;

use Core\BaseModel;

class SalesDetailModel extends BaseModel
{
    protected string $table = 'sales_details';
    protected string $primaryKey = 'id';

    public function createDetail(array $data): int
    {
        return $this->create($data);
    }
}

