<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillReceptionResourceCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'bill_amount' => $this->bill_amount,
            'system_amount' => $this->system_amount,
            'queue_number' => $this->dailyPatientQueue?->queue_number,
            'patient_name' => $this->patient?->name,
            'doctor_name' => $this->doctor?->name,
            'queue_date' => $this->created_at->format('d M - h:i a'),
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type,
            'appointment_type' => $this->appointment_type,
        ];
    }
}
