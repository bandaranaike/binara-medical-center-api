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

    private function createMedicineBillItemIfNotExists($billId): bool|array
    {

        $medicineServiceId = Service::where('key', Service::MEDICINE_KEY)->first()->id;
        $billItem = BillItem::where('bill_id', $billId)->where('service_id', $medicineServiceId)->first();
        if (!$billItem) {
            $billItem = BillItem::create(['bill_id' => $billId, 'service_id' => $medicineServiceId])->load('service:id,name');
            return [
                'id' => $billItem->id,
                'service_id' => $billItem->service_id,
                'bill_amount' => $billItem->bill_amount,
                'system_amount' => $billItem->system_amount,
                'bill_id' => $billItem->bill_id,
                'service' => ["id" => $billItem->service->id, "name" => $billItem->service->name, "price" => "0"]
            ];
        }
        return false;
    }
}
