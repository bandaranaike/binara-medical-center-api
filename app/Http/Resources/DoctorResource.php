<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
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
            'name' => $this->name,
            'hospital' => $this->hospital?->name,
            'hospital_id' => $this->hospital_id,
            'specialty' => $this->specialty?->name,
            'specialty_id' => $this->specialty_id,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'type' => $this->doctor_type,
            'user' => $this->user?->name,
            'user_id' => $this->user_id,
        ];
    }
}
