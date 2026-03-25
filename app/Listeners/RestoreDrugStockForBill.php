<?php

namespace App\Listeners;

use App\Events\AddedDrugForBill;
use App\Http\Controllers\Traits\StockTrait;

class RestoreDrugStockForBill
{
    use StockTrait;

    /**
     * Handle the event.
     */
    public function handle(AddedDrugForBill $event): void
    {
        $this->addSaleItem($event->brandId, $event->quantity, $event->billId);
    }
}
