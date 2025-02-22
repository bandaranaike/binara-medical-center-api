<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingListResource extends JsonResource
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
            'queue_number' => $this->dailyPatientQueue?->queue_number ?? null,
            'patient_name' => $this->patient->name ?? null,
            'doctor_name' => $this->doctor->name ?? null,
            'queue_date' => $this->dailyPatientQueue?->queue_date ?? null,
            'bill_amount' => $this->bill_amount ?? null,
            'system_amount' => $this->system_amount ?? null,
            'appointment_type' => $this->appointment_type,
            'payment_type' => $this->payment_type,
            'payment_status' => $this->payment_status,
        ];
    }
}
