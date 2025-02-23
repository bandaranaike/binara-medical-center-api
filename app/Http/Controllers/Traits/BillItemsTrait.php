<?php

namespace App\Http\Controllers\Traits;

use App\Models\BillItem;
use App\Models\Service;

trait BillItemsTrait
{
    private function insertBillItems($serviceId, $billAmount, $systemAmount, $billId): void
    {
        $data = [['bill_id' => $billId, 'service_id' => $serviceId, 'bill_amount' => $billAmount, 'system_amount' => $systemAmount]];
        BillItem::insert($data);
    }

    private function createMedicineBillItemIfNotExists($billId): bool
    {

        $medicineServiceId = Service::where('key', Service::MEDICINE_KEY)->first()->id;
        $billItem = BillItem::where('bill_id', $billId)->where('service_id', $medicineServiceId)->first();
        if (!$billItem) {
            BillItem::create(['bill_id' => $billId, 'service_id' => $medicineServiceId]);
            return true;
        }
        return false;
    }
}
