<?php

namespace App\Http\Controllers\Traits;

use App\Models\Bill;
use App\Models\BillItem;

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
    public function preparePrintData($service, $billAmount, string $systemAmount = "0"): array
    {
        $printingData = [];

        if ($service) {
            $printingData[] = ['name' => $service->name . ' ' . Bill::FEE_ORIGINAL, 'price' => $billAmount];
            if ($service->separate_items) {
                $systemAmount = $systemAmount == "0" ? $this->calculateSystemPrice($service, $billAmount, $systemAmount) : $systemAmount;
                $printingData[] = ['name' => $service->name . ' ' . Bill::FEE_INSTITUTION, 'price' => $systemAmount];
            }
        }
        return $printingData;
    }

    public function getBillItemsFroPrint($billId)
    {
        $billItems = BillItem::where('bill_id', $billId)->select(['bill_amount', 'system_amount', 'service_id'])->with('service')->get();

        return $billItems->flatMap(function ($item) {
            return $this->preparePrintData($item->service, $item->bill_amount, $item->system_amount);
        })->toArray();
    }
}
