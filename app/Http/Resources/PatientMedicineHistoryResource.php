<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientMedicineHistoryResource extends JsonResource
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
            "medicine_id" => $this->medicine_id,
            "medication_frequency_id" => $this->medication_frequency_id,
            "duration" => $this->duration,
        ];
    }
}
