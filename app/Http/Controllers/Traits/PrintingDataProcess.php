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
     * @param $service
     * @param $billAmount
     * @param int $systemAmount
     * @return array
     *
     * If seperated fields required, need to add two different records in the bill
     */
    public function preparePrintData($service, $billAmount, int $systemAmount = 0): array
    {
        $printingData = [];

        if ($service) {
            if ($service->separate_items) {
                $printingData[] = ['name' => $service->name . ' ' . Bill::FEE_ORIGINAL, 'price' => number_format($billAmount, 2)];
                $printingData[] = ['name' => $service->name . ' ' . Bill::FEE_INSTITUTION, 'price' => number_format($systemAmount, 2)];
            } else {
                $printingData[] = ['name' => $service->name . ' ' . Bill::FEE_ORIGINAL, 'price' => number_format($billAmount + $systemAmount, 2)];
            }
        }
        return $printingData;
    }

    public function getBillItemsFroPrint($billId): array
    {
        $excludedServiceId = Service::where('key', ServiceKey::DENTAL_REGISTRATION->value)->value('id');

        $billItems = BillItem::where('bill_id', $billId)
            ->where('service_id', '!=', $excludedServiceId)
            ->with('service:id,name')
            ->get(['bill_amount', 'system_amount', 'service_id']);

        $total = 0;
        $systemTotal = 0;
        $items = [];

        foreach ($billItems as $item) {
            $total += $item->bill_amount;
            $systemTotal += $item->system_amount;

            $items = array_merge($items, $this->preparePrintData($item->service, $item->bill_amount, $item->system_amount));
        }

        return [
            'items' => $items,
            'total' => $total,
            'system_total' => $systemTotal
        ];
    }


}
