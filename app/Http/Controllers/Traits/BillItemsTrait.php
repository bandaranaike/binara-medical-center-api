<?php

namespace App\Http\Controllers\Traits;

use App\Enums\ServiceKey;
use App\Models\BillItem;
use App\Models\Service;

trait BillItemsTrait
{
    private function insertBillItems($serviceId, $referredAmount, $systemAmount, $billId): void
    {
        $data = [
            [
                'bill_id' => $billId,
                'service_id' => $serviceId,
                'referred_amount' => $referredAmount,
                'system_amount' => $systemAmount,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        BillItem::insert($data);
    }

    private function createMedicineBillItemIfNotExists($billId): bool|array
    {

        $medicineServiceId = Service::where('key', ServiceKey::MEDICINE->value)->first()->id;
        $billItem = BillItem::where('bill_id', $billId)->where('service_id', $medicineServiceId)->first();
        if (! $billItem) {
            $billItem = BillItem::create(['bill_id' => $billId, 'service_id' => $medicineServiceId])->load('service:id,name');

            return [
                'id' => $billItem->id,
                'service_id' => $billItem->service_id,
                'system_amount' => $billItem->system_amount,
                'referred_amount' => $billItem->referred_amount,
                'bill_id' => $billItem->bill_id,
                'service' => ['id' => $billItem->service->id, 'name' => $billItem->service->name, 'price' => '0'],
            ];
        }

        return false;
    }
}
