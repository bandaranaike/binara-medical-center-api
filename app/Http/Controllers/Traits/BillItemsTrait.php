<?php

namespace App\Http\Controllers\Traits;

use App\Models\BillItem;

trait BillItemsTrait
{
    private function insertBillItems($serviceId, $billAmount, $systemAmount, $billId): void
    {
        $data = [['bill_id' => $billId, 'service_id' => $serviceId, 'bill_amount' => $billAmount, 'system_amount' => $systemAmount]];
        BillItem::insert($data);
    }
}
