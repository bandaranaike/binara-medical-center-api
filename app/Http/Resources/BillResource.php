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
            'uuid' => $this->uuid,
            'system_amount' => (float) $this->system_amount,
            'referred_amount' => (float) $this->referred_amount,
            'total_amount' => round((float) $this->referred_amount + (float) $this->system_amount, 2),
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bill_items' => $this->billItems->map(function ($billItem) {
                return [
                    'id' => $billItem->id,
                    'service_id' => $billItem->service_id,
                    'system_amount' => (float) $billItem->system_amount,
                    'referred_amount' => (float) $billItem->referred_amount,
                    'total_amount' => round((float) $billItem->referred_amount + (float) $billItem->system_amount, 2),
                    'created_at' => $billItem->created_at,
                    'updated_at' => $billItem->updated_at,
                ];
            }),
        ];
    }
}
