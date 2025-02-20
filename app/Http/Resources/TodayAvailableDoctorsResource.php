<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TodayAvailableDoctorsResource extends JsonResource
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
            'doctor' => $this->doctor->name,
            'doctor_id' => $this->doctor->id,
            'doctor_type' => $this->doctor->doctor_type,
            'specialty' => $this->doctor->specialty?->name ?? $this->doctor->doctor_type,
            'time' => $this->time,
            'seats' => $this->seats,
            'available_seats' => $this->available_seats,
        ];
    }
}
