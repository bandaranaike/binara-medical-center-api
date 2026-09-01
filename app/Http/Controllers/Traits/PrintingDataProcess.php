<?php

namespace App\Http\Controllers\Traits;

use App\Enums\ServiceKey;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Service;

trait PrintingDataProcess
{
    use SystemPriceCalculator;

    /**
     * @param  $billAmount
     * @return array
     *
     * If seperated fields required, need to add two different records in the bill
     */
    public function preparePrintData($service, $referredAmount, int $systemAmount = 0): array
    {
        $printingData = [];

        if ($service) {
            if ($service->separate_items) {
                $printingData[] = ['name' => $service->name.' '.Bill::FEE_ORIGINAL, 'price' => number_format($referredAmount, 2)];
                $printingData[] = ['name' => $service->name.' '.Bill::FEE_INSTITUTION, 'price' => number_format($systemAmount, 2)];
            } else {
                $printingData[] = ['name' => $service->name.' '.Bill::FEE_ORIGINAL, 'price' => number_format($referredAmount + $systemAmount, 2)];
            }
        }

        return $printingData;
    }

    public function getBillItemsFroPrint($billId, $excludeDentalRegFee): array
    {
        $excludedServiceId = $excludeDentalRegFee ? Service::where('key', ServiceKey::DENTAL_REGISTRATION->value)->value('id') : null;

        $billItems = BillItem::where('bill_id', $billId)
            ->where('service_id', '!=', $excludedServiceId)
            ->with('service:id,name,separate_items')
            ->get(['referred_amount', 'system_amount', 'service_id']);

        $total = 0;
        $systemTotal = 0;
        $items = [];

        foreach ($billItems as $item) {
            $total += $item->referred_amount;
            $systemTotal += $item->system_amount;

            $items = array_merge($items, $this->preparePrintData($item->service, $item->referred_amount, $item->system_amount));
        }

        return [
            'items' => $items,
            'total' => $total,
            'system_total' => $systemTotal,
        ];
    }
}
