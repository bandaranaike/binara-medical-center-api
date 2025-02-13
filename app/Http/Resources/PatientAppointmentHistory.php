<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientAppointmentHistory extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "appointmentType" => $this->appointment_type,
            "doctorName" => $this->doctor?->name ?? 'No doctor',
            "date" => $this->date,
            "paymentStatus" => $this->payment_tatus,
            "status" => $this->status,
        ];
    }
}
