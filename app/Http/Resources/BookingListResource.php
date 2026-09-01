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
            'uuid' => $this->uuid,
            'queue_number' => $this->dailyPatientQueue?->queue_number ?? null,
            'patient_name' => $this->patient->name ?? null,
            'doctor_name' => $this->doctor->name ?? null,
            'queue_date' => $this->dailyPatientQueue?->queue_date ?? null,
            'referred_amount' => $this->referred_amount === null ? null : (float) $this->referred_amount,
            'total_amount' => round((float) ($this->referred_amount ?? 0) + (float) ($this->system_amount ?? 0), 2),
            'system_amount' => $this->system_amount === null ? null : (float) $this->system_amount,
            'appointment_type' => $this->appointment_type,
            'payment_type' => $this->payment_type,
            'payment_status' => $this->payment_status,
        ];
    }
}
