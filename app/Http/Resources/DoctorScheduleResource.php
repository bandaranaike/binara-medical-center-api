<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorScheduleResource extends JsonResource
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
            'doctor_id' => $this->doctor_id,
            'weekday' => $this->weekday,
            'time' => $this->time,
            'recurring' => $this->recurring,
            'status' => $this->status,
            'seats' => $this->seats,
        ];
    }
}
