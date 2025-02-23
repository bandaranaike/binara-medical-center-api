<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorAvailabilityResource extends JsonResource
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
            "doctor_id" => $this->doctor_id,
            "date" => $this->date,
            "time" => $this->time,
            "seats" => $this->seats,
            "available_seats" => $this->available_seats,
            "status" => $this->status,
            "doctor" => $this->doctor?->name,
        ];
    }
}
