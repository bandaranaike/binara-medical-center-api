<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'system_amount' => $this->system_amount,
            'bill_amount' => $this->bill_amount,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bill_items' => $this->billItems->map(function($billItem) {
                return [
                    'id' => $billItem->id,
                    'service_id' => $billItem->service_id,
                    'system_amount' => $billItem->system_amount,
                    'bill_amount' => $billItem->bill_amount,
                    'created_at' => $billItem->created_at,
                    'updated_at' => $billItem->updated_at,
                ];
            })
        ];
    }
}
