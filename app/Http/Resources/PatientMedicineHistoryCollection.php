<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;

class PatientMedicineHistoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return Collection
     */
    public function toArray(Request $request): Collection
    {
        return $this->collection->groupBy('bill_id')->map(function ($items, $billId) {
            return [
                'billId' => $billId,
                'date' => $items->first()->bill->created_at->format('Y-m-d'),
                'medicines' => $items->map(function ($item) {
                    return [
                        'name' => $item->medicine->name,
                        'dosage' => $item->dosage,
                        'type' => $item->type,
                        'duration' => $item->duration,
                    ];
                })->toArray(),
            ];
        })->values();
    }
}
