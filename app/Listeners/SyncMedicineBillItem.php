<?php

namespace App\Listeners;

use App\Enums\ServiceKey;
use App\Events\PatientMedicineListUpdated;
use App\Models\BillItem;
use App\Models\Sale;
use App\Models\Service;

class SyncMedicineBillItem
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(PatientMedicineListUpdated $event): void
    {
        $billId = $event->billId;
        $total = $event->totalAmount;

        $medicineServiceId = Service::where("key", ServiceKey::MEDICINE->value)->value("id");

        // Check if a bill item for medicines already exists
        $existing = BillItem::where('bill_id', $billId)
            ->where('service_id', $medicineServiceId)
            ->first();

        if ($existing) {
            $existingTotal = Sale::where('bill_id', $billId)->sum('total_price');
            $existing->update(['system_amount' => $existingTotal]);
        } else {
            BillItem::create([
                'bill_id' => $billId,
                'service_id' => $medicineServiceId,
                'bill_amount' => 0,
                'system_amount' => $total,
            ]);
        }
    }
}
