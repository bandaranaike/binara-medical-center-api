<?php

namespace App\Listeners;

use App\Events\RemovedDrugFromBill;
use App\Http\Controllers\Traits\StockTrait;

class DeductDrugStockForBill
{
    use StockTrait;

    /**
     * Handle the event.
     */
    public function handle(RemovedDrugFromBill $event): void
    {
        $this->removeSaleItem($event->stockId);
    }
}
