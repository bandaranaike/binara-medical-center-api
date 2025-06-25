<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $note
 * @property mixed $created_at
 */
class PatientsHistoryResource extends JsonResource
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
            'note' => $this->note,
            'date' => $this->created_at->format('Y-m-d'),
            'doctor' => ["name" => $this->doctor->name, "id" => $this->doctor->id]
        ];
    }
}
